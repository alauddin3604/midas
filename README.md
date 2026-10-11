# Midas

A Laravel boilerplate built around a Domain-Driven Design structure. It ships with a working
Identity domain (users, roles, permissions, password reset), an Audit domain (append-only activity
log with retention), a Livewire dashboard and a versioned JSON API, so you can see every layer
wired together before you add your own domains.

## Stack

- PHP 8.3+ (developed on 8.4), Laravel 13
- Livewire 4 for the dashboard, Blaze for Blade rendering, Tailwind + Vite for assets
- Pest 5 for tests, including architecture tests that enforce the layer rules
- Larastan (PHPStan level 7) and Pint for static analysis and formatting
- SQLite by default

## Getting started

```bash
composer setup     # install, copy .env, generate key, migrate, build assets
php artisan db:seed  # optional: creates admin@example.com (role: admin)
composer dev       # run the dev processes
```

The seeded admin is `admin@example.com` with the factory's default password, `password`. Change it
before using the app anywhere other than local development.

## Everyday commands

| Command | What it does |
|---|---|
| `composer dev` | Run the dev processes (`php artisan dev`) |
| `composer test` | Clear config, lint check, static analysis, then the test suite |
| `php artisan test --compact` | Test suite only |
| `composer lint` | Fix code style with Pint |
| `composer types:check` | PHPStan analysis |
| `php artisan identity:delete-user {email}` | Delete a user by email |
| `php artisan model:prune --model="Domain\Audit\Models\AuditEntry"` | Prune expired audit entries (scheduled daily) |

## Configuration

| Variable | Default | Purpose |
|---|---|---|
| `AUDIT_RETENTION_DAYS` | `365` | Audit entries older than this are pruned by the daily schedule |
| `SANCTUM_EXPIRATION` | `10080` | Minutes until an API token expires (`0` disables expiry) |

## What's included

| Area | Where | Notes |
|---|---|---|
| Identity domain | `src/Domain/Identity` | Register, authenticate, update, delete users; password reset; `Role` and `Permission` enums |
| Audit domain | `src/Domain/Audit` | Generic append-only `AuditEntry`; knows nothing about other domains |
| Dashboard | `src/App/*/Http/Dashboard` | Livewire pages: login, forgot/reset password, users CRUD, audit log |
| API v1 | `src/App/Identity/Http/Api/V1` | `POST /api/v1/register`, `/login`, `/logout`; Sanctum bearer tokens, see [REST API](docs/rest-api.md) |

## Project layout

```
src/
├── App/<Domain>/      delivery: HTTP, Livewire, console, listeners, providers
├── Domain/<Domain>/   business model: actions, payloads, models, enums, events, exceptions
└── Support/           cross-cutting infrastructure with no business meaning
```

Dependencies point inward only: `App → Domain → Support`. This is enforced by `tests/Arch`.

## Further reading

- [Architecture](docs/architecture.md): layers, the flow of an operation, events, authorization, errors
- [Adding a feature](docs/adding-a-feature.md): step-by-step recipe for a new domain operation
- [AGENTS.md](AGENTS.md) and [.ai/guidelines](.ai/guidelines): coding conventions used in this repo
