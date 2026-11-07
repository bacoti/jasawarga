<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerPortalController extends Controller
{
    public function index(Request $request): View
    {
        return view('auth.partner-portal', [
            'user' => $request->user(),
            'profile' => $request->user()->partnerProfile,
        ]);
    }
}
