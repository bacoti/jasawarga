<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminManagementPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User([
            'name'=>'Admin Pengujian', 'email'=>'admin@example.test',
            'role'=>'admin', 'account_status'=>'active', 'password'=>'not-used',
        ]));
    }


    public function test_admin_warga_page_renders_and_links_to_mitra(): void
    {
        $this->get(route('admin.citizens'))
            ->assertOk()
            ->assertSee('Manajemen Warga')
            ->assertSee('Akun Warga lokal')
            ->assertSee('data-citizen-table', false)
            ->assertSee('data-citizen-action-form', false)
            ->assertSee(route('admin.partners'));
    }

    public function test_admin_mitra_page_has_seed_and_simulation_disclaimer(): void
    {
        $this->get(route('admin.partners'))
            ->assertOk()
            ->assertSee('Manajemen Mitra & Verifikasi', false)
            ->assertSee('Budi Teknik')
            ->assertSee('data-partner-action-form', false)
            ->assertSee('Verifikasi simulasi');
    }

    public function test_no_post_endpoints_for_prototype_admin(): void
    {
        $this->post('/admin/warga')->assertStatus(405);
        $this->post('/admin/mitra')->assertStatus(405);
    }
}
