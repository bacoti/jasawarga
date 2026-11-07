<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_catalog_displays_demo_providers(): void
    {
        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Kategori Jasa')
            ->assertSee('Budi Teknik')
            ->assertSee('Elektrik Jaya Makassar')
            ->assertSee('Jahit Bu Nia');
    }
}
