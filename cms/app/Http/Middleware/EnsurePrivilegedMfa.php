<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrivilegedMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->isPrivileged()) {
            return $next($request);
        }
        if (! $user->hasConfirmedMfa()) {
            return redirect()->route('admin.mfa.show')->with('warning', 'Multi-factor authentication is required for privileged access.');
        }
        if (! $request->session()->get('mfa_passed')) {
            return redirect()->guest(route('admin.mfa.challenge'));
        }

        return $next($request);
    }
}
