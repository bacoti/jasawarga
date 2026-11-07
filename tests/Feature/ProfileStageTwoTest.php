<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\UserAddress;
use App\Models\UserProfile;
use App\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileStageTwoTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $user->forceFill(['role' => $role])->save();
        return $user;
    }

    public function test_warga_can_update_own_profile(): void
    {
        $user = $this->userWithRole('citizen');
        $this->actingAs($user)->put(route('account.profile.update'), [
            'phone' => '081234567890', 'bio' => 'Warga JasaWarga di Makassar.',
            'gender' => 'tidak_disebutkan',
        ])->assertRedirect(route('account.profile.show'));

        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'phone' => '081234567890']);
    }

    public function test_warga_can_create_addresses_and_only_one_is_primary(): void
    {
        $user = $this->userWithRole('citizen');
        $payload = [
            'label' => 'Rumah', 'recipient_name' => 'Dina Putri', 'phone' => '081234567890',
            'province' => 'Sulawesi Selatan', 'city' => 'Makassar', 'district' => 'Rappocini',
            'subdistrict' => 'Gunung Sari', 'address_detail' => 'Jalan Contoh Nomor 1',
        ];
        $this->actingAs($user)->post(route('account.addresses.store'), $payload)->assertRedirect();
        $first = UserAddress::where('user_id', $user->id)->firstOrFail();
        $this->assertTrue($first->is_primary);

        $this->post(route('account.addresses.store'), array_merge($payload, ['label' => 'Kantor', 'is_primary' => 1]))->assertRedirect();
        $this->assertSame(1, UserAddress::where('user_id', $user->id)->where('is_primary', true)->count());
    }

    public function test_warga_cannot_change_or_delete_another_users_address(): void
    {
        $owner = $this->userWithRole('citizen');
        $other = $this->userWithRole('citizen');
        $address = UserAddress::create([
            'user_id' => $owner->id, 'label' => 'Rumah', 'recipient_name' => 'Pemilik', 'phone' => '081234567890',
            'province' => 'Sulawesi Selatan', 'city' => 'Makassar', 'district' => 'Rappocini',
            'subdistrict' => 'Gunung Sari', 'address_detail' => 'Jalan Contoh 1', 'is_primary' => true,
        ]);
        $this->actingAs($other)->put(route('account.addresses.update', $address), [
            'label' => 'Rumah', 'recipient_name' => 'Penyusup', 'phone' => '081234567890',
            'province' => 'Sulawesi Selatan', 'city' => 'Makassar', 'district' => 'Rappocini',
            'subdistrict' => 'Gunung Sari', 'address_detail' => 'Percobaan ubah alamat',
        ])->assertNotFound();
        $this->delete(route('account.addresses.destroy', $address))->assertNotFound();
        $this->assertDatabaseHas('user_addresses', ['id' => $address->id]);
    }

    public function test_partner_cannot_edit_verification_status_and_area_change_flags_review(): void
    {
        $partner = $this->userWithRole('partner');
        $profile = PartnerProfile::create([
            'user_id' => $partner->id, 'business_name' => 'Bengkel Makmur', 'business_phone' => '081200000001', 'service_area' => 'Panakkukang', 'slug' => 'bengkel-makmur-'.$partner->id,
            'city' => 'Makassar', 'service_areas' => ['Panakkukang'],
        ]);
        $profile->forceFill(['verification_status' => 'approved'])->save();
        $this->actingAs($partner)->put(route('partner.profile.update'), [
            'business_name' => 'Bengkel Makmur', 'business_phone' => '081200000001', 'service_area' => 'Panakkukang', 'category_name' => 'Bengkel', 'description' => str_repeat('Layanan bengkel profesional. ', 2),
            'experience_years' => 5, 'phone' => '081234567890', 'whatsapp' => '081234567890', 'city' => 'Makassar',
            'service_areas' => ['Rappocini'], 'verification_status' => 'rejected', 'verification_note' => 'Injected note',
        ])->assertRedirect(route('partner.profile.edit'));

        $profile->refresh();
        $this->assertSame('approved', $profile->verification_status);
        $this->assertTrue($profile->reverification_required);
        $this->assertSame(['Rappocini'], $profile->service_areas);
    }

    public function test_public_profile_only_shows_approved_partner_without_reverification_flag(): void
    {
        $partner = $this->userWithRole('partner');
        $profile = PartnerProfile::create([
            'user_id' => $partner->id, 'business_name' => 'Teknik Sejahtera', 'business_phone' => '081200000002', 'service_area' => 'Tallo', 'slug' => 'teknik-sejahtera-'.$partner->id,
            'city' => 'Makassar', 'verification_status' => 'pending', 'service_areas' => ['Tallo'],
        ]);
        $this->get(route('partners.public.show', $profile->slug))->assertNotFound();
        $profile->forceFill(['verification_status' => 'approved'])->save();
        $this->get(route('partners.public.show', $profile->slug))->assertOk()->assertSee('Teknik Sejahtera');
        $profile->update(['reverification_required' => true]);
        $this->get(route('partners.public.show', $profile->slug))->assertNotFound();
    }

    public function test_account_email_change_requires_current_password_and_resets_verification(): void
    {
        Notification::fake();
        $user = $this->userWithRole('citizen');
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user)->put(route('account.settings.account'), [
            'name' => 'Dina Putri Baru', 'email' => 'dina.baru@example.test', 'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('account.settings.account'), [
            'name' => 'Dina Putri Baru', 'email' => 'dina.baru@example.test', 'current_password' => 'password',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('dina.baru@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_change_password_after_current_password_confirmation(): void
    {
        $user = $this->userWithRole('citizen');
        $this->actingAs($user)->put(route('account.settings.password'), [
            'current_password' => 'password', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
        ])->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_user_preferences_are_persisted_per_account(): void
    {
        $user = $this->userWithRole('citizen');
        $this->actingAs($user)->put(route('account.settings.preferences'), [
            'notifications' => ['order_updates' => 1, 'review_updates' => 0, 'platform_news' => 1],
            'privacy' => ['profile_visible' => 1, 'show_phone' => 0],
            'timezone' => 'Asia/Makassar',
        ])->assertRedirect();

        $preferences = UserPreference::where('user_id', $user->id)->firstOrFail();
        $this->assertTrue($preferences->notifications['order_updates']);
        $this->assertFalse($preferences->notifications['review_updates']);
        $this->assertSame('Asia/Makassar', $preferences->timezone);
    }

    public function test_partner_portfolio_is_scoped_to_authenticated_partner(): void
    {
        Storage::fake('public');
        $one = $this->userWithRole('partner');
        $two = $this->userWithRole('partner');
        $profileOne = PartnerProfile::create(['user_id' => $one->id, 'business_name' => 'Mitra Satu', 'business_phone' => '081200000003', 'service_area' => 'Makassar', 'slug' => 'mitra-satu-'.$one->id]);
        $profileTwo = PartnerProfile::create(['user_id' => $two->id, 'business_name' => 'Mitra Dua', 'business_phone' => '081200000004', 'service_area' => 'Makassar', 'slug' => 'mitra-dua-'.$two->id]);
        $portfolio = $profileOne->portfolios()->create(['title' => 'Pemasangan AC', 'image_path' => 'partners/portfolio/ac.jpg']);
        $this->actingAs($two)->get(route('partner.portfolio.edit', $portfolio))->assertNotFound();
        $this->assertNotSame($profileOne->id, $profileTwo->id);
    }
}
