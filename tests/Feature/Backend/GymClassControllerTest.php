<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymClassControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_index_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        GymClass::factory()->for($gym)->create(['name' => 'HIIT']);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/corsi');

        $response->assertOk();
        $response->assertSee('HIIT');
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/corsi?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_gym_admin_cannot_manage_classes_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();
        $otherClass = GymClass::factory()->for($otherGym)->create();

        $this->actingAs($gymAdmin)->get("http://gestione.fitframe.test/setup/corsi/{$otherClass->id}/edit")->assertForbidden();
        $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/corsi/{$otherClass->id}", [
            'name' => 'Rubato', 'description' => 'x', 'icon' => 'fa-solid fa-bolt',
        ])->assertForbidden();
        $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/corsi/{$otherClass->id}")->assertForbidden();
    }

    public function test_a_class_can_be_created(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->post('http://gestione.fitframe.test/setup/corsi', [
            'gym_id' => $gym->id,
            'name' => 'HIIT',
            'description' => 'Allenamento a intervalli.',
            'icon' => 'fa-solid fa-bolt',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gym_classes', [
            'gym_id' => $gym->id,
            'name' => 'HIIT',
            'description' => 'Allenamento a intervalli.',
            'icon' => 'fa-solid fa-bolt',
        ]);
    }

    public function test_a_class_can_be_updated(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $class = GymClass::factory()->for($gym)->create(['name' => 'Vecchio nome']);

        $response = $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/corsi/{$class->id}", [
            'name' => 'Nuovo nome',
            'description' => $class->description,
            'icon' => $class->icon,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gym_classes', ['id' => $class->id, 'name' => 'Nuovo nome']);
    }

    public function test_a_class_can_be_deleted(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $class = GymClass::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/corsi/{$class->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('gym_classes', ['id' => $class->id]);
    }

    public function test_moving_a_class_up_swaps_order_with_the_previous_one(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $first = GymClass::factory()->for($gym)->create(['order' => 0]);
        $second = GymClass::factory()->for($gym)->create(['order' => 1]);

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/corsi/{$second->id}/move-up");

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }

    public function test_moving_the_first_class_up_does_nothing(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $first = GymClass::factory()->for($gym)->create(['order' => 0]);

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/corsi/{$first->id}/move-up");

        $this->assertSame(0, $first->fresh()->order);
    }

    public function test_classes_are_listed_in_order(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        GymClass::factory()->for($gym)->create(['name' => 'Secondo', 'order' => 1]);
        GymClass::factory()->for($gym)->create(['name' => 'Primo', 'order' => 0]);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/corsi');

        $response->assertOk();
        $response->assertSeeInOrder(['Primo', 'Secondo']);
    }
}
