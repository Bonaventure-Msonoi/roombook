<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->isActive() || $user->isPendingApproval() || $user->isRejected())) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = match (true) {
                $user->isPendingApproval() => 'Your account is waiting for approval. Please contact an administrator.',
                $user->isRejected() => 'Your signup request was not approved. Contact an administrator.',
                default => 'Your account has been deactivated. Contact an administrator.',
            };

            return redirect()
                ->route('login')
                ->with('error', $message);
        }

        return $next($request);
    }
}
