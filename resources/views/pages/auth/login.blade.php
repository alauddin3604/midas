<x-auth.card
    title="Sign in to your account"
    description="Enter your credentials below to login to your account."
>
    <form class="mt-6 space-y-6" wire:submit="login">
        @if (session('status'))
            <div class="uk-alert">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="uk-alert uk-alert-destructive">
                There were errors in your submission.
            </div>
        @endif
        <div class="grid gap-y-1">
            <label class="uk-form-label @error('email') text-destructive @enderror" for="email">Email</label>
            <input
                class="uk-input @error('email') uk-form-destructive @enderror"
                id="email"
                type="email"
                autocomplete="username"
                placeholder="john@example.com"
                wire:model="email"
            />
            @error('email')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label @error('password') text-destructive @enderror" for="password">Password</label>
            <input
                class="uk-input @error('password') uk-form-destructive @enderror"
                id="password"
                type="password"
                autocomplete="current-password"
                placeholder="Password"
                wire:model="password"
            />
            @error('password')
                <div class="uk-anmt-shake uk-form-help text-destructive">{{ $message }}</div>
            @enderror
        </div>
        <div class="flex justify-between">
            <label for="remember">
                <input class="uk-checkbox mr-1" id="remember" type="checkbox" wire:model="remember" />
                Remember me
            </label>
            <a class="uk-link" href="{{ route('password.request') }}" wire:navigate>Forgot your password?</a>
        </div>
        <button class="uk-btn uk-btn-primary block w-full" type="submit" wire:loading.attr="disabled">
            Sign in
        </button>
    </form>
</x-auth.card>
