<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminReportsSettingsPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User([
            'name'=>'Admin Pengujian', 'email'=>'admin@example.test',
            'role'=>'admin', 'account_status'=>'active', 'password'=>'not-used',
        ]));
    }


    public function test_reports_page_contains_seed_filters_charts_exports_and_navigation(): void
    {
        $this->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('Laporan & Analitik', false)
            ->assertSee('jw-admin-reports-seed', false)
            ->assertSee('Budi Teknik')
            ->assertSee('data-ar-filter-form', false)
            ->assertSee('data-ar-chart', false)
            ->assertSee('data-ar-export="orders"', false)
            ->assertSee(route('admin.settings'));
    }

    public function test_admin_settings_contains_forms_and_demo_logout(): void
    {
        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Pengaturan Admin')
            ->assertSee('data-as-identity-form', false)
            ->assertSee('data-as-preferences-form', false)
            ->assertSee('data-as-password-form', false)
            ->assertSee('data-as-logout', false)
            ->assertSee('Pengaturan Admin');
    }

    public function test_demo_entry_is_not_login_and_pages_are_get_only(): void
    {
        $this->get(route('admin.demo.entry'))->assertRedirect(route('account.login', ['role'=>'admin']));
        $this->post('/admin/laporan')->assertStatus(405);
        $this->post('/admin/pengaturan')->assertStatus(405);
        $this->post('/admin/masuk')->assertStatus(405);
    }
}
