<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleDashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_roles_to_the_correct_dashboard_design_and_shows_three_second_success_toast(): void
    {
        $roles = [
            User::ROLE_CITIZEN => 'account.analytics.dashboard',
            User::ROLE_PARTNER => 'partner.analytics.dashboard',
            User::ROLE_ADMIN => 'admin.dashboard',
        ];

        foreach ($roles as $role => $destination) {
            $user = User::create([
                'name' => 'Pengguna '.ucfirst($role),
                'email' => $role.'-redirect@example.test',
                'role' => $role,
                'account_status' => 'active',
                'password' => 'StrongPass2026!',
            ]);

            if ($role === User::ROLE_PARTNER) {
                PartnerProfile::create([
                    'user_id' => $user->id,
                    'business_name' => 'Usaha Bandung',
                    'business_phone' => '081234567890',
                    'service_area' => 'Coblong',
                    'verification_status' => 'pending',
                ]);
            }

            $this->post(route('auth.login'), [
                'email' => $user->email,
                'password' => 'StrongPass2026!',
                'role' => $role,
            ])->assertRedirect(route($destination))
                ->assertSessionHas('success');

            $this->get(route('account.login'))->assertRedirect(route($destination));

            $this->get(route($destination))
                ->assertOk()
                ->assertSee('data-jw-auth-flash', false)
                ->assertSee('data-jw-duration="3000"', false)
                ->assertSee('family=Inter', false);

            $this->post(route('auth.logout'))->assertRedirect(route('account.login'));
        }
    }

    public function test_registration_redirects_citizen_and_pending_partner_to_their_respective_dashboards(): void
    {
        $this->post(route('auth.register.citizen'), [
            'name' => 'Warga Baru',
            'email' => 'warga-new@example.test',
            'phone' => '081234567890',
            'password' => 'StrongPass2026!',
            'password_confirmation' => 'StrongPass2026!',
            'terms' => '1',
        ])->assertRedirect(route('account.analytics.dashboard'))
            ->assertSessionHas('success');
        $this->post(route('auth.logout'))->assertRedirect(route('account.login'));

        $this->post(route('auth.register.partner'), [
            'name' => 'Mitra Baru',
            'email' => 'mitra-new@example.test',
            'business_name' => 'Jasa Bandung',
            'business_phone' => '081234567890',
            'service_area' => 'Coblong',
            'description' => 'Jasa lokal Bandung',
            'password' => 'StrongPass2026!',
            'password_confirmation' => 'StrongPass2026!',
            'terms' => '1',
        ])->assertRedirect(route('partner.analytics.dashboard'))
            ->assertSessionHas('success');
        $this->get(route('partner.analytics.dashboard'))
            ->assertOk()
            ->assertSee('Akun Mitra sedang diverifikasi')
            ->assertSee('data-jw-duration="3000"', false);
    }

    public function test_admin_uses_the_same_legacy_dashboard_as_the_navy_sidebar_screenshot(): void
    {
        $admin = User::create([
            'name' => 'Admin JasaWarga',
            'email' => 'admin-legacy@example.test',
            'password' => 'StrongPass2026!',
            'account_status' => 'active',
            'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('jw-admin-dashboard', false)
            ->assertSee('admin', false)
            ->assertSee('Ringkasan platform');
        $this->get(route('admin.analytics.dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.analytics.database'))->assertOk()->assertSee('Overview Admin');
    }

    public function test_approved_demo_partner_uses_legacy_dashboard_but_new_partner_does_not(): void
    {
        $demo = User::create([
            'name' => 'Mitra Demo', 'email' => 'mitra-demo-legacy@example.test',
            'role' => User::ROLE_PARTNER, 'account_status' => 'active',
            'password' => 'StrongPass2026!',
        ]);
        PartnerProfile::create([
            'user_id' => $demo->id, 'business_name' => 'Budi Teknik',
            'business_phone' => '081234567890', 'service_area' => 'Bandung',
            'verification_status' => 'approved', 'demo_provider_id' => 1,
        ]);
        $this->post(route('auth.login'), [
            'email' => $demo->email, 'password' => 'StrongPass2026!',
            'role' => User::ROLE_PARTNER,
        ])->assertRedirect(route('partner.dashboard'));
        $this->get(route('partner.dashboard'))->assertOk()
            ->assertSee('jw-partner', false)
            ->assertSee('data-jw-duration="3000"', false);
        $this->post(route('auth.logout'));

        $newPartner = User::create([
            'name' => 'Mitra Baru', 'email' => 'mitra-real-legacy@example.test',
            'role' => User::ROLE_PARTNER, 'account_status' => 'active',
            'password' => 'StrongPass2026!',
        ]);
        PartnerProfile::create([
            'user_id' => $newPartner->id, 'business_name' => 'Usaha Baru Bandung',
            'business_phone' => '081234567890', 'service_area' => 'Bandung',
            'verification_status' => 'pending', 'demo_provider_id' => null,
        ]);
        $this->post(route('auth.login'), [
            'email' => $newPartner->email, 'password' => 'StrongPass2026!',
            'role' => User::ROLE_PARTNER,
        ])->assertRedirect(route('partner.analytics.dashboard'));
        $this->get(route('partner.analytics.dashboard'))->assertOk()
            ->assertSee('Usaha Baru Bandung')
            ->assertSee('sedang diverifikasi');
        $this->get(route('partner.dashboard'))->assertRedirect(route('partner.portal'));
    }

    public function test_citizen_dashboard_has_its_own_sidebar_not_an_admin_link(): void
    {
        $citizen = User::create([
            'name' => 'Warga Bandung', 'email' => 'warga-design@example.test',
            'password' => 'StrongPass2026!', 'account_status' => 'active',
            'role' => User::ROLE_CITIZEN,
        ]);
        $this->actingAs($citizen)->get(route('account.analytics.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Warga')
            ->assertSee('Menu Warga')
            ->assertDontSee('Buka Dashboard Admin (demo)');
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

}
