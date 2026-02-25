# Contributing to Abstractly

Thank you for your interest in contributing.

Abstractly is a research radar for tracking scientific trends and translating emerging techniques into practical applications. Contributions should keep the tool focused, local-first, and useful for researchers and developers alike.

---

## Code of Conduct

Be kind, respectful, and constructive. We're building something useful together — treat fellow contributors the way you'd want to be treated. Harassment, dismissive behavior, and unconstructive criticism have no place here.

---

## New to Contributing?

If this is your first open source contribution, welcome! Here's how to get started:

1. **Find an issue** — Look for issues labeled [`good first issue`](https://github.com/akuligowski9/abstractly/labels/good%20first%20issue) for beginner-friendly tasks.
2. **Fork the repo** — Click "Fork" on GitHub, then clone your fork locally.
3. **Create a branch** — See [Branch Naming](#branch-naming) below.
4. **Make your changes** — Follow the setup instructions and test before submitting.
5. **Open a PR** — Push your branch and open a pull request against `main`.

If you're new to Git and GitHub, [GitHub's guide](https://docs.github.com/en/get-started/quickstart/contributing-to-projects) is a great place to start.

---

## Issue Etiquette

- **Comment before you start** — If you'd like to work on an issue, leave a comment so others know it's being tackled. This avoids duplicate effort.
- **Ask questions in the issue thread** — If you're stuck or unsure about the approach, ask! We're happy to help.
- **Don't go silent** — If you claimed an issue but can't finish it, that's totally fine. Just leave a comment so someone else can pick it up.

---

## Philosophy

- No database by design — file-based sessions and cache keep setup trivial.
- AI usage is transparent and bounded — summaries enhance, not replace, reading papers.
- Prefer simple solutions over clever ones.
- Keep the tool single-user and local-first.

---

## Development Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ (for Vite asset compilation)
- An AI provider API key (Gemini, OpenAI, or Ollama)

### Docker (Recommended)

```bash
git clone https://github.com/akuligowski9/abstractly.git
cd abstractly
cp .env.example .env    # set your AI provider API key
docker compose up --build
# Visit http://localhost:8000
```

### Manual Setup

```bash
git clone https://github.com/akuligowski9/abstractly.git
cd abstractly
cp .env.example .env
composer install
npm install && npm run build
php artisan serve
```

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Livewire 3, Tailwind CSS v4, Alpine.js |
| Build | Vite 7 |
| Storage | File-based sessions and cache (no database) |
| AI Providers | Google Gemini, OpenAI, Ollama |
| Data Sources | arXiv, bioRxiv, medRxiv, OSF Preprints, Europe PMC |

---

## Common Contribution Areas

### AI Providers

AI providers live in `app/Services/Ai/`. To add a new provider:

1. Implement the existing provider interface
2. Add configuration to `.env.example`
3. Register in the service container
4. Test with a real digest generation

### Source Parsers

Source parsers handle fetching papers from different APIs (Atom, JSON, JSON:API). See `app/Services/Sources/` for existing implementations.

### Disciplines and Sources

Adding new disciplines or sources is primarily data work in the configuration files.

---

## Branch Naming

Use a descriptive branch name with a prefix:

- `feature/add-pubmed-source`
- `fix/cache-invalidation`
- `docs/update-setup`

Keep it short, lowercase, and hyphen-separated.

---

## Commit Messages

- Use the imperative mood: "Add provider" not "Added provider"
- Keep the first line under 72 characters
- Add a blank line before any extended description

---

## Pull Request Guidelines

Please ensure:

- The app starts and generates a digest without errors
- Code is readable without AI context
- Changes are documented if behavior changes
- New features include tests where applicable

Small, focused PRs are preferred.

---

## AI-Assisted Contributions

AI-assisted contributions are welcome.

Please review and understand generated code before submitting.
Maintainers may request clarification if behavior is unclear.
