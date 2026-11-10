<?php

declare(strict_types=1);

namespace App\Console\Commands\Identity;

use App\Actions\Identity\DeleteUserFromEmailAction;
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
     * Create a new console command instance.
     */
    public function __construct(private readonly Factory $factory)
    {
        parent::__construct();
    }

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

        $validator = $this->factory->make([
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
        } catch (ModelNotFoundException) {
            $this->error("User with email [{$email}] does not exist. Please check the email.");

            return self::FAILURE;
        }
    }
}
