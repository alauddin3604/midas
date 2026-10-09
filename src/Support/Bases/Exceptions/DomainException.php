<?php

declare(strict_types=1);

namespace Support\Bases\Exceptions;

use Exception;
use Throwable;

abstract class DomainException extends Exception
{
    final protected function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
