<?php

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PlanControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gym_admin_can_view_the_index_for_their_own_gym(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        Plan::factory()->for($gym)->create(['name' => 'Pro']);

        $response = $this->actingAs($gymAdmin)->get('http://gestione.fitframe.test/setup/piani');

        $response->assertOk();
        $response->assertSee('Pro');
    }

    public function test_super_admin_can_select_a_gym_via_the_dropdown(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create(['name' => 'Pulse Gym']);

        $response = $this->actingAs($superAdmin)->get("http://gestione.fitframe.test/setup/piani?gym_id={$gym->id}");

        $response->assertOk();
        $response->assertSee('Pulse Gym');
    }

    public function test_gym_admin_cannot_manage_plans_of_another_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();
        $otherPlan = Plan::factory()->for($otherGym)->create();

        $this->actingAs($gymAdmin)->get("http://gestione.fitframe.test/setup/piani/{$otherPlan->id}/edit")->assertForbidden();
        $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/piani/{$otherPlan->id}", [
            'name' => 'Rubato', 'price' => 10, 'features' => ['x'],
        ])->assertForbidden();
        $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/piani/{$otherPlan->id}")->assertForbidden();
    }

    public function test_a_plan_can_be_created_with_features(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->post('http://gestione.fitframe.test/setup/piani', [
            'gym_id' => $gym->id,
            'name' => 'Pro',
            'price' => 49.90,
            'features' => ['Accesso illimitato', 'Personal trainer'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('plans', ['gym_id' => $gym->id, 'name' => 'Pro']);
        $plan = Plan::where('name', 'Pro')->firstOrFail();
        $this->assertSame(['Accesso illimitato', 'Personal trainer'], $plan->planFeatures->pluck('description')->all());
    }

    public function test_a_plan_can_be_updated_and_features_are_replaced(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $plan = Plan::factory()->for($gym)->create(['name' => 'Vecchio']);
        $plan->planFeatures()->create(['description' => 'Vecchia feature', 'order' => 0]);

        $response = $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/piani/{$plan->id}", [
            'name' => 'Nuovo',
            'price' => 59.90,
            'features' => ['Nuova feature'],
        ]);

        $response->assertRedirect();
        $plan->refresh();
        $this->assertSame('Nuovo', $plan->name);
        $this->assertSame(['Nuova feature'], $plan->planFeatures->pluck('description')->all());
    }

    public function test_a_plan_can_be_deleted(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $plan = Plan::factory()->for($gym)->create();

        $response = $this->actingAs($gymAdmin)->delete("http://gestione.fitframe.test/setup/piani/{$plan->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    public function test_marking_a_plan_as_highlighted_unmarks_the_others(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $alreadyHighlighted = Plan::factory()->for($gym)->create(['highlighted' => true]);
        $newPlan = Plan::factory()->for($gym)->create(['highlighted' => false]);

        $this->actingAs($gymAdmin)->put("http://gestione.fitframe.test/setup/piani/{$newPlan->id}", [
            'name' => $newPlan->name,
            'price' => $newPlan->price,
            'highlighted' => '1',
            'features' => ['x'],
        ]);

        $this->assertTrue($newPlan->fresh()->highlighted);
        $this->assertFalse($alreadyHighlighted->fresh()->highlighted);
    }

    public function test_moving_a_plan_up_swaps_order_with_the_previous_one(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();
        $first = Plan::factory()->for($gym)->create(['order' => 0]);
        $second = Plan::factory()->for($gym)->create(['order' => 1]);

        $this->actingAs($gymAdmin)->post("http://gestione.fitframe.test/setup/piani/{$second->id}/move-up");

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }
}
