# Project Architecture

## Architecture Rationale

The project uses a layered Laravel architecture organized into **Controllers**, **Services**, **Repositories**, and **Eloquent Models**. This separation reduces coupling between HTTP transport, application rules, and persistence while still relying on Laravel's native capabilities.

This choice addresses three core needs of this project:

1. **Resource organization**: districts, UBS units, users, patients, assessments, risks, and reports follow the same controller, service, repository, and model flow.
2. **Application rule reuse**: UUID validation, email lookup, pagination, and deletion rules live in services instead of being repeated in controllers.
3. **Gradual evolution**: the codebase still uses raw Requests and Eloquent Models directly, but the current separation allows Form Requests, Resources, and targeted tests to be added without rewriting the API.
4. **UBS-scoped access control**: the API uses Keycloak/OpenID to authenticate the UBS account and policies to limit access to data linked to the authenticated unit.

On the web interface side, the architecture uses **Blade templates** with a base layout, simple pages, and public assets. Vite is configured to compile `resources/css/app.css` and `resources/js/app.js`, while some screens use CSS under `public/css`.

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
│                        Controllers                          │
│  Receive Request, apply Gates, and coordinate JSON responses│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                          Services                           │
│  Validate UUID/email, normalize pagination, orchestrate CRUD│
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
│  home.blade.php | register.blade.php | contact.blade.php    │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                 resources/views/layouts/main.blade.php      │
│  Base HTML, Bootstrap CDN, Roboto font, and public assets   │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                    public/css and public/js                 │
│  styles.css and scripts.js; register.styles.css is in resources/css│
└─────────────────────────────────────────────────────────────┘
```

---

## Data Flow — Typical Request

### API: Patient Creation

```text
1. Client sends POST /api/patients with JSON body and Authorization: Bearer <token>.
2. The keycloak guard validates the token in Keycloak and resolves the active UBS.
3. Laravel routes the request to PatientControllers\PatientController@store.
4. Controller authorizes the operation with PatientPolicy.
5. Controller passes $request->all() to PatientService::createPatient().
6. Service delegates to PatientRepository::createPatient().
7. Repository creates the record through PatientModel::newQuery()->create($data).
8. Eloquent applies fillable fields and casts from PatientModel.
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
5. KeycloakUbsAuthService finds the active UBS by keycloak_id or email.
6. API returns access_token, refresh_token, expires_in, and UBS data.
```

### Web: Registration Form

```text
1. Client opens GET /register/{id?}.
2. The route renders resources/views/register.blade.php.
3. The form submits POST to the named route web, exposed as /login.
4. The route redirects to `ubs.auth.login`, using Keycloak as the primary authentication source.
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
| `District`   | District registration and lookup for UBS units.                                                                          |
| `Ubs`        | UBS unit registration with contact data, neighborhood, address, and active status.                                       |
| `User`       | System user registration, including `admin` or `user` role, personal data, and UBS linkage.                              |
| `Patient`    | Patient registration linked to a UBS unit.                                                                               |
| `Assessment` | Assessment created by a user for a patient in a UBS unit, with symptoms and answers.                                     |
| `Risk`       | Risk record associated with an assessment, including percentage, score, and `low`, `moderate`, or `high` classification. |
| `Report`     | Report associated with an assessment, including title, description, and comment.                                         |

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
```

These relationships are declared in the models under `application/app/Models`. The versioned migrations create the main entity tables in resource subdirectories and are loaded by `AppServiceProvider`.
