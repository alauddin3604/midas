<?php

declare(strict_types=1);

namespace Interfaces\Audit\Http\Dashboard\Livewire;

use Domain\Audit\Models\AuditEntry;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read LengthAwarePaginator<int, AuditEntry> $entries
 */
#[Layout('layouts::dashboard'), Title('Audit log')]
final class AuditLogPage extends Component
{
    use WithPagination;

    #[Url]
    public string $event = '';

    #[Url]
    public string $actor = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $until = '';

    public function render(): View
    {
        return view('pages::audit.index');
    }

    /**
     * Go back to the first page whenever a filter changes.
     */
    public function updated(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, AuditEntry>
     */
    #[Computed]
    public function entries(): LengthAwarePaginator
    {
        return AuditEntry::query()
            ->ofEvent($this->event)
            ->byActor($this->actor)
            ->between(
                $this->from === '' ? null : Carbon::parse($this->from)->startOfDay(),
                $this->until === '' ? null : Carbon::parse($this->until)->endOfDay(),
            )
            ->latest('occurred_at')
            ->paginate(25);
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function eventOptions(): Collection
    {
        return AuditEntry::query()->distinct()->orderBy('event')->pluck('event');
    }

    /**
     * Names of every user who has acted, keyed by ID, for the actor filter.
     *
     * @return Collection<string, string>
     */
    #[Computed]
    public function actorOptions(): Collection
    {
        $actorIds = AuditEntry::query()->whereNotNull('actor_id')->distinct()->pluck('actor_id');

        return User::query()->whereKey($actorIds)->orderBy('name')->pluck('name', 'id');
    }

    /**
     * Names of the users shown on the current page, keyed by ID.
     *
     * @return Collection<string, string>
     */
    #[Computed]
    public function names(): Collection
    {
        $ids = collect($this->entries->items())->flatMap(fn (AuditEntry $entry): array => [
            $entry->actor_id,
            $entry->subject_type === 'user' ? $entry->subject_id : null,
        ])->filter()->unique();

        return User::query()->whereKey($ids)->pluck('name', 'id');
    }
}
