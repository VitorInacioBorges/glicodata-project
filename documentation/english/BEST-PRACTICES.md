# Best Practices

## SOLID Principles

### Single Responsibility (SRP)

Each class tends to have one main responsibility:

- **Controllers**: receive requests and return JSON.
- **Policies**: authorize access according to the authenticated UBS.
- **Services**: validate IDs, emails, pagination, and coordinate create/update/delete operations.
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
| **Input validation** | Create Form Requests per resource to replace `$request->all()` in controllers. |
| **Error format** | Standardize JSON responses for validation, not found, and conflict errors. |
| **Authentication** | Keep Keycloak as the primary source and document realm/client configuration per environment. |
| **Authorization** | Evolve policies into more granular roles when profiles beyond the authenticated UBS exist. |
| **Transactions** | Use `DB::transaction()` when an operation writes to multiple tables. |

---

## Testing

### Configured Test Types

| Type | Framework | Configuration |
| --- | --- | --- |
| **Unit** | PHPUnit 11 | `tests/Unit` suite in `phpunit.xml`. |
| **Feature** | Laravel TestCase + PHPUnit | `tests/Feature` suite with in-memory SQLite. |

### Test Structure

```bash
application/tests/
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
- Basic invalid-email validations for user routes exist in the checkout, but need review for the new `keycloak` guard.
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

`UbsModel` extends `Authenticatable`, hides `password`, and has the `password => hashed` cast. The API's main authentication uses Keycloak/OpenID through Laravel Socialite and the `keycloak` guard. Local username/password login is no longer the primary source.

### Authorization

Entity policies are registered in `AppServiceProvider` and called by controllers through `Gate::authorize()`. They restrict listings and actions to the authenticated UBS scope, with districts in read-only mode.

### Validation and Sanitization

| Aspect | Current implementation |
| --- | --- |
| **UUID** | `ValidateUtils::validateId()` uses `Str::isUuid()`. |
| **Email** | `ValidateUtils::validateEmail()` uses Laravel validator with `email:rfc` and `max:255`. |
| **Mass assignment** | Models use `fillable`, reducing exposure of disallowed fields. |
| **Password** | `UserModel` and `UbsModel` hide `password` and apply the `hashed` cast. |
| **Bearer token** | `KeycloakUbsAuthService` calls Keycloak's `userinfo` endpoint to resolve the active UBS. |
| **CSRF** | The Blade form uses `@csrf`. |

### Environment Variables

`.env` is not versioned. The `.env.example` template defines:

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
KEYCLOAK_BASE_URL=
KEYCLOAK_REALM=
SESSION_DRIVER=database
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
| No Form Requests | Invalid fields may reach models until rejected by the database or casts. |
| Misconfigured Keycloak dependency | Tokens will not be validated and protected routes will return 401. |
| Default hard delete | Deletions remove records without a logical recycle bin. |

---

## Recommended Best Practices for Next Changes

1. Introduce Form Requests for each resource `store` and `update`.
2. Add API Resources to control response shape.
3. Define additional roles and rules when profiles beyond UBS exist.
4. Cover services, policies, and controllers with feature tests.
5. Evaluate caching or token introspection if the `userinfo` endpoint becomes a bottleneck.
