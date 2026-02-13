# Progress Log

---

## 2026-02-10 — Initial Documentation Setup

### Summary

Scaffolded the authoritative documentation structure per `INSTRUCTIONS.md` v1.1. This is the first documentation pass for the research-digest project.

### What was done

- Created `docs/INSTRUCTIONS.md` (collaboration contract and rules)
- Created `docs/PROGRESS.md` (this file)
- Created `docs/BACKLOG.md` (empty structure, ready for items)
- Created `docs/TECH_SPEC.md` (skeleton with required sections)
- Created `docs/OPS.md` (placeholder, procedural)
- Created `CLAUDE.md` (root, AI behavior preferences)
- Confirmed project prefix: **RDIG**
- Initialized git repository
- Added `docs/OPS_PRIVATE.md` to `.gitignore`

### Decisions made

- **Project prefix:** `RDIG` (confirmed by user)
- **Documentation structure:** Following INSTRUCTIONS.md v1.1 standard

### What's next

- Populate BACKLOG.md with known work items
- Replace boilerplate README.md with project-specific content

---

## 2026-02-10 — Tech Spec Populated from Prior Context + Code Review

### Summary

Populated TECH_SPEC.md with real architecture derived from reviewing all application code and consolidating prior conversation context (tech spec v0.1 + Abstractly vision notes).

### What was done

- Reviewed all controllers, services, config files, routes, and views
- Consolidated prior tech spec (v0.1) and Abstractly vision notes into TECH_SPEC.md
- Verified code matches documented architecture — all details confirmed against source
- Documented current sources (16 total: 14 arXiv subfields + bioRxiv + medRxiv)
- Documented all 3 AI providers (Gemini, OpenAI, Ollama) with actual config values
- Captured open questions/risks (session expiry, no caching, missing .env.example vars, etc.)
- Documented planned evolution phases (Stability → Intelligence → Abstractly Vision)

### Decisions made

- TECH_SPEC reflects current state of code, not aspirational features
- Planned evolution captured separately at bottom of TECH_SPEC
- The Shelf integration noted as future in External Integrations

### What's next

- Populate BACKLOG.md with known work items
- Replace boilerplate README.md with project-specific content
- Initialize git and make first commit

---

## 2026-02-10 — README Replaced with Project Content

### Summary

Replaced the Laravel boilerplate README with project-specific content for Abstractly.

### What was done

- Wrote `README.md` with project overview, features, tech stack, quickstart, and environment variable documentation
- Committed as `b6fea33`

### Decisions made

- README reflects current v0.1 state (single discipline, session-based, no auth)
- Links to BACKLOG.md and TECH_SPEC.md included per INSTRUCTIONS.md requirements

### What's next

- Populate BACKLOG.md with known work items

---

## 2026-02-12 — Livewire 3 Migration + Dusk E2E Tests

### Summary

Major frontend architecture migration. Replaced traditional MVC controller-based UI with Livewire 3 full-page components. Added comprehensive Dusk E2E test suite. Updated frontend stack to Tailwind CSS v4 with zero-config setup.

### What was done

- **Livewire 3 components created:**
  - `DisciplinePicker` — replaces `DisciplineController` index/update flows
  - `SourcePicker` — replaces `DisciplineController` show/updateSources flows
  - `DigestViewer` — replaces `DigestController` generate/show flows
- **Routes updated** to mount Livewire components directly as full-page endpoints
- **Companion Blade views created** in `resources/views/livewire/` with modern Tailwind UI:
  - Interactive card grids with toggle, select all/none, save
  - Breadcrumb navigation, loading states, color-coded AI perspectives
  - `wire:navigate` for SPA-like transitions
- **Layout updated** (`components/layouts/app.blade.php`) with sticky nav, responsive container, footer
- **Dusk E2E tests added** (37 test cases across 7 files) covering full user workflow
- **Legacy controllers retained** (`DigestController`, `DisciplineController`) but no longer routed
- **SourceController** remains the only active traditional controller (preview endpoint)
- **Frontend dependencies updated:** Tailwind v4 (`@tailwindcss/vite`), Vite 7, `livewire/livewire` ^3.0

### Decisions made

- Livewire components replace controllers for all interactive UI flows
- Legacy controllers kept in codebase (not deleted) — may serve as reference or future API endpoints
- Source preview remains a traditional controller endpoint (no interactivity needed)
- Tailwind v4 zero-config approach: no `tailwind.config.js`, all config in CSS via `@theme`
- Dusk tests use `chrome-headless-shell-mac-arm64` for native ARM64 support

### What's next

- Sync documentation to reflect Livewire migration (TECH_SPEC was out of date)
- Populate BACKLOG.md with known work items
- Decide whether to remove legacy controllers or keep for API use

---

## 2026-02-12 — Documentation Sync (Codebase Audit)

### Summary

Full codebase audit and documentation sync to bring TECH_SPEC.md and PROGRESS.md in line with the Livewire 3 migration (commit `99b7e89`) and README replacement (commit `b6fea33`) that were not previously logged.

### What was done

- **Audited entire codebase:** Livewire components, controllers, services, config, routes, views, tests, frontend tooling
- **Updated TECH_SPEC.md:**
  - Feature/View Breakdown: all sections updated from controller references to Livewire component references
  - Source Preview: clarified as only active traditional controller endpoint
  - Digest Generation & Display: merged into one section reflecting Livewire reactive pattern
  - Architecture Overview: rewritten with Livewire-first diagram, legacy controller annotations, routing table, updated frontend stack
  - Testing Strategy: expanded from 4 lines to full PHPUnit + Dusk breakdown (37 E2E test cases)
  - Fixed source count: arXiv subfields 13 → 14, total sources 16 → 17
- **Updated PROGRESS.md:** Added entries for README replacement, Livewire migration, and this sync

### Inconsistencies resolved

- TECH_SPEC architecture diagram no longer references controller-based UI flows
- arXiv subfield count corrected (listed 14 codes but labeled as 13)
- Total source count corrected to 17
- PROGRESS.md now reflects all committed work to date

### What's next

- Populate BACKLOG.md with proposed work items (derived from audit findings, TECH_SPEC open questions, and planned evolution phases)

---

## 2026-02-12 — Multi-Discipline Source Expansion

### Summary

Expanded from 1 active discipline (math) to 13 active disciplines with 49 total source entries. Added two new parser types (OSF Preprints, Europe PMC) to `SourcePreviewer`. Added Http::fake() canned responses for all source types in the Dusk test environment. Renamed branding from "Research Digest" to "Abstractly" in the layout.

### What was done

- **30 new sources** added to `config/sources.php` across 12 disciplines:
  - CS & AI: 8 sources (arXiv cs.* categories)
  - Earth & Environmental: 4 sources (arXiv physics + astro-ph + Europe PMC)
  - Economics & Finance: 4 sources (arXiv econ + q-fin)
  - Informatics: 3 sources (arXiv q-bio: QM, GN, MN)
  - Engineering: 3 sources (arXiv eess + cs.RO)
  - Neuroscience: 2 sources (arXiv q-bio.NC + Europe PMC)
  - Pharmacology: 2 sources (arXiv q-bio.BM + Europe PMC)
  - Agriculture: 2 sources (arXiv q-bio.PE + Europe PMC)
  - Psychology: 1 source (PsyArXiv via OSF) + shared q-bio.NC
  - Linguistics: 1 source (arXiv cs.CL)
  - Education: 1 source (EdArXiv via OSF)
  - Communication: 1 source (SocArXiv via OSF)
- **13 of 15 disciplines** set to `ready=true` (only Law and Arts remain disabled)
- **New parser methods** in `SourcePreviewer`:
  - `fetchOsfPreprints()` — handles OSF Preprints JSON:API (PsyArXiv, SocArXiv, EdArXiv)
  - `fetchEuropePmc()` — handles Europe PMC REST JSON
- **Http::fake() canned responses** added to `AppServiceProvider` for Dusk environment (OSF, Europe PMC, in addition to existing arXiv/bioRxiv/medRxiv/Gemini stubs)
- **Branding committed:** layout title and footer changed from "Research Digest" to "Abstractly"
- **Dusk test assertions** updated for branding change
- Committed as `ef07bee`

### Decisions made

- Cross-listing: `arxiv_qbio_NC` (Neurons and Cognition) mapped to both neuroscience and psychology disciplines
- Law and Arts remain `ready=false` — no obvious open-access preprint sources identified yet
- OSF Preprints API used for psychology, education, and communication (PsyArXiv, EdArXiv, SocArXiv)
- Europe PMC used as aggregated preprint source for neuroscience, earth, pharmacology, and agriculture

### What's next

- Re-sync documentation to reflect this expansion (TECH_SPEC source counts, external integrations, parser docs are stale)
- Mark RDIG-007 as Done in BACKLOG.md

---

## 2026-02-12 — Documentation Re-Sync (Post-Expansion)

### Summary

Second documentation sync in this session. Updated TECH_SPEC.md, BACKLOG.md, and PROGRESS.md to reflect the multi-discipline source expansion (commit `ef07bee`).

### What was done

- **Updated TECH_SPEC.md:**
  - Discipline readiness: "only math is ready" → "13 of 15 are ready"
  - Source table: replaced 4-row math-only table with full 13-discipline breakdown (49 sources)
  - Source Fetching: added `fetchOsfPreprints()` and `fetchEuropePmc()` to parser table
  - External Integrations: added OSF Preprints API and Europe PMC REST API
  - Testing Strategy: added note about Http::fake() Dusk test isolation
- **Updated BACKLOG.md:**
  - RDIG-007: moved from Medium to Done with updated description reflecting actual scope (12 disciplines, not 1)
  - RDIG-002: updated description to reflect 49 sources across 5 provider types
  - RDIG-005: updated acceptance criteria to cover all 5 parser types
- **Updated PROGRESS.md:** Added entry for commit `ef07bee` and this sync

### What's next

- Work on backlog items (RDIG-001 through RDIG-006 remain open)
- Law and Arts disciplines still need sources before they can be enabled

---

## 2026-02-12 — Drop Database, Add Digest Export (RDIG-001, RDIG-010)

### Summary

Architectural simplification: removed database dependency entirely. The app is a local-only, single-user research tool — file-based sessions and cache are sufficient. Added digest JSON export feature (local file + browser download). Completed RDIG-001 (AI env vars) and created/completed RDIG-010 (export). Archived RDIG-006 (DB persistence — no longer applicable).

### What was done

- **Removed database dependency:**
  - `.env.example`: SESSION_DRIVER → `file`, CACHE_STORE → `file`, QUEUE_CONNECTION → `sync`, DB_CONNECTION commented out
  - `composer.json`: removed `php artisan migrate --force` from setup script
  - APP_NAME updated to `Abstractly` in `.env.example`
- **Added AI env vars to `.env.example`** (RDIG-001): `GOOGLE_API_KEY`, `DIGEST_AI_PROVIDER`, `DIGEST_AI_MODEL`, `OPENAI_API_KEY`, `DIGEST_AI_MODEL_OPENAI`, `OLLAMA_HOST` with descriptive comments
- **Added digest export** (RDIG-010):
  - `DigestViewer::export()` method saves JSON to `storage/app/digests/` and triggers browser download
  - "Export JSON" button in digest-viewer blade (visible only when digest has content)
  - Created `storage/app/digests/.gitkeep` directory
- **Updated documentation:**
  - TECH_SPEC: Data Model section rewritten for no-database architecture + export feature
  - BACKLOG: RDIG-001 moved to Done, RDIG-006 archived, RDIG-010 added as Done
  - PROGRESS: this entry

### Decisions made

- **No database at all** — file-based sessions, cache, and sync queue are sufficient for local single-user use
- **Export over persistence** — digests are ephemeral by default; user exports interesting ones on demand as JSON
- **RDIG-006 archived** — DB persistence is not needed; if selection persistence becomes needed, a JSON config file approach would be more appropriate
- **RDIG-003 (queue jobs) deprioritized** — sync queue is fine for single-user local; keeping it in backlog but less urgent

### What's next

- Remaining backlog: RDIG-002 (caching), RDIG-003 (queue jobs), RDIG-004 (legacy controllers), RDIG-005 (unit tests), RDIG-008 (dedup), RDIG-009 (wire:navigate)
- Update local `.env` to match new `.env.example` drivers
- Law and Arts disciplines still need sources
