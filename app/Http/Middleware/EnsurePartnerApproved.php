<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $profile = $request->user()?->partnerProfile;
        if (!$profile || $profile->verification_status !== 'approved') {
            return redirect()->route('partner.portal');
        }
        return $next($request);
    }
}
