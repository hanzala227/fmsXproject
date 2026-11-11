<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Invoice;

class DocumentSequenceService
{
    /**
     * Generate a new concurrency-safe sequence number for an invoice/DN/CN.
     * Includes A/B/C group logic for invoices of the same source transaction.
     *
     * @param int $companyId
     * @param string $sourceType e.g., 'App\Models\OceanImport'
     * @param int|null $sourceId e.g., 5
     * @param string $docType e.g., 'INV', 'DN', 'CN'
     * @return array [ 'main_sequence_no' => string, 'group_letter' => string, 'invoice_no' => string ]
     */
    public function generateSequence(int $companyId, string $sourceType, ?int $sourceId, string $docType): array
    {
        return DB::transaction(function () use ($companyId, $sourceType, $sourceId, $docType) {
            
            // 1. Check if an invoice of this type already exists for this source transaction
            if ($sourceId) {
                // We use sharedLock() or lockForUpdate() here on existing invoices?
                // Not strictly necessary to lock the entire invoices table, but let's query the latest group letter.
                $existingInvoice = Invoice::where('company_id', $companyId)
                    ->where('invoiceable_type', $sourceType)
                    ->where('invoiceable_id', $sourceId)
                    ->where('document_type', $docType)
                    ->lockForUpdate() // Prevent race conditions on this specific group
                    ->orderByRaw('LENGTH(group_letter) DESC, group_letter DESC')
                    ->first();
                
                if ($existingInvoice && $existingInvoice->main_sequence_no) {
                    $nextLetter = $this->incrementLetter($existingInvoice->group_letter);
                    $fullInvoiceNo = "{$existingInvoice->main_sequence_no}-{$nextLetter}";
                    
                    return [
                        'main_sequence_no' => $existingInvoice->main_sequence_no,
                        'group_letter' => $nextLetter,
                        'invoice_no' => $fullInvoiceNo
                    ];
                }
            }

            // 2. No existing invoice found for this source. We need a new main sequence number.
            // Lock the document_sequences row for this company and docType
            $sequence = DB::table('document_sequences')
                ->where('company_id', $companyId)
                ->where('document_type', $docType)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                // If row doesn't exist, create it concurrently using insertOrIgnore
                try {
                    DB::table('document_sequences')->insertOrIgnore([
                        'company_id' => $companyId,
                        'document_type' => $docType,
                        'current_number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Concurrent insert collision handled gracefully
                }
                
                // Re-fetch with lock
                $sequence = DB::table('document_sequences')
                    ->where('company_id', $companyId)
                    ->where('document_type', $docType)
                    ->lockForUpdate()
                    ->first();
            }

            // Increment the counter, skipping any existing invoice numbers to prevent duplicate collisions
            do {
                $nextNumber = ($sequence->current_number ?? 0) + 1;
                $sequence->current_number = $nextNumber;
                $mainSequenceNo = $docType . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                $groupLetter = 'A';
                $fullInvoiceNo = "{$mainSequenceNo}-{$groupLetter}";
            } while (\App\Models\Invoice::withoutGlobalScopes()->where('company_id', $companyId)->where('invoice_no', $fullInvoiceNo)->withTrashed()->exists());

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'current_number' => $nextNumber,
                    'updated_at' => now()
                ]);

            return [
                'main_sequence_no' => $mainSequenceNo,
                'group_letter' => $groupLetter,
                'invoice_no' => $fullInvoiceNo
            ];
        });
    }

    /**
     * Increment a single or multi-character alphabetical string.
     * e.g., A -> B, Z -> AA, AA -> AB
     */
    private function incrementLetter(string $letter): string
    {
        if (empty($letter)) return 'A';
        return function_exists('str_increment') ? str_increment($letter) : ++$letter;
    }

    /**
     * Generate standard short Booking No. (File No.) for shipments.
     * Format: PREFIX-YYMM#### (e.g. MOE-26100001, MOI-26100001, MAE-26100001, MAI-26100001)
     */
    public static function generateBookingNo(string $prefix, string $modelClass): string
    {
        $ym = date('ym');
        $pattern = $prefix . '-' . $ym . '%';
        $usesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass), true);

        // Scan globally (ignoring tenant scopes and including soft-deleted rows) so generated
        // booking numbers never collide with database-wide unique constraints on `file_no`.
        $baseQuery = fn() => $usesSoftDeletes
            ? $modelClass::withoutGlobalScopes()->withTrashed()
            : $modelClass::withoutGlobalScopes();

        $records = $baseQuery()->where('file_no', 'like', $pattern)->pluck('file_no');
        $maxSeq = 0;
        foreach ($records as $fileNo) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-' . $ym . '(\d{4})$/', $fileNo, $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        do {
            $proposed = sprintf('%s-%s%04d', $prefix, $ym, $nextSeq);
            $nextSeq++;
        } while ($baseQuery()->where('file_no', $proposed)->exists());

        return $proposed;
    }
}
