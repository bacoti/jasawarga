<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderHistoryPageTest extends TestCase
{
    public function test_orders_list_is_available_without_login_or_database(): void
    {
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Pesanan Saya')
            ->assertSee('Tracking simulasi');
    }

    public function test_demo_order_detail_page_is_loaded_client_side(): void
    {
        $reference = 'JW-DEMO-20261107-A1B2C3';
        $this->get(route('orders.show', $reference))
            ->assertOk()
            ->assertSee($reference)
            ->assertSee('Tracking status pesanan');
    }

    public function test_invalid_detail_reference_is_not_a_valid_route(): void
    {
        $this->get('/pesanan-saya/nomor-acak')->assertNotFound();
    }

    public function test_previous_booking_and_success_pages_are_still_reachable(): void
    {
        $this->get(route('bookings.create', 1))->assertOk();
        $this->get(route('bookings.success'))->assertOk()->assertSee('Lihat Pesanan Saya');
    }

    public function test_only_get_routes_are_defined_for_history(): void
    {
        $this->post(route('orders.index'))->assertStatus(405);
    }
}
