<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_is_public_and_shows_the_screenshots(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('How this site came to be');
        $response->assertSee('images/about/2022-plain-php.webp');
    }

    public function test_hamsters_are_only_loaded_on_the_about_page(): void
    {
        $this->get('/about')->assertSee('images/about/hamster.gif');
        $this->get('/')->assertDontSee('images/about/hamster.gif');
    }

    public function test_footer_links_to_about_page(): void
    {
        $this->get('/')->assertSee(route('about'));
    }
}
