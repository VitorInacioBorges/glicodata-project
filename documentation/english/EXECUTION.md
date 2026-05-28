# Execution Guide

## Local Setup

### 1. Clone the Repository

```bash
git clone <repository-url> ubs-system
cd ubs-system/glicodata
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
KEYCLOAK_WEB_REDIRECT_URI="${APP_URL}/auth/ubs/callback"
KEYCLOAK_BASE_URL=https://keycloak.example
KEYCLOAK_REALM=your_realm
```

To temporarily work on design and API calls without a configured local Keycloak provider:

```env
GLICODATA_AUTH_DISABLED=true
GLICODATA_AUTH_BYPASS_UBS_EMAIL=
```

This mode opens UBS web routes and makes the `keycloak` guard resolve one active local UBS. Never enable it in staging or production.

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

### PHP / Composer (`glicodata/composer.json`)

| Script | Command | Description |
| --- | --- | --- |
| `setup` | `composer install`, copy `.env`, generate key, run migrate, install npm, and build | Automated initial setup. |
| `dev` | `concurrently` with Laravel server, queue, pail, and Vite | Full development environment. |
| `test` | `php artisan config:clear --ansi` and `php artisan test` | Runs Laravel tests. |

### JavaScript (`glicodata/package.json`)

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
| `GET` | `/districts/{id}` | `DistrictController@show` |
| `GET` | `/ubs` and `/ubs/{id}` | `UbsController@index/show` |
| `PUT/PATCH` | `/ubs/{id}` | `UbsController@update`, restricted to `audit-admin` |
| `apiResource` | `/users`, `/patients`, `/assessments`, `/risks`, `/reports` | Operational CRUD with logical delete on destroy |
| `GET` | `/audit-events` and `/audit-events/{id}` | `AuditEventController@index/show` |
| `POST` | `/audit-events/{id}/redact` | `AuditEventController@redact`, restricted to `audit-admin` |

Every route above except `/api/auth/ubs/login` and `/api/auth/ubs/callback` requires:

```http
Authorization: Bearer <keycloak_token>
```

With `GLICODATA_AUTH_DISABLED=true` in a local environment, the middleware remains registered, but the `keycloak` guard accepts requests without a Bearer token to ease development.

Web routes stay outside the `/api` prefix:

| Method | Route | Description |
| --- | --- | --- |
| `GET` | `/` | Redirects to `/login`. |
| `GET` | `/login` | Renders UBS login or redirects to the lobby. |
| `GET` | `/auth/ubs/redirect` | Starts web Keycloak login. |
| `GET` | `/auth/ubs/callback` | Receives the web callback and creates the `auth:ubs` session. |
| `GET` | `/ubs/lobby` | Renders the operational lobby. |
| `GET` | `/ubs/pacientes*` | Renders patient listing and visual detail screens. |
| `GET` | `/ubs/profissionais*` | Renders professional listing and visual detail screens. |
| `GET` | `/ubs/avaliacoes*` | Renders assessment listing and visual detail screens. |
| `POST` | `/ubs/logout` | Ends the UBS web session. |

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

### Create a Fresh Database for This Schema

```bash
php artisan migrate:fresh --seed
```

Use `migrate:fresh` only in local environments or disposable databases because it drops existing tables. The consolidated entity migrations and initial institutional data migration are designed for a new PostgreSQL database; an existing production database needs a separate transition plan.

---

## Tests and Validation

### Test Status

The existing tests were not modified or executed in this implementation stage. They require a later testing pass for the new Form Request, `birth`, logical deletion, audit, and Keycloak authorization contracts.

### Validate Routes

```bash
php artisan route:list
```

Observed result after the Form Request, catalog, and audit route implementation:

```text
Showing [37] routes
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
cd /var/www/ubs-system/glicodata
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
glicodata/public
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
- Apply this consolidated migration set only to a fresh production database, or prepare reviewed transition migrations for existing data.
- Provision and restrict the Keycloak client role `audit-admin`.
- Ensure `GLICODATA_AUTH_DISABLED=false` before caching configuration.
- Restrict `audit_events` database and backup access because its snapshots may contain personal data; define retention/redaction procedures with NTI.
- Review and activate UBS catalog entries containing provisional contact information before allowing login.
- Ensure write permission for `storage/` and `bootstrap/cache/`.
- Do not version `.env`, logs, caches, `vendor/`, or `node_modules/`.
