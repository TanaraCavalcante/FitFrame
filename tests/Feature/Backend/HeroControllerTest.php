<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_hero_page_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/hero')->assertOk();
    }

    public function test_gym_admin_cannot_update_the_hero_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();

        $response = $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $otherGym->id,
            'visual_mode' => 'images',
            'hero_title' => 'Titolo rubato',
        ]);

        $response->assertForbidden();
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/hero?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_updating_text_fields_persists_to_contents(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'images',
            'hero_title' => 'Nuovo titolo',
            'hero_subtitle' => 'Nuovo sottotitolo',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'hero_title', 'value' => 'Nuovo titolo']);
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'hero_subtitle', 'value' => 'Nuovo sottotitolo']);
    }

    public function test_leaving_a_field_empty_removes_the_existing_override(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->contents()->create(['key' => 'hero_title', 'value' => 'Vecchio titolo']);

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'images',
            'hero_title' => '',
        ]);

        $this->assertDatabaseMissing('contents', ['gym_id' => $gym->id, 'key' => 'hero_title']);
    }

    public function test_uploading_images_stores_them_in_the_three_slots(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'images',
            'image_1' => UploadedFile::fake()->image('uno.jpg'),
            'image_2' => UploadedFile::fake()->image('due.jpg'),
        ]);

        $gym->refresh();
        $this->assertNotNull($gym->getFirstMedia('hero_image_1'));
        $this->assertNotNull($gym->getFirstMedia('hero_image_2'));
        $this->assertNull($gym->getFirstMedia('hero_image_3'));
    }

    public function test_removing_an_image_slot_clears_it(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->addMedia(UploadedFile::fake()->image('vecchia.jpg'))->toMediaCollection('hero_image_1');

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'images',
            'remove_image_1' => '1',
        ]);

        $this->assertNull($gym->fresh()->getFirstMedia('hero_image_1'));
    }

    public function test_removing_the_video_clears_it(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->addMedia(UploadedFile::fake()->create('vecchio.mp4', 1024, 'video/mp4'))->toMediaCollection('hero_video');

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'video',
            'remove_video' => '1',
        ]);

        $this->assertNull($gym->fresh()->getFirstMedia('hero_video'));
    }

    public function test_uploading_a_video_keeps_existing_images_intact(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->addMedia(UploadedFile::fake()->image('vecchia.jpg'))->toMediaCollection('hero_image_1');

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'video',
            'video' => UploadedFile::fake()->create('video.mp4', 1024, 'video/mp4'),
        ]);

        $gym->refresh();
        $this->assertNotNull($gym->getFirstMedia('hero_image_1'));
        $this->assertNotNull($gym->getFirstMedia('hero_video'));
    }

    public function test_selecting_images_mode_keeps_an_existing_video_intact(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->addMedia(UploadedFile::fake()->create('vecchio.mp4', 1024, 'video/mp4'))->toMediaCollection('hero_video');

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'images',
            'image_1' => UploadedFile::fake()->image('nuova.jpg'),
        ]);

        $gym->refresh();
        $this->assertNotNull($gym->getFirstMedia('hero_video'));
        $this->assertNotNull($gym->getFirstMedia('hero_image_1'));
    }

    public function test_visual_mode_is_persisted_to_contents(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/hero', [
            'gym_id' => $gym->id,
            'visual_mode' => 'video',
        ]);

        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'hero_visual_mode', 'value' => 'video']);
    }
}
