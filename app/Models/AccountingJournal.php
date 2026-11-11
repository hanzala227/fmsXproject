<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingJournal extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id',
        'entry_no',
        'entry_date',
        'description',
        'remark',
        'office_id',
        'created_by',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateEntryNo(): string
    {
        $prefix = 'JE-' . date('Ymd') . '-';
        $last = self::withoutGlobalScopes()
            ->withTrashed()
            ->where('entry_no', 'LIKE', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(entry_no, " . (strlen($prefix) + 1) . ") AS UNSIGNED) DESC")
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->entry_no, strlen($prefix));
            return $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }
}
