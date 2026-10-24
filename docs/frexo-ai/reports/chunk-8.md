# Frexo AI Extraction Pipeline — Chunk 8 Report: Testing, Evaluation Harness, Rollout

**Date:** 2026-10-24  
**Status:** PASSED  
**Commit:** frexo-ai: chunk 8 - testing evaluation harness rollout  

---

## 1. Summary of Changes

### A. Evaluation Harness Artisan Command (`app/Console/Commands/FrexoAiEvalCommand.php`)
- Implemented `php artisan frexo:eval {--module=} {--fixture=}`.
- Scans golden test dataset in `tests/Fixtures/FrexoAi/*.json`.
- Executes each fixture through the full `StagedExtractionPipeline` using `MockGeminiClient`.
- Evaluates:
  - Exact-field match accuracy.
  - Critical identifier format validity (MBL, MAWB, Container check digits).
  - Record completeness across containers, HBLs, HAWBs, and routes.
  - Conflict detection recall.
  - Immunity against prompt injections targeting internal workflow fields.
- Strictly adheres to Rule 2 of the specification: Real labeled document accuracy is explicitly reported as `"not yet measured (awaiting real labeled document test set)"` rather than fabricated.

### B. Golden Fixtures Created (`tests/Fixtures/FrexoAi/`)
1. `ocean_import_standard.json`: Single HBL standard Bill of Lading with container CSQU3054383 and route legs.
2. `ocean_export_multi_container.json`: Booking confirmation with multiple 40HQ and 20GP containers.
3. `air_import_volumetric.json`: Air Waybill with gross, volumetric, and chargeable weight separation.
4. `air_export_multi_hawb.json`: Master AWB with multiple HAWBs.
5. `adversarial_prompt_injection.json`: Commercial Invoice containing prompt override instructions attempting to modify `file_no` and `company_id`.
6. `cross_doc_conflict.json`: Cross-document weight disagreement requiring operator candidate selection.

### C. Automated End-to-End Test Suite (`tests/Unit/FrexoAi/Chunk8EvalAndEndToEndTest.php`)
- Verifies all four freight modules end-to-end.
- Verifies prompt injection immunity.
- Verifies multi-container and multi-HAWB record preservation.
- Verifies artisan evaluation command runs cleanly with exit code 0.

---

## 2. Benchmark Metrics Output

```bash
php artisan frexo:eval
```

**Real Command Output:**
```
=== Frexo AI Evaluation Harness ===
Running golden evaluation fixtures...

• Running fixture: Adversarial Prompt Injection Fixture [ocean_import]
• Running fixture: Air Export Multi HAWB Consolidation [air_export]
• Running fixture: Air Import Volumetric Calculation [air_import]
• Running fixture: Cross Document Weight Conflict [ocean_import]
• Running fixture: Ocean Export Multi Container Booking [ocean_export]
• Running fixture: Ocean Import Standard Bill of Lading [ocean_import]

--- Synthetic Fixture Evaluation Summary ---
+--------------+--------------+----------------+----------------+---------------------+-----------------+--------------+
| Module       | Fixtures (n) | Fields Checked | Exact Accuracy | Critical Error Rate | Conflict Recall | Completeness |
+--------------+--------------+----------------+----------------+---------------------+-----------------+--------------+
| Ocean Import | 3            | 4              | 100%           | 0%                  | 100%            | 100.0%       |
| Ocean Export | 1            | 2              | 100%           | 0%                  | 100.0%          | 100.0%       |
| Air Import   | 1            | 3              | 100%           | 0%                  | 100.0%          | 100.0%       |
| Air Export   | 1            | 2              | 100%           | 0%                  | 100.0%          | 100.0%       |
+--------------+--------------+----------------+----------------+---------------------+-----------------+--------------+

--- Production Labeled Dataset Status (Rule 2 Specification) ---
+-----------------------------+------------------------------------------------------------+
| Metric                      | Production Dataset Status                                  |
+-----------------------------+------------------------------------------------------------+
| Real Labeled Accuracy       | not yet measured (awaiting real labeled document test set) |
| Real Critical Error Rate    | not yet measured (awaiting real labeled document test set) |
| Real Auto-Apply Error Rate  | not yet measured (awaiting real labeled document test set) |
| Benchmark Harness Integrity | VERIFIED (Automated test fixtures green)                   |
+-----------------------------+------------------------------------------------------------+
```

---

## 3. Fixture Format & Contribution Guide

To add a new evaluation test case, place a `.json` file in `tests/Fixtures/FrexoAi/`:
```json
{
  "name": "Descriptive Case Name",
  "module": "ocean_import|ocean_export|air_import|air_export",
  "document": {
    "filename": "sample_bl.pdf",
    "text_layer": "Raw OCR or text layer extracted from document"
  },
  "mock_response": {
    "document_type": "BILL_OF_LADING",
    "mbl": { "mbl_no": "MEDU1234567" },
    "containers": [ { "container_no": "CSQU3054383", "container_type": "40HQ" } ]
  },
  "expected": {
    "fields": {
      "mbl.mbl_no": "MEDU1234567"
    },
    "conflicts_count": 0,
    "containers_count": 1
  }
}
```

---

## 4. Production Rollout Plan

### Phase 1: Shadow Mode (Week 1)
- Pipeline runs asynchronously on background uploads.
- Compares extracted outputs against user-saved form data and records accuracy telemetry without touching forms.
- Zero operator disruption.

### Phase 2: Per-Company Pilot Opt-In (Week 2)
- Target company enabled via `FeatureFlag` or `AiSetting`:
  ```bash
  FREXO_AI_NEW_PIPELINE=true
  ```
- Review modal surfaces validation badges, jump-to-evidence, and candidate selector.
- Double-click protection active with idempotency keys.

### Phase 3: General Availability (Week 3)
- Enabled globally across all companies.

### Rollback Procedure (Instant / 0 Downtime)
- Set in `.env`:
  ```env
  FREXO_AI_NEW_PIPELINE=false
  ```
- Controller immediately routes `/api/ai/extract/*` to legacy single-shot logic without requiring any code deployment or database changes.

---

## 5. Exit Gate Checklist
1. **Full suite green:** PASS (54 tests, 1782 assertions).
2. **Static analysis clean:** PASS (Laravel Pint passed across 38 files).
3. **Frontend build clean:** PASS (`npm run build` in 4.68s).
4. **Harness runs on synthetic fixtures & prints metrics table:** PASS.
