<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('setup') || $request->is('setup/*') || $request->is('api/*') || $request->is('up')) {
            return $next($request);
        }

        if (! User::where('role', User::ROLE_ADMIN)->exists()) {
            return redirect('/setup');
        }

        return $next($request);
    }
}
