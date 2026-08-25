# Execution Guide

## Local Setup

### 1. Clone the Repository

```bash
git clone <repository-url> ubs-system
cd ubs-system/glicodata
```

### 2. Install Dependencies

On Ubuntu, install the runtimes, PostgreSQL, and the PHP extensions required by
Composer, the application, and the test suite:

```bash
sudo apt update
sudo apt install php-cli php-curl php-intl php-mbstring php-pgsql \
  php-sqlite3 php-xml php-zip composer \
  nodejs npm postgresql postgresql-client
```

Check the environment before continuing:

```bash
php -v
composer --version
node --version
php -m | grep -E 'dom|mbstring|pdo_pgsql|pdo_sqlite|xml'
pg_isready
```

Install exactly the versions recorded in the lockfiles:

```bash
composer install
npm ci
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
DB_DATABASE=glicodata_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database before running migrations:

```bash
sudo systemctl enable --now postgresql
createdb -h 127.0.0.1 -U postgres glicodata_db
```

If your PostgreSQL installation uses local `peer` authentication, create the
database with `sudo -u postgres createdb glicodata_db` and set a password for the
user configured in `.env`.

#### Local authentication and Sanctum

Configure Argon2id hashes, sessions, and 24-hour tokens:

```env
HASH_DRIVER=argon2id
HASH_VERIFY=false
SANCTUM_EXPIRATION=1440
SESSION_DRIVER=database
SESSION_LIFETIME=1440
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
```

Use `SESSION_SECURE_COOKIE=false` only for local HTTP development. There is no authentication bypass.

### 4. Run Migrations

```bash
php artisan migrate
```

Migrations load only the five institutional districts and never create UBS accounts or credentials. The incremental cleanup irreversibly deletes the 42 legacy catalog UBS records and every linked clinical/audit record; take and verify a PostgreSQL backup before deploying it. SQLite remains configured in `phpunit.xml` only for in-memory automated tests.

### 5. Run Seeders

```bash
php artisan db:seed
```

The default seeder does not create UBS accounts, users, or credentials. Automated tests use isolated factories. To reset an existing UBS password by CNES:

```bash
php artisan glicodata:admin-create
php artisan glicodata:ubs-password 1234567
```

### 6. Start in Development Mode

Use the interactive commands above for local credentials; passwords are never accepted as command arguments.

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

| Script  | Command                                                                            | Description                   |
| ------- | ---------------------------------------------------------------------------------- | ----------------------------- |
| `setup` | `composer install`, copy `.env`, generate key, run migrate, install npm, and build | Automated initial setup.      |
| `dev`   | `concurrently` with Laravel server, queue, pail, and Vite                          | Full development environment. |
| `test`  | `php artisan config:clear --ansi` and `php artisan test`                           | Runs Laravel tests.           |

The `composer setup` script assumes PostgreSQL and `.env` are already configured
because it runs migrations. For a first installation, follow this guide in order.

### JavaScript (`glicodata/package.json`)

| Script  | Command      | Description                  |
| ------- | ------------ | ---------------------------- |
| `dev`   | `vite`       | Starts the asset dev server. |
| `build` | `vite build` | Generates production build.  |

### Artisan

| Command                  | Description              |
| ------------------------ | ------------------------ |
| `php artisan route:list` | Lists registered routes. |
| `php artisan migrate`    | Runs pending migrations. |
| `php artisan db:seed`    | Runs seeders.            |
| `php artisan test`       | Runs tests.              |
| `php artisan tinker`     | Opens Laravel REPL.      |

---

## Main Endpoints

All endpoints below use the `/api` prefix.

| Method        | Route                                                       | Controller                                                 |
| ------------- | ----------------------------------------------------------- | ---------------------------------------------------------- |
| `POST`        | `/auth/login`                                               | UBS/admin login and Sanctum token issuance                 |
| `GET`         | `/auth/me`                                                  | Returns the token identity                                 |
| `POST`        | `/auth/logout`                                              | Revokes the current token                                  |
| `PUT`         | `/auth/password`                                            | Changes password and revokes every token                   |
| `GET`         | `/districts`                                                | `DistrictController@index`                                 |
| `GET`         | `/districts/{id}`                                           | `DistrictController@show`                                  |
| `POST`        | `/ubs`                                                       | Administrator creates a pending UBS by CNES                 |
| `GET`         | `/ubs` and `/ubs/{id}`                                      | `UbsController@index/show`                                 |
| `PUT/PATCH`   | `/ubs/{id}`                                                 | Admin manages any UBS; UBS updates only its own profile     |
| `apiResource` | `/users`, `/patients`, `/assessments`, `/risks`, `/reports` | Operational CRUD with logical delete on destroy            |
| `GET`         | `/audit-events` and `/audit-events/{id}`                    | `AuditEventController@index/show`                          |
| `POST`        | `/audit-events/{id}/redact`                                 | `AuditEventController@redact`, restricted to administrators |

Every route above except `POST /api/auth/login` requires:

```http
Authorization: Bearer <sanctum_token>
```

The login requires `account_type` (`ubs` or `admin`), `identifier`, password, and device name. `identifier` is the seven-digit CNES for UBS and the email address for administrators. Each account keeps at most 20 active tokens.

Web routes stay outside the `/api` prefix:

| Method | Route                 | Description                                                   |
| ------ | --------------------- | ------------------------------------------------------------- |
| `GET`  | `/`                   | Redirects to `/login/ubs`.                                    |
| `GET`  | `/login/ubs`          | Renders the UBS login.                                        |
| `GET`  | `/login/admin`        | Renders the administrator login.                              |
| `POST` | `/login`              | Creates a session for the explicit account type.              |
| `GET/POST` | `/cadastro/ubs`     | Public CNES registration; the account remains pending.        |
| `GET`  | `/ubs/lobby`          | Renders the operational lobby.                                |
| `GET/PUT` | `/ubs/conta/perfil` | Allows a UBS to maintain its own institutional profile.       |
| `GET`  | `/ubs/pacientes*`     | Renders patient listing and visual detail screens.            |
| `GET`  | `/ubs/profissionais*` | Renders professional listing and visual detail screens.       |
| `GET`  | `/ubs/avaliacoes*`    | Renders assessment listing and visual detail screens.         |
| `POST` | `/ubs/logout`         | Ends the UBS web session.                                     |
| `GET`  | `/admin`              | Renders the global administrator dashboard.                   |
| `GET/PUT` | `/admin/ubs*`      | Lists, reviews, edits, and activates UBS accounts.             |

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

### Run Tests

```bash
composer test
```

The test suite uses in-memory SQLite and requires the `pdo_sqlite` extension.

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
Laravel Framework 12.67.0
```

---

## Deploy Strategy (Production)

The repository does not include a versioned deploy configuration. A minimal flow for a VPS with Nginx/Apache and PHP-FPM would be:

```bash
cd /var/www/ubs-system/glicodata
git pull
composer install --no-dev --optimize-autoloader
npm ci
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
- Create the first administrator and set UBS passwords with the interactive commands.
- Use HTTPS, secure session cookies, Argon2id, and run the scheduler for expired Sanctum tokens.
- Restrict `audit_events` database and backup access because its snapshots may contain personal data; define retention/redaction procedures with NTI.
- Review and activate UBS catalog entries containing provisional contact information before allowing login.
- Ensure write permission for `storage/` and `bootstrap/cache/`.
- Do not version `.env`, logs, caches, `vendor/`, or `node_modules/`.
