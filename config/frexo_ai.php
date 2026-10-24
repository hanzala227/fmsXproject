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
    | Freight Rules & Tolerances
    |--------------------------------------------------------------------------
    */
    'air_volumetric_factor' => 166.67, // 1 CBM = 166.67 KG (6000 cm3/kg)
    'max_documents' => 10,
    'max_file_size_kb' => 15360, // 15 MB
];
