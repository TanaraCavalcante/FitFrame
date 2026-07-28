<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TestimonialControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_index_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        Testimonial::factory()->for($gym)->create(['author_name' => 'Marco Ferrari']);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/testimonianze');

        $response->assertOk();
        $response->assertSee('Marco Ferrari');
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/testimonianze?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_gym_admin_cannot_manage_testimonials_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();
        $otherTestimonial = Testimonial::factory()->for($otherGym)->create();

        $this->actingAs($gymAdmin)->get("http://gestione.fitframe.test/setup/testimonianze/{$otherTestimonial->id}/edit")->assertForbidden();
        $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/testimonianze/{$otherTestimonial->id}", [
            'author_name' => 'Rubato', 'text' => 'x',
        ])->assertForbidden();
        $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/testimonianze/{$otherTestimonial->id}")->assertForbidden();
    }

    public function test_a_testimonial_can_be_created(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->post('http://gestione.fitframe.test/setup/testimonianze', [
            'gym_id' => $gym->id,
            'author_name' => 'Marco Ferrari',
            'text' => 'Palestra fantastica, staff eccezionale.',
            'member_since' => 'Cliente dal 2022',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('testimonials', [
            'gym_id' => $gym->id,
            'author_name' => 'Marco Ferrari',
            'text' => 'Palestra fantastica, staff eccezionale.',
            'member_since' => 'Cliente dal 2022',
        ]);
    }

    public function test_a_testimonial_can_be_updated(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $testimonial = Testimonial::factory()->for($gym)->create(['author_name' => 'Vecchio nome']);

        $response = $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/testimonianze/{$testimonial->id}", [
            'author_name' => 'Nuovo nome',
            'text' => $testimonial->text,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'author_name' => 'Nuovo nome']);
    }

    public function test_a_testimonial_can_be_deleted(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $testimonial = Testimonial::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/testimonianze/{$testimonial->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    public function test_moving_a_testimonial_up_swaps_order_with_the_previous_one(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $first = Testimonial::factory()->for($gym)->create(['order' => 0]);
        $second = Testimonial::factory()->for($gym)->create(['order' => 1]);

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/testimonianze/{$second->id}/move-up");

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }

    public function test_testimonials_are_listed_in_order(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        Testimonial::factory()->for($gym)->create(['author_name' => 'Secondo', 'order' => 1]);
        Testimonial::factory()->for($gym)->create(['author_name' => 'Primo', 'order' => 0]);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/testimonianze');

        $response->assertOk();
        $response->assertSeeInOrder(['Primo', 'Secondo']);
    }
}
