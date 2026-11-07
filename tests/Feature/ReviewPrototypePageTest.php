<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReviewPrototypePageTest extends TestCase
{
    public function test_review_history_and_form_are_available_without_database(): void
    {
        $this->get(route('reviews.index'))
            ->assertOk()
            ->assertSee('Riwayat Ulasan Saya');

        $reference = 'JW-DEMO-20261107-A1B2C3';
        $this->get(route('reviews.create', $reference))
            ->assertOk()
            ->assertSee('Rating keseluruhan')
            ->assertSee($reference);
    }

    public function test_review_form_is_read_only_route_without_post_endpoint(): void
    {
        $reference = 'JW-DEMO-20261107-A1B2C3';
        $this->post(route('reviews.create', $reference))->assertStatus(405);
        $this->get('/ulasan/tulis/tidak-valid')->assertNotFound();
    }

    public function test_review_links_appear_on_order_and_provider_pages(): void
    {
        $this->get(route('orders.show', 'JW-DEMO-20261107-A1B2C3'))
            ->assertOk()->assertSee('Beri rating');
        $this->get(route('providers.show', 1))
            ->assertOk()->assertSee('Ulasan dari simulasi Anda');
    }
}
