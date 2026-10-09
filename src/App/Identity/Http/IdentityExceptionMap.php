<?php

declare(strict_types=1);

namespace App\Identity\Http;

use Domain\Identity\Exceptions\CannotModifyOwnAccount;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Exceptions\InvalidCredentials;
use Domain\Identity\Exceptions\InvalidPasswordResetToken;
use Override;
use Support\Contracts\MapsDomainExceptions;
use Symfony\Component\HttpFoundation\Response;

final class IdentityExceptionMap implements MapsDomainExceptions
{
    #[Override]
    public function statusCodes(): array
    {
        return [
            CannotModifyOwnAccount::class => Response::HTTP_FORBIDDEN,
            EmailAlreadyRegistered::class => Response::HTTP_CONFLICT,
            InvalidCredentials::class => Response::HTTP_UNAUTHORIZED,
            InvalidPasswordResetToken::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        ];
    }
}
