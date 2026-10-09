<?php

declare(strict_types=1);

namespace App\Identity\Http\Dashboard\Livewire;

use Domain\Identity\Actions\RegisterUserAction;
use Domain\Identity\Enums\Permission;
use Domain\Identity\Enums\Role;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Payloads\RegisterUserPayload;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::dashboard'), Title('New user')]
final class CreateUserPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'member';

    public function render(): View
    {
        return view('pages::users.create');
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    public function save(RegisterUserAction $action): void
    {
        $this->authorize(Permission::ManageUsers);
        $this->validate();

        try {
            $action->execute(new RegisterUserPayload($this->name, $this->email, $this->password, Role::from($this->role)));
        } catch (EmailAlreadyRegistered $exception) {
            $this->addError('email', $exception->getMessage());

            return;
        }

        session()->flash('status', 'User created.');

        $this->redirectRoute('users.index', navigate: true);
    }
}
