<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\TradePartner;
use Illuminate\Support\Facades\DB;
use App\Services\DocumentSequenceService;

class InvoiceGenerationService
{
    protected DocumentSequenceService $sequenceService;

    public function __construct(DocumentSequenceService $sequenceService)
    {
        $this->sequenceService = $sequenceService;
    }

    /**
     * Generate invoices for a set of selected charges.
     * 
     * @param int $companyId
     * @param string $sourceType (e.g. 'App\Models\OceanImport')
     * @param int $sourceId
     * @param string $operation ('Export' or 'Import' or 'C&F')
     * @param string $mode ('Sea' or 'Air' or 'Truck')
     * @param array|\Illuminate\Support\Collection $charges (The charge models, must be already filtered by what the user selected)
     * @return array [ 'success' => bool, 'message' => string, 'invoices' => array of invoice_no ]
     */
    public function generateInvoicesFromCharges(int $companyId, string $sourceType, int $sourceId, string $operation, string $mode, $charges)
    {
        if (count($charges) === 0) {
            return ['success' => false, 'message' => 'No charges selected.'];
        }

        // Group charges by bill_to_id and PR (Payable/Receivable)
        $groups = [];
        foreach ($charges as $charge) {
            $billToId = null;
            $rawId = $charge->bill_to_id ?: ($charge->vendor_id ?: null);
            if ($rawId && $rawId > 0 && TradePartner::where('id', $rawId)->exists()) {
                $billToId = (int)$rawId;
            } elseif (!empty($charge->party_name)) {
                $partner = TradePartner::where('name', trim($charge->party_name))
                    ->orWhere('code', trim($charge->party_name))
                    ->first();
                if ($partner) {
                    $billToId = $partner->id;
                }
            }

            if (!$billToId) {
                $billToId = $this->resolveShipmentPartnerId($sourceType, $sourceId);
            }

            $pr = $charge->type ?? 'AR'; 
            
            $key = ($billToId ?? 'null') . '_' . $pr;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'bill_to_id' => $billToId,
                    'pr' => $pr,
                    'charges' => []
                ];
            }
            $groups[$key]['charges'][] = $charge;
        }

        $generatedInvoiceNos = [];

        DB::transaction(function () use ($companyId, $sourceType, $sourceId, $operation, $mode, $groups, &$generatedInvoiceNos) {
            
            foreach ($groups as $group) {
                $billToId = $group['bill_to_id'];
                $pr = $group['pr']; // 'AR' or 'AP'
                $groupCharges = $group['charges'];
                
                // Determine Document Type (INV / DN / CN)
                $docType = $this->determineDocumentType($billToId, $pr, $operation, $mode);
                
                // Get Document Sequence
                $seqData = $this->sequenceService->generateSequence(
                    $companyId,
                    $sourceType,
                    $sourceId,
                    $docType
                );
                
                $invoiceNo = $seqData['invoice_no'];
                $generatedInvoiceNos[] = $invoiceNo;

                // Attempt to fetch MBL / MAWB No from source object
                $mblNo = null;
                if ($sourceType && $sourceId) {
                    try {
                        $sourceObj = $sourceType::find($sourceId);
                        if ($sourceObj) {
                            $mblNo = $sourceObj->mbl_no ?? $sourceObj->mawb_no ?? $sourceObj->file_no ?? null;
                        }
                    } catch (\Throwable $e) {
                        // Ignore if model query fails
                    }
                }

                // Create Invoice Record
                $invoice = Invoice::create([
                    'company_id' => $companyId,
                    'type' => $pr, // 'AR' or 'AP'
                    'document_type' => $docType,
                    'invoice_no' => $invoiceNo,
                    'main_sequence_no' => $seqData['main_sequence_no'],
                    'group_letter' => $seqData['group_letter'],
                    'invoiceable_type' => $sourceType,
                    'invoiceable_id' => $sourceId,
                    'bill_to_id' => $billToId,
                    'status' => 'DRAFT', 
                    'verification_status' => 'Pending Verification',
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(30),
                    'currency_id' => $groupCharges[0]->currency_id ?? \App\Models\Currency::where('code', 'USD')->value('id') ?? 1,
                    'mbl_no' => $mblNo,
                    'subtotal' => 0,
                    'total_amount' => 0,
                    'balance_amount' => 0,
                    'paid_amount' => 0,
                ]);

                $totalAmount = 0;

                // Create Invoice Lines and Update Charges
                foreach ($groupCharges as $charge) {
                    // Make sure it's not already invoiced
                    if ($charge->is_invoiced) {
                        throw new \Exception("Charge {$charge->charge_code} is already invoiced.");
                    }

                    $lineAmount = (float)($charge->total_amount ?? $charge->amount);

                    $chrgCode = !empty($charge->charge_code) ? $charge->charge_code : (!empty($charge->chrg_code) ? $charge->chrg_code : 'FREIGHT');
                    $chrgDesc = (!empty($charge->charge_name) && $charge->charge_name !== 'Charge') 
                        ? $charge->charge_name 
                        : (!empty($charge->charge_code) ? $charge->charge_code : (!empty($charge->chrg_code) ? $charge->chrg_code : 'Freight & Handling Charge'));

                    InvoiceLine::create([
                        'invoice_id' => $invoice->id,
                        'charge_code' => $chrgCode,
                        'description' => $chrgDesc,
                        'type' => $pr,
                        'qty' => $charge->qty ?? 1,
                        'rate' => $charge->rate ?? $lineAmount,
                        'amount' => $lineAmount,
                    ]);

                    $totalAmount += $lineAmount;

                    // Update Charge
                    $charge->update([
                        'is_invoiced' => true,
                        'invoice_no' => $invoiceNo,
                        'invoice_date' => now()
                    ]);
                }

                // Update Invoice Totals
                $invoice->update([
                    'subtotal' => $totalAmount,
                    'total_amount' => $totalAmount,
                    'balance_amount' => $totalAmount,
                ]);
            }

        });

        return [
            'success' => true,
            'message' => 'Invoice(s) generated successfully.',
            'invoices' => $generatedInvoiceNos
        ];
    }

    /**
     * Determine Document Type based on Business Rules.
     * 
     * Rule 1 (DN): Agent + Receivable -> DN
     * Rule 2 (CN): Agent + Payable -> CN
     * Rule 3 (Export): Party=Shipper + Operation=Export + Mode=Sea/Air -> INV
     * Rule 4 (Import): Party=Consignee + Operation=Import + Mode=Sea/Air -> INV
     * Rule 5 (C&F): Service=C&F -> INV
     */
    protected function determineDocumentType($billToId, $pr, $operation, $mode)
    {
        if (!$billToId) {
            return $pr === 'AR' ? 'INV' : 'CN';
        }

        $partner = TradePartner::find($billToId);
        $partnerType = $partner ? strtoupper(trim($partner->type)) : '';
        
        $agentTypes = ['PR', 'AGENT', 'FR', 'FORWARDER', 'FW', 'AG', 'OA'];
        $isAgent = in_array($partnerType, $agentTypes) || str_contains(strtolower($partnerType), 'agent');

        if ($isAgent) {
            if ($pr === 'AR') {
                return 'DN';
            } else {
                return 'CN';
            }
        } else {
            if ($pr === 'AR') {
                return 'INV';
            } else {
                return 'INV';
            }
        }
    }

    /**
     * Helper to resolve a valid trade partner ID from shipment source object when charge bill_to_id is missing.
     */
    protected function resolveShipmentPartnerId(string $sourceType, int $sourceId): ?int
    {
        if (!$sourceType || !$sourceId) return null;
        try {
            $sourceObj = $sourceType::find($sourceId);
            if (!$sourceObj) return null;

            $candidateId = $sourceObj->customer_id 
                ?? $sourceObj->consignee_id 
                ?? $sourceObj->shipper_id 
                ?? $sourceObj->agent_id 
                ?? $sourceObj->dm_customer_id 
                ?? null;

            if ($candidateId && TradePartner::where('id', $candidateId)->exists()) {
                return (int)$candidateId;
            }

            $firstPartner = TradePartner::first();
            return $firstPartner ? $firstPartner->id : null;
        } catch (\Throwable $e) {
            $firstPartner = TradePartner::first();
            return $firstPartner ? $firstPartner->id : null;
        }
    }
}
