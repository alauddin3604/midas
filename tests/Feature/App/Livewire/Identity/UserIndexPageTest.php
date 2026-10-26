<?php

use Domain\Identity\Models\User;
use App\Livewire\Identity\UserIndexPage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;

it('redirects guests to the login page', function () {
    get('/users')->assertRedirectToRoute('login');
});

it('forbids members from the users page', function () {
    actingAs(User::factory()->create())
        ->get('/users')
        ->assertForbidden();
});

it('lists users for admins', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    actingAs($admin)
        ->get('/users')
        ->assertOk()
        ->assertSeeLivewire(UserIndexPage::class)
        ->assertSee('Jane Doe')
        ->assertSee('jane@example.com');
});

it('filters users by name or email', function () {
    actingAs(User::factory()->admin()->create(['name' => 'Admin Person']));
    User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    User::factory()->create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);

    Livewire::test(UserIndexPage::class)
        ->set('search', 'jane')
        ->assertSee('Jane Doe')
        ->assertDontSee('Bob Smith');
});

it('deletes a user', function () {
    actingAs(User::factory()->admin()->create());
    $user = User::factory()->create();

    Livewire::test(UserIndexPage::class)
        ->call('delete', $user->id);

    assertDatabaseMissing('users', ['id' => $user->id]);
});

it('does not let an admin delete their own account', function () {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    Livewire::test(UserIndexPage::class)
        ->call('delete', $admin->id);

    assertDatabaseHas('users', ['id' => $admin->id]);
});

it('does not let a member delete users', function () {
    actingAs(User::factory()->create());
    $user = User::factory()->create();

    Livewire::test(UserIndexPage::class)
        ->call('delete', $user->id)
        ->assertForbidden();

    assertDatabaseHas('users', ['id' => $user->id]);
});
