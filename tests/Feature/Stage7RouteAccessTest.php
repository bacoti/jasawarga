<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Baseline acceptance gate: roles from Tahap 1 / 3 / 5 remain isolated after merge. */
class Stage7RouteAccessTest extends TestCase
{
    use RefreshDatabase;

    private int $number = 0;

    private function account(string $role, string $status = 'active'): User
    {
        $this->number++;
        return User::create([
            'name' => 'Stage7 '.$role.' '.$this->number,
            'email' => "stage7.{$role}.{$this->number}@example.test",
            'role' => $role,
            'account_status' => $status,
            'password' => 'StrongPass2026!',
        ]);
    }

    public function test_guest_is_redirected_from_all_real_management_areas(): void
    {
        foreach ([
            'admin.dashboard', 'admin.db-categories.index',
            'admin.db-feedback.reviews.index', 'admin.db-feedback.complaints.index',
            'partner.db-services.index', 'partner.db-feedback.reviews.index',
            'account.profile', 'db-feedback.reviews.mine', 'db-feedback.complaints.mine',
        ] as $name) {
            $this->get(route($name))->assertRedirect(route('account.login'));
        }
        $this->get(route('db-catalog.index'))->assertOk();
        $this->get(route('db-feedback.reviews.public'))->assertOk();
    }

    public function test_citizen_cannot_read_admin_or_partner_data_and_partner_cannot_read_citizen_data(): void
    {
        $citizen = $this->account('citizen');
        $this->actingAs($citizen);
        foreach ([
            'admin.dashboard', 'admin.db-categories.index',
            'admin.db-feedback.reviews.index', 'admin.db-feedback.complaints.index',
            'partner.db-services.index', 'partner.db-feedback.reviews.index',
        ] as $name) {
            $this->get(route($name))->assertForbidden();
        }

        $partner = $this->account('partner');
        PartnerProfile::create([
            'user_id' => $partner->id, 'business_name' => 'Jasa Stage7',
            'business_phone' => '081234567890', 'service_area' => 'Makassar',
            'verification_status' => 'approved',
        ]);
        $this->actingAs($partner);
        $this->get(route('account.profile'))->assertForbidden();
        $this->get(route('db-feedback.reviews.mine'))->assertForbidden();
        $this->get(route('db-feedback.complaints.mine'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('partner.db-services.index'))->assertOk();
        $this->get(route('partner.dashboard'))->assertRedirect(route('partner.portal'));
    }

    public function test_pending_partner_has_portal_but_not_operational_or_feedback_tools(): void
    {
        $partner = $this->account('partner');
        PartnerProfile::create([
            'user_id' => $partner->id, 'business_name' => 'Mitra Menunggu',
            'business_phone' => '081234567899', 'service_area' => 'Makassar',
            'verification_status' => 'pending',
        ]);
        $this->actingAs($partner)->get(route('partner.portal'))->assertOk();
        $this->get(route('partner.db-services.index'))->assertRedirect(route('partner.portal'));
        $this->get(route('partner.db-feedback.reviews.index'))->assertRedirect(route('partner.portal'));
        $this->get(route('partner.dashboard'))->assertRedirect(route('partner.portal'));
    }

    public function test_deactivated_user_is_blocked_by_role_middleware_even_with_existing_session(): void
    {
        $admin = $this->account('admin', 'disabled');
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertForbidden();

        $citizen = $this->account('citizen', 'disabled');
        $this->actingAs($citizen)->get(route('db-feedback.complaints.mine'))->assertForbidden();
    }

    public function test_login_with_mismatched_role_cannot_grant_admin_access(): void
    {
        $citizen = $this->account('citizen');
        $this->post(route('auth.login'), [
            'email' => $citizen->email, 'password' => 'StrongPass2026!', 'role' => 'admin',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect(route('account.login'));
    }
}
