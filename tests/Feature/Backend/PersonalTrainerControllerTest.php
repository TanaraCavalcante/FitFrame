<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\PersonalTrainer;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonalTrainerControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_index_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        PersonalTrainer::factory()->for($gym)->create(['name' => 'Marco Ferrari']);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/team');

        $response->assertOk();
        $response->assertSee('Marco Ferrari');
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/team?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_gym_admin_cannot_manage_trainers_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();
        $otherTrainer = PersonalTrainer::factory()->for($otherGym)->create();

        $this->actingAs($gymAdmin)->get("http://gestione.fitframe.test/setup/team/{$otherTrainer->id}/edit")->assertForbidden();
        $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/team/{$otherTrainer->id}", [
            'name' => 'Rubato', 'specialty' => 'x',
        ])->assertForbidden();
        $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/team/{$otherTrainer->id}")->assertForbidden();
    }

    public function test_a_trainer_can_be_created(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->post('http://gestione.fitframe.test/setup/team', [
            'gym_id' => $gym->id,
            'name' => 'Marco Ferrari',
            'specialty' => 'Musculação',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('personal_trainers', [
            'gym_id' => $gym->id,
            'name' => 'Marco Ferrari',
            'specialty' => 'Musculação',
        ]);
    }

    public function test_a_trainer_can_be_updated(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $trainer = PersonalTrainer::factory()->for($gym)->create(['name' => 'Vecchio nome']);

        $response = $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/team/{$trainer->id}", [
            'name' => 'Nuovo nome',
            'specialty' => $trainer->specialty,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('personal_trainers', ['id' => $trainer->id, 'name' => 'Nuovo nome']);
    }

    public function test_a_trainer_can_be_deleted(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $trainer = PersonalTrainer::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/team/{$trainer->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('personal_trainers', ['id' => $trainer->id]);
    }

    public function test_moving_a_trainer_up_swaps_order_with_the_previous_one(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $first = PersonalTrainer::factory()->for($gym)->create(['order' => 0]);
        $second = PersonalTrainer::factory()->for($gym)->create(['order' => 1]);

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/team/{$second->id}/move-up");

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }

    public function test_trainers_are_listed_in_order(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        PersonalTrainer::factory()->for($gym)->create(['name' => 'Secondo', 'order' => 1]);
        PersonalTrainer::factory()->for($gym)->create(['name' => 'Primo', 'order' => 0]);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/team');

        $response->assertOk();
        $response->assertSeeInOrder(['Primo', 'Secondo']);
    }

    public function test_uploading_a_photo_stores_it_in_the_media_collection(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->post('http://gestione.fitframe.test/setup/team', [
            'gym_id' => $gym->id,
            'name' => 'Marco Ferrari',
            'specialty' => 'Musculação',
            'photo' => UploadedFile::fake()->image('marco.jpg'),
        ]);

        $trainer = PersonalTrainer::where('name', 'Marco Ferrari')->firstOrFail();
        $this->assertNotNull($trainer->getFirstMedia('photo'));
    }

    public function test_removing_the_photo_clears_it(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $trainer = PersonalTrainer::factory()->for($gym)->create();
        $trainer->addMedia(UploadedFile::fake()->image('vecchia.jpg'))->toMediaCollection('photo');

        $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/team/{$trainer->id}", [
            'name' => $trainer->name,
            'specialty' => $trainer->specialty,
            'remove_photo' => '1',
        ]);

        $this->assertNull($trainer->fresh()->getFirstMedia('photo'));
    }
}
