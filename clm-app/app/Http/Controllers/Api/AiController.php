<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    /**
     * Generate case summary using Gemini AI
     */
    public function generateCaseSummary(Request $request): JsonResponse
    {
        $request->validate([
            'case_id' => 'required|exists:cases,id',
            'language' => 'required|in:en,ar',
        ]);

        $case = \App\Models\CaseModel::with(['client', 'opponent', 'hearings'])->findOrFail($request->case_id);

        $prompt = $this->buildCaseSummaryPrompt($case, $request->language);

        try {
            $summary = $this->callGeminiApi($prompt);
            
            return response()->json([
                'summary' => $summary,
                'message' => 'Case summary generated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'case_id' => $request->case_id,
            ]);
            return response()->json([
                'error' => 'Failed to generate summary',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze document using Gemini AI
     */
    public function analyzeDocument(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string',
            'language' => 'required|in:en,ar',
        ]);

        $prompt = $this->buildDocumentAnalysisPrompt($request->text, $request->language);

        try {
            $analysis = $this->callGeminiApi($prompt);
            
            return response()->json([
                'analysis' => $analysis,
                'message' => 'Document analyzed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to analyze document',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Call Gemini API
     */
    private function callGeminiApi(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        
        if (!$apiKey) {
            Log::error('Gemini API key not found in config');
            throw new \Exception('Gemini API key not configured. Please check GEMINI_API_KEY in .env file.');
        }

        Log::info('Calling Gemini API', ['api_key_length' => strlen($apiKey), 'prompt_length' => strlen($prompt)]);

        // Configure HTTP client options
        $httpOptions = [
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
        
        // Disable SSL verification for local development (Windows SSL certificate issue)
        // Only disable if APP_ENV is local and GEMINI_VERIFY_SSL is false
        if (config('app.env') === 'local' && !config('services.gemini.verify_ssl', true)) {
            $httpOptions['verify'] = false;
            Log::warning('SSL verification disabled for Gemini API (local development only)');
        }
        
        // Try multiple API versions and models in order
        // Newer models first, then fallback to older ones
        $attempts = [
            ['version' => 'v1', 'model' => 'gemini-2.5-flash'],
            ['version' => 'v1', 'model' => 'gemini-2.5-pro'],
            ['version' => 'v1', 'model' => 'gemini-1.5-flash'],
            ['version' => 'v1', 'model' => 'gemini-1.5-pro'],
            ['version' => 'v1', 'model' => 'gemini-pro'],
            ['version' => 'v1beta', 'model' => 'gemini-pro'],
        ];
        
        $lastError = null;
        foreach ($attempts as $attempt) {
            $apiVersion = $attempt['version'];
            $model = $attempt['model'];
            $url = "https://generativelanguage.googleapis.com/{$apiVersion}/models/{$model}:generateContent?key={$apiKey}";
            
            Log::info('Trying Gemini API', [
                'version' => $apiVersion,
                'model' => $model,
                'url' => str_replace($apiKey, '***', $url)
            ]);
            
            $response = $this->tryGeminiApiCall($url, $httpOptions, $prompt, $apiKey);
            
            if ($response->successful()) {
                Log::info('Gemini API call successful', ['version' => $apiVersion, 'model' => $model]);
                break;
            }
            
            $lastError = [
                'version' => $apiVersion,
                'model' => $model,
                'status' => $response->status(),
                'body' => $response->body(),
            ];
            
            Log::warning('Gemini API attempt failed', $lastError);
            
            // If not a 404, don't try other models (likely auth/permission issue)
            if ($response->status() !== 404) {
                break;
            }
        }

        Log::info('Gemini API response', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body_preview' => substr($response->body(), 0, 500),
        ]);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error('All Gemini API attempts failed', [
                'last_error' => $lastError,
                'final_status' => $response->status(),
                'final_body' => $errorBody,
            ]);
            
            // Try to get available models for better error message
            try {
                $modelsUrl = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";
                $modelsResponse = Http::withOptions($httpOptions)->get($modelsUrl);
                if ($modelsResponse->successful()) {
                    $models = $modelsResponse->json();
                    Log::info('Available Gemini models', ['models' => $models]);
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch available models', ['error' => $e->getMessage()]);
            }
            
            throw new \Exception('Gemini API request failed (Status: ' . $response->status() . '): ' . $errorBody);
        }

        $data = $response->json();
        
        // Handle different response structures
        $text = null;
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'];
        } elseif (isset($data['text'])) {
            $text = $data['text'];
        } elseif (isset($data['content']['parts'][0]['text'])) {
            $text = $data['content']['parts'][0]['text'];
        }
        
        if (!$text) {
            Log::error('Invalid Gemini API response structure', ['response' => $data]);
            throw new \Exception('Invalid response from Gemini API. Response structure: ' . json_encode($data));
        }

        return $text;
    }

    /**
     * Try Gemini API call with given URL and options
     */
    private function tryGeminiApiCall(string $url, array $httpOptions, string $prompt, string $apiKey)
    {
        Log::info('Trying Gemini API call', ['url' => str_replace($apiKey, '***', $url)]);
        
        return Http::withOptions($httpOptions)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
        ]);
    }

    /**
     * Build case summary prompt
     */
    private function buildCaseSummaryPrompt($case, string $language): string
    {
        $lang = $language === 'ar' ? 'Arabic' : 'English';
        
        return "Generate a comprehensive case summary in {$lang} for the following case:\n\n" .
               "Case Name (EN): {$case->matter_name_en}\n" .
               "Case Name (AR): {$case->matter_name_ar}\n" .
               "Client: " . ($case->client ? $case->client->client_name_en : 'N/A') . "\n" .
               "Opponent: " . ($case->opponent ? $case->opponent->opponent_name_en : 'N/A') . "\n" .
               "Number of Hearings: " . $case->hearings->count() . "\n\n" .
               "Please provide a detailed summary including key facts, status, and important dates.";
    }

    /**
     * Build document analysis prompt
     */
    private function buildDocumentAnalysisPrompt(string $text, string $language): string
    {
        $lang = $language === 'ar' ? 'Arabic' : 'English';
        
        return "Analyze the following legal document text in {$lang} and provide:\n" .
               "1. Key points and findings\n" .
               "2. Important dates and deadlines\n" .
               "3. Parties involved\n" .
               "4. Recommendations\n\n" .
               "Document text:\n{$text}";
    }
}

