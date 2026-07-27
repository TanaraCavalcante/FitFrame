<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_gallery_page_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/gallery')->assertOk();
    }

    public function test_gym_admin_cannot_update_the_gallery_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();

        $response = $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/gallery', [
            'gym_id' => $otherGym->id,
        ]);

        $response->assertForbidden();
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/gallery?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_uploading_images_stores_them_in_the_five_slots(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/gallery', [
            'gym_id' => $gym->id,
            'image_1' => UploadedFile::fake()->image('uno.jpg'),
            'image_5' => UploadedFile::fake()->image('cinque.jpg'),
        ]);

        $gym->refresh();
        $this->assertNotNull($gym->getFirstMedia('gallery_image_1'));
        $this->assertNotNull($gym->getFirstMedia('gallery_image_5'));
        $this->assertNull($gym->getFirstMedia('gallery_image_2'));
    }

    public function test_removing_an_image_slot_clears_it(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->addMedia(UploadedFile::fake()->image('vecchia.jpg'))->toMediaCollection('gallery_image_1');

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/gallery', [
            'gym_id' => $gym->id,
            'remove_image_1' => '1',
        ]);

        $this->assertNull($gym->fresh()->getFirstMedia('gallery_image_1'));
    }
}
