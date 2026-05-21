# Methodologies and Technologies

## Main Stack

### Backend

| Technology | Version | Function |
| --- | --- | --- |
| **PHP** | `^8.2` in Composer; `8.3.6` observed locally | Laravel application runtime. |
| **Laravel Framework** | `^12.0`; `12.60.2` installed | MVC framework, routing, container, Eloquent, migrations, and tests. |
| **Eloquent ORM** | Included in Laravel | Models, relationships, casts, fillable fields, and queries. |
| **Laravel Socialite** | `^5.27`; `5.27.0` installed | OAuth/OpenID client used by the UBS Keycloak login. |
| **SocialiteProviders Keycloak** | `^5.3`; `5.3.0` installed | Keycloak provider for Socialite. |
| **PostgreSQL** | Default in `.env.example` and `config/database.php` | Project default database following PDS-UEPG. |
| **SQLite** | Configured in `phpunit.xml` | In-memory database for local automated tests. |
| **Laravel Tinker** | `^2.10.1` | REPL for local inspection and operations. |

### Web Interface and Assets

| Technology | Version | Function |
| --- | --- | --- |
| **Blade** | Included in Laravel | Server-side templates in `resources/views`. |
| **Vite** | `^7.0.7`; `7.3.2` installed | Asset build and dev server. |
| **laravel-vite-plugin** | `^2.0.0`; `2.1.0` installed | Laravel/Vite integration. |
| **Tailwind CSS** | `^4.0.0`; `4.2.4` installed | Utility CSS configured in `resources/css/app.css`. |
| **@tailwindcss/vite** | `^4.0.0`; `4.2.4` installed | Tailwind plugin for Vite. |
| **Axios** | `^1.11.0`; `1.15.2` installed | HTTP client exposed by `resources/js/bootstrap.js`. |
| **Bootstrap CDN** | `5.3.8` in the Blade layout | Quick styling for the current views. |

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

- **Controllers**: HTTP entry point and JSON serialization.
- **Policies**: authorization by UBS authenticated through the `keycloak` guard.
- **Services**: validation, application rules, and orchestration.
- **Repositories**: Eloquent queries and record creation.
- **Models**: table mapping, casts, fillable fields, and relationships.

This is not strict Clean Architecture because services depend on concrete repositories and repositories depend directly on Eloquent. It still improves responsibility separation compared to controllers that contain embedded business logic.

### Conventional Commits

The Git history shows Conventional Commits in Brazilian Portuguese:

```text
feat(services): valida ids e emails nas buscas
refactor(routes): aplica prefixo api pelo provider
docs(models): documenta hard delete nos models
```

### REST CRUD by Resource

The main resources use `Route::apiResource`, producing index, store, show, update, and destroy endpoints for each module. The additional `delete-self` route repeats deletion using an explicit `id`. Except for UBS login/callback, these routes are protected by `auth:keycloak`.

---

## State and Data Management

### Backend — Persistence

| Aspect | Implementation |
| --- | --- |
| **ORM** | Eloquent Models in `application/app/Models`. |
| **Model IDs** | Models use `HasUuids` and entity migrations use UUID columns. |
| **Pagination** | Services normalize `per_page` into the 1 to 20 range. |
| **Casts** | `boolean`, `integer`, `date`, `array`, `float`, and native PHP enums. |
| **Migrations** | Loaded by Laravel from `database/migrations` and subdirectories. |
| **Test database** | In-memory SQLite configured in `phpunit.xml`. |

### Web Interface — State

The current views are rendered server-side with Blade. There is no global frontend state, SPA routing, or client-side authentication implemented in the versioned code.

### Client ↔ Backend Communication

| Aspect | Implementation |
| --- | --- |
| **API format** | JSON for REST controllers. |
| **Authentication** | `Authorization: Bearer <token>` validated against Keycloak by the `keycloak` guard. |
| **Pagination** | `?per_page=N` query string, with an effective maximum of 20. |
| **ID validation** | UUID validation through `ValidateUtils::validateId()` for lookups, updates, and deletes by ID. |
| **Email validation** | `ValidateUtils::validateEmail()` used by email lookup in UBS and user services. |
| **Authorization** | Policies restrict data by authenticated UBS and block district writes. |
