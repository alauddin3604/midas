<div class="space-y-6">
    <div>
        <h1 class="uk-h3">Dashboard</h1>
        <p class="text-muted-foreground">
            Welcome back, {{ auth()->user()->name }}. You are signed in as {{ auth()->user()->role->label() }}.
        </p>
    </div>
</div>
