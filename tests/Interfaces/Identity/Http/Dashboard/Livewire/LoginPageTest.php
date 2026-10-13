<?php

use Domain\Identity\Models\User;
use Interfaces\Identity\Http\Dashboard\Livewire\LoginPage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

it('renders the login page', function () {
    get('/login')
        ->assertOk()
        ->assertSeeText('Sign in to your account');
});

it('redirects authenticated users away from the login page', function () {
    actingAs(User::factory()->create())
        ->get('/login')
        ->assertRedirect();
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(LoginPage::class)
        ->set('email', 'jane@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('dashboard');

    assertAuthenticatedAs($user);
});

it('shows an error for wrong credentials', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(LoginPage::class)
        ->set('email', 'jane@example.com')
        ->set('password', 'wrong')
        ->call('login')
        ->assertHasErrors(['email']);

    assertGuest();
});

it('validates required fields', function () {
    Livewire::test(LoginPage::class)
        ->call('login')
        ->assertHasErrors(['email' => 'required', 'password' => 'required']);
});

it('locks out after repeated failed attempts', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $component = Livewire::test(LoginPage::class)
        ->set('email', 'jane@example.com')
        ->set('password', 'wrong');

    foreach (range(1, 5) as $ignored) {
        $component->call('login');
    }

    $component->set('password', 'password')
        ->call('login')
        ->assertHasErrors(['email']);

    assertGuest();
});
