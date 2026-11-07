<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PartnerProfile;
use Tests\TestCase;

class PartnerSchedulePageTest extends TestCase
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


    public function test_schedule_page_renders_without_database(): void
    {
        $this->get(route('partner.schedule.index'))
            ->assertOk()
            ->assertSee('Jadwal & ketersediaan')
            ->assertSee('Budi Teknik')
            ->assertSee('Simpan jadwal')
            ->assertSee('Kalender pekerjaan')
            ->assertSee('Tanggal libur & cuti');
    }

    public function test_booking_and_partner_pages_still_render(): void
    {
        $this->get(route('partner.dashboard'))->assertOk();
        $this->get(route('partner.orders.index'))->assertOk();
        $this->get(route('partner.services.index'))->assertOk();
        $this->get(route('bookings.create', ['id' => 1]))->assertOk();
    }
}
