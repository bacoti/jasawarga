<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateUserProfileRequest;
use App\Models\UserProfile;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    private function profile(): UserProfile
    {
        return UserProfile::firstOrCreate(['user_id' => auth()->id()]);
    }

    public function show(): View
    {
        $profile = $this->profile();
        $addresses = UserAddress::where('user_id', auth()->id())->orderByDesc('is_primary')->latest()->get();
        return view('account.profile.show', compact('profile', 'addresses'));
    }

    public function edit(): View
    {
        $profile = $this->profile();
        return view('account.profile.edit', compact('profile'));
    }

    public function update(UpdateUserProfileRequest $request): RedirectResponse
    {
        $profile = $this->profile();
        $data = $request->validated();
        unset($data['avatar']);

        if ($request->hasFile('avatar')) {
            $oldPath = $profile->avatar_path;
            $data['avatar_path'] = $request->file('avatar')->store('profiles/avatars', 'public');
        }

        $profile->fill($data)->save();
        if (! empty($oldPath)) Storage::disk('public')->delete($oldPath);
        return redirect()->route('account.profile.show')->with('success', 'Profil Warga berhasil diperbarui.');
    }
}
