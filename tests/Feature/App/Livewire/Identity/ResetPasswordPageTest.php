<?php

use App\Livewire\Identity\ResetPasswordPage;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders the reset password page', function () {
    get('/reset-password/some-token')
        ->assertOk()
        ->assertSeeLivewire(ResetPasswordPage::class);
});

it('resets the password with a valid token', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    $token = Password::createToken($user);

    Livewire::test(ResetPasswordPage::class, ['token' => $token])
        ->set('email', 'jane@example.com')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('login');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('rejects an invalid token', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(ResetPasswordPage::class, ['token' => 'wrong-token'])
        ->set('email', 'jane@example.com')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertHasErrors(['email']);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

it('requires the password confirmation to match', function () {
    Livewire::test(ResetPasswordPage::class, ['token' => 'any'])
        ->set('email', 'jane@example.com')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'different')
        ->call('resetPassword')
        ->assertHasErrors(['password' => 'confirmed']);
});
