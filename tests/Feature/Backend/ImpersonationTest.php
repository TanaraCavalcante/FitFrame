<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_impersonate_a_gym_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($superAdmin)
            ->get(route('backend.impersonate', $gymAdmin->id))
            ->assertRedirect();

        $this->assertAuthenticatedAs($gymAdmin);
    }

    public function test_leaving_impersonation_restores_the_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($superAdmin)->get(route('backend.impersonate', $gymAdmin->id));
        $this->get(route('backend.impersonate.leave'));

        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_a_gym_admin_cannot_impersonate_anyone(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $anotherGymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)
            ->get(route('backend.impersonate', $anotherGymAdmin->id))
            ->assertForbidden();
    }

    public function test_a_super_admin_cannot_impersonate_another_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $anotherSuperAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('backend.impersonate', $anotherSuperAdmin->id))
            ->assertRedirect();

        $this->assertAuthenticatedAs($superAdmin);
    }
}
