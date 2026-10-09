<x-auth.card title="Reset your password" description="Choose a new password for your account.">
    <form class="mt-6 space-y-6" wire:submit="resetPassword">
        <div class="grid gap-y-1">
            <label class="uk-form-label @error('email') text-destructive @enderror" for="email">Email</label>
            <input
                class="uk-input @error('email') uk-form-destructive @enderror"
                id="email"
                type="email"
                autocomplete="username"
                wire:model="email"
            />
            @error('email')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label @error('password') text-destructive @enderror" for="password">New password</label>
            <input
                class="uk-input @error('password') uk-form-destructive @enderror"
                id="password"
                type="password"
                autocomplete="new-password"
                wire:model="password"
            />
            @error('password')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label" for="password_confirmation">Confirm password</label>
            <input
                class="uk-input"
                id="password_confirmation"
                type="password"
                autocomplete="new-password"
                wire:model="password_confirmation"
            />
        </div>
        <button class="uk-btn uk-btn-primary block w-full" type="submit" wire:loading.attr="disabled">
            Reset password
        </button>
    </form>
</x-auth.card>
