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
}
