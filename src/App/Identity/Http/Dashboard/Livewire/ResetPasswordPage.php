<?php

declare(strict_types=1);

namespace App\Identity\Http\Dashboard\Livewire;

use Domain\Identity\Actions\ResetPasswordAction;
use Domain\Identity\Exceptions\InvalidPasswordResetToken;
use Domain\Identity\Payloads\ResetPasswordPayload;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ResetPasswordPage extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email')->toString();
    }

    public function render(): View
    {
        return view('pages::auth.reset-password');
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function resetPassword(ResetPasswordAction $action): void
    {
        $this->validate();

        try {
            $action->execute(new ResetPasswordPayload($this->email, $this->token, $this->password));
        } catch (InvalidPasswordResetToken $exception) {
            $this->addError('email', $exception->getMessage());

            return;
        }

        session()->flash('status', 'Your password has been reset. You can now sign in.');

        $this->redirectRoute('login', navigate: true);
    }
}
