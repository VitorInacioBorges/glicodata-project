# Organization and Naming Standards

## Naming Conventions

### Backend (PHP / Laravel)

| Element | Current convention | Example |
| --- | --- | --- |
| **Namespaces** | PSR-4 under `App\`, with entity subfolders in main layers | `App\Services\UserServices\UserService` |
| **Classes** | `PascalCase` | `UserService`, `PatientRepository` |
| **Controllers** | Singular resource + `Controller` | `RiskController` |
| **Services** | Singular resource + `Service` | `AssessmentService` |
| **Repositories** | Singular resource + `Repository` | `DistrictRepository` |
| **Models** | Singular resource + `Model` | `UbsModel`, `ReportModel` |
| **Enums** | `PascalCase` | `UserRole`, `RiskClassification` |
| **Enum values** | Persisted values in `lowercase` | `admin`, `user`, `low` |
| **Methods** | `camelCase` | `getUserById()`, `createRisk()` |
| **Variables** | `camelCase` | `$perPage`, `$assessment` |
| **Tables** | Plural `snake_case` | `users`, `assessments` |
| **Columns** | Mostly `snake_case` | `ubs_id`, `assessment_id` |

### Views and Assets

| Element | Current convention | Example |
| --- | --- | --- |
| **Blade views** | Lowercase simple names or kebab-style names | `home.blade.php`, `register.blade.php` |
| **Blade layouts** | `layouts/` subdirectory | `layouts/main.blade.php` |
| **Screen CSS** | Descriptive names with dots | `register.styles.css` |
| **Public JS** | Simple lowercase names | `scripts.js` |
| **Vite entries** | `resources/css/app.css`, `resources/js/app.js` | Configured in `vite.config.js` |

---

## File Type Suffix Standard

| Suffix / Pattern | Type | Layer |
| --- | --- | --- |
| `*Controller.php` | HTTP Controller | Entry point |
| `*Policy.php` | Laravel policy | Authorization |
| `*Service.php` | Application service | Rules and orchestration |
| `*Repository.php` | Eloquent repository | Persistence |
| `*Model.php` | Eloquent model | Data and relationships |
| `*.blade.php` | Blade template | Server-side interface |
| `*.css` | Styles | Assets |
| `*.js` | JavaScript | Assets |
| `*Test.php` | PHPUnit test | Tests |

---

## Design Patterns Used

### Service Layer

Services encapsulate rules that do not belong directly to HTTP transport. Example:

```php
public function getUserById(string $id): UserModel
{
    $this->validateId($id);

    $user = $this->repository->findUserById($id);

    if ($user === null) {
        throw (new ModelNotFoundException())->setModel(UserModel::class, [$id]);
    }

    return $user;
}
```

### Repository Pattern

Repositories encapsulate Eloquent queries and record creation. The current pattern uses concrete classes, without interfaces:

```text
UserServices/UserService -> UserRepositories/UserRepository -> UserModel
```

### Policy / Gate

Controllers use `Gate::authorize()` before returning or changing resources. Policies live in entity subfolders, such as `app/Policies/UserPolicies/UserPolicy.php`, and receive the authenticated UBS as the user from the `keycloak` guard.

### Shared Validation Trait

`ValidateUtils` centralizes UUID and email validation to avoid repetition across services.

### Active Record / Eloquent Model

Models centralize fillable fields, casts, and relationships. This is the native Laravel approach and is used by every main resource.

### Resource Routing

`Route::apiResource()` generates predictable REST routes for index, store, show, update, and destroy. The project applies this pattern to seven resources.

### Provider Pattern

`RouteServiceProvider` and `AppServiceProvider` customize framework bootstrapping: route loading with the `/api` prefix and migration loading from subdirectories.

---

## Resource-Based Organization

Each main resource has parallel files across layers, separated by entity folder:

```text
app/Http/Controllers/UserControllers/UserController.php
app/Services/UserServices/UserService.php
app/Repositories/UserRepositories/UserRepository.php
app/Policies/UserPolicies/UserPolicy.php
app/Models/UserModel.php
```

The same pattern exists for:

| Resource | Controller | Service | Repository | Policy | Model |
| --- | --- | --- | --- | --- | --- |
| District | `DistrictControllers/DistrictController` | `DistrictServices/DistrictService` | `DistrictRepositories/DistrictRepository` | `DistrictPolicies/DistrictPolicy` | `DistrictModel` |
| UBS | `UbsControllers/UbsController` | `UbsServices/UbsService` | `UbsRepositories/UbsRepository` | `UbsPolicies/UbsPolicy` | `UbsModel` |
| User | `UserControllers/UserController` | `UserServices/UserService` | `UserRepositories/UserRepository` | `UserPolicies/UserPolicy` | `UserModel` |
| Patient | `PatientControllers/PatientController` | `PatientServices/PatientService` | `PatientRepositories/PatientRepository` | `PatientPolicies/PatientPolicy` | `PatientModel` |
| Assessment | `AssessmentControllers/AssessmentController` | `AssessmentServices/AssessmentService` | `AssessmentRepositories/AssessmentRepository` | `AssessmentPolicies/AssessmentPolicy` | `AssessmentModel` |
| Risk | `RiskControllers/RiskController` | `RiskServices/RiskService` | `RiskRepositories/RiskRepository` | `RiskPolicies/RiskPolicy` | `RiskModel` |
| Report | `ReportControllers/ReportController` | `ReportServices/ReportService` | `ReportRepositories/ReportRepository` | `ReportPolicies/ReportPolicy` | `ReportModel` |

---

## Operational Conventions

| Area | Convention |
| --- | --- |
| **Pagination** | Controllers read `per_page`; services limit it between 1 and 20. |
| **Deletion** | Models comment on hard delete usage; there is no `SoftDeletes` trait in the current models. |
| **Routes** | `routes/api.php` receives the `/api` prefix; `routes/web.php` stays outside the API prefix. |
| **Responses** | Controllers return JSON for the API; `store` uses status 201 and delete uses 204. |
| **HTTP validation** | There are no Form Requests yet; controllers pass `$request->all()`. |
| **Authentication** | API uses the `keycloak` guard; UBS login/callback are the only open API routes. |
| **Authorization** | Controllers use `Gate::authorize()` with entity policies. |

---

## Known Inconsistencies

- The register view references `/css/register.styles.css`, but the checkout only versions `public/css/styles.css`; `register.styles.css` is present under `resources/css`.
- There are no Form Requests yet; controllers still pass `$request->all()` to services.
