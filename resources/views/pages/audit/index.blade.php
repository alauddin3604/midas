<div class="space-y-6">
    <div>
        <h1 class="uk-h3">Audit log</h1>
        <p class="text-muted-foreground">Who did what, and when.</p>
    </div>

    <p class="text-cyan-600">
        Test
    </p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="grid gap-y-1">
            <label class="uk-form-label" for="event">Event</label>
            <select class="uk-select" id="event" wire:model.live="event">
                <option value="">All events</option>
                @foreach ($this->eventOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label" for="actor">Actor</label>
            <select class="uk-select" id="actor" wire:model.live="actor">
                <option value="">Anyone</option>
                @foreach ($this->actorOptions as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label" for="from">From</label>
            <input class="uk-input" id="from" type="date" wire:model.live="from" />
        </div>
        <div class="grid gap-y-1">
            <label class="uk-form-label" for="until">Until</label>
            <input class="uk-input" id="until" type="date" wire:model.live="until" />
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="uk-table uk-table-divider uk-table-middle">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Event</th>
                    <th>Actor</th>
                    <th>Subject</th>
                    <th>Changes</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->entries as $entry)
                    <tr wire:key="entry-{{ $entry->id }}">
                        <td class="whitespace-nowrap">{{ $entry->occurred_at->format('Y-m-d H:i:s') }}</td>
                        <td><span class="uk-label">{{ $entry->event }}</span></td>
                        <td>{{ $entry->actor_id === null ? 'System' : ($this->names[$entry->actor_id] ?? $entry->actor_id) }}</td>
                        <td>
                            {{ $entry->subject_type === 'user' ? ($this->names[$entry->subject_id] ?? $entry->subject_id) : $entry->subject_id }}
                            <span class="text-muted-foreground">({{ $entry->subject_type }})</span>
                        </td>
                        <td>
                            @foreach ($entry->changes ?? [] as $field => $change)
                                <div>{{ $field }}: {{ $change['old'] ?? '—' }} → {{ $change['new'] ?? '—' }}</div>
                            @endforeach
                        </td>
                        <td>{{ $entry->context['ip'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted-foreground" colspan="6">No audit entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->entries->links() }}
</div>
