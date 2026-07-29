<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\GymSection;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymSectionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_index_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        GymSection::factory()->for($gym)->create(['section' => 'classes', 'order' => 0]);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/order');

        $response->assertOk();
        $response->assertSee('Corsi');
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/order?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_gym_admin_cannot_reorder_sections_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();
        $otherSection = GymSection::factory()->for($otherGym)->create();

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/order/{$otherSection->id}/move-up")->assertForbidden();
        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/order/{$otherSection->id}/move-down")->assertForbidden();
    }

    public function test_moving_a_section_up_swaps_order_with_the_previous_one(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $first = GymSection::factory()->for($gym)->create(['section' => 'classes', 'order' => 0]);
        $second = GymSection::factory()->for($gym)->create(['section' => 'plans', 'order' => 1]);

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/order/{$second->id}/move-up");

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }

    public function test_moving_the_first_section_up_does_nothing(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $first = GymSection::factory()->for($gym)->create(['order' => 0]);

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/order/{$first->id}/move-up");

        $this->assertSame(0, $first->fresh()->order);
    }

    public function test_sections_are_listed_in_order(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        GymSection::factory()->for($gym)->create(['section' => 'plans', 'order' => 1]);
        GymSection::factory()->for($gym)->create(['section' => 'classes', 'order' => 0]);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/order');

        $response->assertOk();
        $response->assertSeeInOrder(['Corsi', 'Piani']);
    }

    public function test_gym_admin_can_update_section_titles(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/order', [
            'gym_id' => $gym->id,
            'classes_title' => 'I nostri corsi',
            'plans_title' => 'I nostri piani',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'classes_title', 'value' => 'I nostri corsi']);
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'plans_title', 'value' => 'I nostri piani']);
    }

    public function test_leaving_a_title_empty_removes_the_existing_override(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->contents()->create(['key' => 'classes_title', 'value' => 'Vecchio titolo']);

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/order', [
            'gym_id' => $gym->id,
            'classes_title' => '',
        ]);

        $this->assertDatabaseMissing('contents', ['gym_id' => $gym->id, 'key' => 'classes_title']);
    }

    public function test_gym_admin_cannot_update_titles_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/order', [
            'gym_id' => $otherGym->id,
            'classes_title' => 'Rubato',
        ])->assertForbidden();
    }

    public function test_creating_a_gym_seeds_the_default_section_order(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post('http://gestione.fitframe.test/strutture', [
            'name' => 'Nuova Palestra',
            'slug' => 'nuova-palestra',
            'domain' => 'nuova-palestra.test',
        ]);

        $gym = Gym::where('slug', 'nuova-palestra')->firstOrFail();

        $this->assertSame(GymSection::DEFAULT_ORDER, $gym->gymSections()->pluck('section')->all());
    }
}
