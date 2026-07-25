<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy;
    }

    public function test_super_admin_can_manage_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherSuperAdmin = User::factory()->superAdmin()->create();

        $this->assertTrue($this->policy->viewAny($superAdmin));
        $this->assertTrue($this->policy->create($superAdmin));
        $this->assertTrue($this->policy->update($superAdmin, $otherSuperAdmin));
        $this->assertTrue($this->policy->delete($superAdmin, $otherSuperAdmin));
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertFalse($this->policy->delete($superAdmin, $superAdmin));
    }

    public function test_gym_admin_cannot_manage_users(): void
    {
        $gymAdmin = User::factory()->create();
        $anotherGymAdmin = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($gymAdmin));
        $this->assertFalse($this->policy->create($gymAdmin));
        $this->assertFalse($this->policy->update($gymAdmin, $anotherGymAdmin));
        $this->assertFalse($this->policy->delete($gymAdmin, $anotherGymAdmin));
    }
}
