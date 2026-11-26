<?php

declare(strict_types=1);

namespace Domain\Identity\QueryBuilders;

use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<User>
 */
final class UserQueryBuilder extends Builder
{
    /**
     * Match users whose name or email contains the term.
     */
    public function search(?string $term): static
    {
        return $this->when(
            $term !== null && $term !== '',
            fn (Builder $query): Builder => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"),
            ),
        );
    }
}
