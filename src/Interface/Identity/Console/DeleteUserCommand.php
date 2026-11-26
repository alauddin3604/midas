<?php

declare(strict_types=1);

namespace Interface\Identity\Console;

use Application\Identity\Actions\PerformUserDeletionAction;
use Domain\Identity\Queries\FindUserByEmailQuery;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Factory;

use function is_string;

#[Signature('identity:delete-user {email}')]
#[Description('Delete a user from an email')]
final class DeleteUserCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        Factory $validator,
        FindUserByEmailQuery $query,
        PerformUserDeletionAction $action,
    ): int {
        $email = $this->argument('email');

        if (! is_string($email) || $validator->make(
            ['email' => $email],
            ['email' => ['required', 'email']],
        )->fails()) {
            $this->error('Invalid email. Please enter a valid email address.');

            return self::FAILURE;
        }

        try {
            $user = $query->fetch($email);
        } catch (ModelNotFoundException) {
            $this->error("User with email [{$email}] does not exist. Please check the email.");

            return self::FAILURE;
        }

        $action->execute($user);

        $this->info("User with email [{$email}] is successfully deleted.");

        return self::SUCCESS;
    }
}
