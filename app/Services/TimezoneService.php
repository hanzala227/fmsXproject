<?php

namespace App\Services;

class TimezoneService
{
    /**
     * Standard list of international freight & global logistics operational timezones.
     *
     * @return array<string, string>
     */
    public static function getTimezones(): array
    {
        return [
            'UTC' => 'UTC (GMT+00:00 - Universal Coordinated)',
            'Asia/Karachi' => 'Asia/Karachi (PKT GMT+05:00 - Pakistan)',
            'Asia/Dubai' => 'Asia/Dubai (GST GMT+04:00 - UAE / Gulf)',
            'Asia/Riyadh' => 'Asia/Riyadh (AST GMT+03:00 - Saudi Arabia)',
            'Asia/Singapore' => 'Asia/Singapore (SGT GMT+08:00 - Singapore Hub)',
            'Asia/Hong_Kong' => 'Asia/Hong_Kong (HKT GMT+08:00 - Hong Kong)',
            'Asia/Shanghai' => 'Asia/Shanghai (CST GMT+08:00 - China)',
            'Asia/Tokyo' => 'Asia/Tokyo (JST GMT+09:00 - Japan)',
            'Asia/Bangkok' => 'Asia/Bangkok (ICT GMT+07:00 - Thailand)',
            'Asia/Kolkata' => 'Asia/Kolkata (IST GMT+05:30 - India)',
            'Asia/Dhaka' => 'Asia/Dhaka (BST GMT+06:00 - Bangladesh)',
            'Europe/London' => 'Europe/London (GMT/BST GMT+00:00 - United Kingdom)',
            'Europe/Frankfurt' => 'Europe/Frankfurt (CET GMT+01:00 - Germany Hub)',
            'Europe/Paris' => 'Europe/Paris (CET GMT+01:00 - France)',
            'Europe/Amsterdam' => 'Europe/Amsterdam (CET GMT+01:00 - Netherlands Hub)',
            'Europe/Istanbul' => 'Europe/Istanbul (TRT GMT+03:00 - Turkey)',
            'America/New_York' => 'America/New_York (EST GMT-05:00 - US East Coast)',
            'America/Chicago' => 'America/Chicago (CST GMT-06:00 - US Central)',
            'America/Los_Angeles' => 'America/Los_Angeles (PST GMT-08:00 - US West Coast)',
            'America/Toronto' => 'America/Toronto (EST GMT-05:00 - Canada)',
            'America/Sao_Paulo' => 'America/Sao_Paulo (BRT GMT-03:00 - Brazil)',
            'Australia/Sydney' => 'Australia/Sydney (AEST GMT+10:00 - Australia)',
            'Africa/Cairo' => 'Africa/Cairo (EET GMT+02:00 - Egypt)',
            'Africa/Johannesburg' => 'Africa/Johannesburg (SAST GMT+02:00 - South Africa)',
        ];
    }
}
