<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Dashboard\Livewire;

use Domain\Identity\Actions\SendPasswordResetLinkAction;
use Domain\Identity\Payloads\SendPasswordResetLinkPayload;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class ForgotPasswordPage extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    public bool $linkSent = false;

    public function render(): View
    {
        return view('pages::auth.forgot-password');
    }

    public function sendLink(SendPasswordResetLinkAction $action): void
    {
        $this->validate();

        $action->execute(new SendPasswordResetLinkPayload($this->email));

        $this->linkSent = true;
    }
}
