<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthPostgresFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' Testing',
            'email' => $role.'@example.test',
            'role' => $role,
            'account_status' => 'active',
            'password' => 'StrongPass2026!',
        ]);
    }

    public function test_guest_redirects_from_admin_and_partner_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/masuk');
        $this->get('/mitra/dashboard')->assertRedirect('/masuk');
        $this->get('/akun')->assertRedirect('/masuk');
    }

    public function test_citizen_registration_hashes_password_and_does_not_create_partner(): void
    {
        $this->post('/daftar', [
            'name' => 'Siti Warga',
            'email' => 'Siti@Example.test',
            'phone' => '081234567890',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'terms' => '1',
        ])->assertRedirect(route('account.analytics.dashboard'))->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'siti@example.test', 'role' => 'citizen']);
        $citizen = User::where('email', 'siti@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('SecurePass2026', $citizen->password));
        $this->assertNull($citizen->partnerProfile);
        $this->assertAuthenticatedAs($citizen);
    }

    public function test_partner_registration_is_pending_and_cannot_access_demo_partner_dashboard(): void
    {
        $this->post('/daftar/mitra', [
            'name' => 'Rani Mitra', 'email' => 'rani@example.test',
            'business_name' => 'Rani Instalasi', 'business_phone' => '081234567890',
            'service_area' => 'Panakkukang', 'description' => 'Instalasi listrik rumah',
            'password' => 'SecurePass2026', 'password_confirmation' => 'SecurePass2026',
            'terms' => '1',
        ])->assertRedirect(route('partner.analytics.dashboard'))->assertSessionHas('success');
        $user = User::where('email', 'rani@example.test')->firstOrFail();
        $this->assertSame('pending', $user->partnerProfile->verification_status);
        $this->assertNull($user->partnerProfile->demo_provider_id);
        $this->get('/mitra/dashboard')->assertRedirect('/mitra/portal');
    }

    public function test_admin_role_blocks_citizen_and_verifies_real_partner_with_audit_log(): void
    {
        $admin = $this->createUser('admin');
        $citizen = $this->createUser('citizen');
        $partner = $this->createUser('partner');
        $profile = PartnerProfile::create([
            'user_id'=>$partner->id,'business_name'=>'Mitra Baru',
            'business_phone'=>'081234567890','service_area'=>'Makassar',
            'verification_status'=>'pending',
        ]);
        $this->actingAs($citizen)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->post(route('admin.partner-applications.decide', $profile), [
            'decision'=>'approved', 'reason'=>'Kontak dan pendaftaran telah diperiksa.',
        ])->assertRedirect();
        $this->assertDatabaseHas('partner_profiles', ['id'=>$profile->id,'verification_status'=>'approved','verified_by'=>$admin->id]);
        $this->assertDatabaseHas('partner_verification_logs', ['partner_profile_id'=>$profile->id,'from_status'=>'pending','to_status'=>'approved','admin_id'=>$admin->id]);
        // Persetujuan tidak memberikan akses ke katalog Budi Teknik ID=1.
        $this->actingAs($partner)->get('/mitra/dashboard')->assertRedirect('/mitra/portal');
    }

    public function test_logout_ends_session_and_wrong_role_cannot_login_as_admin(): void
    {
        $citizen = $this->createUser('citizen');
        $this->post('/masuk', [
            'email'=>$citizen->email, 'password'=>'StrongPass2026!', 'role'=>'admin',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/masuk', [
            'email'=>$citizen->email, 'password'=>'StrongPass2026!', 'role'=>'citizen',
        ])->assertRedirect(route('account.analytics.dashboard'))->assertSessionHas('success');
        $this->post('/keluar')->assertRedirect('/masuk');
        $this->assertGuest();
    }
}
