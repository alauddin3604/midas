<?php

declare(strict_types=1);

use App\Exceptions\Domain\Contracts\DomainExceptionsMapper;
use App\Exceptions\Domain\DomainExceptionsRegistry;
use App\Exceptions\DomainExceptionHandler;
use Illuminate\Http\Request;

it('returns null when request is not json type', function (): void {
    $e = new class extends DomainException {};
    $registry = new DomainExceptionsRegistry([
        $e::class => 400,
    ]);

    $handler = (new DomainExceptionHandler($registry))($e, Request::create('/_anything'));

    expect($handler)->toBeNull();
});

it('returns null when the exception is not mapped', function (): void {
    $handler = new DomainExceptionHandler(new DomainExceptionsRegistry([]));

    $request = Request::create('/api/v1/anything', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);

    expect($handler(new DomainException('Unmapped.'), $request))->toBeNull();
});

it('returns the mapped response for a mapped exception', function (): void {
    $mapper = new class implements DomainExceptionsMapper
    {
        public function map(): array
        {
            return [DomainException::class => 409];
        }
    };

    $handler = new DomainExceptionHandler(new DomainExceptionsRegistry([$mapper]));

    $request = Request::create('/api/v1/anything', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);

    $response = $handler(new DomainException('Conflict.'), $request);

    expect($response->getStatusCode())->toBe(409)
        ->and($response->getData(true)['message'])->toBe('Conflict.');
});
