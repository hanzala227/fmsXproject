# Frexo AI Extraction Pipeline — Chunk 5 Report: Tenant Safety, Matching, Apply, Audit

**Date:** 2026-10-24  
**Status:** PASSED  
**Commit:** frexo-ai: chunk 5 - tenant safety matching apply audit  

---

## 1. Summary of Changes

### A. Database Migrations
Created `database/migrations/2026_10_24_220000_create_frexo_ai_pipeline_tables.php` with backward-compatible tables and explicit down rollbacks:
- `frexo_extraction_runs`: UUID primary identifier, tenant `company_id`, `user_id`, `module`, `schema_version`, `status`, `idempotency_token`, `processing_summary`, timestamps.
- `frexo_extraction_documents`: document metadata, file names, page counts, processing limitations.
- `frexo_field_evidence`: verbatim snippets, normalized values, confidence status, document/page pointers.
- `frexo_review_decisions`: per-field audit decisions (ACCEPTED, REJECTED, EDITED).
- `frexo_extraction_audits`: immutable log recording old value, new value, model/prompt versions, source references.

### B. Eloquent Models
Created under `App\Models\FrexoAi\`:
- `ExtractionRun`: Uses `BelongsToTenant` trait with tenant scoping and relationships.
- `ExtractionDocument`: Document tracking per run.
- `FieldEvidence`: Evidential provenance records.
- `ReviewDecision`: Audit trail for user review interactions.
- `ExtractionAudit`: Immutable field change logs.

### C. TenantEntityMatcher
Created `App\Services\FrexoAi\Matching\TenantEntityMatcher`:
- Enforces server-side `auth()->user()->company_id`.
- Rejects unauthenticated lookups or cross-tenant query attempts with `InvalidArgumentException`.
- Matching hierarchy: Exact match -> Normalized token match (stripping legal suffixes) -> Candidate list.
- Never silently auto-creates partners, users, or ports from AI predictions.

### D. Transactional Apply Service
Created `App\Services\FrexoAi\Apply\ExtractionApplyService`:
- Idempotency token checking (`idempotency_token`) to prevent double-submit.
- Optimistic form version checking (`expected_form_version === current_form_version`) to prevent applying to stale state.
- Strictly guards against overwriting immutable identifiers (`file_no`, `id`, `company_id`, and `INTERNAL_WORKFLOW` fields).
- Enforces `FieldRegistry` overwrite policies (`never`, `only_if_empty`).
- Deterministic revalidation using Chunk 4 validators on all submitted fields.
- Atomic execution in `DB::transaction()` writing audit entries and decisions.
- Retention cleanup: deletes temporary extraction directories after apply; never stores full document text.

### E. Controller & Route Exposure
- `POST /api/ai/extract/apply` mapped to `AiExtractionController@apply`.
- `POST /api/ai/extract/match-entity` mapped to `AiExtractionController@matchEntity`.
- Scoped inside `['auth', 'verified']` middleware in `routes/web.php`.

---

## 2. Test Execution & Evidence

### Test Suite: `tests/Unit/FrexoAi/Chunk5TenantApplyTest.php`
```bash
php vendor/bin/phpunit tests/Unit/FrexoAi/Chunk5TenantApplyTest.php
```
**Output:**
```
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.4
Configuration: /home/muhammad-hanzala/Downloads/backups/fms2.0/phpunit.xml

...........                                                       11 / 11 (100%)

Time: 00:00.734, Memory: 38.00 MB

OK (11 tests, 35 assertions)
```

### Cumulative Suite Status
```bash
php vendor/bin/phpunit tests/Unit/FrexoAi/
```
**Output:**
```
PHPUnit 11.5.55 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.4
Configuration: /home/muhammad-hanzala/Downloads/backups/fms2.0/phpunit.xml

......................................                            38 / 38 (100%)

Time: 00:01.587, Memory: 46.50 MB

OK, but there were issues!
Tests: 38, Assertions: 1702, PHPUnit Deprecations: 2.
```

---

## 3. Exit Gate Verification

1. **All listed tests pass:** PASS (11 Chunk 5 tests, 38 total suite tests passing).
2. **Migrations defined with up/down:** PASS (Verified in `Chunk5TenantApplyTest::test_migration_file_exists_and_has_up_and_down`).
3. **No untrusted `company_id` reads:** PASS (Grep verified across all pipeline and controller files; strict server-side session enforcement only).
