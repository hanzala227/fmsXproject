<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class FrexAssistantController extends Controller
{
    private array $allowedActions = [
        'navigate_dashboard',
        'navigate_module',
        'open_create_form',
        'search_shipment',
        'read_dashboard_summary',
        'conversational_reply',
    ];

    public function process(Request $request)
    {
        $request->validate([
            'transcript' => 'required|string|max:500'
        ]);

        $settings = \App\Models\AiSetting::current();
        $apiKey = $settings->api_key ?? null;
        $model = $settings->model_name ?? 'gemini-1.5-pro';
        
        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'error' => 'Frex configuration error: Gemini API key is missing.',
                'voice_reply' => 'I am sorry, but my AI system is not configured. Please add the Gemini API key.'
            ], 500);
        }

        // Rate limiting: 10 requests per minute per user
        $key = 'frex:' . auth()->id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success' => false,
                'error' => 'Too many requests.',
                'voice_reply' => 'Please slow down. You are sending too many requests.'
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $transcript = $request->input('transcript');
        $companyId = auth()->user()->company_id ?? 1;

        $systemPrompt = "You are Frex, a highly intelligent and helpful AI assistant for a Freight Management System. 
Analyze the user's speech and return a strict JSON object representing their intent. 
The JSON must have the following keys:
- 'action': strictly one of [" . implode(', ', $this->allowedActions) . "]
- 'parameters': an object with specific data needed for the action
- 'voice_reply': a friendly, helpful, conversational response spoken back to the user (e.g. 'Opening Ocean Import.' or answering their question).

Actions & Parameters Mapping:
1. 'navigate_dashboard' -> No params. (Use if they want to go to home or dashboard)
2. 'navigate_module' -> params: { 'module': 'ocean-import' | 'ocean-export' | 'air-import' | 'air-export' | 'truck' | 'warehouse/receiving' | 'warehouse/shipping' | 'quotation' | 'trade-partner' }
3. 'open_create_form' -> params: { 'module': 'ocean-import' | 'ocean-export' | 'air-import' | 'air-export' | 'truck' | 'warehouse/receiving' | 'warehouse/shipping' | 'quotation' | 'trade-partner' }
4. 'search_shipment' -> params: { 'query': 'the search term or booking number' }
5. 'read_dashboard_summary' -> No params. (Use if they ask about total profit, active shipments, etc. The backend will fetch the data based on company_id $companyId).
6. 'conversational_reply' -> No params. (CRITICAL: Use this for ALL general questions, greetings, requests for help, asking how to fill out a field, or any chat that doesn't fit the above commands. Provide your full helpful answer in 'voice_reply'.)

If the user's request doesn't make sense or you cannot help, return action 'conversational_reply' and explain in 'voice_reply'. DO NOT return markdown formatting (no ```json). Return raw JSON only.";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nUser said: " . $transcript]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonText = trim($data['candidates'][0]['content']['parts'][0]['text']);
                    
                    // Fallback cleanup if Gemini includes markdown despite instructions
                    $jsonText = preg_replace('/^```json\s*/i', '', $jsonText);
                    $jsonText = preg_replace('/\s*```$/i', '', $jsonText);
                    
                    $result = json_decode($jsonText, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $action = $result['action'] ?? 'unknown';
                        
                        if (!in_array($action, $this->allowedActions)) {
                            return response()->json([
                                'success' => false,
                                'error' => 'Action not supported.',
                                'voice_reply' => $result['voice_reply'] ?? 'I cannot perform this action yet.'
                            ]);
                        }

                        // If action is read_dashboard_summary, we fetch basic summary data securely
                        if ($action === 'read_dashboard_summary') {
                            // Basic placeholder implementation respecting company_id
                            // In a full implementation, you'd query the DB here.
                            $result['voice_reply'] = "Dashboard summary data retrieved successfully. Your company ID is $companyId.";
                            $result['data'] = ['company_id' => $companyId];
                        }

                        return response()->json([
                            'success' => true,
                            'action' => $action,
                            'parameters' => $result['parameters'] ?? [],
                            'voice_reply' => $result['voice_reply'] ?? 'Action completed.'
                        ]);
                    }
                }
            }

            Log::error('Frex Gemini API Error: ' . $response->body());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process AI response.',
                'voice_reply' => 'I encountered an error while processing your request.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Frex Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Server error.',
                'voice_reply' => 'There is a server error connecting to the AI system.'
            ], 500);
        }
    }
}
