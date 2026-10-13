<?php

declare(strict_types=1);

use App\Exceptions\DomainExceptionHandler;
use Illuminate\Http\Request;
use Support\Bases\Exceptions\DomainException;
use Support\Bases\Exceptions\NotFoundException;

it('returns null when request is not json type', function (): void {
    $e = new class extends NotFoundException {};

    $handler = app(DomainExceptionHandler::class)($e, Request::create('/_anything'));

    expect($handler)->toBeNull();
});

it('returns null when exception is not extending the domain exception', function (): void {
    $e = new class extends DomainException {};

    $handler = app(DomainExceptionHandler::class)($e, Request::create('api/v1/_anything'));

    expect($handler)->toBeNull();
});
