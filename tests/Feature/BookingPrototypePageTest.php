<?php

namespace Tests\Feature;

use Tests\TestCase;

class BookingPrototypePageTest extends TestCase
{
    public function test_each_demo_provider_has_booking_form(): void
    {
        foreach (config('jasawarga.providers') as $provider) {
            $this->get(route('bookings.create', $provider['id']))
                ->assertOk()
                ->assertSee('Lengkapi pemesanan jasa')
                ->assertSee($provider['name']);
        }
    }

    public function test_booking_form_preselects_service_from_query_string(): void
    {
        $this->get(route('bookings.create', ['id' => 1, 'layanan' => 2]))
            ->assertOk()
            ->assertSee('Bongkar pasang AC')
            ->assertSee('value="2"', false);
    }

    public function test_invalid_service_index_falls_back_to_first_service(): void
    {
        $this->get('/pemesanan/1?layanan=999')
            ->assertOk()
            ->assertSee('Cuci AC standar');
    }

    public function test_unknown_provider_cannot_create_booking(): void
    {
        $this->get('/pemesanan/999')->assertNotFound();
        $this->get('/pemesanan/abc')->assertNotFound();
    }

    public function test_success_page_is_a_frontend_placeholder_without_database(): void
    {
        $this->get(route('bookings.success'))
            ->assertOk()
            ->assertSee('Simulasi pemesanan berhasil');
    }

    public function test_provider_detail_and_catalog_offer_booking_links(): void
    {
        $this->get(route('providers.show', 1))
            ->assertOk()
            ->assertSee(route('bookings.create', 1), false);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee(route('bookings.create', 1), false);
    }
}
