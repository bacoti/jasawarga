<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PartnerProfile;
use Tests\TestCase;

class PartnerReviewsPageTest extends TestCase
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


    public function test_partner_reviews_page_displays_rating_and_reply_controls(): void
    {
        $this->get(route('partner.reviews.index'))
            ->assertOk()
            ->assertSee('Ulasan pelanggan')
            ->assertSee('Distribusi rating')
            ->assertSee('Penilaian detail')
            ->assertSee('Simpan balasan')
            ->assertSee('Budi Teknik');
    }

    public function test_public_profile_and_dashboard_navigation_remain_available(): void
    {
        $this->get(route('providers.show', ['id' => 1]))->assertOk()
            ->assertSee('data-provider-local-review-list', false);
        $this->get(route('partner.dashboard'))->assertOk()
            ->assertSee('Kelola ulasan pelanggan');
    }

    public function test_reviews_are_frontend_only_without_server_post_endpoint(): void
    {
        $this->post('/mitra/ulasan')->assertStatus(405);
    }
}
