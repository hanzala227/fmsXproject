<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\AI\AiManager;
use Illuminate\Http\Request;
use Exception;

class AiIntegrationController extends Controller
{
    public function settings()
    {
        $settings = AiSetting::current();
        
        // If settings don't exist (e.g. migration ran but seeder didn't), create defaults
        if (!$settings) {
            $settings = AiSetting::create([
                'api_type' => 'openai_compatible',
                'base_url' => 'https://api.openai.com/v1',
                'model_name' => 'gpt-4o'
            ]);
        }

        return view('super-admin.integrations.ai-settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'api_type' => 'required|in:openai_compatible,gemini',
            'base_url' => 'required_if:api_type,openai_compatible|url',
            'api_key' => 'required|string',
            'model_name' => 'required|string',
        ]);

        $settings = AiSetting::current();
        
        $settings->update([
            'api_type' => $request->api_type,
            'base_url' => $request->base_url ?? 'https://api.openai.com/v1',
            'api_key' => $request->api_key,
            'model_name' => $request->model_name,
        ]);

        return back()->with('success', 'Universal AI Settings updated successfully.');
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            $ai = AiManager::provider();
            $response = $ai->generateResponse($request->message);
            
            return response()->json([
                'success' => true,
                'reply' => $response
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
