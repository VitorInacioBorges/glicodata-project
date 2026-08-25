# Methodologies and Technologies

## Main Stack

### Backend

| Technology | Version | Function |
| --- | --- | --- |
| **PHP** | `^8.2` in Composer; `8.3.6` observed locally | Laravel application runtime. |
| **Laravel Framework** | `^12.0`; `12.67.0` installed | MVC framework, routing, container, Eloquent, migrations, and tests. |
| **Eloquent ORM** | Included in Laravel | Models, relationships, casts, fillable fields, and queries. |
| **Laravel Sanctum** | `^4.0`; `4.3.3` installed | Bearer tokens for UBS and administrator API identities. |
| **PostgreSQL** | Default in `.env.example` and `config/database.php` | Project default database following PDS-UEPG. |
| **SQLite** | Configured in `phpunit.xml` | In-memory database for local automated tests. |
| **Laravel Tinker** | `^2.10.1` | REPL for local inspection and operations. |

### Web Interface and Assets

| Technology | Version | Function |
| --- | --- | --- |
| **Blade** | Included in Laravel | Server-side templates in `resources/views`. |
| **Vite** | `^7.0.7`; `7.3.2` installed | Asset build and dev server. |
| **laravel-vite-plugin** | `^2.0.0`; `2.1.0` installed | Laravel/Vite integration. |
| **Axios** | `^1.11.0`; `1.15.2` installed | HTTP client exposed by `resources/js/bootstrap.js`. |
| **Bootstrap** | `^5.3.8`; `5.3.8` installed | Components and styles imported by Vite in `resources/css/app.css` and `resources/js/app.js`. |

### Development Tools

| Tool | Version | Function |
| --- | --- | --- |
| **Composer** | `2.7.1` observed locally | PHP dependency management. |
| **Node.js** | `24.14.0` observed locally | Runtime for Vite and JS tooling. |
| **npm** | `11.9.0` observed locally | JS dependency management. |
| **PHPUnit** | `^11.5.3`; `11.5.55` installed | Unit and feature tests. |
| **Laravel Pint** | `^1.24`; `1.25.1` installed | PHP code formatting. |
| **Laravel Sail** | `^1.41`; `1.47.0` installed | Optional Docker environment for Laravel. |
| **Laravel Pail** | `^1.2.2`; `1.2.3` installed | Log inspection in the development script. |
| **concurrently** | `^9.0.1`; `9.2.1` installed | Runs the server, queue, logs, and Vite in parallel in `composer dev`. |

---

## Development Methodology

### Layered Laravel Architecture

The backend is split into four main layers:

- **Controllers / Form Requests**: HTTP entry, payload validation, and JSON serialization.
- **Policies**: authorization for Sanctum/session identities with UBS isolation and separate administrators.
- **Services**: application rules, transactions, and audit recording.
- **Repositories**: Eloquent queries and record creation.
- **Models**: table mapping, casts, fillable fields, and relationships.

This is not strict Clean Architecture because services depend on concrete repositories and repositories depend directly on Eloquent. It still improves responsibility separation compared to controllers that contain embedded business logic.

### Conventional Commits

The Git history shows Conventional Commits in Brazilian Portuguese:

```text
feat(services): valida ids e emails nas buscas
refactor(routes): aplica prefixo api pelo provider
feat(audit): registra eventos das operacoes persistidas
```

### REST CRUD by Resource

Operational resources use `Route::apiResource` CRUD protected by `auth:sanctum`. Districts are read-only; UBS and audit routes support scoped UBS access and global administrators. Clinical resources accept only UBS identities.

---

## State and Data Management

### Backend — Persistence

| Aspect | Implementation |
| --- | --- |
| **ORM** | Eloquent Models in `glicodata/app/Models`. |
| **Model IDs** | Models use `HasUuids` and entity migrations use UUID columns. |
| **Pagination** | `PaginationRequest` accepts `per_page` only in the 1 to 20 range. |
| **Casts** | `boolean`, `date`, `array`, `float`, native PHP enums, and age calculated from `birth`. |
| **Logical deletion** | Users, patients, assessments, risks, and reports use `SoftDeletes`. |
| **Audit** | `audit_events` stores `jsonb` snapshots and permanent records of payload redaction. |
| **Migrations** | Consolidated for a fresh PostgreSQL installation, including the initial institutional catalog and database queues. |
| **Test database** | In-memory SQLite configured in `phpunit.xml`. |

### Web Interface — State

The current views are rendered server-side with Blade for UBS login, lobby, listings, and detail screens. There is no global frontend state, SPA routing, or client-side authentication implemented in the versioned code.

### Client ↔ Backend Communication

| Aspect | Implementation |
| --- | --- |
| **API format** | JSON for REST controllers. |
| **Authentication** | `Authorization: Bearer <token>` validated locally by Sanctum; Blade uses session guards. |
| **Credentials** | Argon2id password hashes; 24-hour tokens stored only as SHA-256 hashes. |
| **Pagination** | `?per_page=N` query string, with an effective maximum of 20. |
| **Payload validation** | Form Requests expose only `$request->validated()` to controllers. |
| **ID validation** | UUID validation through `ValidateUtils::validateId()` for lookups, updates, and deletes by ID. |
| **UBS identity** | Unique seven-digit numeric CNES; email remains optional profile data. |
| **Authorization** | Policies scope data by UBS; `AdministratorModel` manages UBS and global audit without automatic clinical access. |
