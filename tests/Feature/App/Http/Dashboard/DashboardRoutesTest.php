<?php

declare(strict_types=1);

use App\Livewire\DashboardPage;
use Database\Factories\UserFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects to login if guest', function (): void {
    get(route('dashboard'))->assertRedirectToRoute('login');
});

it('renders the dashboard page', function (): void {
    actingAs(UserFactory::new()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeLivewire(DashboardPage::class);
});

it('redirects to dashboard from home', function (): void {
    get(route('home'))->assertRedirectToRoute('dashboard');
});
