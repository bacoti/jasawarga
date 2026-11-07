<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403,
            'Akun ini tidak memiliki akses ke halaman tersebut.');

        abort_if($request->user()->account_status !== 'active', 403, 'Akun dinonaktifkan.');

        return $next($request);
    }
}
