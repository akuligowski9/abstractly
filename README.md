# Abstractly

A research radar for tracking AI trends and translating emerging techniques into practical applications. Aggregates newly published scientific research from open-access sources and generates AI-assisted digests with multiple perspectives.

> **Status:** Early development (v0.1)

---

## Demo

*Coming soon — screenshots and live link to follow.*

---

## Features

### Current

- Browse and enable/disable research disciplines
- Configure sources per discipline (arXiv subfields, bioRxiv, medRxiv)
- Preview raw feed entries from any source
- Generate AI-summarized digests grouped by discipline and source
- Three summary perspectives per paper:
  - **ELI5** — plain-language explanation
  - **SWE** — solo developer opportunity framing
  - **Investor** — high-risk opportunity thesis
- Multi-provider AI support (Gemini, OpenAI, Ollama)
- Graceful degradation when AI calls fail

### Planned

- Queue-based digest generation
- Source result caching
- Paper deduplication across sources
- Research radar view with trend tracking
- Integration with [The Shelf](https://github.com/akuligowski9/the-shelf) for research-to-project pipeline

---

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade templates, Vite
- **Database:** SQLite (session/cache storage)
- **AI Providers:** Google Gemini (default), OpenAI, Ollama
- **Data Sources:** arXiv (Atom), bioRxiv (JSON), medRxiv (JSON)

---

## Quickstart

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
php artisan migrate
npm install
npm run build

# Start development servers
composer dev
```

This runs the Laravel server, queue worker, log tail, and Vite dev server concurrently.

---

## Environment Variables

Copy `.env.example` to `.env` and configure:

| Variable | Required | Description |
|----------|----------|-------------|
| `GOOGLE_API_KEY` | Yes (if using Gemini) | Google AI API key for Gemini |
| `DIGEST_AI_PROVIDER` | No | `gemini` (default), `openai`, or `ollama` |
| `DIGEST_AI_MODEL` | No | Gemini model (default: `gemini-2.0-flash`) |
| `OPENAI_API_KEY` | Only if provider is `openai` | OpenAI API key |
| `DIGEST_AI_MODEL_OPENAI` | No | OpenAI model (default: `gpt-4o-mini`) |
| `OLLAMA_HOST` | No | Ollama endpoint (default: `http://127.0.0.1:11434`) |

---

## Project Documentation

- [BACKLOG.md](docs/BACKLOG.md) — committed work and priorities
- [TECH_SPEC.md](docs/TECH_SPEC.md) — architecture, data model, and feature breakdown
- [PROGRESS.md](docs/PROGRESS.md) — session log and decisions

---

## License

MIT
