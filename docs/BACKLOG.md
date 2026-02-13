# Backlog

**Project Prefix:** RDIG

---

## Status Flow

```
Planned → In Progress → Done
              ↓
           Blocked
              ↓
           Archived
```

---

## Critical

_No items._

---

## High

### RDIG-002: Cache source fetch results

#### Description

Every digest generation re-fetches all feeds (arXiv, bioRxiv, medRxiv, OSF Preprints, Europe PMC — 49 sources total) with no caching layer. With 13 active disciplines, a full generation can issue dozens of HTTP requests. Repeated generation within the same session or across short intervals still hits external APIs, wasting time and risking rate limiting (arXiv enforcement is unclear but documented as a concern). A cache layer with a configurable TTL (e.g., 1 hour) should sit between `SourcePreviewer::fetch()` and the external HTTP calls, keyed on source URL and limit.

#### Acceptance Criteria

- [ ] Source fetch results are cached with a configurable TTL
- [ ] Cache key includes source URL and limit parameter
- [ ] Cached results are returned without HTTP requests on subsequent calls within TTL
- [ ] Cache can be bypassed or cleared manually (e.g., via artisan command or force-refresh flag)
- [ ] Existing tests still pass; new tests cover cache hit/miss behavior

#### Metadata

- **Status:** Planned
- **Priority:** High
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-003: Move digest generation to a queued job

#### Description

Digest generation currently runs synchronously inside the `DigestViewer` Livewire component with a 120-second timeout. This blocks the browser for the full duration, risks timeout on slow AI providers or many sources, and provides no progress feedback beyond a spinner. Generation should be dispatched to a queue job, with the Livewire component polling for completion or receiving an event when the job finishes. This unblocks the UI and makes generation more resilient to transient failures.

#### Acceptance Criteria

- [ ] Digest generation dispatched as a queued job
- [ ] UI shows meaningful progress state while job runs (polling or event-based)
- [ ] User can navigate away and return to see completed digest
- [ ] Timeout and failure handling work correctly in the queued context
- [ ] Existing Dusk E2E tests updated to account for async generation flow

#### Metadata

- **Status:** Planned
- **Priority:** High
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

## Medium

### RDIG-004: Remove or archive legacy controllers

#### Description

`DigestController` and `DisciplineController` are not referenced in any route and have been fully superseded by Livewire components (`DigestViewer`, `DisciplinePicker`, `SourcePicker`). They remain in `app/Http/Controllers/` as dead code, which adds confusion for future contributors who may assume they are active. A decision is needed: delete entirely, or relocate to a dedicated namespace (e.g., `App\Http\Controllers\Api\`) if they might serve as the basis for future API endpoints.

#### Acceptance Criteria

- [ ] `DigestController` and `DisciplineController` are removed from `app/Http/Controllers/` (or relocated with clear intent)
- [ ] No remaining imports or references to removed controllers
- [ ] Decision rationale logged in PROGRESS.md

#### Metadata

- **Status:** Planned
- **Priority:** Medium
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-005: Add unit tests for SourcePreviewer and AiSummarizer services

#### Description

The two core services — `SourcePreviewer` (feed fetching and parsing) and `AiSummarizer` (multi-provider AI summarization) — contain significant logic with no test coverage. Current unit and feature tests are Laravel example stubs only. `SourcePreviewer` now handles 5 parser types (arXiv Atom, bioRxiv/medRxiv JSON, OSF Preprints JSON:API, Europe PMC REST JSON, and fallback RSS/Atom). `AiSummarizer` handles batch processing, multi-provider dispatch, error handling, and placeholder fallback behavior. Mocked HTTP responses should verify parsing correctness, batching logic, error paths, and graceful degradation. Note: canned response patterns already exist in `AppServiceProvider` for Dusk and can be reused.

#### Acceptance Criteria

- [ ] Unit tests exist for `SourcePreviewer` covering all 5 parser types (Atom, bioRxiv JSON, OSF JSON:API, Europe PMC JSON, RSS fallback)
- [ ] Unit tests exist for `AiSummarizer` covering at least one provider's happy path and failure/placeholder path
- [ ] HTTP calls are mocked (no real external requests in tests)
- [ ] Tests run via `composer test` without additional setup
- [ ] All new tests pass

#### Metadata

- **Status:** Planned
- **Priority:** Medium
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-006: Persist user selections to database

#### Description

Discipline and source selections are currently stored in the session only, which has a 120-minute default lifetime. Returning users must re-select their preferences every time the session expires. A simple persistence layer (e.g., a `user_preferences` table or key-value store) would allow selections to survive across sessions.

**Archived:** Database has been removed from the project entirely. The app is local-only, single-user, and uses file-based sessions. Session-based selections are sufficient for the current use case. If persistence becomes needed, a JSON config file approach would be more appropriate than a database.

#### Metadata

- **Status:** Archived
- **Priority:** Medium
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

## Low

### RDIG-008: Paper deduplication across sources

#### Description

A paper appearing in multiple arXiv subfields (e.g., cross-listed in `math.PR` and `math.ST`) will show up multiple times in the generated digest, each with its own AI summary. This wastes AI summarization budget and clutters the output. Deduplication should happen after fetching and before summarization, keyed on URL or arXiv ID. Duplicates should be collapsed into a single item with a note about which sources it appeared in.

#### Acceptance Criteria

- [ ] Papers with identical URLs or arXiv IDs are deduplicated before AI summarization
- [ ] Deduplicated items retain a reference to all sources they appeared in
- [ ] AI summarization cost is reduced proportionally to duplicates removed
- [ ] Digest display indicates when a paper appeared in multiple sources

#### Metadata

- **Status:** Planned
- **Priority:** Low
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-009: Standardize wire:navigate on all internal links

#### Description

Most internal navigation links use Livewire's `wire:navigate` directive for SPA-like page transitions without full reloads, but some breadcrumb links and navigation elements still use plain `href` attributes. This creates an inconsistent experience where some navigations are instant and others trigger a full page reload. All internal links between Livewire pages should use `wire:navigate` consistently.

#### Acceptance Criteria

- [ ] All internal `<a>` links between Livewire-rendered pages include `wire:navigate`
- [ ] Source preview link (to traditional controller) intentionally excluded from `wire:navigate`
- [ ] No regressions in navigation behavior (Dusk tests pass)

#### Metadata

- **Status:** Planned
- **Priority:** Low
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

## Parking Lot

- **Research radar view** — Visual overview of trending research areas across disciplines
- **Saved papers** — Bookmark individual papers from the digest for later reference
- **Personal trend tracking** — Track which topics and disciplines the user engages with over time
- **Research-to-experiment mapping** — Connect papers to personal project ideas or hypotheses
- **The Shelf integration** — Pipeline from research digest into the attention-management app (cross-project)
- **Ranking by novelty or citations** — Prioritize papers in the digest by impact signals
- **Cross-discipline clustering** — Group related papers that span multiple discipline boundaries

---

## Documented Gaps

_No items._

---

## Done

### RDIG-007: Expand beyond single discipline

#### Description

Originally scoped as "expand to a second discipline." Exceeded scope: 30 new sources added across 12 disciplines, with 13 of 15 disciplines now `ready=true`. New parser methods (`fetchOsfPreprints`, `fetchEuropePmc`) support OSF Preprints and Europe PMC alongside existing arXiv and bioRxiv/medRxiv parsers. Http::fake() canned responses added for all new source types in Dusk environment. Completed in commit `ef07bee`.

#### Acceptance Criteria

- [x] Multiple disciplines set to `ready=true` in `config/disciplines.php`
- [x] Sources added for each ready discipline in `config/sources.php`
- [x] End-to-end digest generation works for new disciplines
- [x] New parsers added for OSF Preprints and Europe PMC
- [x] Dusk test isolation updated with canned responses for all source types

#### Metadata

- **Status:** Done
- **Priority:** Medium
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-001: Document AI-specific environment variables in .env.example

#### Description

Added all 6 AI-provider environment variables to `.env.example` with descriptive comments. Also updated APP_NAME to Abstractly and switched all framework drivers to file-based/sync (removing database dependency entirely).

#### Acceptance Criteria

- [x] `.env.example` includes all 6 AI-related env vars with placeholder values
- [x] Each variable has an inline comment explaining its purpose and default
- [x] `DIGEST_AI_PROVIDER` comment lists valid options (`gemini`, `openai`, `ollama`)

#### Metadata

- **Status:** Done
- **Priority:** High
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-010: Add digest JSON export

#### Description

Added an "Export JSON" action to the digest viewer. When clicked, the current digest is saved as a timestamped JSON file to `storage/app/digests/` and simultaneously downloaded via the browser. This provides a local archive of interesting digests without requiring a database. The export button is only visible when a digest has been generated and contains content.

#### Acceptance Criteria

- [x] "Export JSON" button visible on digest page when digest has content
- [x] Clicking export saves JSON to `storage/app/digests/{date}.json`
- [x] Clicking export triggers browser download of the same file
- [x] Export button hidden when no digest exists

#### Metadata

- **Status:** Done
- **Priority:** High
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No
