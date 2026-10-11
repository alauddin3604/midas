<?php

declare(strict_types=1);

use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Exceptions\InvalidCredentials;
use Illuminate\Http\Request;
use Support\Contracts\MapsDomainExceptions;
use Support\Exceptions\DomainExceptionRenderer;
use Symfony\Component\HttpFoundation\Response;

/**
 * @param  array<class-string<Throwable>, int>  $statusCodes
 */
function exceptionMap(array $statusCodes): MapsDomainExceptions
{
    return new readonly class($statusCodes) implements MapsDomainExceptions
    {
        /**
         * @param  array<class-string<Throwable>, int>  $statusCodes
         */
        public function __construct(private array $statusCodes) {}

        public function statusCodes(): array
        {
            return $this->statusCodes;
        }
    };
}

function jsonRequest(): Request
{
    return Request::create('/', server: ['HTTP_ACCEPT' => 'application/json']);
}

it('renders a mapped domain exception as JSON with its status and message', function (): void {
    $renderer = new DomainExceptionRenderer([
        exceptionMap([EmailAlreadyRegistered::class => Response::HTTP_CONFLICT]),
    ]);

    $response = $renderer->render(EmailAlreadyRegistered::withEmail('jane@example.com'), jsonRequest());

    expect($response->getStatusCode())->toBe(Response::HTTP_CONFLICT)
        ->and($response->getData(true))->toBe([
            'message' => 'A user with email [jane@example.com] is already registered.',
            'errors' => [],
        ]);
});

it('finds the status in a later map when earlier maps do not list the exception', function (): void {
    $renderer = new DomainExceptionRenderer([
        exceptionMap([EmailAlreadyRegistered::class => Response::HTTP_CONFLICT]),
        exceptionMap([InvalidCredentials::class => Response::HTTP_UNAUTHORIZED]),
    ]);

    $response = $renderer->render(InvalidCredentials::provided(), jsonRequest());

    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
});

it('does not render a domain exception that no map lists', function (): void {
    $renderer = new DomainExceptionRenderer([
        exceptionMap([EmailAlreadyRegistered::class => Response::HTTP_CONFLICT]),
    ]);

    expect($renderer->render(InvalidCredentials::provided(), jsonRequest()))->toBeNull();
});

it('does not render a domain exception when the request does not expect JSON', function (): void {
    $renderer = new DomainExceptionRenderer([
        exceptionMap([EmailAlreadyRegistered::class => Response::HTTP_CONFLICT]),
    ]);

    expect($renderer->render(EmailAlreadyRegistered::withEmail('jane@example.com'), Request::create('/')))->toBeNull();
});

it('does not render an exception that is not a domain exception', function (): void {
    $renderer = new DomainExceptionRenderer([
        exceptionMap([RuntimeException::class => Response::HTTP_CONFLICT]),
    ]);

    expect($renderer->render(new RuntimeException('Boom'), jsonRequest()))->toBeNull();
});
