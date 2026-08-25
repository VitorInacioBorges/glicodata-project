# Prerequisites and Performance

## System Dependencies

### Runtime

| Dependency | Minimum version | Verification |
| --- | --- | --- |
| **PHP** | `>=8.2` | `php -v` |
| **Composer** | `>=2.x` | `composer --version` |
| **Node.js** | Recommended `>=20 LTS` for modern Vite | `node --version` |
| **npm** | Compatible with the Node version | `npm --version` |

Versions observed while generating this documentation:

| Tool | Observed version |
| --- | --- |
| PHP | `8.3.6` |
| Composer | `2.7.1` |
| Laravel | `12.67.0` |
| Node.js | `24.14.0` |
| npm | `11.9.0` |

### Database

| Database | Project status | Verification |
| --- | --- | --- |
| **PostgreSQL** | Default database in `.env.example` and `config/database.php`, following PDS-UEPG. | `psql --version` |
| **SQLite** | Used only for automated tests through `phpunit.xml`, when configured. | `php -m | grep sqlite` |
| **MySQL/MariaDB/SQL Server** | Default Laravel connections kept in `config/database.php`. | Corresponding client |

For new systems in the NTI/UEPG context, PostgreSQL is the project default database. SQLite remains only as an in-memory database for local automated tests.

### Relevant PHP Extensions

| Extension | Reason |
| --- | --- |
| `pdo` | Database access through Laravel. |
| `pdo_pgsql` | Required for the default PostgreSQL connection. |
| `pdo_sqlite` | Required only for in-memory SQLite automated tests. |
| `mbstring` | Common Laravel and Symfony dependency. |
| `openssl` | Cryptography, keys, and secure operations. |
| `fileinfo` | File validation and handling. |
| `dom` / `xml` | Required by Laravel, Pint, and PHPUnit during `composer install`. |
| `curl` | HTTP communication used by Composer and integrations. |
| `intl` | Internationalization and Symfony/Laravel utilities. |
| `zip` | Efficient Composer package installation. |

### Authentication

No external identity service is required. PostgreSQL stores Argon2id password hashes and Laravel Sanctum issues API Bearer tokens.

---

## Project Dependencies

### PHP — Direct Dependencies

| Package | Installed version | Category |
| --- | --- | --- |
| `laravel/framework` | `v12.67.0` | Main framework |
| `laravel/sanctum` | `v4.3.3` | Personal Bearer tokens with abilities and expiration. |
| `laravel/tinker` | `v2.10.1` | REPL |
| `fakerphp/faker` | `v1.24.1` | Fake data for tests/factories |
| `laravel/pail` | `v1.2.3` | Development logs |
| `laravel/pint` | `v1.25.1` | Formatting |
| `laravel/sail` | `v1.47.0` | Optional Docker |
| `mockery/mockery` | `1.6.12` | Test doubles |
| `nunomaduro/collision` | `v8.8.2` | CLI error handling |
| `phpunit/phpunit` | `11.5.55` | Tests |

### JavaScript — Direct Dependencies

| Package | Installed version | Category |
| --- | --- | --- |
| `vite` | `7.3.2` | Build/dev server |
| `laravel-vite-plugin` | `2.1.0` | Laravel/Vite integration |
| `bootstrap` | `5.3.8` | Blade interface components and styles compiled by Vite |
| `axios` | `1.15.2` | HTTP client |
| `concurrently` | `9.2.1` | Parallel process execution |

---

## Suggested Hardware

### Local Development

| Resource | Minimum | Recommended |
| --- | --- | --- |
| **RAM** | 4 GB | 8 GB |
| **CPU** | 2 cores | 4 cores |
| **Disk** | 2 GB free without `vendor`/`node_modules`; 5 GB with dependencies | 10 GB |
| **OS** | Linux, macOS, or Windows with WSL2 | Ubuntu 22.04+ |

### Production Server

| Resource | Minimum | Recommended |
| --- | --- | --- |
| **RAM** | 1 GB for a small API | 2 GB+ |
| **CPU** | 1 vCPU | 2 vCPU |
| **Disk** | 10 GB SSD | 20 GB SSD+ |
| **OS** | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |

### Ports Used

| Port | Service | Environment |
| --- | --- | --- |
| `8000` | `php artisan serve` | Development |
| `5173` | Vite dev server | Development |
| `5432` | PostgreSQL | Development/production |
| `80` | HTTP through Nginx/Apache | Production |
| `443` | HTTPS through Nginx/Apache | Production |

---

## Environment Requirements

Before running the application:

1. Install PHP dependencies with Composer.
2. Install JS dependencies with npm.
3. Copy `.env.example` to `.env`.
4. Generate `APP_KEY`.
5. Configure PostgreSQL credentials.
6. Configure Argon2id, secure sessions, and the 1,440-minute Sanctum expiration.
7. Run the incremental migrations.
8. Create the first administrator with `php artisan glicodata:admin-create`.
9. Register UBS units at `/cadastro/ubs`, review them at `/admin/ubs`, and use `php artisan glicodata:ubs-password 1234567` only for operational resets.

There is no authentication bypass. Global administrators are separate from UBS accounts, and professional `users` are not HTTP principals.

The migrations load only the five Ponta Grossa districts. UBS accounts are never created automatically: public CNES registrations remain inactive until administrator approval. The old catalog removal migration is irreversible and requires a verified backup.

For automated tests, `phpunit.xml` already defines in-memory SQLite:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

This in-memory database is appropriate for fast tests, but depends on migrations that match the models used by tests.
