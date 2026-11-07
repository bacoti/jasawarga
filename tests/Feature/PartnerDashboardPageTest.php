<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PartnerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerDashboardPageTest extends TestCase
{
    use RefreshDatabase;

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


    public function test_partner_dashboard_renders_without_database(): void
    {
        $this->get(route('partner.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Mitra')
            ->assertSee('Budi Teknik')
            ->assertSee('Ringkasan operasional')
            ->assertSee('Status ketersediaan');
    }

    public function test_existing_warga_pages_remain_available(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('orders.index'))->assertOk();
        $this->get(route('providers.show', ['id' => 1]))->assertOk();
    }

    public function test_partner_dashboard_does_not_replace_numeric_provider_route(): void
    {
        $this->get('/mitra/dashboard')->assertOk();
        $this->get('/mitra/999999')->assertNotFound();
    }
}
