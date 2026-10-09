<?php

declare(strict_types=1);

namespace Domain\Audit\Actions;

use Domain\Audit\Models\AuditEntry;
use Domain\Audit\Payloads\RecordAuditEntryPayload;

final readonly class RecordAuditEntryAction
{
    public function execute(RecordAuditEntryPayload $payload): AuditEntry
    {
        return AuditEntry::query()->forceCreate($payload->toArray());
    }
}
