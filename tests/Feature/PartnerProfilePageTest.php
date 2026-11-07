<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PartnerProfile;
use Tests\TestCase;

class PartnerProfilePageTest extends TestCase
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


    public function test_partner_profile_editor_renders_with_read_only_verification(): void
    {
        $this->get(route('partner.profile.index'))
            ->assertOk()
            ->assertSee('Profil & portofolio')
            ->assertSee('Nama usaha')
            ->assertSee('Area jangkauan')
            ->assertSee('Tambah portofolio')
            ->assertSee('Hanya dapat dibaca')
            ->assertSee('Budi Teknik');
    }

    public function test_public_profile_and_existing_partner_pages_still_render(): void
    {
        $this->get(route('providers.show', ['id' => 1]))->assertOk()
            ->assertSee('data-public-projects', false);
        $this->get(route('partner.dashboard'))->assertOk();
        $this->get(route('partner.orders.index'))->assertOk();
        $this->get(route('partner.services.index'))->assertOk();
        $this->get(route('partner.schedule.index'))->assertOk();
    }
}
