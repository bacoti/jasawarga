<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PartnerProfile;
use Tests\TestCase;

class PartnerServicesPageTest extends TestCase
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


    public function test_partner_services_page_renders_without_database(): void
    {
        $this->get(route('partner.services.index'))
            ->assertOk()
            ->assertSee('Layanan & tarif')
            ->assertSee('Budi Teknik')
            ->assertSee('Tambah layanan')
            ->assertSee('Hapus layanan ini?');
    }

    public function test_previous_routes_remain_available(): void
    {
        $this->get(route('partner.dashboard'))->assertOk();
        $this->get(route('partner.orders.index'))->assertOk();
        $this->get(route('providers.show', ['id' => 1]))->assertOk();
        $this->get(route('bookings.create', ['id' => 1]))->assertOk();
    }
}
