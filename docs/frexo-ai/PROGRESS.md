# Frexo AI Extraction Pipeline — Progress State

## Status Matrix
| Chunk | Status | Attempts | Report file | Commit |
|---|---|---|---|---|
| Chunk 1: Audit | PASSED | 1 | docs/frexo-ai/reports/chunk-1.md | frexo-ai: chunk 1 - audit and baseline |
| Chunk 2: Schemas & Field Registry | PASSED | 1 | docs/frexo-ai/reports/chunk-2.md | frexo-ai: chunk 2 - module schemas and field registry |
| Chunk 3: Staged Extraction Pipeline | PASSED | 1 | docs/frexo-ai/reports/chunk-3.md | frexo-ai: chunk 3 - staged extraction pipeline |
| Chunk 4: Deterministic Validation | PASSED | 1 | docs/frexo-ai/reports/chunk-4.md | frexo-ai: chunk 4 - deterministic validation |
| Chunk 5: Tenant Safety, Matching, Apply, Audit | PASSED | 1 | docs/frexo-ai/reports/chunk-5.md | frexo-ai: chunk 5 - tenant safety matching apply audit |
| Chunk 6: Review Modal & Form Application | PASSED | 1 | docs/frexo-ai/reports/chunk-6.md | frexo-ai: chunk 6 - review modal and form application |
| Chunk 7: Resilience & Observability | PASSED | 1 | docs/frexo-ai/reports/chunk-7.md | frexo-ai: chunk 7 - resilience and observability |
| Chunk 8: Testing, Eval Harness, Rollout | PASSED | 1 | docs/frexo-ai/reports/chunk-8.md | frexo-ai: chunk 8 - testing evaluation harness rollout |
| Chunk 9: Final Verification Loop | PASSED | 1 | docs/frexo-ai/reports/chunk-9.md | frexo-ai: chunk 9 - final verification loop and definition of done |

---

## Assumptions
1. Local test environment runs PHP 8.5.4 CLI with MySQL available on VPS/remote but `php8.5-sqlite3` is not installed locally. Thus, baseline feature tests configured with `DB_CONNECTION=sqlite` fail at the PDO driver level. Chunk 4 deterministic validators must be pure unit tests (zero DB dependency) and pipeline tests must use mock drivers or direct Unit test harnesses.
2. The legacy extraction pipeline (`/api/ai/extract/*`) must remain operational when the new staged pipeline feature flag (`FREXO_AI_NEW_PIPELINE=false`) is disabled.
3. Feature flag will default to `false` in config/env.
4. Multimodal extraction supports single and multiple documents up to 10 files (PDF, JPG, PNG, WebP, TXT, CSV).

---

## Open Questions
- None blocking Chunk 1.

---

## Decisions
- Switched to feature branch `feature/frexo-ai-pipeline` locally.
- Chunk 1 is strictly an audit and baseline report; no application code is modified.

---

## Known Limitations
- Local PHP environment lacks `pdo_sqlite` extension, preventing in-memory SQLite feature tests from running locally without MySQL or sqlite driver installation. Pure unit tests run and pass without issue.
