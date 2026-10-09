<?php

declare(strict_types=1);

namespace App\Identity\Http\Dashboard\Livewire;

use Domain\Identity\Actions\UpdateUserAction;
use Domain\Identity\Enums\Permission;
use Domain\Identity\Enums\Role;
use Domain\Identity\Exceptions\CannotModifyOwnAccount;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\UpdateUserPayload;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::dashboard'), Title('Edit user')]
final class EditUserPage extends Component
{
    #[Locked]
    public string $userId = '';

    public string $name = '';

    public string $email = '';

    public string $role = 'member';

    public function mount(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
    }

    public function render(): View
    {
        return view('pages::users.edit');
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    public function save(UpdateUserAction $action): void
    {
        $this->authorize(Permission::ManageUsers);
        $this->validate();

        try {
            $action->execute(
                auth()->user(),
                User::query()->findOrFail($this->userId),
                new UpdateUserPayload($this->name, $this->email, Role::from($this->role)),
            );
        } catch (EmailAlreadyRegistered $exception) {
            $this->addError('email', $exception->getMessage());

            return;
        } catch (CannotModifyOwnAccount $exception) {
            $this->addError('role', $exception->getMessage());

            return;
        }

        session()->flash('status', 'User updated.');

        $this->redirectRoute('users.index', navigate: true);
    }
}
