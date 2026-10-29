<?php

declare(strict_types=1);

namespace App\Http\V1\Requests\Ticketing;

use Domain\Ticketing\Enums\TicketPriority;
use Domain\Ticketing\Payloads\CreateTicketPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ];
    }

    public function payload(): CreateTicketPayload
    {
        return new CreateTicketPayload(
            $this->input('subject'),
            $this->input('description'),
            $this->enum('priority', TicketPriority::class),
            $this->user()->id,
        );
    }
}
