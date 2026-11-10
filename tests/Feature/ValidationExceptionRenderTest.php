<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\getJson;

it('renders the valid format for validation exception', function (): void {
    Route::get('v1/test-exception', function () {
        throw ValidationException::withMessages([
            'status' => 'The selected status field is invalid.',
        ]);
    });

    getJson('v1/test-exception')
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'Validation failed.',
            'errors' => [
                'status' => [
                    'The selected status field is invalid.',
                ],
            ],
        ]);
});
