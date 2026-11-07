<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Account\UpdateUserPreferencesRequest;
use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $preferences = UserPreference::firstOrCreate(['user_id' => $user->id], [
            'notifications' => ['order_updates' => true, 'review_updates' => true, 'platform_news' => false],
            'privacy' => ['profile_visible' => true, 'show_phone' => false],
            'operational' => ['accepting_orders' => true],
        ]);
        return view('account.settings.index', compact('user', 'preferences'));
    }

    public function updateAccount(UpdateAccountRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        unset($data['current_password']);
        $emailChanged = $data['email'] !== $user->email;
        $user->fill($data);
        if ($emailChanged) $user->email_verified_at = null;
        $user->save();
        if ($emailChanged && method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
        }
        return back()->with('success', 'Informasi akun berhasil diperbarui. Jika email berubah, verifikasi email perlu dilakukan kembali.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => Hash::make($request->validated()['password']),
        ])->save();
        $request->session()->regenerate();
        return back()->with('success', 'Password berhasil diperbarui. Password tersimpan dalam bentuk hash.');
    }

    public function updatePreferences(UpdateUserPreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();
        $current = UserPreference::firstOrCreate(['user_id' => $user->id], [
            'notifications' => ['order_updates' => true, 'review_updates' => true, 'platform_news' => false],
            'privacy' => ['profile_visible' => true, 'show_phone' => false],
            'operational' => ['accepting_orders' => true],
        ]);
        $input = $request->validated();
        $notifications = $input['notifications'] ?? [];
        foreach ($notifications as $key => $value) $notifications[$key] = (bool) $value;
        $privacy = $input['privacy'] ?? [];
        foreach ($privacy as $key => $value) $privacy[$key] = (bool) $value;
        $operational = $input['operational'] ?? [];
        foreach ($operational as $key => $value) $operational[$key] = (bool) $value;
        $current->notifications = array_replace($current->notifications ?? [], $notifications);
        $current->privacy = array_replace($current->privacy ?? [], $privacy);
        $current->operational = array_replace($current->operational ?? [], $operational);
        if (isset($input['timezone'])) $current->timezone = $input['timezone'];
        $current->save();
        return back()->with('success', 'Preferensi akun berhasil disimpan.');
    }
}
