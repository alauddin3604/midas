<?php

declare(strict_types=1);

namespace Interfaces\Audit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stores where the request came from so audit listeners can record it without reading the request.
 */
final class CaptureAuditContext
{
    public const string KEY = 'audit';

    public function handle(Request $request, Closure $next): Response
    {
        Context::addHidden(self::KEY, [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);

        return $next($request);
    }
}
