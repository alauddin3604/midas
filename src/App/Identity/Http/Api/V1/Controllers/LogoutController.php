<?php

declare(strict_types=1);

namespace App\Identity\Http\Api\V1\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Support\Bases\Controllers\Controller;

final class LogoutController extends Controller
{
    public function __invoke(Request $request, AuthManager $auth): Response
    {
        $auth->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
