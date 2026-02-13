# Abstractly

A research radar for tracking scientific trends and translating emerging techniques into practical applications. Aggregates newly published research from open-access sources across 15 disciplines and generates AI-assisted digests with multiple perspectives.

![Digest example — Mathematics / Number Theory with ELI5, Solo SWE, and Investor summaries](docs/images/digest-example.png)

---

## Features

### Current

- **15 research disciplines** — mathematics, CS, neuroscience, economics, law, arts, and more
- **51 configurable sources** — arXiv (Atom), bioRxiv/medRxiv (JSON), OSF Preprints (JSON:API), Europe PMC (JSON)
- Browse and enable/disable disciplines, configure sources per discipline
- Preview raw feed entries from any source
- **AI-summarized digests** grouped by discipline and source with three perspectives per paper:
  - **ELI5** — plain-language explanation
  - **Solo SWE** — developer opportunity framing
  - **Investor** — high-risk opportunity thesis
- **Progressive streaming** — digest sections render as each discipline completes (Livewire 3 `$this->stream()`)
- **Paper deduplication** — cross-listed papers collapsed within each discipline with "Also in" badges
- **Source caching** — configurable TTL to avoid re-fetching (bypass via "Skip cache" checkbox)
- **AI summary caching** — cached per paper URL to avoid re-summarization on repeat generations
- **Rate limiting** — configurable inter-batch delay with 429 retry and exponential backoff
- **JSON export** — download digest as self-describing JSON with metadata envelope (timestamp, disciplines, sources, format version)
- **Saved papers** — bookmark papers from the digest, view on dedicated `/saved` page, persisted to local JSON file across sessions
- **Visual progress bar** — animated bar with source counter during digest generation
- **Error visibility** — amber warning banner listing failed sources, with specific messaging for API rate limits
- **Session lifetime warnings** — help text on picker pages indicating session-based persistence
- Multi-provider AI support (Gemini, OpenAI, Ollama) with graceful degradation on failure

### Planned

- Research radar view with trend tracking
- Personal trend tracking
- Ranking by novelty or citations
- Cross-discipline clustering
- Integration with [The Shelf](https://github.com/akuligowski9/the-shelf) for research-to-project pipeline

---

## Architecture

The digest generation pipeline is a three-pass loop per discipline:

1. **Fetch** — `SourcePreviewer` pulls papers from 5 different API formats (Atom, JSON, JSON:API, REST, RSS) with per-source caching
2. **Deduplicate** — `PaperDeduplicator` collapses cross-listed papers across sources within each discipline, annotating first occurrences with "Also in" references
3. **Summarize** — `AiSummarizer` batches papers to the configured AI provider (Gemini/OpenAI/Ollama) with per-paper summary caching

Results stream to the browser progressively via Livewire 3's `$this->stream()` as each discipline completes — no queue infrastructure needed.

**No database by design.** This is a local-only, single-user research tool. File-based sessions, file-based cache, and a flat JSON file for saved papers are sufficient and keep the setup trivial. The deliberate trade: no multi-user support in exchange for zero database configuration and instant portability.

---

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Livewire 3 (full-page components), Tailwind CSS v4, Alpine.js, Vite 7
- **Storage:** File-based sessions and cache (no database)
- **AI Providers:** Google Gemini (default), OpenAI, Ollama
- **Data Sources:** arXiv (Atom), bioRxiv/medRxiv (JSON), OSF Preprints (JSON:API), Europe PMC (JSON)

---

## Quickstart

### Docker (recommended)

```bash
git clone https://github.com/akuligowski9/abstractly.git
cd abstractly
cp .env.example .env    # set GOOGLE_API_KEY (or other AI provider keys)
docker compose up --build
# → http://localhost:8000
```

Saved papers, cache, and sessions persist across restarts via a named volume. To reset all data: `docker compose down -v`.

### Local

```bash
# Clone
git clone https://github.com/akuligowski9/abstractly.git
cd abstractly

# Install dependencies and set up
composer setup

# Or manually:
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build

# Start development servers
composer dev
```

This runs the Laravel server, log tail, and Vite dev server concurrently.

---

## Environment Variables

Copy `.env.example` to `.env` and configure:

| Variable | Required | Description |
|----------|----------|-------------|
| `GOOGLE_API_KEY` | Yes (if using Gemini) | Google AI API key for Gemini |
| `DIGEST_AI_PROVIDER` | No | `gemini` (default), `openai`, or `ollama` |
| `DIGEST_AI_MODEL` | No | Gemini model (default: `gemini-2.5-flash`) |
| `OPENAI_API_KEY` | Only if provider is `openai` | OpenAI API key |
| `DIGEST_AI_MODEL_OPENAI` | No | OpenAI model (default: `gpt-4o-mini`) |
| `OLLAMA_HOST` | No | Ollama endpoint (default: `http://127.0.0.1:11434`) |
| `DIGEST_AI_MODEL_OLLAMA` | No | Ollama model (default: `llama3.1`) |
| `SOURCE_CACHE_TTL` | No | Source fetch cache in seconds (default: `3600`, 0 disables) |
| `AI_SUMMARY_CACHE_TTL` | No | AI summary cache in seconds (default: `86400`, 0 disables) |
| `AI_BATCH_DELAY_MS` | No | Delay between AI batches in ms (default: `200`) |

---

## Testing

```bash
# Unit + feature tests (84 tests, 587 assertions)
composer test

# E2E browser tests (47 tests via Laravel Dusk)
php artisan dusk
```

---

## Project Documentation

- [BACKLOG.md](docs/BACKLOG.md) — committed work and priorities
- [TECH_SPEC.md](docs/TECH_SPEC.md) — architecture, data model, and feature breakdown
- [PROGRESS.md](docs/PROGRESS.md) — session log and decisions

---

## License

MIT
