<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AccountingBlockDate extends Model
{
    protected $table = 'accounting_block_dates';

    protected $fillable = [
        'office_id',
        'block_date',
        'action',
        'created_by',
    ];

    protected $casts = [
        'block_date' => 'date',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Determine if a given transaction date is blocked for an office (or globally).
     */
    public static function isDateBlocked(?string $date, ?int $officeId = null): bool
    {
        if (!$date) return false;

        $targetDate = Carbon::parse($date)->toDateString();

        // 1. Check for specific office block
        if ($officeId) {
            $latestOffice = static::where('office_id', $officeId)
                ->orderByDesc('id')
                ->first();
            if ($latestOffice) {
                if ($latestOffice->action === 'BLOCK' && $latestOffice->block_date?->toDateString() >= $targetDate) {
                    return true;
                }
            }
        }

        // 2. Check for global all-offices block (office_id is null)
        $latestGlobal = static::whereNull('office_id')
            ->orderByDesc('id')
            ->first();
        if ($latestGlobal && $latestGlobal->action === 'BLOCK' && $latestGlobal->block_date?->toDateString() >= $targetDate) {
            return true;
        }

        return false;
    }
}
