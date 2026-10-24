<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Frexo AI Staged Pipeline Feature Flag
    |--------------------------------------------------------------------------
    | When false, the legacy extraction flow is used.
    | When true, the new 4-stage pipeline is used.
    */
    'enabled' => env('FREXO_AI_NEW_PIPELINE', false),

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    */
    'model' => env('FREXO_AI_MODEL', 'gemini-1.5-pro'),
    'temperature' => (float) env('FREXO_AI_TEMPERATURE', 0.1),
    'timeout' => (int) env('FREXO_AI_TIMEOUT', 60),
    'concurrency' => (int) env('FREXO_AI_CONCURRENCY', 3),
    'prompt_version' => env('FREXO_AI_PROMPT_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Resilience & Retry Policies
    |--------------------------------------------------------------------------
    */
    'max_retries' => (int) env('FREXO_AI_MAX_RETRIES', 2),
    'retry_backoff_ms' => (int) env('FREXO_AI_RETRY_BACKOFF_MS', 500),
    'max_json_repairs' => 1,
    'rate_limit_rpm' => (int) env('FREXO_AI_RATE_LIMIT_RPM', 60),

    /*
    |--------------------------------------------------------------------------
    | Document Limits & Freight Tolerances
    |--------------------------------------------------------------------------
    */
    'max_documents' => 10,
    'max_file_size_kb' => (int) env('FREXO_AI_MAX_FILE_SIZE_KB', 25600), // 25 MB
    'air_volumetric_factor' => (float) env('FREXO_AI_VOLUMETRIC_FACTOR', 166.67), // 1 CBM = 166.67 KG (6000 cm3/kg)
    'weight_sum_tolerance_pct' => 0.05, // 5% tolerance for sum checks

    /*
    |--------------------------------------------------------------------------
    | Queue & Worker Specification
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'connection' => env('FREXO_AI_QUEUE_CONNECTION', 'database'),
        'name' => env('FREXO_AI_QUEUE_NAME', 'frexo-ai'),
        'timeout' => (int) env('FREXO_AI_JOB_TIMEOUT', 120),
        'max_tries' => (int) env('FREXO_AI_JOB_MAX_TRIES', 2),
    ],
];
