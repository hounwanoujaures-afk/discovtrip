<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private const SYSTEM_PROMPT = "Tu es DiscovGuide, assistant IA de DiscovTrip, plateforme de voyage au Bénin (Afrique de l'Ouest). "
        . "Réponds en français, chaleureusement, en 2-3 phrases maximum. "
        . "Tu aides les visiteurs à découvrir le Bénin : destinations (Cotonou, Porto-Novo, Ouidah, Abomey, Ganvié, Natitingou...), "
        . "expériences culturelles, nature, gastronomie, vaudou, histoire du Dahomey. "
        . "Infos pratiques : visa à l'arrivée (50 USD), monnaie FCFA (1 EUR = 655 FCFA), meilleure période novembre-mars. "
        . "Si la question ne concerne pas le Bénin ou le voyage, redirige poliment vers DiscovTrip.";

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages'   => ['required', 'array', 'max:6'],
            'messages.*.role'    => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
        ]);

        $apiKey = config('services.groq.key');

        if (empty($apiKey)) {
            Log::error('Chatbot: GROQ_API_KEY manquante');
            return response()->json(['error' => 'Service temporairement indisponible.'], 503);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => config('services.groq.model', 'llama-3.3-70b-versatile'),
                    'max_tokens'  => 300,
                    'temperature' => 0.7,
                    'messages'    => array_merge(
                        [['role' => 'system', 'content' => self::SYSTEM_PROMPT]],
                        $validated['messages']
                    ),
                ]);

            if (! $response->successful()) {
                Log::warning('Chatbot: erreur Groq', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Erreur du service IA.'], 502);
            }

            $content = $response->json('choices.0.message.content') ?? 'Réessayez dans un instant.';

            return response()->json(['content' => $content]);

        } catch (\Throwable $e) {
            Log::error('Chatbot: exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur du service IA.'], 500);
        }
    }
}
