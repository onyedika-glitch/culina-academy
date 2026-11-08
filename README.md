# Culina Academy: course and booking platform for cooking schools

A Laravel 13 platform for culinary schools and other course providers. It includes:

- **Course catalogue** with course detail pages, intake dates and availability overrides
- **Online booking and enrolment** with Stripe payments, waiting lists and document uploads (Cloudinary)
- **Multilingual site**: English, French, Italian, Spanish, Simplified and Traditional Chinese, with per-locale menus, footers and UI strings
- **Admin panel** for courses, intakes, menus, media, form submissions and SEO metadata
- **SEO**: per-page meta, Open Graph, JSON-LD and slug redirects
- **WordPress / ACF / Polylang compatibility layer** that reads a legacy WordPress schema, for migrating existing WordPress sites to Laravel

## Requirements

- PHP 8.4, Composer 2
- PostgreSQL (or MySQL)
- Node 18+ for assets

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```

Configure Stripe, Cloudinary and mail in `.env`. Docker: `docker build -t culina-academy .`

## Tests and checks

```bash
php artisan test            # PHPUnit suite (in-memory SQLite, needs pdo_sqlite)
vendor/bin/pint --test      # code style
npm run build               # asset build
```

CI (`.github/workflows/ci.yml`) runs Pint, PHPUnit with coverage, the Vite
build and a Composer/npm security audit on every push and pull request. See
[CONTRIBUTING.md](CONTRIBUTING.md) for the workflow and branch protection.

For production logging set `LOG_CHANNEL=json` to write one JSON object per
line to stderr.

## Project layout

- `app/` — the Laravel application (controllers, services, models, mail)
- `legacy/wordpress-theme/` — the original WordPress theme, kept as reference
  and never loaded by the app (see its README)
- `tests/Unit`, `tests/Feature` — PHPUnit tests

## Branding

All brand text uses the placeholder name "Culina Academy", the domain `example.com` and "Your City".
Replace these with your own details and add your own logo, favicon and images.
