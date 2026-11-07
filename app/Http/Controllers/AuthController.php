<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterCitizenRequest;
use App\Http\Requests\RegisterPartnerRequest;
use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public static function destination(User $user): string
    {
        // Admin memakai panel biru navy yang dipakai seluruh halaman Admin lama.
        // Dashboard Mitra demo hanya aman untuk akun seed yang memang memiliki katalog #1.
        // Mitra PostgreSQL lainnya harus memakai dashboard dengan data miliknya sendiri.
        if ($user->role === User::ROLE_ADMIN) {
            return route('admin.dashboard');
        }

        if ($user->role === User::ROLE_PARTNER) {
            $partner = $user->partnerProfile;
            if ($partner && (int) $partner->demo_provider_id === 1
                && $partner->verification_status === 'approved'
                && ! $partner->reverification_required) {
                return route('partner.dashboard');
            }
            return route('partner.analytics.dashboard');
        }

        return $user->role === User::ROLE_CITIZEN
            ? route('account.analytics.dashboard')
            : route('home');
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) return redirect(self::destination($request->user()));
        $requestedRole = $request->query('role', 'citizen');
        if (!in_array($requestedRole, ['citizen', 'partner', 'admin'], true)) $requestedRole = 'citizen';
        return view('auth.login', ['requestedRole' => $requestedRole]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $values = $request->validated();
        $credentials = [
            'email' => $values['email'],
            'password' => $values['password'],
            'account_status' => 'active',
        ];
        if (!empty($values['role'])) $credentials['role'] = $values['role'];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email, kata sandi, role, atau status akun tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();
        $roleName = match ($user->role) {
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_PARTNER => 'Mitra',
            default => 'Warga',
        };

        return redirect(self::destination($user))
            ->with('success', "Anda berhasil login sebagai {$roleName}.");
    }

    public function showCitizenRegister(): View|RedirectResponse
    {
        if (Auth::check()) return redirect(self::destination(Auth::user()));
        return view('auth.register-citizen');
    }

    public function registerCitizen(RegisterCitizenRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => User::ROLE_CITIZEN,
            'account_status' => 'active',
            'password' => $data['password'], // cast 'hashed' dari model, BUKAN plaintext DB
        ]);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect(self::destination($user))
            ->with('success', 'Akun Warga berhasil dibuat. Selamat datang di JasaWarga!');
    }

    public function showPartnerRegister(): View|RedirectResponse
    {
        if (Auth::check()) return redirect(self::destination(Auth::user()));
        return view('auth.register-partner');
    }

    public function registerPartner(RegisterPartnerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => User::ROLE_PARTNER,
                'account_status' => 'active',
                'password' => $data['password'],
            ]);
            PartnerProfile::create([
                'user_id' => $user->id,
                'slug' => (\Illuminate\Support\Str::slug(trim($data['business_name'])) ?: 'mitra').'-'.$user->id,
                'city' => 'Bandung',
                'business_name' => trim($data['business_name']),
                'business_phone' => $data['business_phone'],
                'service_area' => trim($data['service_area']),
                'description' => trim($data['description'] ?? ''),
                'verification_status' => 'pending',
                'demo_provider_id' => null,
            ]);
            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();
        return redirect(self::destination($user))
            ->with('success', 'Akun Mitra berhasil dibuat. Pengajuan menunggu verifikasi Admin.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('account.login')->with('success', 'Kamu sudah keluar.');
    }
}
