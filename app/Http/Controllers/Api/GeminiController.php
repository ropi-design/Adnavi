<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GeminiController extends Controller
{
    /**
     * Gemini APIエンドポイント（マルチモーダル対応）
     * 
     * POST /api/gemini
     * Body: {
     *   "prompt": "質問テキスト",
     *   "imageUrl": "https://example.com/image.jpg" (オプション),
     *   "system": "システム指示" (オプション)
     * }
     */
    public function generate(Request $request, GeminiService $geminiService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|min:1|max:10000',
            'imageUrl' => 'nullable|url|max:2048',
            'system' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'name' => 'ValidationError',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
            ], 400);
        }

        $prompt = $request->input('prompt');
        $imageUrl = $request->input('imageUrl');
        $systemInstruction = $request->input('system');

        try {
            Log::info('Gemini API request', [
                'has_image' => !empty($imageUrl),
                'has_system' => !empty($systemInstruction),
                'prompt_length' => strlen($prompt),
            ]);

            $result = $geminiService->generateContent(
                $prompt,
                [],
                $imageUrl,
                $systemInstruction
            );

            if (!$result || isset($result['error'])) {
                return response()->json([
                    'error' => [
                        'name' => 'GenerationError',
                        'message' => $result['error'] ?? 'Failed to generate content',
                    ],
                ], 500);
            }

            $text = $result['raw_text'] ?? '';

            return response()->json([
                'model' => config('gemini.model', 'gemini-2.5-flash'),
                'text' => $text,
            ], 200);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API timeout', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => [
                    'name' => 'TimeoutError',
                    'message' => 'Request timeout',
                ],
            ], 504);
        } catch (\Exception $e) {
            Log::error('Gemini API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => [
                    'name' => 'ServerError',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}
