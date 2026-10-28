<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpressumPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_images_are_marked_as_protected(): void
    {
        $response = $this->get('/impressum');

        $response->assertStatus(200);
        $response->assertSee('width="130" data-protected>', false);
        $response->assertSee('width="155" data-protected>', false);
    }
}
