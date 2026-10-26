<?php

use Domain\Identity\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use App\Livewire\Identity\ForgotPasswordPage;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders the forgot password page', function () {
    get('/forgot-password')
        ->assertOk()
        ->assertSeeLivewire(ForgotPasswordPage::class);
});

it('sends a reset link to a registered email', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(ForgotPasswordPage::class)
        ->set('email', 'jane@example.com')
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('linkSent', true);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('gives the same response for an unknown email without sending anything', function () {
    Notification::fake();

    Livewire::test(ForgotPasswordPage::class)
        ->set('email', 'nobody@example.com')
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('linkSent', true);

    Notification::assertNothingSent();
});

it('validates the email', function () {
    Livewire::test(ForgotPasswordPage::class)
        ->set('email', 'not-an-email')
        ->call('sendLink')
        ->assertHasErrors(['email' => 'email']);
});
