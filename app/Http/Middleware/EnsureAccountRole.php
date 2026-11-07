<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $aliases = ['warga' => 'citizen', 'mitra' => 'partner'];
        $allowed = array_map(fn (string $role) => $aliases[$role] ?? $role, $roles);
        $user = $request->user();
        abort_unless($user && $user->account_status === 'active' && in_array($user->role, $allowed, true), 403);
        return $next($request);
    }
}
