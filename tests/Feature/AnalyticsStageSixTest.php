<?php

namespace Tests\Feature;

use App\Models\PlatformNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsStageSixTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_dashboard_and_reports(): void
    {
        $admin = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->get(route('admin.analytics.dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get(route('admin.analytics.database'))->assertOk();
        $this->actingAs($admin)->get(route('admin.analytics.reports', ['period' => '30']))->assertOk();
    }

    public function test_non_admin_cannot_open_admin_reports(): void
    {
        $warga = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $warga->forceFill(['role' => 'citizen'])->save();

        $this->actingAs($warga)->get(route('admin.analytics.dashboard'))->assertForbidden();
        $this->actingAs($warga)->get(route('admin.analytics.reports'))->assertForbidden();
    }

    public function test_user_can_only_mark_own_notification_as_read(): void
    {
        $owner = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $other = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $notification = PlatformNotification::create([
            'user_id' => $other->id,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test notification',
        ]);

        $this->actingAs($owner)->patch(route('notifications.read', $notification))->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_warga_and_partner_dashboard_routes_require_correct_role(): void
    {
        $admin = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)->get(route('account.analytics.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('partner.analytics.dashboard'))->assertForbidden();
    }
}
