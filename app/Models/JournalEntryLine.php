<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    use BelongsToTenant;

    protected static function booted(): void
    {
        static::creating(function (self $line) {
            if (empty($line->company_id) && $line->journal_entry_id) {
                $line->company_id = AccountingJournal::withoutGlobalScopes()
                    ->where('id', $line->journal_entry_id)
                    ->value('company_id');
            }
        });
    }

    protected $fillable = [
        'company_id',
        'journal_entry_id',
        'line_no',
        'gl_account_id',
        'sub',
        'entity_type',
        'trade_partner_id',
        'description',
        'office_id',
        'local_debit',
        'local_credit',
        'currency_id',
        'foreign_rate',
        'foreign_debit',
        'foreign_credit',
    ];

    protected $casts = [
        'local_debit'     => 'decimal:2',
        'local_credit'    => 'decimal:2',
        'foreign_rate'    => 'decimal:6',
        'foreign_debit'   => 'decimal:2',
        'foreign_credit'  => 'decimal:2',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_entry_id');
    }

    public function glAccount()
    {
        return $this->belongsTo(GlAccount::class, 'gl_account_id');
    }

    public function tradePartner()
    {
        return $this->belongsTo(TradePartner::class, 'trade_partner_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
