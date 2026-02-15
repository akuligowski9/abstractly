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

---

## 2026-02-12 — Config Refactor, Unit Tests, and .env Update (RDIG-005)

### Summary

Centralized AI provider configuration into `config/ai.php`, added comprehensive unit tests for both core services (21 test cases total), cleaned up dead code in `SourcePreviewer`, and updated local `.env` to match the new no-database `.env.example`. Documentation sync triggered by reaching 5 completed backlog items.

### What was done

- **`config/ai.php` created:** Centralizes all AI provider env vars (`DIGEST_AI_PROVIDER`, `GOOGLE_API_KEY`, `DIGEST_AI_MODEL`, `OPENAI_API_KEY`, `DIGEST_AI_MODEL_OPENAI`, `OLLAMA_HOST`, `DIGEST_AI_MODEL_OLLAMA`) into proper Laravel config
- **`AiSummarizer` refactored:** All `env()` calls replaced with `config('ai.*')` calls for consistency with Laravel conventions
- **`SourcePreviewer` cleaned:** Removed dead `hal_math` case from the match statement
- **Unit tests added (RDIG-005):**
  - `SourcePreviewerTest` — 12 test cases covering all 5 parser types, limit, empty feed, HTTP errors
  - `AiSummarizerTest` — 9 test cases covering all 3 providers, missing keys, failures, batching, fallback, placeholder
- **`.env` updated:** `APP_NAME=Abstractly`, DB_CONNECTION/DB_DATABASE commented out
- **Documentation sync:**
  - TECH_SPEC: `config/ai.php` added to architecture diagram, AI config reference updated, unit test coverage table added
  - BACKLOG: RDIG-005 moved to Done
  - PROGRESS: this entry
- Committed as `90bce15` (code + tests), documentation committed separately

### Decisions made

- All AI config centralized in `config/ai.php` — `AiSummarizer` no longer calls `env()` directly
- `APP_ENV=dusk` left as-is in `.env` (activates Http::fake() stubs in AppServiceProvider — user aware)
- Dead `hal_math` source case removed rather than left as commented-out code

### What's next

- Remaining backlog: RDIG-002 (caching), RDIG-003 (queue jobs), RDIG-004 (legacy controllers), RDIG-008 (dedup), RDIG-009 (wire:navigate)
- Law and Arts disciplines still need sources

---

## 2026-02-12 — Legacy Controller Cleanup + wire:navigate Standardization (RDIG-004, RDIG-009)

### Summary

Two maintenance backlog items completed as a batch. Deleted dead legacy controllers and standardized `wire:navigate` across all internal links for consistent SPA-like navigation.

### What was done

- **RDIG-004 — Deleted legacy controllers:**
  - Removed `app/Http/Controllers/DigestController.php` (80 lines)
  - Removed `app/Http/Controllers/DisciplineController.php` (105 lines)
  - Verified zero PHP references remain (no routes, imports, or tests)
  - Only `Controller.php` (base class) and `SourceController.php` (preview endpoint) remain
- **RDIG-009 — Added `wire:navigate` to 8 internal links:**
  - `components/layouts/app.blade.php`: logo link, Disciplines nav link, Digest nav link (3 links)
  - `livewire/discipline-picker.blade.php`: "View digest" button (1 link)
  - `sources/preview.blade.php`: "Disciplines" breadcrumb, discipline label breadcrumb, "Back to {label}" button (3 links)
  - `livewire/source-picker.blade.php`: "Preview" button (1 link — navigates to traditional controller but benefits from smoother transition)
- **Documentation updated:** BACKLOG.md (both items moved to Done), PROGRESS.md (this entry)

### Decisions made

- **Delete over relocate** for legacy controllers — no API endpoints are planned, and the Livewire component logic has diverged enough that the old controllers wouldn't be a useful starting point
- **Include preview link** in `wire:navigate` standardization — even though it targets a traditional controller endpoint, `wire:navigate` still provides a smoother transition experience with no downside

### What's next

- Remaining backlog: RDIG-002 (caching, in progress separately), RDIG-003 (queue jobs), RDIG-008 (dedup)
- Law and Arts disciplines still need sources

---

## 2026-02-12 — Source Fetch Caching (RDIG-002)

### Summary

Added a cache layer to `SourcePreviewer::fetch()` with configurable TTL. Repeated digest generations within the TTL window now return cached results without issuing HTTP requests. Cache bypass is available via UI checkbox, query parameter, and artisan command.

### What was done

- **`config/sources.php`:** Added `'cache_ttl' => env('SOURCE_CACHE_TTL', 3600)` config key
- **`.env.example`:** Added `SOURCE_CACHE_TTL=3600` with descriptive comment block
- **`app/Services/SourcePreviewer.php`:** Cache read/write wrapping the `match` dispatch in `fetch()`. Cache key is `source_preview:` + md5 of URL + limit. TTL=0 disables caching entirely. `$forceRefresh` parameter forgets key before fetching. Exceptions bubble up uncached.
- **`app/Livewire/DigestViewer.php`:** Added `public bool $forceRefresh = false` property, passed through to `fetch()`
- **`app/Http/Controllers/SourceController.php`:** Reads `?fresh=1` query param, passes as `$forceRefresh` to `fetch()`
- **`app/Console/Commands/ClearSourceCache.php`:** New artisan command `source:clear-cache` that calls `Cache::flush()`
- **`resources/views/livewire/digest-viewer.blade.php`:** Added "Skip cache" checkbox bound to `wire:model="forceRefresh"` next to the Generate button
- **`tests/Unit/SourcePreviewerTest.php`:** 5 new test cases — cache on first call, cached return without HTTP, force refresh bypass, different limits = different keys, TTL=0 disables caching
- **Documentation:** BACKLOG.md (RDIG-002 moved to Done), PROGRESS.md (this entry)
- Committed as `0aeab73`

### Decisions made

- **Cache inside `fetch()`** rather than at the caller level — all callers (DigestViewer, SourceController) benefit automatically
- **`Cache::flush()` in artisan command** — safe because nothing else uses the cache store in this app
- **TTL=0 as kill switch** — allows disabling caching entirely without code changes
- **Exceptions not cached** — transient failures should be retried on the next call, not served from cache

### What's next

- Remaining backlog: RDIG-003 (queue jobs), RDIG-008 (dedup)
- Law and Arts disciplines still need sources

---

## 2026-02-12 — RDIG-003 Revised: Streaming over Queue

### Summary

Revised RDIG-003 from "move digest generation to a queued job" to "stream digest generation with progressive UI using Livewire 3's `$this->stream()`." A full queue stack (driver, workers, polling, failure handling) is unnecessary overhead for a local single-user app. Livewire streaming achieves the same UX goal — progressive results instead of a blocked spinner — without infrastructure changes.

### What was done

- **BACKLOG.md:** Rewrote RDIG-003 title, description, and acceptance criteria to reflect the streaming approach. Added context on why the queue approach was dropped and how RDIG-002 (caching) complements this change.
- **PROGRESS.md:** This entry.

### Decisions made

- **Streaming over queuing** — `$this->stream()` pushes partial HTML to the browser as each source completes. No queue driver, workers, or polling needed.
- **Queue approach archived** — original description preserved in git history (pre-edit state) but the backlog now reflects the revised plan
- **Caching as complement** — RDIG-002's cache layer means only cold first-run generations are slow; streaming addresses the UX of those cold runs specifically

### What's next

- Implement RDIG-003 (Livewire streaming)
- Remaining backlog: RDIG-008 (dedup)
- Law and Arts disciplines still need sources

---

## 2026-02-12 — Paper Deduplication Across Sources (RDIG-008)

### Summary

Added per-discipline paper deduplication between the fetch and summarize stages of digest generation. Cross-listed papers (e.g., appearing in both `math.PR` and `math.ST`) now appear only once — under the first source that returned them — with small gray pill badges indicating other sources that also had the paper. This saves AI summarization budget and reduces digest clutter.

### What was done

- **New file: `app/Services/PaperDeduplicator.php`** — Stateless service with two public methods:
  - `dedup(array $fetchedBySource): array` — Removes duplicates from later sources, annotates first occurrences with `also_in` (list of other source labels). Fast-paths for 0–1 sources. Items with non-dedupable URLs (`#`, empty) always kept.
  - `normalizeUrl(string $url): ?string` — Lowercases, upgrades http→https, strips trailing slash, strips arXiv version suffix (`v1`, `v3`, etc.). Returns null for `#`, empty, whitespace. Does not affect DOI or other non-arXiv URLs.
- **New file: `tests/Unit/PaperDeduplicatorTest.php`** — 25 test cases across 5 groups:
  - `normalizeUrl` null returns (3): `#`, empty, whitespace
  - `normalizeUrl` transformations (7): lowercase, http→https, trailing slash, arXiv version strip (v1, vN), non-arXiv preservation, DOI preservation
  - `dedup` fast paths (2): empty input, single source
  - `dedup` core behavior (5): single dupe removed, also_in annotation, multi-source dupe, mixed unique/dupe, no also_in on unique items
  - `dedup` edge cases (8): `#` URLs kept, http/https normalization, arXiv version normalization, fully-emptied source, DOI dedup, empty URL items, source order preservation, also_in survives `+` merge operator
- **Modified: `app/Livewire/DigestViewer.php`** — Refactored inner loop from sequential fetch+summarize to three-pass:
  1. Fetch all sources for the discipline into `$fetchedBySource`
  2. Run `PaperDeduplicator::dedup()` to remove cross-source duplicates
  3. Summarize only unique items per source (sources emptied by dedup are skipped)
- **Modified: `resources/views/livewire/partials/digest-section.blade.php`** — Added `also_in` badge display between the paper title link and the summary paragraph. Small gray pill badges with metadata styling.
- **Updated documentation:** BACKLOG.md (RDIG-008 moved to Done), PROGRESS.md (this entry)

### Decisions made

- **Dedup per-discipline only** — Cross-discipline duplication (e.g., `arxiv_qbio_NC` in both Neuroscience and Psychology) is intentional and left alone
- **First occurrence wins** — The paper stays in whichever source appears first in the config ordering; later sources lose their copy
- **`also_in` is additive** — The key survives through `AiSummarizer::mergeBatchSummaries()` because PHP's `+` operator gives left-side precedence, so existing keys on the item are preserved
- **Stateless service** — `PaperDeduplicator` has no dependencies; injected via Livewire method injection like `SourcePreviewer` and `AiSummarizer`

### What's next

- Implement RDIG-003 (Livewire streaming)
- Law and Arts disciplines still need sources

---

## 2026-02-12 — Streaming Digest Generation (RDIG-003)

### Summary

Replaced the static loading spinner with Livewire 3 streaming. Digest sections now render progressively as each discipline completes, and a real-time status bar shows which source is being fetched or summarized. No queue infrastructure — the generation remains synchronous on the server, but the UI updates incrementally via `$this->stream()`.

### What was done

- **New file: `resources/views/livewire/partials/digest-section.blade.php`** — Extracted discipline section markup into a reusable partial. Used for both streaming (rendered to HTML via `view()->render()`) and the final Livewire re-render (via `@include`).
- **Modified: `resources/views/livewire/digest-viewer.blade.php`** — Replaced the static loading overlay with two `wire:loading` areas:
  - Progress status bar with `wire:stream="progress-status"` (replaces text in-place)
  - Streamed results container with `wire:stream="digest-stream"` (appends discipline sections)
  - Final content area (`wire:loading.remove`) uses `@include` of the partial for the post-action render
- **Modified: `app/Livewire/DigestViewer.php`** — Added `$this->stream()` calls in `generate()`:
  - Before each source fetch: `"Fetching {source label}…"` → `progress-status` (replace)
  - Before each AI summarization: `"Summarizing {source label}…"` → `progress-status` (replace)
  - After each discipline completes: rendered section HTML → `digest-stream` (append)
- **Documentation:** BACKLOG.md (RDIG-003 moved to Done), PROGRESS.md (this entry)
- Committed as `456aa49`

### Decisions made

- **Streaming over queuing** — `$this->stream()` achieves the same progressive UX without queue drivers, workers, or polling. Appropriate for a local single-user app.
- **`wire:loading` structure preserved** — During the action, `wire:loading` divs show the progress bar + streamed results. After the action, `wire:loading.remove` shows the final rendered digest. Content is identical, so the swap is seamless.
- **Blade partial extraction** — Single source of truth for discipline section markup, avoiding duplication between streamed HTML and the final Livewire render.

### What's next

- All formal backlog items are complete
- Law and Arts disciplines still need sources
- Parking lot ideas available for future work

---

## 2026-02-12 — Gap Analysis Batch: RDIG-011 through RDIG-018

### Summary

Batch implementation of 8 gap-analysis items identified from a tech spec audit. Covers error visibility, AI caching, Dusk E2E coverage, rate limiting, export metadata, session lifetime UX, config validation, and enabling the final two disciplines. All 15 disciplines are now active with 51 total sources.

### What was done

- **RDIG-011 (Critical) — Surface failed sources:**
  - Added `$failures` array to `DigestViewer`, populated in catch blocks with source label and error type (`fetch` or `summarize`)
  - Added amber warning banner to `digest-viewer.blade.php` listing failed sources after generation

- **RDIG-012 (High) — Cache AI summaries:**
  - Added cache layer to `AiSummarizer::summarizeItems()` keyed on `ai_summary:` + md5(lowercase URL)
  - Extracted `callProvider()` and `summaryCacheKey()` helpers
  - Configurable via `AI_SUMMARY_CACHE_TTL` env var (default 24h, 0 disables)
  - `forceRefresh` flag passes through from DigestViewer to bypass both source and summary caches
  - 5 new unit tests for cache behavior

- **RDIG-013 (Medium) — Dusk tests for shipped features:**
  - Created `tests/Browser/DigestFeaturesTest.php` with 9 test cases
  - Covers: export button visibility, skip cache checkbox, streaming elements, also_in badges, session lifetime warning, failure banner

- **RDIG-014 (Medium) — Rate-limit API calls:**
  - Added `AI_BATCH_DELAY_MS` config (default 200ms) with `usleep()` between AI batches
  - All 3 providers now use `->retry(2, ...)` with conditional 429 backoff and `throw: false`
  - Added `batchDelay()` helper to AiSummarizer

- **RDIG-015 (Medium) — Export metadata envelope:**
  - Wrapped digest JSON export in `{ meta: { generated_at, format_version, disciplines, sources }, digest: [...] }`
  - Also writes to `storage/app/digests/` for local archival

- **RDIG-016 (Medium) — Session lifetime warning:**
  - Added help text below save buttons on both discipline-picker and source-picker views
  - Text reads: "Selections are stored in your browser session and persist for N minutes of inactivity"

- **RDIG-017 (Low) — Config validation:**
  - Created `tests/Unit/ConfigIntegrityTest.php` with 7 test cases
  - Validates: unique keys, required fields, non-empty URLs, discipline references, labels, ready flags

- **RDIG-018 (Low) — Law and Arts sources:**
  - Added LawArXiv (`lawarxiv_recent`) for Law, MediArXiv (`mediarxiv_recent`) for Arts (both OSF Preprints)
  - Set both disciplines to `ready=true` — all 15 disciplines now active
  - Existing `api.osf.io/*` Http::fake() stub covers new sources

### Test suite

- 64 tests, 538 assertions — all passing
- 9 new Dusk test cases (DigestFeaturesTest)
- 7 new unit tests (ConfigIntegrityTest)
- 5 new unit tests (AiSummarizerTest cache behavior)

### Decisions made

- **Batch implementation** — User explicitly requested all 8 items be worked on together rather than one-at-a-time
- **429 retry with `throw: false`** — Necessary to preserve existing placeholder-on-failure logic in AiSummarizer
- **LawArXiv and MediArXiv** selected as sources for Law and Arts respectively — both are OSF Preprints servers, reusing the existing `fetchOsfPreprints` parser
- **No new Http::fake() stubs needed** — existing `api.osf.io/*` wildcard covers all OSF-based sources

### What's next

- Commit all changes
- Documentation sync (triggered by 5+ backlog items completed)
- Parking lot ideas available for future work

---

## 2026-02-13 — Saved Papers Feature (RDIG-019)

### Summary

Added a "Saved Papers" feature that lets users bookmark individual papers from the digest for later reference. Papers persist in a local JSON file (`storage/app/saved-papers.json`), surviving session expiry. Includes a dedicated `/saved` page with viewing, removing, and exporting capabilities, plus a nav link with count badge.

### What was done

- **New file: `app/Services/SavedPapersRepository.php`** — Stateless read/write service with in-memory cache per request. Public methods: `all()`, `save()`, `remove()`, `has()`, `savedUrls()`, `count()`, `clear()`, `export()`. Constructor accepts optional `$path` for testability. File format: `{ format_version: 1, papers: [...] }`.

- **New file: `tests/Unit/SavedPapersRepositoryTest.php`** — 20 test cases across 7 groups: empty state (4), save (7 — persist, saved_at, field preservation, multiple, ordering, dedup, timestamp update), remove (3), clear (1), savedUrls (1), export (2), file format (2 — format_version, corruption recovery).

- **Modified: `app/Livewire/DigestViewer.php`** — Added `$savedUrls` public array property, populated in `mount()` via repository injection. Added `toggleSave(string $url)` action that toggles save/remove and refreshes `$savedUrls`. Added private `findPaperByUrl()` helper that walks the nested `digest[].sections[].items[]` structure and extracts the paper with discipline/source context. Streaming render now passes `$savedUrls` to the partial.

- **Modified: `resources/views/livewire/partials/digest-section.blade.php`** — Paper title wrapped in flex container with bookmark toggle button. When `$savedUrls` is defined (Livewire render): functional toggle with `wire:click="toggleSave(...)"`, filled amber bookmark icon when saved, outline gray when not. When `$savedUrls` is not defined (streaming render): inert gray placeholder icon, no `wire:click`.

- **Modified: `resources/views/livewire/digest-viewer.blade.php`** — `@include` now passes `$savedUrls` to the digest-section partial.

- **New file: `app/Livewire/SavedPapers.php`** — Full-page Livewire component at `/saved`. Methods: `mount()` (loads papers), `removePaper()` (single remove), `clearAll()` (with `wire:confirm`), `export()` (JSON streamDownload).

- **New file: `resources/views/livewire/saved-papers.blade.php`** — Breadcrumb navigation, header with count and Export/Clear All buttons, empty state with bookmark icon and link to digest, paper cards with title link, discipline badge (indigo), source badge (gray), relative timestamp, also_in badges, ELI5/SWE/Investor sections (same color-coded borders as digest), X remove button, and footer persistence note.

- **Modified: `routes/web.php`** — Added `Route::get('/saved', SavedPapers::class)->name('saved.index')`.

- **Modified: `resources/views/components/layouts/app.blade.php`** — Added `@inject('savedPapers', ...)` and "Saved" nav link after "Digest" with amber count badge when `count > 0`.

- **Modified: `tests/Browser/DigestFeaturesTest.php`** — Added 3 new Dusk test cases: bookmark button present on generated digest, bookmark toggle saves paper (verifies on `/saved` page), empty state on `/saved` page. Added `tearDown()` to clean up `saved-papers.json`.

- **Updated documentation:** BACKLOG.md (RDIG-019 added to Done, removed from Parking Lot), PROGRESS.md (this entry).

### Test suite

- 84 unit/feature tests, 587 assertions — all passing
- 20 new unit tests (SavedPapersRepositoryTest)
- 3 new Dusk test cases (DigestFeaturesTest)

### Decisions made

- **JSON file over database** — Consistent with the project's no-database architecture. File at `storage/app/saved-papers.json` is already git-ignored by the existing wildcard.
- **Stateless repository with in-memory cache** — Avoids redundant file reads within the same request. Fresh instance per request ensures consistency.
- **Dedup by URL on save** — Re-saving the same paper updates `saved_at` timestamp rather than creating duplicates.
- **Streaming render uses inert placeholder** — During `$this->stream()`, `$savedUrls` is not available in the Blade context, so an inert gray bookmark icon is shown. After the action completes and Livewire re-renders, the functional toggle replaces it.
- **`@inject` in layout** — Provides the saved papers count to the nav badge without modifying every Livewire component that uses the layout.

### What's next

- Commit all changes
- Parking lot ideas available for future work

---

## 2026-02-13 — Docker Setup for Local Development (RDIG-020)

### Summary

Added Docker support for zero-friction local development, addressing GitHub issue #3. Three-stage multi-stage Dockerfile handles composer deps, Vite/Tailwind build (with vendor for `@source` scanning), and a `php:8.4-cli` runtime. Named volume persists saved papers, cache, and sessions across restarts.

### What was done

- **New file: `.dockerignore`** — Excludes `vendor/`, `node_modules/`, `.git/`, `.env`, IDE dirs, test artifacts, `public/build/`, `bootstrap/cache/*.php`, and docs from Docker build context
- **New file: `docker-entrypoint.sh`** — Runtime init script: copies `.env.example` → `.env` if missing, generates `APP_KEY` via `key:generate --show` + `sed`, caches config/routes/views, then `exec "$@"`
- **New file: `Dockerfile`** — Three-stage build:
  - Stage 1: `composer:2` with `--no-dev --ignore-platform-req=php+` (composer image ships PHP 8.5, lockfile deps cap at 8.4)
  - Stage 2: `node:20-alpine` runs `npm ci` + `npm run build` with vendor copied from stage 1 (Tailwind v4 `@source` needs vendor blade files)
  - Stage 3: `php:8.4-cli` with bcmath, opcache, pcntl, zip extensions; `php artisan package:discover` regenerates manifest for production-only deps; `artisan serve` as CMD
- **New file: `docker-compose.yml`** — Single `app` service, port 8000, `env_file: .env`, named volume `app-storage` for `storage/`, `restart: unless-stopped`
- **Modified: `README.md`** — Added "Docker (recommended)" subsection to Quickstart with 4-line setup instructions; existing setup moved under "Local" subheading
- **Updated documentation:** BACKLOG.md (RDIG-020 added to Done), TECH_SPEC.md (new Docker / Deployment section), PROGRESS.md (this entry)
- Committed as `08754f0`

### Issues encountered and resolved

1. **`composer:2` ships PHP 8.5** — `nette/schema v1.3.2` in lockfile requires PHP 8.1–8.4. Fixed with `--ignore-platform-req=php+` since the final runtime is `php:8.4-cli`
2. **`bootstrap/cache/packages.php` from local dev** — Referenced dev-only service providers (Dusk, Pail, Sail, Collision) causing crash loop. Fixed by adding `bootstrap/cache/*.php` to `.dockerignore` and running `php artisan package:discover` in the Dockerfile
3. **`key:generate --force` failed silently** — Warning "No APP_KEY variable was found in the .env file" despite `APP_KEY=` being present. Replaced with `key:generate --show` + `sed` for reliable key writing

### Decisions made

- **Three stages, not two** — Tailwind v4 `@source` directives scan vendor blade files, requiring composer output in the node build stage
- **`php:8.4-cli` base** — No nginx/apache needed; `artisan serve` is sufficient for single-user local tool
- **`--no-dev` on composer** — Dusk, Pail, PHPUnit not needed in runtime image
- **APP_KEY generated at container start** — Not baked into image layer; uses `--show` + `sed` instead of `--force` for reliability
- **Named volume for `storage/`** — Saved papers, AI summary cache, and sessions persist across container restarts and rebuilds

### What's next

- Parking lot ideas available for future work

---

## 2026-02-14 — arXiv Fetch Fix + Nav Layout Update

### Summary

Fixed a bug where all arXiv source fetches failed due to an HTTP-to-HTTPS redirect, and moved navigation links to the right side of the top bar for better visual hierarchy.

### What was done

- **Bug fix — arXiv 301 redirect:**
  - All arXiv URLs in `config/sources.php` used `http://export.arxiv.org/...` but arXiv now enforces HTTPS, returning a 301 redirect
  - Laravel's HTTP client does not follow redirects by default, so `!$res->ok()` treated the 301 as a failure, throwing `RuntimeException`
  - **Fix:** Updated all 40 arXiv URLs from `http://` to `https://` in `config/sources.php`
  - **Additional:** Bumped `SourcePreviewer` HTTP client timeout from 20s to 45s — arXiv API response times observed at 30+ seconds under normal load
  - Updated TECH_SPEC.md to reflect new timeout value

- **UX — Navigation layout:**
  - Moved Disciplines, Digest, and Saved links from the left side (grouped with logo) to the right side of the top navigation bar
  - Logo remains on the left; existing `justify-between` flex container handles the spacing
  - Modified `resources/views/components/layouts/app.blade.php`

### Files changed

| File | Change |
|------|--------|
| `config/sources.php` | All arXiv URLs: `http://` → `https://` |
| `app/Services/SourcePreviewer.php` | Timeout: 20s → 45s |
| `resources/views/components/layouts/app.blade.php` | Nav links moved to right side |
| `docs/TECH_SPEC.md` | HTTP client timeout updated to 45s |

### Decisions made

- **URL fix over redirect-following** — Updating the URLs is cleaner than adding redirect-following behavior to the HTTP client. The root cause was stale URLs, not a missing feature.
- **45s timeout** — arXiv observed at 30+ seconds for single-result queries. 45s gives headroom without being excessively long. The per-request `set_time_limit(120)` in DigestViewer still governs the overall generation timeout.

### What's next

- Parking lot ideas available for future work
