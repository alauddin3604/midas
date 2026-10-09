<div class="max-w-xl space-y-6">
    <div>
        <h1 class="uk-h3">Edit user</h1>
        <p class="text-muted-foreground">Update the account details and role.</p>
    </div>

    <form class="space-y-6" wire:submit="save">
        <div class="grid gap-y-1">
            <label class="uk-form-label @error('name') text-destructive @enderror" for="name">Name</label>
            <input class="uk-input @error('name') uk-form-destructive @enderror" id="name" type="text" wire:model="name" />
            @error('name')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label @error('email') text-destructive @enderror" for="email">Email</label>
            <input class="uk-input @error('email') uk-form-destructive @enderror" id="email" type="email" wire:model="email" />
            @error('email')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label @error('role') text-destructive @enderror" for="role">Role</label>
            <select class="uk-select @error('role') uk-form-destructive @enderror" id="role" wire:model="role">
                @foreach (\Domain\Identity\Enums\Role::cases() as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                @endforeach
            </select>
            @error('role')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="flex gap-2">
            <button class="uk-btn uk-btn-primary" type="submit" wire:loading.attr="disabled">Save changes</button>
            <a class="uk-btn uk-btn-default" href="{{ route('users.index') }}" wire:navigate>Cancel</a>
        </div>
    </form>
</div>
