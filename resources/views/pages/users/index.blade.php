<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="uk-h3">Users</h1>
            <p class="text-muted-foreground">Manage who can access the dashboard.</p>
        </div>
        <a class="uk-btn uk-btn-primary" href="{{ route('users.create') }}" wire:navigate>New user</a>
    </div>

    @if (session('status'))
        <div class="uk-alert" data-uk-alert>
            <a href class="uk-alert-close" data-uk-close></a>
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="uk-alert uk-alert-destructive" data-uk-alert>
            {{ session('error') }}
        </div>
    @endif

    <input
        class="uk-input max-w-sm"
        type="search"
        placeholder="Search by name or email"
        wire:model.live.debounce.300ms="search"
    />

    <div class="overflow-x-auto">
        <table class="uk-table uk-table-divider uk-table-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="uk-label">{{ $user->role->label() }}</span></td>
                        <td class="text-right">
                            <a class="uk-btn uk-btn-default uk-btn-sm" href="{{ route('users.edit', $user->id) }}" wire:navigate>Edit</a>
                            @if ($user->id !== Auth::id())
                                <button
                                    class="uk-btn uk-btn-destructive uk-btn-sm"
                                    type="button"
                                    wire:click="delete('{{ $user->id }}')"
                                    wire:confirm="Delete {{ $user->name }}? This cannot be undone."
                                >
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted-foreground" colspan="4">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->users->links() }}
</div>
