<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CtaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_cta_page_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/cta')->assertOk();
    }

    public function test_gym_admin_cannot_update_the_cta_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();

        $response = $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/cta', [
            'gym_id' => $otherGym->id,
            'cta_title' => 'Titolo rubato',
            'address' => 'Via Roma 1',
            'phone' => '123456789',
            'hours' => 'Lun-Ven 06h-22h',
        ]);

        $response->assertForbidden();
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/cta?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_updating_text_fields_persists_to_contents(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/cta', [
            'gym_id' => $gym->id,
            'cta_title' => 'Nuovo titolo',
            'cta_subtitle' => 'Nuovo sottotitolo',
            'cta_button' => 'Prenota',
            'footer_slogan' => 'Nuovo slogan',
            'address' => 'Via Roma 1',
            'phone' => '123456789',
            'hours' => 'Lun-Ven 06h-22h',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'cta_title', 'value' => 'Nuovo titolo']);
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'cta_subtitle', 'value' => 'Nuovo sottotitolo']);
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'cta_button', 'value' => 'Prenota']);
        $this->assertDatabaseHas('contents', ['gym_id' => $gym->id, 'key' => 'footer_slogan', 'value' => 'Nuovo slogan']);
    }

    public function test_leaving_a_field_empty_removes_the_existing_override(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->contents()->create(['key' => 'cta_title', 'value' => 'Vecchio titolo']);

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/cta', [
            'gym_id' => $gym->id,
            'cta_title' => '',
            'address' => 'Via Roma 1',
            'phone' => '123456789',
            'hours' => 'Lun-Ven 06h-22h',
        ]);

        $this->assertDatabaseMissing('contents', ['gym_id' => $gym->id, 'key' => 'cta_title']);
    }

    public function test_updating_contact_fields_persists_to_contacts(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $gym->contact()->create([
            'address' => 'Via Vecchia 1',
            'phone' => '000000000',
            'hours' => 'Vecchi orari',
        ]);

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/cta', [
            'gym_id' => $gym->id,
            'address' => 'Via Nuova 10',
            'email' => 'info@example.com',
            'phone' => '333123456',
            'whatsapp' => '333123456',
            'instagram' => '@pulse.gym',
            'hours' => 'Lun-Ven 06h-22h, Sab 08h-14h',
        ]);

        $this->assertDatabaseHas('contacts', [
            'gym_id' => $gym->id,
            'address' => 'Via Nuova 10',
            'email' => 'info@example.com',
            'phone' => '333123456',
            'whatsapp' => '333123456',
            'instagram' => '@pulse.gym',
            'hours' => 'Lun-Ven 06h-22h, Sab 08h-14h',
        ]);
    }

    public function test_contact_fields_are_created_on_first_save_when_the_gym_has_no_contact_row_yet(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->assertNull($gym->contact);

        $this->actingAs($gymAdmin)->put('http://gestione.fitframe.test/setup/cta', [
            'gym_id' => $gym->id,
            'address' => 'Via Nuova 10',
            'phone' => '333123456',
            'hours' => 'Lun-Ven 06h-22h',
        ]);

        $this->assertDatabaseHas('contacts', [
            'gym_id' => $gym->id,
            'address' => 'Via Nuova 10',
            'phone' => '333123456',
            'hours' => 'Lun-Ven 06h-22h',
        ]);
    }
}
