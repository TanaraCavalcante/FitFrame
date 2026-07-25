<?php

namespace Tests\Feature\Backend;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SuperAdminControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_cannot_view_super_admin_list(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('/super-admin')->assertForbidden();
    }

    public function test_super_admin_can_create_another_super_admin_without_a_gym(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/super-admin', [
            'name' => 'Nuovo Super',
            'email' => 'nuovosuper@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/super-admin');
        $this->assertDatabaseHas('users', [
            'email' => 'nuovosuper@example.test',
            'role' => UserRole::SuperAdmin,
            'gym_id' => null,
        ]);
    }

    public function test_super_admin_can_update_another_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $other = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->put("/super-admin/{$other->id}", [
            'name' => 'Nome Cambiato',
            'email' => $other->email,
        ]);

        $response->assertRedirect('/super-admin');
        $this->assertDatabaseHas('users', ['id' => $other->id, 'name' => 'Nome Cambiato']);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->delete("/super-admin/{$superAdmin->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_super_admin_can_delete_another_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $other = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->delete("/super-admin/{$other->id}");

        $response->assertRedirect('/super-admin');
        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    public function test_the_utenti_route_returns_404_for_a_super_admin_id(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $other = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get("/utenti/{$other->id}/edit")->assertNotFound();
    }
}
