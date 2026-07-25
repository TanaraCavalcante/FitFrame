<?php

namespace Tests\Unit\Policies;

use App\Models\Gym;
use App\Models\User;
use App\Policies\GymPolicy;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    private GymPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new GymPolicy;
    }

    public function test_super_admin_can_view_any_and_manage_any_gym(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $gym = Gym::factory()->create();

        $this->assertTrue($this->policy->viewAny($superAdmin));
        $this->assertTrue($this->policy->create($superAdmin));
        $this->assertTrue($this->policy->update($superAdmin, $gym));
        $this->assertTrue($this->policy->delete($superAdmin, $gym));
        $this->assertTrue($this->policy->manage($superAdmin, $gym));
    }

    public function test_gym_admin_cannot_view_any_or_write_strutture(): void
    {
        $gym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($gym)->create();

        $this->assertFalse($this->policy->viewAny($gymAdmin));
        $this->assertFalse($this->policy->create($gymAdmin));
        $this->assertFalse($this->policy->update($gymAdmin, $gym));
        $this->assertFalse($this->policy->delete($gymAdmin, $gym));
    }

    public function test_gym_admin_can_manage_only_their_own_gym(): void
    {
        $ownGym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $gymAdmin = User::factory()->for($ownGym)->create();

        $this->assertTrue($this->policy->manage($gymAdmin, $ownGym));
        $this->assertFalse($this->policy->manage($gymAdmin, $otherGym));
    }

    public function test_a_gym_admin_with_no_gym_id_cannot_manage_anything(): void
    {
        $gymAdmin = User::factory()->create(['gym_id' => null]);
        $gym = new Gym;

        $this->assertFalse($this->policy->manage($gymAdmin, $gym));
    }
}
