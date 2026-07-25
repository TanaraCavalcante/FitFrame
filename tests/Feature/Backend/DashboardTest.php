<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_sees_the_shared_dashboard(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('http://gestione.fitframe.test/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertDontSee('Strutture');
        $response->assertDontSee('Utenti');
    }

    public function test_gym_admin_sees_the_shared_dashboard(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertDontSee('Strutture');
        $response->assertDontSee('Utenti');
    }

    public function test_layout_ships_the_sidebar_toggle_and_theme_switch(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('http://gestione.fitframe.test/dashboard');

        $response->assertOk();
        $response->assertSee('id="sidebar-toggle"', false);
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('backend.css', false);
        $response->assertSee('backend.js', false);
    }

    public function test_backend_root_redirects_authenticated_users_to_dashboard_path(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get('http://gestione.fitframe.test/')
            ->assertRedirect('http://gestione.fitframe.test/dashboard');
    }
}
