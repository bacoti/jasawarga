<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PartnerProfile;
use Tests\TestCase;

class PartnerSettingsPageTest extends TestCase
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


    public function test_settings_page_contains_forms_read_only_verification_and_navigation(): void
    {
        $this->get(route('partner.settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan Akun Mitra')
            ->assertSee('Informasi pemilik akun')
            ->assertSee('Status verifikasi')
            ->assertSee('data-ps-password-form', false)
            ->assertSee('data-ps-availability', false)
            ->assertSee('Budi Teknik');
    }

    public function test_demo_entry_and_existing_partner_dashboard_remain_accessible(): void
    {
        $this->get(route('partner.demo.entry'))->assertRedirect(route('account.login', ['role'=>'partner']));
        $this->get(route('partner.dashboard'))->assertOk()
            ->assertSee('Ringkasan operasional');
    }

    public function test_settings_do_not_expose_server_mutation_endpoints(): void
    {
        $this->post('/mitra/pengaturan')->assertStatus(405);
    }
}
