<?php

use App\Prism\Http\Dashboard\Livewire\DashboardPage;
use Domain\Identity\Models\User;
use Livewire\Livewire;

it('shows the dashboard to signed in users', function () {
    Livewire::actingAs(User::factory()->create(['name' => 'Jane Doe']))
        ->test(DashboardPage::class)
        ->assertSee('Welcome back, Jane Doe');
});
