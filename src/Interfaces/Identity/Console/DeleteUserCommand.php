<?php

declare(strict_types=1);

namespace Interfaces\Identity\Console;

use Domain\Identity\Actions\DeleteUserFromEmailAction;
use Domain\Identity\Exceptions\UserNotFound;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('identity:delete-user {email}')]
#[Description('Delete a user from an email')]
final class DeleteUserCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DeleteUserFromEmailAction $action): int
    {
        $email = $this->argument('email');

        if (! is_string($email)) {
            $this->error('Invalid email. Please enter a valid email address.');

            return self::FAILURE;
        }

        $validator = Validator::make([
            'email' => $email,
        ], [
            'email' => ['email'],
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email. Please enter a valid email address.');

            return self::FAILURE;
        }

        try {
            $action->execute($email);

            $this->info("User with email [{$email}] is successfully deleted.");

            return self::SUCCESS;
        } catch (UserNotFound $e) {
            $this->error($e->getMessage().' Please check the email.');

            return self::FAILURE;
        }
    }
}
