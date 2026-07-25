<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_sees_platform_menu(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Strutture');
        $response->assertSee('Utenti');
        $response->assertSee('Super Admin');
    }

    public function test_gym_admin_does_not_see_platform_menu(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->get('/admin');

        $response->assertOk();
        $response->assertDontSee('Strutture');
        $response->assertDontSee('Utenti');
        $response->assertDontSee('Super Admin');
    }

    public function test_layout_ships_the_sidebar_toggle_and_theme_switch(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertSee('id="sidebar-toggle"', false);
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('backend.css', false);
        $response->assertSee('backend.js', false);
    }
}
