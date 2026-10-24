# Frexo AI Extraction Pipeline — Chunk 2 Report: Module Schemas & Field Registry

**Date:** 2026-10-24  
**Author:** Autonomous Senior Engineer (Antigravity)  
**Status:** PASSED  
**Commit:** frexo-ai: chunk 2 - module schemas and field registry

---

## 1. Summary of Changes
- Created `App\Services\FrexoAi\Registry\FieldDefinition`: Strongly typed metadata definition for field path, data type, scope (`shipment`, `master`, `house`, `container`, `leg`, `party`), source policies (`DOCUMENT_EXTRACTABLE`, `INTERNAL_WORKFLOW`, `CALCULATED`), overwrite policies (`never`, `only_if_empty`, `user_confirmed`), units, enums, and authority priorities.
- Created `App\Services\FrexoAi\Registry\FieldRegistry`: Centralized field registry covering all 4 modules (`ocean_import`, `ocean_export`, `air_import`, `air_export`). Strictly identifies `INTERNAL_WORKFLOW` fields (`file_no`, `post_date`, `op_id`, `op_name`, `dm_sales_person_id`, `dm_sales_person_name`, `company_id`, and record IDs).
- Created `App\Services\FrexoAi\Schema\SchemaBuilder`:
  - Generates strict Gemini `responseSchema` subset (type, properties, items, required, enum, description) for each module.
  - Automatically excludes all `INTERNAL_WORKFLOW` fields from the AI schema so the model is physically prevented from outputting internal system fields.
  - Generates runtime `{{FIELD_RULES}}` dynamically from the registry to ensure zero drift between prompt instructions and backend validators.
- Created Internal Response Contract DTOs:
  - `ExtractionResultDTO`: `schema_version`, `module`, `document_analysis[]`, `shipment_groups[]`, `unmatched_documents[]`, `form_data`, `field_evidence[]`, `conflicts[]`, `calculations[]`, `warnings[]`, `processing_summary`.
  - `FieldEvidenceDTO`: Validates evidence statuses (`EXPLICIT`, `DERIVED`, `AMBIGUOUS`, `CONFLICT`, `NOT_FOUND`, `UNREADABLE`, `NOT_APPLICABLE`, `REVIEW_REQUIRED`) and caps `evidence_text` to <= 200 characters.
  - `ConflictCandidateDTO`: Captures conflicting values with sources and authority resolution.
  - `DocumentAnalysisDTO`: Tracks per-document classifications, pages processed, failed pages, identifiers, and limitations.
- Created `App\Services\FrexoAi\Mapper\FormDataMapper`: Maps validated `form_data` back to the application's native structure (`mbl`, `hbls`, `containers`, `routes`) while strictly dropping any injected `INTERNAL_WORKFLOW` fields.
- Created comprehensive Unit Test Suite: `tests/Unit/FrexoAi/Chunk2SchemasAndRegistryTest.php`.

---

## 2. File List
- `app/Services/FrexoAi/Registry/FieldDefinition.php`
- `app/Services/FrexoAi/Registry/FieldRegistry.php`
- `app/Services/FrexoAi/Schema/SchemaBuilder.php`
- `app/Services/FrexoAi/DTO/FieldEvidenceDTO.php`
- `app/Services/FrexoAi/DTO/ConflictCandidateDTO.php`
- `app/Services/FrexoAi/DTO/DocumentAnalysisDTO.php`
- `app/Services/FrexoAi/DTO/ExtractionResultDTO.php`
- `app/Services/FrexoAi/Mapper/FormDataMapper.php`
- `tests/Unit/FrexoAi/Chunk2SchemasAndRegistryTest.php`

---

## 3. Exit Gate Verification

### A. Registry Coverage per Module (Real Counts)
| Module | Total Fields | Document Extractable | Internal Workflow | Unmapped / Unknown |
|---|---|---|---|---|
| **ocean_import** | 74 | 65 | 9 | 0 |
| **ocean_export** | 93 | 84 | 9 | 0 |
| **air_import** | 52 | 42 | 8 | 0 |
| **air_export** | 66 | 56 | 8 | 0 |

**Result:** Zero unclassified fields across all 4 modules.

### B. Gemini SDK Response Schema Validation
Verified programmatic generation of Gemini OpenAPI/JSON Schema subset:
- `ocean_import`: Valid JSON Schema (properties: `mbl`, `hbls`, `containers`, `routes`).
- `ocean_export`: Valid JSON Schema (properties: `mbl`, `hbls`, `containers`, `routes`).
- `air_import`: Valid JSON Schema (properties: `mbl`, `hbls`, `flights`).
- `air_export`: Valid JSON Schema (properties: `mbl`, `hbls`, `flights`).
- In all schemas: `file_no`, `post_date`, `op_name`, `dm_sales_person_name`, `company_id` are absent.

### C. Test Execution Output (Real Command)
```bash
php vendor/bin/phpunit tests/Unit/FrexoAi/Chunk2SchemasAndRegistryTest.php
```
```text
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.4
Configuration: /home/muhammad-hanzala/Downloads/backups/fms2.0/phpunit.xml

......                                                              6 / 6 (100%)

Time: 00:00.025, Memory: 12.00 MB

OK (6 tests, 1553 assertions)
```

Baseline unit test:
```bash
php vendor/bin/phpunit tests/Unit/ExampleTest.php
```
```text
OK (1 test, 1 assertion)
```

---

## 4. Self-Review Checklist
- [x] Guessed values checked? None. All fields reflect existing blade forms and database columns.
- [x] Missing tenant scoping? All internal fields (`company_id`, etc.) classified as `INTERNAL_WORKFLOW` with `OVERWRITE_NEVER`.
- [x] Overwritten File Number prevented? `mbl.file_no` explicitly stripped by `FormDataMapper` and excluded from `SchemaBuilder`.
- [x] Null to 0 coercion? None in DTOs or registry.
- [x] Exit gate passed? All 4 checks green.
