<?php

declare(strict_types=1);

namespace App\Livewire\Identity;

use Domain\Identity\Actions\AuthenticateUserAction;
use Domain\Identity\Exceptions\InvalidCredentials;
use Domain\Identity\Payloads\AuthenticateUserPayload;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class LoginPage extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function render(): View
    {
        return view('pages::auth.login');
    }

    public function login(AuthenticateUserAction $action): void
    {
        $this->validate();

        $throttleKey = Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Please try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                ]),
            ]);
        }

        try {
            $user = $action->execute(new AuthenticateUserPayload($this->email, $this->password));
        } catch (InvalidCredentials $exception) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages(['email' => $exception->getMessage()]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('web')->loginUsingId($user->id, $this->remember);
        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }
}
