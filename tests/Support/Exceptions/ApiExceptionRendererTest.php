<?php

declare(strict_types=1);

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Support\Exceptions\ApiExceptionRenderer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

function apiRequest(): Request
{
    return Request::create('/api/v1/anything');
}

it('renders only the first message of each failing field as a 422', function (): void {
    $exception = ValidationException::withMessages([
        'email' => ['The email must be valid.', 'The email is too long.'],
        'items.0.name' => ['The name is required.'],
    ]);

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
    $exception = ValidationException::withMessages(['email' => ['Invalid.']]);

    expect((new ApiExceptionRenderer)->render($exception, Request::create('/dashboard')))->toBeNull();
});

it('does not render exceptions it does not know', function (): void {
    expect((new ApiExceptionRenderer)->render(new RuntimeException('Boom'), apiRequest()))->toBeNull();
});
