<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Api\V1\Controllers;

use Domain\Identity\Actions\UpdateUserPasswordAction;
use Illuminate\Http\Response;
use Interfaces\Identity\Http\Api\V1\Requests\UpdateUserPasswordRequest;
use Support\Bases\Controllers\Controller;

final class UpdatePasswordController extends Controller
{
    public function __invoke(UpdateUserPasswordRequest $request, UpdateUserPasswordAction $action): Response
    {
        $action->execute($request->user(), $request->toPayload());

        return response()->noContent();
    }
}
