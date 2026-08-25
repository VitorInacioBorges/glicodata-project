# Execution guide

## Setup

```bash
cd glicodata
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Configure PostgreSQL without committing secrets:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=glicodata_db
DB_USERNAME=glicodata_user
DB_PASSWORD=<local-secret>
```

```bash
php artisan migrate --seed
php artisan glicodata:admin-create ADMIN_001
```

The default seeder creates districts and the versioned questionnaire only. UBS and administrator passwords are provisioned interactively. Professionals do not have login credentials.

## Development ports

```bash
composer run dev
```

- Port 8000: Laravel Blade routes and JSON API.
- Port 5173: Vite CSS/JavaScript hot reload only.

Vite is not a second application or database. A direct `php artisan serve` tries port 8000 by default and may select 8001 if 8000 is occupied, so seeing both 8000 and 8001 usually means two Laravel processes were started. The Composer development script pins Laravel to port 8000.

## Single-port homologation

```bash
composer run homolog
```

This removes the `public/hot` marker, builds assets, applies migrations, optimizes Laravel, and serves the whole browser application at `http://127.0.0.1:8000`. CSS and JavaScript come from `public/build` on the same origin.

## Verification

```bash
php artisan test
npm run build
vendor/bin/pint --test
php artisan route:list --except-vendor
```

Tests use in-memory SQLite. Before a destructive development reset, verify that the connection targets exactly `glicodata_db`, then use `php artisan migrate:fresh --seed --force`. No accounts are seeded afterward.
