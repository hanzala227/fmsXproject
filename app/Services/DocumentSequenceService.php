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
                    ->orderBy('group_letter', 'desc')
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
                // If row doesn't exist, create it (safe because we wrapped in transaction, but better to insert if not exists)
                DB::table('document_sequences')->insert([
                    'company_id' => $companyId,
                    'document_type' => $docType,
                    'current_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Re-fetch with lock
                $sequence = DB::table('document_sequences')
                    ->where('company_id', $companyId)
                    ->where('document_type', $docType)
                    ->lockForUpdate()
                    ->first();
            }

            // Increment the counter
            $nextNumber = $sequence->current_number + 1;

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'current_number' => $nextNumber,
                    'updated_at' => now()
                ]);

            $mainSequenceNo = $docType . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $groupLetter = 'A';
            $fullInvoiceNo = "{$mainSequenceNo}-{$groupLetter}";

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
        return ++$letter; // PHP automatically handles alphabetical incrementing Z -> AA
    }
}
