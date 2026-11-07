<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminOperationsPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User([
            'name'=>'Admin Pengujian', 'email'=>'admin@example.test',
            'role'=>'admin', 'account_status'=>'active', 'password'=>'not-used',
        ]));
    }


    public function test_category_admin_has_management_forms_and_seed(): void
    {
        $this->get(route('admin.categories'))
            ->assertOk()
            ->assertSee('Manajemen Kategori Jasa')
            ->assertSee('data-category-seed', false)
            ->assertSee('data-form-dialog', false)
            ->assertSee('data-confirm-delete', false)
            ->assertSee('Budi Teknik');
    }

    public function test_order_admin_has_filters_read_only_history(): void
    {
        $this->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Monitoring Pesanan')
            ->assertSee('data-status', false)
            ->assertSee('Linimasa status')
            ->assertSee('Ekspor CSV tanpa identitas');
    }

    public function test_reviews_and_complaints_admin_has_demo_workflows(): void
    {
        $this->get(route('admin.reviews'))
            ->assertOk()
            ->assertSee('Ulasan &amp; Pengaduan', false)
            ->assertSee('data-review-list', false)
            ->assertSee('data-case-form', false)
            ->assertSee('data-action-reason', false);
    }

    public function test_routes_are_get_only_prototypes(): void
    {
        $this->post('/admin/kategori')->assertStatus(405);
        $this->post('/admin/pesanan')->assertStatus(405);
        $this->post('/admin/ulasan-pengaduan')->assertStatus(405);
    }
}
