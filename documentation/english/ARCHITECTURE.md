# Project Architecture

## Architecture Rationale

The project uses a layered Laravel architecture organized into **Controllers**, **Services**, **Repositories**, and **Eloquent Models**. This separation reduces coupling between HTTP transport, application rules, and persistence while still relying on Laravel's native capabilities.

This choice addresses three core needs of this project:

1. **Resource organization**: districts, UBS units, users, patients, assessments, risks, reports, and audit events follow explicit application flows.
2. **Input boundary**: Laravel Form Requests normalize and validate HTTP payloads before services receive data.
3. **Application rule reuse**: UUID and email lookup checks, tenant rules, transactions, logical deletion, and audit recording live in services instead of controllers or repositories.
4. **UBS-scoped access control**: the API uses Keycloak/OpenID to authenticate the UBS account and policies to limit access to unit data; the `audit-admin` client role governs institutional administration and global audit access.

On the web interface side, the architecture uses **Blade templates** with a base layout, UBS screens, and Vite-compiled assets. Bootstrap is imported from npm in `resources/css/app.css` and `resources/js/app.js`, and navigation SVGs live under `public/images`.

---

## Architecture Diagram (Backend)

```text
┌─────────────────────────────────────────────────────────────┐
│                         HTTP / API                          │
│  routes/web.php and api.php -> RouteServiceProvider         │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                    Keycloak Auth / Policies                 │
│  keycloak guard resolves UBS and Gates authorize by entity  │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                  Form Requests / Controllers               │
│  Validate payload, apply Gates, and coordinate JSON output │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                          Services                           │
│  Enforce rules, transact writes, and record audit events   │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                        Repositories                         │
│  Encapsulate Eloquent queries and record creation           │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                       Eloquent Models                       │
│  Tables, fillable fields, casts, and relationships          │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                          Database                           │
│  PostgreSQL by default; SQLite only for tests               │
└─────────────────────────────────────────────────────────────┘
```

## Architecture Diagram (Web Interface)

```text
┌─────────────────────────────────────────────────────────────┐
│                      resources/views                        │
│  ubs/auth, ubs/lobby, listings, and entity detail screens   │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                  resources/views/layouts/app.blade.php      │
│  Base layout, UBS navigation, and local-bypass warning      │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│               resources/css, resources/js, and public/images│
│  Bootstrap through Vite, product CSS, and module SVGs       │
└─────────────────────────────────────────────────────────────┘
```

---

## Data Flow — Typical Request

### API: Patient Creation

```text
1. Client sends POST /api/patients with JSON body and Authorization: Bearer <token>.
2. The keycloak guard validates the token in Keycloak and resolves the active UBS.
3. Laravel routes the request to PatientControllers\PatientController@store.
4. StorePatientRequest validates CPF and birth date and normalizes blank address/phone input to `null`; the controller uses `$request->validated()`.
5. Controller injects the authenticated `ubs_id` and authorizes the operation with PatientPolicy.
6. PatientService executes persistence and the audit event in a database transaction.
7. PatientRepository creates the record through PatientModel::newQuery()->create($data).
8. Eloquent applies fillable fields and casts, storing `birth` and deriving `age` in serialization.
9. Controller returns JSON with HTTP 201.
```

### API: Lookup by ID

```text
1. Client sends GET /api/users/{id} with Authorization: Bearer <token>.
2. UserControllers\UserController@show calls UserService::getUserById($id).
3. ValidateUtils::validateId() requires a valid UUID.
4. UserRepository::findUserById($id) queries the record with Eloquent.
5. If missing, the service throws ModelNotFoundException.
6. If found, the controller authorizes access with UserPolicy.
7. If authorized, the model is serialized as JSON.
```

### API: UBS Login Through Keycloak

```text
1. Client opens GET /api/auth/ubs/login.
2. UbsAuthController redirects to the Keycloak provider through Socialite.
3. Keycloak authenticates the institutional UBS account.
4. Callback GET /api/auth/ubs/callback receives the authenticated user.
5. KeycloakUbsAuthService finds an active UBS by `keycloak_id`, or binds a verified official-email record that is not yet linked.
6. The first binding is audited and the service derives the `audit-admin` client role from the verified token.
7. API returns access_token, refresh_token, expires_in, and UBS data.
```

### Web: UBS Login and Navigation

```text
1. Client opens GET /login.
2. The route renders resources/views/ubs/auth/login.blade.php or redirects to /ubs/lobby when a session already exists.
3. The institutional access link calls GET /auth/ubs/redirect.
4. UbsAuthController uses `KEYCLOAK_WEB_REDIRECT_URI` to authenticate in Keycloak and create an `auth:ubs` session.
5. The /ubs/lobby, /ubs/pacientes, /ubs/profissionais, and /ubs/avaliacoes pages use the shared Blade layout.
6. In local development, `GLICODATA_AUTH_DISABLED=true` temporarily removes the `auth:ubs` middleware from those pages.
```

---

## Dependency Inversion

The project uses Laravel container constructor injection:

```php
class UserController extends Controller
{
    public function __construct(
        protected \App\Services\UserServices\UserService $service,
    ) {
    }
}
```

Each service receives its corresponding repository, and each repository receives its corresponding Eloquent model:

```php
class UserService
{
    public function __construct(
        protected \App\Repositories\UserRepositories\UserRepository $repository,
    ) {
    }
}
```

There are no formal repository interfaces at the moment. The current separation still helps keep query changes away from controllers, but replacing repositories with mocks requires manual bindings or test doubles.

---

## System Modules

| Module       | Responsibility                                                                                                           |
| ------------ | ------------------------------------------------------------------------------------------------------------------------ |
| `District`   | Read-only institutional district catalog for UBS units.                                                                  |
| `Ubs`        | Institutional unit contact/activation data administered by the Keycloak `audit-admin` role.                             |
| `User`       | UBS-linked `professional` (doctor/nurse) or `admin` profile with optional contact data and logical deletion.            |
| `Patient`    | UBS-linked patient with optional contact data, stored birth date, calculated age, and logical deletion.                 |
| `Assessment` | UBS assessment associated through `user_id` with a same-unit executor, either `professional` or `admin`.                |
| `Risk`       | Risk record associated with an assessment, including percentage, score, and `low`, `moderate`, or `high` classification. |
| `Report`     | Report associated with an assessment, including title, description, and comment.                                         |
| `AuditEvent` | Immutable audit trail with before/after `jsonb` snapshots and audited sensitive-payload redaction.                     |

---

## Data Relationships

```text
District 1 ── N Ubs
Ubs      1 ── N User
Ubs      1 ── N Patient
Ubs      1 ── N Assessment
User     1 ── N Assessment
Patient  1 ── N Assessment
Assessment 1 ── 1 Risk
Assessment 1 ── 1 Report
Ubs      1 ── N AuditEvent
```

These relationships are declared in the models under `glicodata/app/Models`. The consolidated migrations target a fresh PostgreSQL deployment, enforce UBS ownership for assessments, add logical deletion/audit storage, and insert the initial institutional catalog; they are loaded from resource subdirectories by `AppServiceProvider`.
