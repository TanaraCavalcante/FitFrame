<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserInitialsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_initials_use_first_letter_of_name_and_surname(): void
    {
        $user = User::factory()->make(['name' => 'Tanara', 'surname' => 'Cavalcante']);

        $this->assertSame('TC', $user->initials());
    }

    public function test_initials_fall_back_to_a_single_letter_when_surname_is_empty(): void
    {
        $user = User::factory()->make(['name' => 'Admin', 'surname' => '']);

        $this->assertSame('A', $user->initials());
    }
}
