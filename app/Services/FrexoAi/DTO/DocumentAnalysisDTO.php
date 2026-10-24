<?php

namespace App\Services\FrexoAi\DTO;

class DocumentAnalysisDTO
{
    public string $documentId;

    public string $filename;

    public string $documentType; // BILL_OF_LADING, COMMERCIAL_INVOICE, PACKING_LIST, etc.

    public int $pagesTotal;

    public int $pagesProcessed;

    public array $failedPages;

    public string $status; // SUCCESS, PARTIAL, FAILED

    public array $identifiers; // ['booking_no' => ..., 'mbl_no' => ..., 'container_nos' => [...]]

    public array $limitations;

    public function __construct(
        string $documentId,
        string $filename,
        string $documentType = 'UNKNOWN',
        int $pagesTotal = 1,
        int $pagesProcessed = 1,
        array $failedPages = [],
        string $status = 'SUCCESS',
        array $identifiers = [],
        array $limitations = []
    ) {
        $this->documentId = $documentId;
        $this->filename = $filename;
        $this->documentType = $documentType;
        $this->pagesTotal = $pagesTotal;
        $this->pagesProcessed = $pagesProcessed;
        $this->failedPages = $failedPages;
        $this->status = $status;
        $this->identifiers = $identifiers;
        $this->limitations = $limitations;
    }

    public function toArray(): array
    {
        return [
            'document_id' => $this->documentId,
            'filename' => $this->filename,
            'document_type' => $this->documentType,
            'pages_total' => $this->pagesTotal,
            'pages_processed' => $this->pagesProcessed,
            'failed_pages' => $this->failedPages,
            'status' => $this->status,
            'identifiers' => $this->identifiers,
            'limitations' => $this->limitations,
        ];
    }
}
