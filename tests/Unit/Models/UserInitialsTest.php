<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserInitialsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_initials_use_first_and_last_name(): void
    {
        $user = User::factory()->make(['name' => 'W Tech Admin']);

        $this->assertSame('WA', $user->initials());
    }

    public function test_initials_fall_back_to_a_single_letter_for_a_one_word_name(): void
    {
        $user = User::factory()->make(['name' => 'Admin']);

        $this->assertSame('A', $user->initials());
    }
}
