<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NgrokHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('X-Forwarded-For')) {
            $request->headers->set(
                'X-Forwarded-For',
                $request->header('X-Forwarded-For')
            );
        }

        if ($request->hasHeader('X-Forwarded-Proto')) {
            $request->headers->set(
                'X-Forwarded-Proto',
                $request->header('X-Forwarded-Proto')
            );
        }

        if ($request->hasHeader('X-Forwarded-Host')) {
            $request->headers->set(
                'X-Forwarded-Host',
                $request->header('X-Forwarded-Host')
            );
        }

        return $next($request);
    }
}
