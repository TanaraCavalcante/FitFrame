<?php

namespace Tests\Feature\Backend;

use App\Models\Domain;
use App\Models\Gym;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RouteWiringTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_homepage_still_resolves_gym_by_domain(): void
    {
        $gym = Gym::factory()->create(['slug' => 'pulse']);
        Domain::factory()->for($gym)->create(['domain' => 'academiaa.test']);

        $response = $this->get('http://academiaa.test/');

        $response->assertOk();
    }

    public function test_admin_prefix_is_reserved_and_returns_404_with_no_routes_yet(): void
    {
        $response = $this->get('/admin/nonexistent');

        $response->assertNotFound();
    }
}
