<?php

declare(strict_types=1);

namespace App\Prism\Http\Dashboard\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::dashboard'), Title('Dashboard')]
final class DashboardPage extends Component
{
    public function render(): View
    {
        return view('pages::dashboard');
    }
}
