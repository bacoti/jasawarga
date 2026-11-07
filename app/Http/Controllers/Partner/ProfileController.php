<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\UpdatePartnerProfileRequest;
use App\Models\PartnerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private function profile(): PartnerProfile
    {
        $user = auth()->user();
        $base = Str::slug($user->name) ?: 'mitra';
        $slug = $base.'-'.$user->id;
        return PartnerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['business_name' => $user->name, 'business_phone' => $user->phone ?: '00000000', 'service_area' => 'Makassar', 'slug' => $slug, 'city' => 'Makassar', 'verification_status' => 'pending', 'service_areas' => []]
        );
    }

    public function edit(): View
    {
        $profile = $this->profile();
        $districts = config('jasawarga-profile.makassar_districts', []);
        return view('partner.profile.edit', compact('profile', 'districts'));
    }

    public function update(UpdatePartnerProfileRequest $request): RedirectResponse
    {
        $profile = $this->profile();
        $data = $request->validated();
        $newAreas = array_values(array_unique($data['service_areas']));
        unset($data['service_areas'], $data['profile_photo'], $data['cover_photo']);
        $oldAreas = array_values($profile->service_areas ?? []);
        sort($oldAreas); $sortedNewAreas = $newAreas; sort($sortedNewAreas);
        $oldAddressSummary = (string) ($profile->address_summary ?? '');
        $newAddressSummary = (string) ($data['address_summary'] ?? '');
        $areaChanged = $oldAreas !== $sortedNewAreas || $oldAddressSummary !== $newAddressSummary;

        if ($request->hasFile('profile_photo')) {
            $oldProfilePhoto = $profile->profile_photo_path;
            $data['profile_photo_path'] = $request->file('profile_photo')->store('partners/profile', 'public');
        }
        if ($request->hasFile('cover_photo')) {
            $oldCoverPhoto = $profile->cover_photo_path;
            $data['cover_photo_path'] = $request->file('cover_photo')->store('partners/covers', 'public');
        }

        $data['service_areas'] = $newAreas;
        $data['business_phone'] = $data['phone'];
        $data['service_area'] = $newAreas[0] ?? 'Makassar';
        $data['slug'] = Str::slug($data['business_name']).'-'.$request->user()->id;
        if ($areaChanged && $profile->verification_status === 'approved') $data['reverification_required'] = true;
        // verification_status, verification_note, verified_at sengaja tidak pernah diisi dari request.
        $profile->fill($data)->save();
        if (! empty($oldProfilePhoto)) Storage::disk('public')->delete($oldProfilePhoto);
        if (! empty($oldCoverPhoto)) Storage::disk('public')->delete($oldCoverPhoto);

        $message = $areaChanged && $profile->reverification_required
            ? 'Profil disimpan. Perubahan area layanan menunggu pemeriksaan ulang Admin; profil publik sementara disembunyikan.'
            : 'Profil usaha berhasil diperbarui.';
        return redirect()->route('partner.profile.edit')->with('success', $message);
    }
}
