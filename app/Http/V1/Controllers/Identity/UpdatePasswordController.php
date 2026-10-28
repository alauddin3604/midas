<?php

declare(strict_types=1);

namespace App\Http\V1\Controllers\Identity;

use App\Http\V1\Controllers\ApiController;
use App\Http\V1\Requests\Identity\UpdateUserPasswordRequest;
use Domain\Identity\Actions\UpdateUserPasswordAction;
use Illuminate\Http\Response;

final class UpdatePasswordController extends ApiController
{
    public function __invoke(UpdateUserPasswordRequest $request, UpdateUserPasswordAction $action): Response
    {
        $action->execute($request->user(), $request->toPayload());

        return response()->noContent();
    }
}
