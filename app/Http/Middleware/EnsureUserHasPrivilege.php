<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPrivilege
{
    public function handle(Request $request, Closure $next, string $privilege): Response
    {
        if (! $request->user()?->hasPrivilege($privilege)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
