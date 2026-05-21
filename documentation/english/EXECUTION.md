# Execution Guide

## Local Setup

### 1. Clone the Repository

```bash
git clone <repository-url> ubs-system
cd ubs-system/application
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Configure Environment Variables

Copy the template:

```bash
cp .env.example .env
php artisan key:generate
```

Configure the local database in `.env`.

#### PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ubs_system
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database before running migrations:

```bash
createdb ubs_system
```

#### Keycloak / OpenID

Configure the UBS authentication provider:

```env
KEYCLOAK_CLIENT_ID=your_client_id
KEYCLOAK_CLIENT_SECRET=your_client_secret
KEYCLOAK_REDIRECT_URI="${APP_URL}/api/auth/ubs/callback"
KEYCLOAK_BASE_URL=https://keycloak.example
KEYCLOAK_REALM=your_realm
```

### 4. Run Migrations

```bash
php artisan migrate
```

Observation: SQLite remains configured in `phpunit.xml` only for in-memory automated tests.

### 5. Run Seeders

```bash
php artisan db:seed
```

The current seeder creates a district, a test UBS with `keycloak_id = ubs-teste-keycloak-id`, and an operational user with `test@example.com`.

### 6. Start in Development Mode

#### Laravel

```bash
php artisan serve
```

Default server:

```text
http://127.0.0.1:8000
```

Blade screens use web routes, while JSON endpoints live under `/api`.

```text
http://127.0.0.1:8000
http://127.0.0.1:8000/api/users
```

#### Vite

```bash
npm run dev
```

Default dev server:

```text
http://127.0.0.1:5173
```

#### Combined Composer Script

```bash
composer run dev
```

This script runs in parallel:

- `php artisan serve`
- `php artisan queue:listen --tries=1`
- `php artisan pail --timeout=0`
- `npm run dev`

---

## Available Scripts

### PHP / Composer (`application/composer.json`)

| Script | Command | Description |
| --- | --- | --- |
| `setup` | `composer install`, copy `.env`, generate key, run migrate, install npm, and build | Automated initial setup. |
| `dev` | `concurrently` with Laravel server, queue, pail, and Vite | Full development environment. |
| `test` | `php artisan config:clear --ansi` and `php artisan test` | Runs Laravel tests. |

### JavaScript (`application/package.json`)

| Script | Command | Description |
| --- | --- | --- |
| `dev` | `vite` | Starts the asset dev server. |
| `build` | `vite build` | Generates production build. |

### Artisan

| Command | Description |
| --- | --- |
| `php artisan route:list` | Lists registered routes. |
| `php artisan migrate` | Runs pending migrations. |
| `php artisan db:seed` | Runs seeders. |
| `php artisan test` | Runs tests. |
| `php artisan tinker` | Opens Laravel REPL. |

---

## Main Endpoints

All endpoints below use the `/api` prefix.

| Method | Route | Controller |
| --- | --- | --- |
| `GET` | `/auth/ubs/login` | `UbsAuthController@redirect` |
| `GET` | `/auth/ubs/callback` | `UbsAuthController@callback` |
| `GET` | `/auth/ubs/me` | `UbsAuthController@me` |
| `GET` | `/auth/ubs/logout` | `UbsAuthController@logout` |
| `GET` | `/districts` | `DistrictController@index` |
| `POST` | `/districts` | `DistrictController@store` |
| `GET` | `/districts/{id}` | `DistrictController@show` |
| `PUT/PATCH` | `/districts/{id}` | `DistrictController@update` |
| `DELETE` | `/districts/{id}` | `DistrictController@destroy` |
| `DELETE` | `/districts/{id}/delete-self` | `DistrictController@deleteSelf` |

The same pattern repeats for:

- `/api/ubs`
- `/api/users`
- `/api/patients`
- `/api/assessments`
- `/api/risks`
- `/api/reports`

Every route above except `/api/auth/ubs/login` and `/api/auth/ubs/callback` requires:

```http
Authorization: Bearer <keycloak_token>
```

Web routes stay outside the `/api` prefix:

| Method | Route | Description |
| --- | --- | --- |
| `GET` | `/` | Renders the home page. |
| `GET` | `/contact` | Renders the contact page. |
| `GET` | `/register/{id?}` | Renders the registration form. |
| `POST` | `/login` | Redirects to the UBS Keycloak login. |

---

## Database Workflow

### Create a New Migration

```bash
php artisan make:migration create_districts_table
```

### Run Migrations

```bash
php artisan migrate
```

### Roll Back Last Batch

```bash
php artisan migrate:rollback
```

### Recreate Local Database

```bash
php artisan migrate:fresh --seed
```

Use `migrate:fresh` only in local environments or disposable databases because it drops existing tables.

---

## Tests and Validation

### Run Tests

```bash
php artisan test
```

or:

```bash
composer test
```

Historical result observed during earlier documentation work:

```text
Tests: 1 risky, 1 passed (2 assertions)
```

The risky test is `Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response`; PHPUnit reported that the test code or tested code did not close its own output buffers. The command finished with exit code 0.

### Validate Routes

```bash
php artisan route:list
```

Observed result after the auth and folder reorganization:

```text
Showing [53] routes
```

### Validate Framework Version

```bash
php artisan --version
```

Observed result:

```text
Laravel Framework 12.60.2
```

---

## Deploy Strategy (Production)

The repository does not include a versioned deploy configuration. A minimal flow for a VPS with Nginx/Apache and PHP-FPM would be:

```bash
cd /var/www/ubs-system/application
git pull
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Make sure the web server points to:

```text
application/public
```

### Post-deploy Checklist

```bash
php artisan route:list
php artisan migrate:status
curl -i https://your-domain.example/api
```

### Production Cautions

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Configure `APP_KEY`.
- Use a persistent PostgreSQL database.
- Ensure write permission for `storage/` and `bootstrap/cache/`.
- Do not version `.env`, logs, caches, `vendor/`, or `node_modules/`.
