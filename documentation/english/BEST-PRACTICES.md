# Best Practices

## SOLID Principles

### Single Responsibility (SRP)

Each class tends to have one main responsibility:

- **Form Requests / Controllers**: validate HTTP payloads, authorize operations, and return JSON.
- **Policies**: authorize access according to the authenticated UBS.
- **Services**: validate lookups, enforce tenant rules, transact writes, perform logical deletion, and record audits.
- **Repositories**: encapsulate Eloquent queries.
- **Models**: declare table names, fillable fields, casts, and relationships.
- **Enums**: isolate allowed values for roles and risk classification.

### Open/Closed (OCP)

New resources can be added by replicating the controller, service, repository, and model pattern without changing existing resources. To evolve this practice, shared behavior should go into traits, policies, form requests, or dedicated classes instead of large conditionals in controllers.

### Liskov Substitution (LSP)

Because services depend on concrete repositories, replacement with alternative implementations is not automatic yet. If the application starts requiring frequent mocks or multiple persistence mechanisms, introduce contracts and container bindings.

### Interface Segregation (ISP)

There are no interfaces in the current design. Practical segregation happens through small classes per resource. If contracts are added, keep one interface per resource and avoid generic repositories with methods not every model uses.

### Dependency Inversion (DIP)

Laravel's container injects controllers, services, repositories, and models. Dependencies still point to concrete classes, which is pragmatic for the current size of the project. For more complex rules, use interfaces under `app/Contracts` or `app/Repositories/Contracts`.

---

## Error Handling

### Backend

| Layer | Current strategy |
| --- | --- |
| **Controllers** | Return `JsonResponse` and delegate errors to Laravel's default handler. |
| **Services** | Throw `ValidationException` for invalid UUID/email values and `ModelNotFoundException` for missing records. |
| **Repositories** | Propagate Eloquent and database errors. |
| **Models** | Use casts to normalize types during serialization and persistence. |

### Areas to Strengthen

| Area | Recommendation |
| --- | --- |
| **Input validation** | Form Requests are implemented; maintain them as the only input boundary for write payloads. |
| **Error format** | Standardize JSON responses for validation, not found, and conflict errors. |
| **Authentication** | Keep Sanctum expiration, token limits, Argon2id, and revocation covered by tests. |
| **Authorization** | Keep global administrators separate from UBS and deny automatic clinical access. |
| **Transactions** | Written operations and their audit events are transactional; preserve this invariant in new workflows. |

---

## Testing

### Configured Test Types

| Type | Framework | Configuration |
| --- | --- | --- |
| **Unit** | PHPUnit 11 | `tests/Unit` suite in `phpunit.xml`. |
| **Feature** | Laravel TestCase + PHPUnit | `tests/Feature` suite with in-memory SQLite. |

### Test Structure

```bash
glicodata/tests/
├── Feature/
│   ├── ApiValidationTest.php
│   └── ExampleTest.php
├── Unit/
│   └── ExampleTest.php
└── TestCase.php
```

### Current Coverage

The current checkout contains example tests:

- `GET /` must return HTTP 200.
- Authentication tests cover API/web login, expiration, rate limiting, revocation, account separation, and credential commands.
- One basic unit test asserts that `true` is true.

To cover the real API, prioritize:

1. Feature tests for each resource CRUD.
2. Validation tests for invalid UUID and invalid email.
3. Pagination tests for `per_page` below 1 and above 20.
4. Serialization tests for enums and casts.
5. Error tests for `ModelNotFoundException`.

---

## Security

### Authentication

`UbsModel` and `AdministratorModel` extend `Authenticatable`, hide passwords, use the `hashed` cast, and issue Sanctum tokens. Passwords are compared with `Hash::check` and are never decrypted. Blade uses separate session guards.

There is no authentication bypass. Initial credentials are provisioned through interactive Artisan commands.

### Authorization

Entity policies are registered in `AppServiceProvider` and called by controllers through `Gate::authorize()`. They restrict listings and actions to the authenticated UBS scope, with districts in read-only mode.

### Validation and Sanitization

| Aspect | Current implementation |
| --- | --- |
| **UUID** | `ValidateUtils::validateId()` uses `Str::isUuid()`. |
| **Email** | `ValidateUtils::validateEmail()` uses Laravel validator with `email:rfc` and `max:255`. |
| **HTTP payloads** | Resource Form Requests validate normalized data, formatted CPF, date of birth, and pagination before services run. |
| **Optional contact** | `User` and `Patient` address/phone may be `NULL`; their Form Requests normalize blank input to `null`. |
| **Professional role** | `UserRole::Professional` identifies doctors and nurses; `admin` may also be associated as an assessment executor. |
| **Mass assignment** | Models use `fillable`, reducing exposure of disallowed fields. |
| **Password** | `UserModel` and `UbsModel` hide `password` and apply the `hashed` cast. |
| **Bearer token** | Sanctum stores SHA-256 hashes, applies abilities, expires tokens in 24 hours, and limits accounts to 20. |
| **CSRF** | The Blade form uses `@csrf`. |

### Environment Variables

`.env` is not versioned. The `.env.example` template defines:

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
HASH_DRIVER=argon2id
HASH_VERIFY=false
SANCTUM_EXPIRATION=1440
SANCTUM_TOKEN_PREFIX=glicodata_
SESSION_DRIVER=database
SESSION_ENCRYPT=true
QUEUE_CONNECTION=database
CACHE_STORE=database
```

In production:

- `APP_DEBUG` must be `false`.
- `APP_KEY` must be generated and protected.
- Database credentials must live only in `.env`.
- Define `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in application environments.

---

## Persistence and Integrity

### Strengths

- Eloquent relationships are declared in models.
- Native enums reduce invalid values for `role` and `classification`.
- Services validate UUIDs before lookup by ID.
- Pagination has an upper limit to reduce large responses.

### Current Risks

| Risk | Impact |
| --- | --- |
| Public CNES claim without review | An attacker could claim a real CNES; accounts therefore remain inactive until manual administrator approval. |
| Scheduler not running | Expired tokens stop authenticating, but old database rows accumulate until pruning runs. |
| Audit snapshots contain personal data | Database access, backups, retention, and redaction procedures must be restricted according to NTI governance. |
| Irreversible legacy cleanup | The catalog cleanup deletes linked clinical/audit data and requires a verified PostgreSQL backup. |

---

## Recommended Best Practices for Next Changes

1. Add API Resources to control response shape and prevent unnecessary personal-data exposure.
2. Establish NTI-approved audit retention, backup access, and redaction procedures before production data is processed.
3. Provision and review global administrator accounts separately from UBS accounts.
4. Update and expand feature tests in the dedicated testing stage.
5. Evaluate caching or token introspection if the `userinfo` endpoint becomes a bottleneck.
6. Run `sanctum:prune-expired --hours=24` through the production scheduler.
