# Contributing

## Workflow

1. Branch from `main` (`feature/…`, `fix/…`).
2. Keep each commit to one feature or fix, together with the tests that pin
   the new behaviour. Put formatting-only changes in their own commit.
3. Open a pull request. CI must be green before merging.

## Branch protection

`main` is protected. In **Settings → Branches → Branch protection rules** for
`main`, enable:

- **Require a pull request before merging**
- **Require status checks to pass before merging**, with these checks from
  `.github/workflows/ci.yml` marked required:
  - `Lint (Pint)`
  - `Tests (PHPUnit)`
  - `Build assets (Vite)`
  - `Dependency audit`
- **Require branches to be up to date before merging**
- **Do not allow bypassing the above settings**

## Running the checks locally

```bash
composer install
cp .env.example .env && php artisan key:generate

vendor/bin/pint --test          # code style (vendor/bin/pint to fix)
php artisan test                # PHPUnit, in-memory SQLite (needs pdo_sqlite)
vendor/bin/phpunit --coverage-text   # with coverage (needs pcov or xdebug)
npm ci && npm run build         # Vite asset build
composer audit && npm audit --audit-level=high
```

The test suite runs against in-memory SQLite (see `phpunit.xml`). The
`wp_*` tables are created with PostgreSQL `bigserial` keys, which SQLite does
not auto-increment, so test helpers such as
`tests/Feature/Booking/Concerns/CreatesCourses.php` assign IDs explicitly.

## Tests

- `tests/Unit` covers services and configuration without HTTP.
- `tests/Feature` exercises routes and controllers with `RefreshDatabase`.
- Mail is faked with `Mail::fake()`; never call Stripe or Cloudinary from a test.
- `tests/Unit/ArchitectureTest.php` keeps WordPress hook code out of `app/`.
  The original theme lives in `legacy/wordpress-theme/` for reference only.

## Dependencies

Dependabot opens weekly update PRs for Composer and npm, and monthly ones for
GitHub Actions. The `Dependency audit` CI job fails on any Composer advisory
and on high or critical npm advisories.
