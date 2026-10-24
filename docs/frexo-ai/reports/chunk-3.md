# Frexo AI Extraction Pipeline — Chunk 3 Report: Staged Extraction Pipeline

**Date:** 2026-10-24  
**Author:** Autonomous Senior Engineer (Antigravity)  
**Status:** PASSED  
**Commit:** frexo-ai: chunk 3 - staged extraction pipeline

---

## 1. Summary of Changes
- **Versioned Prompts**: Created `resources/prompts/extraction/v1/`:
  - `prompt_a_system.txt`: Shared core system instruction enforcing evidence-only, untrusted document rules, verbatim snippet fidelity, scope separation, null-vs-0, and silent self-check.
  - `prompt_b_document.txt`: Stage 2 single-document extraction task with classification and full table rows.
  - `prompt_c_reconcile.txt`: Stage 3 multi-document reconciliation task for association linking, conflict preservation, and unified grouping.
  - `module_ocean.txt` and `module_air.txt`: Specific domain add-ons for maritime and aviation operations.
- **Configuration & Feature Flag**: Created `config/frexo_ai.php` containing:
  - `'enabled' => env('FREXO_AI_NEW_PIPELINE', false)` (defaults to OFF for 100% backward compatibility).
  - Configurable model, temperature (0.1), timeouts, concurrency, and air volumetric factor (166.67).
- **Gemini Client Abstraction**:
  - `GeminiClientInterface`: Contract for structured content generation.
  - `GeminiClient`: Live client with bounded exponential backoff on HTTP 429 / 5xx.
  - `MockGeminiClient`: Deterministic mock client supporting queued canned responses for CI testing.
- **4-Stage Pipeline Implementation**:
  - `Stage1IngestClassify`: File validation (size <= 15MB, MIME, max 10 docs), server-assigned unique `doc_<uuid>`, digital text extraction with `pdftotext`/`Smalot`, and scanned page rendering via `pdftoppm`.
  - `Stage2PerDocumentExtractor`: Isolated per-document extraction with document classification and partial-failure tolerance (corrupted files marked as `FAILED` in `document_analysis` rather than crashing the batch).
  - `Stage3Reconciler`: Synthesizes multi-document JSON results into `shipment_groups`, `unmatched_documents`, `conflicts`, and merged `form_data`.
  - `Stage4AntiHallucinationVerifier`: Server-side deterministic verification of `evidence_text` against ingested text layers; downgrades fabricated or unverifiable fields to `REVIEW_REQUIRED` with code `EVIDENCE_NOT_FOUND`.
  - `StagedExtractionPipeline`: Orchestrator tying all 4 stages together and running `FormDataMapper`.
- **Controller Integration**: `AiExtractionController.php` updated to check `config('frexo_ai.enabled', false)`. When enabled, delegates to `StagedExtractionPipeline`; when disabled, preserves 100% legacy flow.
- **Automated Tests**: Created `tests/Unit/FrexoAi/Chunk3StagedPipelineTest.php`.

---

## 2. File List
- `config/frexo_ai.php`
- `resources/prompts/extraction/v1/prompt_a_system.txt`
- `resources/prompts/extraction/v1/prompt_b_document.txt`
- `resources/prompts/extraction/v1/prompt_c_reconcile.txt`
- `resources/prompts/extraction/v1/module_ocean.txt`
- `resources/prompts/extraction/v1/module_air.txt`
- `app/Services/FrexoAi/Client/GeminiClientInterface.php`
- `app/Services/FrexoAi/Client/GeminiClient.php`
- `app/Services/FrexoAi/Client/MockGeminiClient.php`
- `app/Services/FrexoAi/Pipeline/Stage1IngestClassify.php`
- `app/Services/FrexoAi/Pipeline/Stage2PerDocumentExtractor.php`
- `app/Services/FrexoAi/Pipeline/Stage3Reconciler.php`
- `app/Services/FrexoAi/Pipeline/Stage4AntiHallucinationVerifier.php`
- `app/Services/FrexoAi/Pipeline/StagedExtractionPipeline.php`
- `app/Http/Controllers/AiExtractionController.php`
- `tests/Unit/FrexoAi/Chunk3StagedPipelineTest.php`

---

## 3. Exit Gate Verification

### A. End-to-End Full Contract Run Across 4 Modules
Tested `ocean_import`, `ocean_export`, `air_import`, and `air_export`. Each run produces all contract keys:
- `schema_version`: `'1.0'`
- `module`: verified for all 4 modules
- `document_analysis`: status `'SUCCESS'`
- `shipment_groups`: grouped documents present
- `form_data`: mapped cleanly without internal workflow fields
- `field_evidence`: verified
- `conflicts`, `warnings`, `processing_summary`: structured and present

### B. Partial Failure Tolerance (Failed Document Test)
Tested with a 2-document upload where document 2 returns malformed output:
- Document 1: `status = SUCCESS`
- Document 2: `status = FAILED`, `limitations` captures the failure reason, and the pipeline finishes successfully without crashing.

### C. Anti-Hallucination Evidence Verification
Tested with fabricated evidence not present in document text:
- Verifier detects snippet mismatch.
- Downgrades field status to `REVIEW_REQUIRED`.
- Records warning `EVIDENCE_NOT_FOUND` in `warnings[]`.
- Increments `processing_summary['anti_hallucination']['downgraded_to_review']`.

### D. Feature Flag OFF Verification
- `config('frexo_ai.enabled')` is `false` by default.
- Controller bypasses staged pipeline and preserves exact legacy baseline execution.

### E. Real Test Command Output
```bash
php vendor/bin/phpunit tests/Unit/FrexoAi/
```
```text
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.4
Configuration: /home/muhammad-hanzala/Downloads/backups/fms2.0/phpunit.xml

..........                                                        10 / 10 (100%)

Time: 00:00.443, Memory: 36.00 MB

OK (10 tests, 1603 assertions)
```

Baseline unit test:
```bash
php vendor/bin/phpunit tests/Unit/ExampleTest.php
```
```text
OK (1 test, 1 assertion)
```
