<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BandungNavigationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_public_catalog_and_provider_profile(): void
    {
        $this->get('/')->assertOk();
        $this->get('/kategori-jasa')->assertOk();
        $this->get('/mitra/1')->assertOk();
    }

    public function test_guest_cannot_open_legacy_booking_order_history_or_review(): void
    {
        foreach (['/pemesanan/1','/pesanan-saya','/ulasan-saya'] as $path) {
            $this->get($path)->assertRedirect(route('account.login'));
        }
    }

    public function test_visitor_with_partner_role_cannot_use_citizen_booking(): void
    {
        $mitra = User::create(['name'=>'Mitra Uji', 'email'=>'mitra-route@example.test', 'role'=>User::ROLE_PARTNER, 'account_status'=>'active', 'password'=>'TestingPassword123']);
        $this->actingAs($mitra)->get('/pemesanan/1')->assertForbidden();
        $this->actingAs($mitra)->get('/pesanan-saya')->assertForbidden();
    }

    public function test_citizen_can_open_legacy_booking_and_citizen_dashboard(): void
    {
        $warga = User::create(['name'=>'Warga Uji', 'email'=>'warga-route@example.test', 'role'=>User::ROLE_CITIZEN, 'account_status'=>'active', 'password'=>'TestingPassword123']);
        $this->actingAs($warga)->get('/pemesanan/1')->assertOk();
        $this->actingAs($warga)->get('/pesanan-saya')->assertOk();
    }
}
