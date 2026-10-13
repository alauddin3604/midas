<?php

declare(strict_types=1);

namespace Support\Bases\Exceptions;

interface BusinessError {}

use RuntimeException;

abstract class DomainException extends RuntimeException implements BusinessError {}
