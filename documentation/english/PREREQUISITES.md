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
| Laravel | `12.60.2` |
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

### External Authentication Service

| Service | Reason |
| --- | --- |
| **Keycloak/OpenID Connect** | Required to authenticate UBS accounts and validate Bearer tokens used by the API. |

---

## Project Dependencies

### PHP — Direct Dependencies

| Package | Installed version | Category |
| --- | --- | --- |
| `laravel/framework` | `v12.60.2` | Main framework |
| `laravel/socialite` | `v5.27.0` | OAuth/OpenID client for external login. |
| `socialiteproviders/keycloak` | `5.3.0` | Keycloak provider for Laravel Socialite. |
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
| `tailwindcss` | `4.2.4` | Utility CSS |
| `@tailwindcss/vite` | `4.2.4` | Tailwind plugin |
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
6. Configure `KEYCLOAK_CLIENT_ID`, `KEYCLOAK_CLIENT_SECRET`, `KEYCLOAK_REDIRECT_URI`, `KEYCLOAK_BASE_URL`, and `KEYCLOAK_REALM`.
7. Run migrations.

For automated tests, `phpunit.xml` already defines in-memory SQLite:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

This in-memory database is appropriate for fast tests, but depends on migrations that match the models used by tests.
