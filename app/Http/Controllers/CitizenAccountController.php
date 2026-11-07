<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CitizenAccountController extends Controller
{
    public function profile(Request $request): View
    {
        return view('auth.citizen-profile', ['user' => $request->user()]);
    }

    public function edit(Request $request): View
    {
        return view('auth.citizen-edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $values = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'regex:/^08[0-9]{8,11}$/'],
        ]);
        $request->user()->update($values);
        return redirect()->route('account.profile')->with('success', 'Profil akun database berhasil diperbarui.');
    }
}
