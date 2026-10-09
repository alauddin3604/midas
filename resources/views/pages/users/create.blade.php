<div class="max-w-xl space-y-6">
    <div>
        <h1 class="uk-h3">New user</h1>
        <p class="text-muted-foreground">Create an account and choose what it can do.</p>
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
            <label class="uk-form-label @error('password') text-destructive @enderror" for="password">Password</label>
            <input class="uk-input @error('password') uk-form-destructive @enderror" id="password" type="password" autocomplete="new-password" wire:model="password" />
            @error('password')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label" for="password_confirmation">Confirm password</label>
            <input class="uk-input" id="password_confirmation" type="password" autocomplete="new-password" wire:model="password_confirmation" />
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
            <button class="uk-btn uk-btn-primary" type="submit" wire:loading.attr="disabled">Create user</button>
            <a class="uk-btn uk-btn-default" href="{{ route('users.index') }}" wire:navigate>Cancel</a>
        </div>
    </form>
</div>
