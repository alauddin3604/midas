<?php

declare(strict_types=1);

use Application\Identity\Actions\SendPasswordResetLinkAction;
use Application\Identity\Payloads\SendPasswordResetLinkPayload;
use Database\Factories\UserFactory;
use Domain\Identity\Events\PasswordResetLinkRequested;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('dispatches an event whether or not the email has an account', function (string $email): void {
    Notification::fake();
    Event::fake([PasswordResetLinkRequested::class]);
    UserFactory::new()->create(['email' => 'jane@example.com']);

    App::make(SendPasswordResetLinkAction::class)->execute(new SendPasswordResetLinkPayload($email));

    Event::assertDispatched(PasswordResetLinkRequested::class, fn (PasswordResetLinkRequested $event): bool => $event->email === $email);
})->with(['known email' => 'jane@example.com', 'unknown email' => 'nobody@example.com']);
