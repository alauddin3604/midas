@props(['title', 'description' => null])

<div class="flex min-h-svh items-center justify-center p-4 md:bg-muted md:p-10">
    <div class="w-full max-w-md">
        <div class="fr-widget border-border bg-background text-foreground md:border md:p-6">
            <div class="flex flex-col space-y-1.5">
                <h1 class="uk-h4">{{ $title }}</h1>
                @if ($description)
                    <p class="text-muted-foreground">{{ $description }}</p>
                @endif
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
