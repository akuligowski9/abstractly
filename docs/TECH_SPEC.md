# Technical Specification

**Project:** Abstractly (research-digest)
**Version:** v0.1
**Last Updated:** 2026-02-12

---

## Purpose

Build a web application that aggregates newly published scientific research from reputable open sources and generates a weekly AI-assisted digest tailored to the user.

The system should:

1. Collect recent research items from selected sources.
2. Normalize metadata (title, abstract, link).
3. Generate AI summaries per paper.
4. Present summaries grouped by discipline and source.
5. Allow users to control which disciplines and sources are included.

The system prioritizes:

- Open-access research sources
- Structured metadata (Atom / RSS / JSON APIs)
- Explainability and readability over academic completeness
- Incremental expansion across disciplines

**Long-term vision (Abstractly):** A research radar and decision-support layer for technical curiosity — research ingestion, AI interpretation, and actionable insight. Eventually integrates with The Shelf (personal attention-management app) to create a research-to-project pipeline.

---

## Non-goals

- Academic citation management
- Full-text paper storage or PDF hosting
- Replacing peer review or editorial processes
- Real-time streaming of papers (batch/periodic is fine)
- Multi-user accounts or auth (single-user for now)

---

## Core Conceptual Model

```
Discipline
    └── Source
            └── Research Item (Paper)
                    └── AI Summary (eli5, swe, investor)
```

### Example

```
Mathematics
    └── arXiv — Algebraic Geometry
            ├── Paper A → { eli5, swe, investor }
            ├── Paper B → { eli5, swe, investor }
            └── Paper C → { eli5, swe, investor }
```

---

## Feature / View Breakdown

### Discipline Management

**Component:** `App\Livewire\DisciplinePicker`
**View:** `livewire/discipline-picker.blade.php`
**Route:** `GET /disciplines` (Livewire full-page component)

- Users can enable/disable disciplines via interactive card grid
- Toggle individual disciplines, or use Select All / Select None
- Selection stored in session (`enabled_disciplines`)
- Only `ready=true` disciplines are selectable and participate in digest generation
- Non-ready disciplines appear with "Coming soon" badge and are visually disabled
- All 15 disciplines are `ready=true` and selectable

**Configuration:** `config/disciplines.php`

Fields: `slug`, `label`, `ready` (boolean)

Includes alias mapping for common typos/synonyms (e.g., `neuro` → `neuroscience`, `comp_sci` → `cs`).

---

### Source Management

**Component:** `App\Livewire\SourcePicker`
**View:** `livewire/source-picker.blade.php`
**Route:** `GET /disciplines/{slug}` (Livewire full-page component)

- Users see all sources for a discipline with breadcrumb navigation back to discipline picker
- Can enable/disable sources per discipline via interactive card list
- Toggle individual sources, or use Select All / Select None
- Kind badges color-coded: primary (indigo), json (emerald), other (gray)
- Preview links open raw source preview (traditional controller endpoint)
- Selection stored in session (`enabled_sources.{slug}`)
- Default "starter pack" per discipline (e.g., math gets arXiv core + select subfields + bioRxiv/medRxiv)

**Configuration:** `config/sources.php`

Fields: `key`, `label`, `kind` (primary | json), `disciplines[]`, `url`, `signal`, `notes`

**Current sources (51 entries across 15 disciplines):**

| Discipline | Sources | Provider Types |
|------------|---------|---------------|
| Mathematics | 17 (arxiv_math_all + 14 subfields + bioRxiv + medRxiv) | Atom, JSON |
| Computer Science & AI | 8 (arxiv_cs_all + 7 subfields) | Atom |
| Earth & Environmental | 4 (3 arXiv physics/astro + Europe PMC) | Atom, JSON |
| Economics & Finance | 4 (3 arXiv econ + arXiv q-fin) | Atom |
| Informatics | 3 (arXiv q-bio: QM, GN, MN) | Atom |
| Engineering | 3 (arXiv eess: SP, SY + cs.RO) | Atom |
| Neuroscience | 2 (arXiv q-bio.NC + Europe PMC) | Atom, JSON |
| Pharmacology | 2 (arXiv q-bio.BM + Europe PMC) | Atom, JSON |
| Agriculture | 2 (arXiv q-bio.PE + Europe PMC) | Atom, JSON |
| Psychology | 1 (PsyArXiv via OSF) + shared q-bio.NC | JSON, Atom |
| Law | 1 (LawArXiv via OSF) | JSON |
| Arts | 1 (MediArXiv via OSF) | JSON |
| Linguistics | 1 (arXiv cs.CL) | Atom |
| Education | 1 (EdArXiv via OSF) | JSON |
| Communication | 1 (SocArXiv via OSF) | JSON |

One source (`arxiv_qbio_NC`) is cross-listed to both Neuroscience and Psychology.

---

### Source Preview

**Controller:** `App\Http\Controllers\SourceController@preview`
**View:** `sources/preview.blade.php` (traditional Blade, not Livewire)
**Route:** `GET /disciplines/{slug}/sources/{key}/preview`

- Preview latest entries from a single source
- Limit parameter (1–10, default 5)
- No AI summarization — raw titles and abstracts
- Breadcrumb navigation back to discipline source picker
- This is the only active traditional controller endpoint in the application

---

### Digest Generation & Display

**Component:** `App\Livewire\DigestViewer`
**View:** `livewire/digest-viewer.blade.php`
**Route:** `GET /digest` (Livewire full-page component)

Generation is triggered in-page via Livewire action (no POST/redirect). The component handles both generation and display. Results stream progressively via Livewire 3's `$this->stream()` — discipline sections appear as they complete rather than all-at-once after a long wait.

**Generation flow (triggered by `wire:click="generate"`):**

1. Read enabled disciplines from session
2. Filter to `ready` disciplines
3. For each discipline:
   a. **Pass 1 — Fetch all:** Fetch items from every enabled source via `SourcePreviewer` (errors caught per-source). Status streamed: "Fetching {source}…"
   b. **Pass 2 — Dedup:** Run `PaperDeduplicator::dedup()` to remove cross-source duplicates within the discipline. First occurrences annotated with `also_in` (list of other source labels). Status streamed: "Deduplicating {discipline}…"
   c. **Pass 3 — Summarize:** Send only unique items per source to `AiSummarizer`. Sources emptied by dedup are skipped entirely. Status streamed: "Summarizing {source}…"
4. Render completed discipline section to HTML and stream to browser via `digest-stream`
5. After all disciplines complete, store full digest in session (`digest.latest`)

**UI states:**

- **Empty state:** Icon + message when no digest exists or has no items
- **Generating state:** Progress status bar showing current operation + completed sections rendered progressively (`wire:stream`)
- **Generated state:** Hierarchical display with color-coded AI perspectives

**Display structure:**

```
Discipline (H2)
    Source (card, bg-white rounded-xl shadow-sm)
        Paper
            Title (linked to source)
            Also in: [Source B] [Source C]  (gray pill badges, if cross-listed)
            Summary (truncated, line-clamp-3)
            ELI5        (green left border)
            Solo SWE    (blue left border)
            Investor    (amber left border)
```

Fixed limit of 5 items per source. Request timeout of 120 seconds.

---

## Architecture Overview

The application uses a **Livewire 3-first architecture**. Core user-facing flows are handled by Livewire full-page components mounted directly as route targets. A single traditional controller remains for source preview.

```
Laravel 12 (PHP 8.2+) + Livewire 3
│
├── Livewire Components (primary UI layer)
│     ├── DisciplinePicker        (enable/disable disciplines)
│     ├── SourcePicker            (enable/disable sources per discipline)
│     ├── DigestViewer            (generate + display digest)
│     └── SavedPapers             (view + manage bookmarked papers)
│
├── Controllers
│     └── SourceController        (preview a single source — only active controller)
│
├── Services
│     ├── SourcePreviewer         (fetch + normalize feed data)
│     ├── AiSummarizer           (multi-provider AI summaries)
│     ├── PaperDeduplicator      (per-discipline URL-based dedup)
│     └── SavedPapersRepository  (JSON file-backed paper bookmarks)
│
├── Console Commands
│     └── ClearSourceCache       (artisan source:clear-cache)
│
├── Config
│     ├── ai.php                  (AI provider settings — centralizes env vars)
│     ├── disciplines.php         (discipline registry)
│     └── sources.php             (source registry + cache TTL)
│
├── Views
│     ├── livewire/
│     │     ├── discipline-picker (Livewire companion view)
│     │     ├── source-picker     (Livewire companion view)
│     │     ├── digest-viewer     (Livewire companion view)
│     │     └── partials/
│     │           └── digest-section (reusable discipline section partial)
│     ├── sources/preview         (traditional Blade view)
│     └── components/layouts/app  (shared layout shell)
│
└── Frontend
      ├── Vite 7 (vite.config.js)
      ├── Tailwind CSS v4 (zero-config, CSS-first via @tailwindcss/vite)
      └── Alpine.js (bundled with Livewire 3)
```

### Routing

Routes mount Livewire components directly as full-page endpoints:

```
GET /                          → redirect to disciplines.index
GET /disciplines               → DisciplinePicker::class
GET /disciplines/{slug}        → SourcePicker::class
GET /disciplines/{slug}/sources/{key}/preview → SourceController@preview
GET /digest                    → DigestViewer::class
GET /saved                     → SavedPapers::class
```

Livewire `wire:navigate` is used on internal links for SPA-like page transitions without full reloads.

---

## Data Model

### Current: Config + Session + File Export (No Database)

- **No database** — all Laravel framework drivers use file-based, in-memory, or sync alternatives
  - Session driver: `file` (stored in `storage/framework/sessions/`)
  - Cache driver: `array` (in-memory, per-request — no persistent cache between requests)
  - Queue driver: `sync` (jobs run inline, no worker process)
- Disciplines and sources are config-driven (`config/disciplines.php`, `config/sources.php`)
- User selections stored in session (`enabled_disciplines`, `enabled_sources.{slug}`)
- Digest output stored in session (`digest.latest`) for current-session viewing
- **Digest export:** JSON files saved to `storage/app/digests/` on demand via "Export JSON" action, also triggers browser download

### Design Rationale

This is a local-only, single-user research tool. There is no need for a database — file-based sessions persist across requests for the duration of the session lifetime (120 min default), and interesting digests are exported to JSON on demand. This avoids migration management and database dependencies entirely.

---

## Key Flows

### Source Fetching (`SourcePreviewer`)

Parses multiple feed formats via key-based dispatch:

| Format | Parser Method | Sources |
|--------|--------------|---------|
| Atom | `fetchArxiv()` | All arXiv feeds (math, cs, econ, physics, q-bio, eess, astro-ph) |
| bioRxiv/medRxiv JSON | `fetchRxivJson()` | bioRxiv, medRxiv |
| OSF Preprints JSON:API | `fetchOsfPreprints()` | PsyArXiv, SocArXiv, EdArXiv, LawArXiv, MediArXiv |
| Europe PMC REST JSON | `fetchEuropePmc()` | Europe PMC topic searches |
| RSS/Atom (fallback) | `fetchRssOrAtom()` | Any unrecognized source |

Normalized output per item:

```php
[
    'title'   => string,
    'summary' => string,
    'url'     => string,
    'also_in' => string[],  // added by PaperDeduplicator (only if cross-listed)
]
```

HTTP client: browser-like headers, 2 retries, 45s timeout.

### AI Summarization (`AiSummarizer`)

Provider selection via `config('ai.provider')` (backed by `DIGEST_AI_PROVIDER` env var, centralized in `config/ai.php`):

| Provider | Model | Batch Size | Timeout |
|----------|-------|------------|---------|
| `gemini` (default) | `gemini-2.5-flash` | 5 | 40s |
| `openai` | `gpt-4o-mini` | 5 | 40s |
| `ollama` | `llama3.1` | 3 | 60s |

Output per item:

| Field | Purpose |
|-------|---------|
| `eli5` | Plain-language explanation (2–3 sentences) |
| `swe` | Solo developer opportunity framing (2–3 sentences) |
| `investor` | High-risk opportunity thesis (2–3 sentences) |

Structured JSON output enforced via `response_mime_type` (Gemini) or `response_format` (OpenAI).

**Caching:** Summaries are cached per paper URL (keyed on `ai_summary:` + md5 of lowercase URL). Cached papers skip the AI call entirely on repeat generations. TTL configurable via `AI_SUMMARY_CACHE_TTL` (default 24h, 0 disables). Cache is bypassed when "Skip cache" is checked.

**Rate limiting:** Configurable inter-batch delay (`AI_BATCH_DELAY_MS`, default 200ms) via `usleep()` between batches. All providers use `->retry(2)` with exponential backoff on 429 responses.

Graceful degradation: placeholder text on failure per batch.

---

## External Integrations

| Service | Purpose | Auth |
|---------|---------|------|
| arXiv API | Research papers (Atom) | None |
| bioRxiv API | Preprints (JSON) | None |
| medRxiv API | Preprints (JSON) | None |
| OSF Preprints API | PsyArXiv, SocArXiv, EdArXiv, LawArXiv, MediArXiv (JSON:API) | None |
| Europe PMC REST API | Aggregated preprints by topic (JSON) | None |
| Google Gemini API | AI summarization (default) | `GOOGLE_API_KEY` |
| OpenAI API | AI summarization (optional) | `OPENAI_API_KEY` |
| Ollama | Local AI summarization (optional) | None (local) |

**Future:** The Shelf API (for research-to-project pipeline).

---

## Docker / Deployment

The project includes a Docker setup for zero-friction local development. Clone, configure one env var, `docker compose up`, done.

### Container Architecture

Three-stage multi-stage Dockerfile:

```
Stage 1 — composer:2
  └─ composer install --no-dev --ignore-platform-req=php+

Stage 2 — node:20-alpine
  ├─ npm ci
  ├─ COPY vendor/ from stage 1 (Tailwind v4 @source scans vendor blade files)
  └─ npm run build → produces public/build/

Stage 3 — php:8.4-cli (final image)
  ├─ Extensions: bcmath, opcache, pcntl, zip
  ├─ COPY app source + vendor from stage 1 + public/build from stage 2
  ├─ php artisan package:discover (regenerates manifest for --no-dev deps)
  └─ ENTRYPOINT → docker-entrypoint.sh
      CMD → php artisan serve --host=0.0.0.0 --port=8000
```

**Why three stages:** Tailwind v4 `@source` directives in `resources/css/app.css` scan `vendor/` blade files (e.g., Laravel pagination, Livewire). The composer stage feeds vendor to both the node build stage and the final runtime image.

**Why `php:8.4-cli`:** No webserver needed — `artisan serve` is sufficient for a single-user local tool. Debian-based (not Alpine) for reliable extension installs.

### Runtime Entrypoint (`docker-entrypoint.sh`)

Runs at container start (not baked into the image):

1. Copies `.env.example` → `.env` if no `.env` file exists
2. Generates `APP_KEY` via `key:generate --show` + `sed` if not set
3. Caches config, routes, and views for performance
4. `exec "$@"` passes through to CMD

### Docker Compose

```yaml
services:
  app:
    build: .
    ports: ["8000:8000"]
    env_file: [.env]           # passes AI API keys into the container
    volumes:
      - app-storage:/var/www/html/storage   # persists saved papers, cache, sessions
    restart: unless-stopped
```

Named volume `app-storage` persists `storage/` across container restarts and image rebuilds. To reset all data: `docker compose down -v`.

### Key Files

| File | Purpose |
|------|---------|
| `Dockerfile` | Three-stage build (composer → node → php) |
| `docker-entrypoint.sh` | Runtime init (env, key, caching) |
| `docker-compose.yml` | Service definition, port mapping, volume |
| `.dockerignore` | Excludes vendor, node_modules, .git, .env, IDE dirs, test artifacts |

---

## Security & Privacy Notes

- No user authentication currently (single-user, session-based)
- API keys stored in `.env` (gitignored)
- No PII collected or stored
- External API calls contain only paper titles/abstracts (public data)

---

## Testing Strategy

### Unit & Feature Tests

- **Directory:** `tests/Unit/`, `tests/Feature/`
- **Runner:** PHPUnit 11 (`phpunit.xml`)
- **Run command:** `composer test` (clears config cache, runs `php artisan test`)
- **Environment:** No database, array session/cache, sync queue

**Unit test coverage (84 test cases across 6 files):**

| Test File | Cases | Coverage |
|-----------|-------|----------|
| `SourcePreviewerTest` | 16 | All 5 parser types (Atom, bioRxiv JSON, OSF JSON:API, Europe PMC, RSS/Atom fallback), limit, empty feed, HTTP errors, cache hit/miss/bypass/key isolation/TTL kill switch |
| `AiSummarizerTest` | 14 | Gemini/OpenAI/Ollama happy paths, missing API keys, API failures, batch splitting, unknown provider fallback, missing index placeholder, summary cache hit/miss/bypass/TTL kill switch/hash URL exclusion |
| `PaperDeduplicatorTest` | 25 | normalizeUrl (null returns for #/empty/whitespace, transformations: lowercase, http→https, trailing slash, arXiv version strip, non-arXiv/DOI preservation), dedup fast paths (empty input, single source), core behavior (single/multi-source dupe, mixed unique/dupe, also_in annotation, no also_in on unique), edge cases (#/empty URLs, http/https normalization, arXiv version normalization, fully-emptied source, DOI dedup, source order preservation, also_in survives + merge) |
| `ConfigIntegrityTest` | 7 | Unique source keys, required fields, non-empty URLs, at least one discipline per source, valid discipline references, discipline labels, discipline ready flags |
| `SavedPapersRepositoryTest` | 20 | Save/remove/clear, dedup by URL, ordering, savedUrls, export JSON envelope, corruption recovery |
| `ExampleTest` | 1 | Basic assertion |

All tests use `Http::fake()` — no real external requests. `composer test` clears config cache and runs the full suite.

### E2E / Browser Tests (Dusk)

- **Directory:** `tests/Browser/`
- **Runner:** Laravel Dusk 8 (headless Chrome)
- **Run command:** `php artisan dusk`
- **Environment:** `.env.dusk.local` (separate from main `.env`)
- **Driver:** `chrome-headless-shell-mac-arm64` (macOS ARM64)
- **Window:** 1920x1080

**E2E test coverage (47 test cases across 7 files):**

| Test File | Cases | Coverage |
|-----------|-------|----------|
| `ExampleTest` | 1 | Basic root redirect |
| `NavigationTest` | 10 | Nav links, breadcrumbs, active highlights, 404 handling |
| `DisciplineSelectionTest` | 7 | Toggle, select all/none, save persistence, disabled states |
| `SourceSelectionTest` | 8 | Toggle, badges, select all/none, save, preview links |
| `SourcePreviewTest` | 7 | Item display, breadcrumbs, back nav, bioRxiv, 404s |
| `DigestGenerationTest` | 5 | Empty state, generate button, full flow, color sections |
| `DigestFeaturesTest` | 9 | Export button visibility, skip cache checkbox, streaming elements, also_in badges, session lifetime warning, failure banner |

**Page objects:** `tests/Browser/Pages/` (HomePage, abstract Page)

### Dusk Test Isolation

`AppServiceProvider` registers `Http::fake()` stubs when running in the `dusk` environment, so Dusk tests never hit real external APIs. Canned responses exist for arXiv (Atom), bioRxiv/medRxiv (JSON), OSF Preprints (JSON:API), Europe PMC (JSON), and Gemini (AI summaries).

### Coverage goals

Not yet established. E2E tests cover the primary user workflow end-to-end (disciplines → sources → preview → digest generation).

---

## Open Questions / Risks

- Session-based state means digest is lost on session expiry (mitigated by JSON export, RDIG-010)
- ~~No caching of source results~~ — **Resolved:** RDIG-002 added configurable TTL caching
- ~~No deduplication if a paper appears in multiple sources~~ — **Resolved:** RDIG-008 added per-discipline dedup
- arXiv API rate limiting (unclear enforcement; mitigated by RDIG-014 inter-batch delays and 429 retry)
- AI provider cost at scale (Gemini free tier has limits; mitigated by dedup reducing API calls + summary caching avoiding re-summarization)
- ~~.env.example doesn't document AI-specific env vars~~ — **Resolved:** RDIG-001 added all 6 vars

---

## Planned Evolution

### Phase 2 — Stability

- ~~Move digest generation to queue jobs~~ → **Revised:** Livewire streaming (RDIG-003). No queue needed.
- ~~Cache source results~~ → **Done:** RDIG-002 (configurable TTL, artisan clear command)
- ~~Avoid re-summarizing unchanged papers~~ → **Done:** RDIG-012 (AI summary caching keyed on URL md5, configurable TTL)
- ~~Persist user selections to database~~ → **Archived:** RDIG-006. No database; file sessions are sufficient.

### Phase 3 — Intelligence

- Ranking by novelty or citations
- ~~Deduplication across sources~~ → **Done:** RDIG-008 (per-discipline, URL-normalized)
- Cross-discipline clustering

### Phase 4 — Abstractly Vision

- Research radar view
- ~~Saved papers~~ → **Done:** RDIG-019 (bookmark from digest, `/saved` page, JSON file persistence)
- Personal trend tracking
- Research-to-experiment mapping
- Integration with The Shelf (project pipeline)
