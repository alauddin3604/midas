<?php

use App\Identity\Http\Dashboard\Livewire\CreateUserPage;
use Domain\Identity\Enums\Role;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;

it('forbids members', function () {
    actingAs(User::factory()->create())
        ->get('/users/create')
        ->assertForbidden();
});

it('renders for admins', function () {
    actingAs(User::factory()->admin()->create())
        ->get('/users/create')
        ->assertOk()
        ->assertSeeLivewire(CreateUserPage::class);
});

it('creates a user with the chosen role', function () {
    actingAs(User::factory()->admin()->create());

    Livewire::test(CreateUserPage::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'secret-password')
        ->set('password_confirmation', 'secret-password')
        ->set('role', 'admin')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('users.index');

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->role)->toBe(Role::Admin)
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('shows an error for a duplicate email', function () {
    actingAs(User::factory()->admin()->create());
    User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(CreateUserPage::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'secret-password')
        ->set('password_confirmation', 'secret-password')
        ->call('save')
        ->assertHasErrors(['email']);
});

it('validates the form', function () {
    actingAs(User::factory()->admin()->create());

    Livewire::test(CreateUserPage::class)
        ->set('role', 'superuser')
        ->call('save')
        ->assertHasErrors(['name' => 'required', 'email' => 'required', 'password' => 'required', 'role']);
});

it('does not let a member create users', function () {
    actingAs(User::factory()->create());

    Livewire::test(CreateUserPage::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'secret-password')
        ->set('password_confirmation', 'secret-password')
        ->call('save')
        ->assertForbidden();

    assertDatabaseMissing('users', ['email' => 'jane@example.com']);
});
