<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManageVehicles()) {
            abort(403, 'Super user access required.');
        }

        return $next($request);
    }
}
