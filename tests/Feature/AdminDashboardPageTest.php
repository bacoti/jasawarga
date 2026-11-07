<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminDashboardPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User([
            'name'=>'Admin Pengujian', 'email'=>'admin@example.test',
            'role'=>'admin', 'account_status'=>'active', 'password'=>'not-used',
        ]));
    }


    public function test_admin_overview_renders_mock_data_labels_and_navigation(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Overview')
            ->assertSee('Data browser')
            ->assertSee('Data contoh katalog')
            ->assertSee('Pesanan terbaru')
            ->assertSee('Verifikasi Mitra')
            ->assertSee('Aktivitas terbaru')
            ->assertSee('data-admin-chart', false)
            ->assertSee('Budi Teknik');
    }

    public function test_admin_route_has_no_mutation_endpoint(): void
    {
        $this->post('/admin/dashboard')->assertStatus(405);
    }

    public function test_previous_dashboard_and_orders_routes_remain_intact(): void
    {
        $this->get(route('partner.dashboard'))->assertForbidden();
        $this->get(route('orders.index'))->assertOk();
    }
}
