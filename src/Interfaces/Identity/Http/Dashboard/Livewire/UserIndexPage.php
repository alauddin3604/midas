<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Dashboard\Livewire;

use Closure;
use Domain\Identity\Actions\DeleteUserAction;
use Domain\Identity\Enums\Permission;
use Domain\Identity\Exceptions\CannotModifyOwnAccount;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts::dashboard'), Title('Users')]
final class UserIndexPage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function render(): View
    {
        return view('pages::users.index');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()->search($this->search)->orderBy('name')->paginate(15);
    }

    public function delete(DeleteUserAction $action, User $user): void
    {
        $this->authorize(Permission::ManageUsers);

        $action->execute(Auth::user(), $user);

        session()->flash('status', 'User deleted.');
    }

    public function exception(Throwable $e, Closure $stopPropagation): void
    {
        $message = match (true) {
            $e instanceof ModelNotFoundException => 'User not found or probably deleted.',
            $e instanceof CannotModifyOwnAccount => $e->getMessage(),
            default => null,
        };

        if ($message === null) {
            return;
        }

        session()->flash('error', $message);
        $stopPropagation();
    }
}
