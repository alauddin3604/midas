<?php

use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\getJson;

it('renders EmailAlreadyRegistered as a 409 response', function () {
    Route::get('/_test/conflict', fn () => throw EmailAlreadyRegistered::withEmail('jane@example.com'));

    getJson('/_test/conflict')
        ->assertConflict()
        ->assertJson(['message' => 'A user with email [jane@example.com] is already registered.']);
});
