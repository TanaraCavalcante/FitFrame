<?php

namespace Tests\Feature\Backend;

use App\Models\Domain;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_cannot_view_strutture_list(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('/admin/strutture')->assertForbidden();
    }

    public function test_super_admin_can_create_a_gym_with_its_domain(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'pulse',
            'domain' => 'nuovapalestra.test',
        ]);

        $response->assertRedirect('/admin/strutture');
        $this->assertDatabaseHas('gyms', ['name' => 'Nuova Palestra', 'slug' => 'pulse']);
        $this->assertDatabaseHas('domains', ['domain' => 'nuovapalestra.test']);
    }

    public function test_slug_must_be_an_installed_theme(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'non-esiste',
            'domain' => 'nuovapalestra.test',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_super_admin_can_update_a_gym(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['slug' => 'pulse']);
        $domain = Domain::factory()->for($gym)->create(['domain' => 'vecchio.test']);

        $response = $this->actingAs($superAdmin)->put("/admin/strutture/{$gym->id}", [
            'name' => 'Nome Aggiornato',
            'slug' => 'zenflow',
            'domain' => 'nuovo.test',
        ]);

        $response->assertRedirect('/admin/strutture');
        $this->assertDatabaseHas('gyms', ['id' => $gym->id, 'name' => 'Nome Aggiornato', 'slug' => 'zenflow']);
        $this->assertDatabaseHas('domains', ['id' => $domain->id, 'domain' => 'nuovo.test']);
    }

    public function test_super_admin_cannot_delete_a_gym_with_assigned_admins(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        User::factory()->for($gym)->create();

        $response = $this->actingAs($superAdmin)->delete("/admin/strutture/{$gym->id}");

        $response->assertRedirect('/admin/strutture');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('gyms', ['id' => $gym->id]);
    }

    public function test_super_admin_can_delete_a_gym_without_admins(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $response = $this->actingAs($superAdmin)->delete("/admin/strutture/{$gym->id}");

        $response->assertRedirect('/admin/strutture');
        $this->assertDatabaseMissing('gyms', ['id' => $gym->id]);
    }
}
