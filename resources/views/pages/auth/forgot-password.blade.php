<x-auth.card
    title="Forgot your password?"
    description="Enter your email and we'll send you a link to reset it."
>
    @if ($linkSent)
        <div class="uk-alert mt-6">
            If an account exists for that email, a reset link is on its way.
        </div>
    @else
        <form class="mt-6 space-y-6" wire:submit="sendLink">
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
            <button class="uk-btn uk-btn-primary block w-full" type="submit" wire:loading.attr="disabled">
                Send reset link
            </button>
        </form>
    @endif
    <div class="mt-6 text-center">
        <a class="uk-link" href="{{ route('login') }}" wire:navigate>Back to sign in</a>
    </div>
</x-auth.card>
