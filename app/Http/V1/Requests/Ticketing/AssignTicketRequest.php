<?php

declare(strict_types=1);

namespace App\Http\V1\Requests\Ticketing;

use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\AssignTicketPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssignTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'assignee_id' => ['required', 'string', Rule::exists('users', 'id')],
        ];
    }

    public function payload(): AssignTicketPayload
    {
        /** @var Ticket $ticket */
        $ticket = $this->route('ticket');

        return new AssignTicketPayload(
            $ticket->id,
            $this->string('assignee_id')->toString(),
            $this->user()->id,
        );
    }
}
