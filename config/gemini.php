<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Gemini API settings here.
    |
    */

    // API接続方式: 'api' or 'sdk'
    'driver' => env('GEMINI_DRIVER', 'api'),

    // APIキー（AI Studio用）
    'api_key' => env('GEMINI_API_KEY'),

    // モデル名
    // 利用可能なモデル: gemini-2.5-flash, gemini-2.5-flash-lite, gemini-1.5-pro-latest, gemini-1.5-flash-latest, gemini-1.5-pro, gemini-1.5-flash
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    // リクエストタイムアウト（ミリ秒）
    'request_timeout_ms' => env('REQUEST_TIMEOUT_MS', 30000),

    // Vertex AI設定（オプション）
    'vertex_ai' => [
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'location' => env('VERTEX_AI_LOCATION', 'us-central1'),
        'model' => env('VERTEX_AI_MODEL', 'gemini-1.5-pro'),
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],

    // 生成パラメータ
    'generation_config' => [
        'temperature' => 0.7,
        'top_p' => 0.95,
        'top_k' => 40,
        'max_output_tokens' => 8192,
    ],

    // レート制限
    'rate_limit' => [
        'requests_per_minute' => 60,
        'tokens_per_minute' => 32000,
    ],

    // プロンプトテンプレート
    'prompt_templates' => [
        'analysis' => resource_path('prompts/analysis.txt'),
        'recommendation' => resource_path('prompts/recommendation.txt'),
    ],
];
