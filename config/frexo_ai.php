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
    'model' => env('FREXO_AI_MODEL', 'gemini-3.5-flash-lite'),
    'temperature' => (float) env('FREXO_AI_TEMPERATURE', 0.0),
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

    /*
    |--------------------------------------------------------------------------
    | Frexo AI Credit & Billing System
    |--------------------------------------------------------------------------
    */
    'billing' => [
        'enabled' => env('FREXO_AI_BILLING_ENABLED', true),
        'initial_company_credits' => (float) env('FREXO_AI_INITIAL_CREDITS', 1000.0), // Complimentary credits for new companies
        'tokens_per_credit' => (int) env('FREXO_AI_TOKENS_PER_CREDIT', 100), // 100 tokens = 1 credit (e.g. 5,000 tokens = 50 credits)
        'min_required_credits' => (float) env('FREXO_AI_MIN_REQUIRED_CREDITS', 1.0), // Minimum balance to start extraction
        'image_tokens_weight' => (int) env('FREXO_AI_IMAGE_TOKENS', 258), // Gemini standard image token weight
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiered Top-Up Pricing Packages
    |--------------------------------------------------------------------------
    */
    'packages' => [
        [
            'id' => 'starter',
            'name' => 'Starter Pack',
            'credits' => 500,
            'price_usd' => 25.00,
            'price_pkr' => 7000,
            'popular' => false,
            'desc' => 'Ideal for small branches processing periodic ocean & air shipping bills.',
            'features' => ['~50 Document Extractions', 'Google Gemini AI Support', 'OCR Table Extraction', 'No Expiration Date'],
        ],
        [
            'id' => 'growth',
            'name' => 'Growth Pack',
            'credits' => 2500,
            'price_usd' => 100.00,
            'price_pkr' => 28000,
            'popular' => true,
            'desc' => 'Best for active freight forwarding hubs with daily cargo movements.',
            'features' => ['~250 Document Extractions', 'Priority Job Queue', 'Multi-Page HBL & MAWB OCR', 'Container Cross-Validation', 'No Expiration Date'],
        ],
        [
            'id' => 'enterprise',
            'name' => 'Enterprise Scale',
            'credits' => 10000,
            'price_usd' => 350.00,
            'price_pkr' => 98000,
            'popular' => false,
            'desc' => 'High-throughput operations handling commercial invoices and manifest sync.',
            'features' => ['~1,000+ Document Extractions', 'Dedicated AI Concurrency', 'Accounting Commercial Invoice Parse', 'Custom Integration Support', 'No Expiration Date'],
        ],
    ],
];

