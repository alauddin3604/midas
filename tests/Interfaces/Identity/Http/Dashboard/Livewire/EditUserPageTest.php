<?php

use Domain\Identity\Enums\Role;
use Domain\Identity\Models\User;
use Interfaces\Identity\Http\Dashboard\Livewire\EditUserPage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('forbids members', function () {
    $user = User::factory()->create();

    actingAs($user)->get("/users/{$user->id}/edit")->assertForbidden();
});

it('returns 404 for an unknown or malformed user id', function () {
    actingAs(User::factory()->admin()->create());

    get('/users/00000000-0000-7000-8000-000000000000/edit')->assertNotFound();
    get('/users/not-a-uuid/edit')->assertNotFound();
});

it('loads the user into the form', function () {
    actingAs(User::factory()->admin()->create());
    $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    Livewire::test(EditUserPage::class, ['user' => $user])
        ->assertSet('name', 'Jane Doe')
        ->assertSet('email', 'jane@example.com')
        ->assertSet('role', 'member');
});

it('updates name, email and role', function () {
    actingAs(User::factory()->admin()->create());
    $user = User::factory()->create();

    Livewire::test(EditUserPage::class, ['user' => $user])
        ->set('name', 'New Name')
        ->set('email', 'new@example.com')
        ->set('role', 'admin')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('users.index');

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.com')
        ->and($user->role)->toBe(Role::Admin);
});

it('keeps the password when updating', function () {
    actingAs(User::factory()->admin()->create());
    $user = User::factory()->create();
    $originalHash = $user->password;

    Livewire::test(EditUserPage::class, ['user' => $user])
        ->set('name', 'New Name')
        ->call('save');

    expect($user->refresh()->password)->toBe($originalHash);
});

it('rejects an email that belongs to another user', function () {
    actingAs(User::factory()->admin()->create());
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    Livewire::test(EditUserPage::class, ['user' => $user])
        ->set('email', 'taken@example.com')
        ->call('save')
        ->assertHasErrors(['email']);
});

it('allows saving without changing the email', function () {
    actingAs(User::factory()->admin()->create());
    $user = User::factory()->create(['email' => 'same@example.com']);

    Livewire::test(EditUserPage::class, ['user' => $user])
        ->set('name', 'Renamed')
        ->call('save')
        ->assertHasNoErrors();
});

it('does not let an admin change their own role', function () {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    Livewire::test(EditUserPage::class, ['user' => $admin])
        ->set('role', 'member')
        ->call('save')
        ->assertHasErrors(['role']);

    expect($admin->refresh()->role)->toBe(Role::Admin);
});
