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

        $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/strutture')->assertForbidden();
    }

    public function test_create_form_can_be_rendered(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get('http://gestione.fitframe.test/strutture/create')->assertOk();
    }

    public function test_edit_form_can_be_rendered(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/strutture/{$gym->id}/edit")->assertOk();
    }

    public function test_super_admin_can_create_a_gym_with_its_domain(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('http://gestione.fitframe.test/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'pulse',
            'domain' => 'nuovapalestra.test',
        ]);

        $response->assertRedirect('http://gestione.fitframe.test/strutture');
        $this->assertDatabaseHas('gyms', ['name' => 'Nuova Palestra', 'slug' => 'pulse']);
        $this->assertDatabaseHas('domains', ['domain' => 'nuovapalestra.test']);
    }

    public function test_slug_does_not_need_to_be_an_installed_theme(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('http://gestione.fitframe.test/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'non-esiste',
            'domain' => 'nuovapalestra.test',
        ]);

        $response->assertRedirect('http://gestione.fitframe.test/strutture');
        $this->assertDatabaseHas('gyms', ['name' => 'Nuova Palestra', 'slug' => 'non-esiste']);
    }

    public function test_slug_must_be_a_valid_identifier_format(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('http://gestione.fitframe.test/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'non valido!',
            'domain' => 'nuovapalestra.test',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_super_admin_can_update_a_gym(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['slug' => 'pulse']);
        $domain = Domain::factory()->for($gym)->create(['domain' => 'vecchio.test']);

        $response = $this->actingAs($superAdmin)->put("http://gestione.fitframe.test/strutture/{$gym->id}", [
            'name' => 'Nome Aggiornato',
            'slug' => 'zenflow',
            'domain' => 'nuovo.test',
        ]);

        $response->assertRedirect('http://gestione.fitframe.test/strutture');
        $this->assertDatabaseHas('gyms', ['id' => $gym->id, 'name' => 'Nome Aggiornato', 'slug' => 'zenflow']);
        $this->assertDatabaseHas('domains', ['id' => $domain->id, 'domain' => 'nuovo.test']);
    }

    public function test_super_admin_cannot_delete_a_gym_with_assigned_admins(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();
        User::factory()->for($gym)->create();

        $response = $this->actingAs($superAdmin)->delete("http://gestione.fitframe.test/strutture/{$gym->id}");

        $response->assertRedirect('http://gestione.fitframe.test/strutture');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('gyms', ['id' => $gym->id]);
    }

    public function test_super_admin_can_delete_a_gym_without_admins(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $response = $this->actingAs($superAdmin)->delete("http://gestione.fitframe.test/strutture/{$gym->id}");

        $response->assertRedirect('http://gestione.fitframe.test/strutture');
        $this->assertDatabaseMissing('gyms', ['id' => $gym->id]);
    }
}
