<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PartnerProfile;
use Tests\TestCase;

class PartnerOrdersPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $partner = new User([
            'name'=>'Mitra Pengujian', 'email'=>'mitra@example.test',
            'role'=>'partner', 'account_status'=>'active', 'password'=>'not-used',
        ]);
        $partner->setRelation('partnerProfile', new PartnerProfile([
            'business_name'=>'Budi Teknik', 'verification_status'=>'approved', 'demo_provider_id'=>1,
        ]));
        $this->actingAs($partner);
    }


    public function test_partner_orders_page_renders_without_database(): void
    {
        $this->get(route('partner.orders.index'))
            ->assertOk()
            ->assertSee('Kelola pesanan masuk')
            ->assertSee('Budi Teknik')
            ->assertSee('Riwayat pekerjaan')
            ->assertSee('Alasan penolakan');
    }

    public function test_partner_and_warga_routes_remain_available(): void
    {
        $this->get(route('partner.dashboard'))->assertOk();
        $this->get(route('orders.index'))->assertOk();
        $this->get(route('orders.show', ['reference' => 'JW-DEMO-20261107-A1B2C3']))->assertOk();
        $this->get(route('providers.show', ['id' => 1]))->assertOk();
    }

    public function test_invalid_provider_detail_is_still_not_found(): void
    {
        $this->get('/mitra/999999')->assertNotFound();
    }
}
