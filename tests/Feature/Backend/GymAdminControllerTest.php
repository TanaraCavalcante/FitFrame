<?php

namespace Tests\Feature\Backend;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymAdminControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_cannot_view_utenti_list(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('/admin/utenti')->assertForbidden();
    }

    public function test_super_admin_can_create_a_gym_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/utenti', [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.test',
            'password' => 'password123',
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('/admin/utenti');
        $this->assertDatabaseHas('users', [
            'email' => 'mario@example.test',
            'role' => UserRole::GymAdmin,
            'gym_id' => $gym->id,
        ]);
    }

    public function test_a_gym_can_have_more_than_one_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        User::factory()->for($gym)->create();

        $response = $this->actingAs($superAdmin)->post('/admin/utenti', [
            'name' => 'Secondo Admin',
            'email' => 'secondo@example.test',
            'password' => 'password123',
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('/admin/utenti');
        $this->assertSame(2, $gym->admins()->count());
    }

    public function test_updating_without_a_password_keeps_the_old_one(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $originalHash = $gymAdmin->password;

        $response = $this->actingAs($superAdmin)->put("/admin/utenti/{$gymAdmin->id}", [
            'name' => 'Nome Cambiato',
            'email' => $gymAdmin->email,
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('/admin/utenti');
        $this->assertSame($originalHash, $gymAdmin->fresh()->password);
    }

    public function test_the_super_admin_route_returns_404_for_a_gym_admin_id(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($superAdmin)->get("/admin/super-admin/{$gymAdmin->id}/edit")->assertNotFound();
    }
}
