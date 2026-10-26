<?php

declare(strict_types=1);

use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

function apiRequest(): Request
{
    return Request::create('/api/v1/anything');
}

it('renders only the first message of each failing field as a 422', function (): void {
    $translator = new Translator(new ArrayLoader(), 'en');
    $validator = new Validator($translator, [], []);

    $validator->errors()->add('email', 'The email must be valid.');
    $validator->errors()->add('email', 'The email is too long.');
    $validator->errors()->add('items.0.name', 'The name is required.');

    $exception = new ValidationException($validator);

    $response = (new ApiExceptionRenderer)->render($exception, apiRequest());

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getData(true))->toBe([
            'message' => 'Validation failed.',
            'errors' => [
                ['field' => 'email', 'error' => 'The email must be valid.'],
                ['field' => 'items.0.name', 'error' => 'The name is required.'],
            ],
        ]);
});

it('renders an authentication failure as a 401', function (): void {
    $response = (new ApiExceptionRenderer)->render(new AuthenticationException, apiRequest());

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))->toBe(['message' => 'Unauthenticated.', 'errors' => []]);
});

it('renders a missing resource as a 404 without leaking the original message', function (): void {
    $exception = new NotFoundHttpException('No query results for model [Domain\Identity\Models\User].');

    $response = (new ApiExceptionRenderer)->render($exception, apiRequest());

    expect($response->getStatusCode())->toBe(404)
        ->and($response->getData(true))->toBe(['message' => 'Not found.', 'errors' => []]);
});

it('keeps the headers of a throttled response', function (): void {
    $exception = new TooManyRequestsHttpException(30, 'Too many attempts.');

    $response = (new ApiExceptionRenderer)->render($exception, apiRequest());

    expect($response->getStatusCode())->toBe(429)
        ->and($response->headers->get('Retry-After'))->toBe('30');
});

it('does not render for a request that does not expect JSON', function (): void {
    $translator = new Translator(new ArrayLoader(), 'en');
    $validator = new Validator($translator, [], []);

    $validator->errors()->add('email', 'The email must be valid.');

    $exception = new ValidationException($validator);

    expect((new ApiExceptionRenderer)->render($exception, Request::create('/dashboard')))->toBeNull();
});

it('does not render exceptions it does not know', function (): void {
    expect((new ApiExceptionRenderer)->render(new RuntimeException('Boom'), apiRequest()))->toBeNull();
});

it('falls back to the status text when the HTTP exception has no message', function (int $status, string $expected): void {
    $request = Request::create('/api/v1/anything', 'GET', server: [
        'HTTP_ACCEPT' => 'application/json',
    ]);
    $response = (new ApiExceptionRenderer)->render(new HttpException($status), $request);

    expect($response)->not->toBeNull()
        ->and($response->getStatusCode())->toBe($status)
        ->and($response->getData(true)['message'])->toBe($expected);
})->with([
    'known status' => [403, 'Forbidden'],
    'another known status' => [429, 'Too Many Requests'],
    'unknown status' => [499, 'Error.'],
]);
