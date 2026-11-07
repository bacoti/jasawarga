<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_demo_providers_have_detail_pages(): void
    {
        foreach (config('jasawarga.providers') as $provider) {
            $this->get(route('providers.show', $provider['id']))
                ->assertOk()
                ->assertSee($provider['name']);
        }
    }

    public function test_unknown_provider_returns_not_found(): void
    {
        $this->get('/mitra/999')->assertNotFound();
        $this->get('/mitra/bukan-angka')->assertNotFound();
    }

    public function test_categories_link_to_provider_detail(): void
    {
        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee(route('providers.show', 1));
    }
}
