<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_renders_both_responsive_experiences(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('JasaWarga')
            ->assertSee('Layanan Gotong Royong')
            ->assertSee('Kategori Layanan Populer')
            ->assertSee('Budi Teknik');
    }
}
