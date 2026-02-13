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
- Currently 15 disciplines defined; 13 are `ready=true` (only Law and Arts remain disabled)

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

**Current sources (49 entries across 13 disciplines):**

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

Generation is triggered in-page via Livewire action (no POST/redirect). The component handles both generation and display.

**Generation flow (triggered by `wire:click="generate"`):**

1. Read enabled disciplines from session
2. Filter to `ready` disciplines
3. Resolve enabled sources per discipline
4. Fetch latest items per source via `SourcePreviewer` (errors caught per-source)
5. Generate AI summaries per item via `AiSummarizer`
6. Assemble digest structure (discipline → sections → items)
7. Store in session: `digest.latest`
8. Update component state reactively

**UI states:**

- **Empty state:** Icon + message when no digest exists or has no items
- **Loading state:** Full overlay spinner during generation (`wire:loading`)
- **Generated state:** Hierarchical display with color-coded AI perspectives

**Display structure:**

```
Discipline (H2)
    Source (card, bg-white rounded-xl shadow-sm)
        Paper
            Title (linked to source)
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
│     └── DigestViewer            (generate + display digest)
│
├── Controllers
│     ├── SourceController        (preview a single source — only active controller)
│     ├── DisciplineController    [LEGACY — not routed, superseded by Livewire]
│     └── DigestController        [LEGACY — not routed, superseded by Livewire]
│
├── Services
│     ├── SourcePreviewer         (fetch + normalize feed data)
│     └── AiSummarizer           (multi-provider AI summaries)
│
├── Config
│     ├── disciplines.php         (discipline registry)
│     └── sources.php             (source registry)
│
├── Views
│     ├── livewire/
│     │     ├── discipline-picker (Livewire companion view)
│     │     ├── source-picker     (Livewire companion view)
│     │     └── digest-viewer     (Livewire companion view)
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
```

Livewire `wire:navigate` is used on internal links for SPA-like page transitions without full reloads.

---

## Data Model

### Current: Config + Session

- **No database tables for application data** — disciplines and sources are config-driven
- User selections stored in session (session driver: `database`, SQLite)
- Digest output stored in session (`digest.latest`)
- Default Laravel migrations only (users, cache, jobs)

### Future Consideration

- Persist user selections to database
- Store generated digests for history/comparison
- Paper deduplication across sources

---

## Key Flows

### Source Fetching (`SourcePreviewer`)

Parses multiple feed formats via key-based dispatch:

| Format | Parser Method | Sources |
|--------|--------------|---------|
| Atom | `fetchArxiv()` | All arXiv feeds (math, cs, econ, physics, q-bio, eess, astro-ph) |
| bioRxiv/medRxiv JSON | `fetchRxivJson()` | bioRxiv, medRxiv |
| OSF Preprints JSON:API | `fetchOsfPreprints()` | PsyArXiv, SocArXiv, EdArXiv |
| Europe PMC REST JSON | `fetchEuropePmc()` | Europe PMC topic searches |
| RSS/Atom (fallback) | `fetchRssOrAtom()` | Any unrecognized source |

Normalized output per item:

```php
[
    'title'   => string,
    'summary' => string,
    'url'     => string,
]
```

HTTP client: browser-like headers, 2 retries, 20s timeout.

### AI Summarization (`AiSummarizer`)

Provider selection via `DIGEST_AI_PROVIDER` env var:

| Provider | Model | Batch Size | Timeout |
|----------|-------|------------|---------|
| `gemini` (default) | `gemini-2.0-flash` | 5 | 40s |
| `openai` | `gpt-4o-mini` | 5 | 40s |
| `ollama` | `llama3.1` | 3 | 60s |

Output per item:

| Field | Purpose |
|-------|---------|
| `eli5` | Plain-language explanation (2–3 sentences) |
| `swe` | Solo developer opportunity framing (2–3 sentences) |
| `investor` | High-risk opportunity thesis (2–3 sentences) |

Structured JSON output enforced via `response_mime_type` (Gemini) or `response_format` (OpenAI).

Graceful degradation: placeholder text on failure per batch.

---

## External Integrations

| Service | Purpose | Auth |
|---------|---------|------|
| arXiv API | Research papers (Atom) | None |
| bioRxiv API | Preprints (JSON) | None |
| medRxiv API | Preprints (JSON) | None |
| OSF Preprints API | PsyArXiv, SocArXiv, EdArXiv (JSON:API) | None |
| Europe PMC REST API | Aggregated preprints by topic (JSON) | None |
| Google Gemini API | AI summarization (default) | `GOOGLE_API_KEY` |
| OpenAI API | AI summarization (optional) | `OPENAI_API_KEY` |
| Ollama | Local AI summarization (optional) | None (local) |

**Future:** The Shelf API (for research-to-project pipeline).

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
- **Environment:** In-memory SQLite, array session/cache, sync queue
- **Current coverage:** Example tests only (basic redirect assertion)

### E2E / Browser Tests (Dusk)

- **Directory:** `tests/Browser/`
- **Runner:** Laravel Dusk 8 (headless Chrome)
- **Run command:** `php artisan dusk`
- **Environment:** `.env.dusk.local` (separate from main `.env`)
- **Driver:** `chrome-headless-shell-mac-arm64` (macOS ARM64)
- **Window:** 1920x1080

**E2E test coverage (37 test cases across 7 files):**

| Test File | Cases | Coverage |
|-----------|-------|----------|
| `ExampleTest` | 1 | Basic root redirect |
| `NavigationTest` | 9 | Nav links, breadcrumbs, active highlights, 404 handling |
| `DisciplineSelectionTest` | 7 | Toggle, select all/none, save persistence, disabled states |
| `SourceSelectionTest` | 8 | Toggle, badges, select all/none, save, preview links |
| `SourcePreviewTest` | 7 | Item display, breadcrumbs, back nav, bioRxiv, 404s |
| `DigestGenerationTest` | 5 | Empty state, generate button, full flow, color sections |

**Page objects:** `tests/Browser/Pages/` (HomePage, abstract Page)

### Dusk Test Isolation

`AppServiceProvider` registers `Http::fake()` stubs when running in the `dusk` environment, so Dusk tests never hit real external APIs. Canned responses exist for arXiv (Atom), bioRxiv/medRxiv (JSON), OSF Preprints (JSON:API), Europe PMC (JSON), and Gemini (AI summaries).

### Coverage goals

Not yet established. E2E tests cover the primary user workflow end-to-end (disciplines → sources → preview → digest generation).

---

## Open Questions / Risks

- Session-based state means digest is lost on session expiry
- No caching of source results — repeated generation re-fetches everything
- No deduplication if a paper appears in multiple sources
- arXiv API rate limiting (unclear enforcement)
- AI provider cost at scale (Gemini free tier has limits)
- .env.example doesn't document AI-specific env vars (`GOOGLE_API_KEY`, `DIGEST_AI_PROVIDER`, etc.)

---

## Planned Evolution

### Phase 2 — Stability

- Move digest generation to queue jobs
- Cache source results
- Avoid re-summarizing unchanged papers
- Persist user selections to database

### Phase 3 — Intelligence

- Ranking by novelty or citations
- Deduplication across sources
- Cross-discipline clustering

### Phase 4 — Abstractly Vision

- Research radar view
- Saved papers
- Personal trend tracking
- Research-to-experiment mapping
- Integration with The Shelf (project pipeline)
