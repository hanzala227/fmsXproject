<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrexoAiCreditSetting extends Model
{
    protected $table = 'frexo_ai_credit_settings';

    protected $fillable = [
        'per_credit_usd',
        'min_custom_credits',
        'exchange_rate_pkr',
        'initial_free_credits',
        'packages',
        'bank_name',
        'bank_account_title',
        'bank_iban',
        'bank_swift',
        'bank_instructions',
    ];

    protected $casts = [
        'per_credit_usd' => 'float',
        'min_custom_credits' => 'integer',
        'exchange_rate_pkr' => 'float',
        'initial_free_credits' => 'float',
        'packages' => 'array',
    ];

    /**
     * Return default tiered packages if none configured.
     */
    public static function defaultPackages(): array
    {
        return [
            [
                'id' => 'starter',
                'name' => 'Starter Pack',
                'credits' => 500,
                'price_usd' => 25.00,
                'popular' => false,
                'desc' => 'Essential for small stations processing periodic ocean/air bills.',
                'features' => ['~50 Document Extractions', 'Google Gemini 2.5 Flash', 'OCR Table Extraction', 'No Expiration'],
            ],
            [
                'id' => 'growth',
                'name' => 'Growth Pack',
                'credits' => 2500,
                'price_usd' => 100.00,
                'popular' => true,
                'desc' => 'Best for active freight forwarding hubs with daily cargo movements.',
                'features' => ['~250 Document Extractions', 'Priority Job Queue', 'Multi-Page HBL & MAWB OCR', 'Container Cross-Validation', 'No Expiration'],
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise Scale',
                'credits' => 10000,
                'price_usd' => 350.00,
                'popular' => false,
                'desc' => 'High-throughput operations handling commercial invoices and manifest sync.',
                'features' => ['~1,000+ Document Extractions', 'Dedicated AI Concurrency', 'Accounting Commercial Invoice Parse', 'Custom Integration Support', 'No Expiration'],
            ],
        ];
    }

    /**
     * Singleton accessor for global Frexo AI credit settings.
     */
    public static function current(): self
    {
        $setting = self::first();
        if (!$setting) {
            $setting = self::create([
                'per_credit_usd' => 0.0450,
                'min_custom_credits' => 100,
                'exchange_rate_pkr' => 280.00,
                'initial_free_credits' => 1000.00,
                'packages' => self::defaultPackages(),
                'bank_name' => 'JPMorgan Chase Bank / Habib Bank Limited',
                'bank_account_title' => 'FreightX Technologies International',
                'bank_iban' => 'PK36HABB0000001234567890',
                'bank_swift' => 'HABBKPKA',
                'bank_instructions' => 'Please attach deposit slip or wire advice for instant approval.',
            ]);
        }

        // If packages array is empty, populate defaults
        if (empty($setting->packages)) {
            $setting->update(['packages' => self::defaultPackages()]);
            $setting->refresh();
        }

        return $setting;
    }
}
