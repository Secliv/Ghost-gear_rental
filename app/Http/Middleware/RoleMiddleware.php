<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->is_active) {
            abort(403, 'Akun Anda telah dinonaktifkan.');
        }

        if ($user->role->value !== $role) {
            abort(403, 'Akses tidak diizinkan untuk peran ini.');
        }

        return $next($request);
    }
}
