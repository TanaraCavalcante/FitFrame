<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\RagServiceUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\Chat\RagServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function ask(Request $request, RagServiceClient $ragServiceClient): JsonResponse
    {
        $validated = $request->validate([
            'domanda' => ['required', 'string'],
        ]);

        try {
            $risposta = $ragServiceClient->ask($validated['domanda']);
        } catch (RagServiceUnavailableException) {
            return response()->json(['error' => 'Assistente temporaneamente non disponibile'], 503);
        }

        ChatMessage::create([
            'user_id' => $request->user()->id,
            'question' => $validated['domanda'],
            'answer' => $risposta,
        ]);

        return response()->json(['risposta' => $risposta]);
    }

    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'before_id' => ['required', 'integer'],
        ]);

        $messages = ChatMessage::query()
            ->where('user_id', $request->user()->id)
            ->where('id', '<', $validated['before_id'])
            ->latest('id')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        $hasMore = $messages->isNotEmpty() && ChatMessage::query()
            ->where('user_id', $request->user()->id)
            ->where('id', '<', $messages->first()->id)
            ->exists();

        return response()->json([
            'messages' => $messages->map(fn (ChatMessage $message) => [
                'id' => $message->id,
                'question' => $message->question,
                'answer' => $message->answer,
            ]),
            'has_more' => $hasMore,
        ]);
    }
}
