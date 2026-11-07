<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoPartner
{
    public function handle(Request $request, Closure $next): Response
    {
        // Dashboard operasional lama selalu membaca katalog dummy ID 1.
        // Jangan menampilkan identitas/data Budi Teknik pada akun mitra baru.
        if ($request->user()?->partnerProfile?->demo_provider_id !== 1) {
            return redirect()->route('partner.portal');
        }
        return $next($request);
    }
}
