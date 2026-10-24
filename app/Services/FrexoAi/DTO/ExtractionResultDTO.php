<?php

namespace App\Services\FrexoAi\DTO;

class ExtractionResultDTO
{
    public string $uuid;
    public string $schemaVersion = '1.0';
    public string $module;
    /** @var DocumentAnalysisDTO[] */
    public array $documentAnalysis = [];
    public array $shipmentGroups = [];
    public array $unmatchedDocuments = [];
    public array $formData = [];
    /** @var FieldEvidenceDTO[] */
    public array $fieldEvidence = [];
    /** @var ConflictCandidateDTO[] */
    public array $conflicts = [];
    public array $calculations = [];
    public array $warnings = [];
    public array $processingSummary = [];

    public function __construct(
        string $module,
        string $schemaVersion = '1.0',
        ?string $uuid = null
    ) {
        $this->uuid = $uuid ?? (string) \Illuminate\Support\Str::uuid();
        $this->module = $module;
        $this->schemaVersion = $schemaVersion;
    }

    public function addDocumentAnalysis(DocumentAnalysisDTO $doc): self
    {
        $this->documentAnalysis[] = $doc;
        return $this;
    }

    public function addFieldEvidence(FieldEvidenceDTO $evidence): self
    {
        $this->fieldEvidence[] = $evidence;
        return $this;
    }

    public function addConflict(ConflictCandidateDTO $conflict): self
    {
        $this->conflicts[] = $conflict;
        return $this;
    }

    public function addWarning(string $code, string $message, ?string $fieldPath = null): self
    {
        $this->warnings[] = [
            'code' => $code,
            'message' => $message,
            'field_path' => $fieldPath,
        ];
        return $this;
    }

    public function toArray(): array
    {
        return [
            'run_uuid' => $this->uuid,
            'schema_version' => $this->schemaVersion,
            'module' => $this->module,
            'document_analysis' => array_map(fn($d) => $d instanceof DocumentAnalysisDTO ? $d->toArray() : $d, $this->documentAnalysis),
            'shipment_groups' => $this->shipmentGroups,
            'unmatched_documents' => $this->unmatchedDocuments,
            'form_data' => $this->formData,
            'field_evidence' => array_map(fn($e) => $e instanceof FieldEvidenceDTO ? $e->toArray() : $e, $this->fieldEvidence),
            'conflicts' => array_map(fn($c) => $c instanceof ConflictCandidateDTO ? $c->toArray() : $c, $this->conflicts),
            'calculations' => $this->calculations,
            'warnings' => $this->warnings,
            'processing_summary' => $this->processingSummary,
        ];
    }
}
