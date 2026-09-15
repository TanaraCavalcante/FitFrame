<?php

// Test generati da tanas:test

namespace Tests\Unit\Chat;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ChatMessageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_a_chat_message_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $chatMessage = ChatMessage::factory()->for($user)->create();

        $this->assertTrue($chatMessage->user->is($user));
    }
}
