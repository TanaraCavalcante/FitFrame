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

        $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/utenti')->assertForbidden();
    }

    public function test_super_admin_can_create_a_gym_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $response = $this->actingAs($superAdmin)->post('http://gestione.fitframe.test/utenti', [
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario@example.test',
            'password' => 'password123',
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('http://gestione.fitframe.test/utenti');
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

        $response = $this->actingAs($superAdmin)->post('http://gestione.fitframe.test/utenti', [
            'name' => 'Secondo',
            'surname' => 'Admin',
            'email' => 'secondo@example.test',
            'password' => 'password123',
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('http://gestione.fitframe.test/utenti');
        $this->assertSame(2, $gym->admins()->count());
    }

    public function test_updating_without_a_password_keeps_the_old_one(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $originalHash = $gymAdmin->password;

        $response = $this->actingAs($superAdmin)->put("http://gestione.fitframe.test/utenti/{$gymAdmin->id}", [
            'name' => 'Nome',
            'surname' => 'Cambiato',
            'email' => $gymAdmin->email,
            'gym_id' => $gym->id,
        ]);

        $response->assertRedirect('http://gestione.fitframe.test/utenti');
        $this->assertSame($originalHash, $gymAdmin->fresh()->password);
    }

    public function test_utenti_routes_404_for_a_super_admin_id(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherSuperAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/utenti/{$otherSuperAdmin->id}/edit")->assertNotFound();

        // PUT goes through UpdateGymAdminRequest::authorize() first, which now correctly
        // rejects a non-GymAdmin target via the tightened UserPolicy::update() — that's a
        // 403 (AuthorizationException), not a 404, since it's blocked before the
        // controller's own abort_unless(404) line ever runs. GET/DELETE have no
        // FormRequest in front of them, so their abort_unless is what fires, giving 404.
        // Either way the action is blocked — this just documents which layer catches it.
        $this->actingAs($superAdmin)->put("http://gestione.fitframe.test/utenti/{$otherSuperAdmin->id}", [
            'name' => 'X', 'email' => $otherSuperAdmin->email, 'gym_id' => Gym::factory()->create()->id,
        ])->assertForbidden();

        $this->actingAs($superAdmin)->delete("http://gestione.fitframe.test/utenti/{$otherSuperAdmin->id}")->assertNotFound();
    }
}
