# Directory Mapping

## Full Structure

```bash
ubs-system/
├── application/
│   ├── app/
│   │   ├── Enums/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   ├── Models/
│   │   ├── Policies/
│   │   ├── Providers/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   └── Utils/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── public/
│   │   ├── css/
│   │   └── js/
│   ├── resources/
│   │   ├── css/
│   │   ├── js/
│   │   └── views/
│   │       └── layouts/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   │   ├── Feature/
│   │   └── Unit/
│   ├── artisan
│   ├── composer.json
│   ├── package.json
│   ├── phpunit.xml
│   └── vite.config.js
├── documentation/
│   ├── english/
│   └── portuguese/
├── .gitignore
└── README.md
```

Directories ignored by `.gitignore`, such as `application/vendor/`, `application/node_modules/`, `application/.env`, caches, logs, and generated files under `storage/`, are not part of the operational documentation.

---

## Backend — Directory Details

### `application/app/Http/Controllers/`

HTTP controllers for the API. They receive `Illuminate\Http\Request`, apply authorization through `Gate`, extract `per_page` when needed, delegate to services, and return `JsonResponse`.

| Path | Base routes |
| --- | --- |
| `DistrictControllers/DistrictController.php` | `/api/districts` |
| `UbsControllers/UbsController.php` | `/api/ubs` |
| `UbsControllers/UbsAuthController.php` | `/api/auth/ubs/*` |
| `UserControllers/UserController.php` | `/api/users` |
| `PatientControllers/PatientController.php` | `/api/patients` |
| `AssessmentControllers/AssessmentController.php` | `/api/assessments` |
| `RiskControllers/RiskController.php` | `/api/risks` |
| `ReportControllers/ReportController.php` | `/api/reports` |

Each controller exposes the standard `Route::apiResource` CRUD actions plus an additional `DELETE /api/{resource}/{id}/delete-self` route.

### `application/app/Services/`

Application layer. Services are separated by entity folder and centralize UUID validation, email validation where email lookup exists, pagination normalization, and update/delete orchestration.

| Path | Responsibility |
| --- | --- |
| `DistrictServices/DistrictService.php` | District CRUD and pagination limited between 1 and 20 items. |
| `UbsServices/UbsService.php` | UBS CRUD, email lookup, and `keycloak_id` lookup. |
| `UbsServices/KeycloakUbsAuthService.php` | Resolves the authenticated UBS from the Bearer token or the Socialite user returned by Keycloak. |
| `UserServices/UserService.php` | User CRUD and email lookup. |
| `PatientServices/PatientService.php` | Patient CRUD. |
| `AssessmentServices/AssessmentService.php` | Assessment CRUD. |
| `RiskServices/RiskService.php` | Risk CRUD. |
| `ReportServices/ReportService.php` | Report CRUD. |

### `application/app/Repositories/`

Data access layer. Repositories are separated by entity folder, use `newQuery()` on Eloquent models, and encapsulate queries reused by services.

| Path | Defined operations |
| --- | --- |
| `DistrictRepositories/DistrictRepository.php` | `paginateDistricts`, `findDistrictById`, `createDistrict` |
| `UbsRepositories/UbsRepository.php` | `paginateUbs`, `paginateAuthenticatedUbs`, `findUbsById`, `findUbsByEmail`, `findUbsByKeycloakId`, `createUbs` |
| `UserRepositories/UserRepository.php` | `paginateUsers`, `paginateUsersForUbs`, `findUserById`, `findUserByEmail`, `createUser` |
| `PatientRepositories/PatientRepository.php` | `paginatePatients`, `paginatePatientsForUbs`, `findPatientById`, `createPatient` |
| `AssessmentRepositories/AssessmentRepository.php` | `paginateAssessments`, `paginateAssessmentsForUbs`, `findAssessmentById`, `createAssessment` |
| `RiskRepositories/RiskRepository.php` | `paginateRisks`, `paginateRisksForUbs`, `findRiskById`, `createRisk` |
| `ReportRepositories/ReportRepository.php` | `paginateReports`, `paginateReportsForUbs`, `findReportById`, `createReport` |

### `application/app/Policies/`

Entity policies registered in `AppServiceProvider`. They authorize the UBS authenticated by the `keycloak` guard to access only data linked to its own UBS, except districts, which are read-only.

| Path | Responsibility |
| --- | --- |
| `DistrictPolicies/DistrictPolicy.php` | Allows listing/lookup for active UBS accounts and blocks writes. |
| `UbsPolicies/UbsPolicy.php` | Allows read, update, and delete only for the authenticated UBS itself. |
| `UserPolicies/UserPolicy.php` | Restricts users to the authenticated UBS `ubs_id`. |
| `PatientPolicies/PatientPolicy.php` | Restricts patients to the authenticated UBS `ubs_id`. |
| `AssessmentPolicies/AssessmentPolicy.php` | Restricts assessments to the authenticated UBS `ubs_id`. |
| `RiskPolicies/RiskPolicy.php` | Restricts risks through the assessment linked to the authenticated UBS. |
| `ReportPolicies/ReportPolicy.php` | Restricts reports through the assessment linked to the authenticated UBS. |

### `application/app/Models/`

Eloquent models with `fillable`, casts, explicit table names, and relationships.

| File | Table | Main relationships |
| --- | --- | --- |
| `DistrictModel.php` | `districts` | `hasMany(UbsModel)` |
| `UbsModel.php` | `ubs` | `belongsTo(DistrictModel)`, `hasMany(UserModel)`, `hasMany(PatientModel)`, `hasMany(AssessmentModel)`; also acts as the authenticatable UBS entity. |
| `UserModel.php` | `users` | `belongsTo(UbsModel)`, `hasMany(AssessmentModel)` |
| `PatientModel.php` | `patients` | `belongsTo(UbsModel)`, `hasMany(AssessmentModel)` |
| `AssessmentModel.php` | `assessments` | `belongsTo(PatientModel)`, `belongsTo(UserModel)`, `belongsTo(UbsModel)`, `hasOne(RiskModel)`, `hasOne(ReportModel)` |
| `RiskModel.php` | `risks` | `belongsTo(AssessmentModel)` |
| `ReportModel.php` | `reports` | `belongsTo(AssessmentModel)` |

### `application/app/Enums/`

Native PHP enums used as model casts.

| File | Values |
| --- | --- |
| `UserRole.php` | `admin`, `user` |
| `RiskClassification.php` | `low`, `moderate`, `high` |

### `application/app/Utils/`

| File | Responsibility |
| --- | --- |
| `ValidateUtils.php` | Trait with UUID, RFC email, and per-entity create/update payload validation. |

### `application/app/Providers/`

| File | Responsibility |
| --- | --- |
| `AppServiceProvider.php` | Registers Socialite Keycloak, the `keycloak` guard, policies, and migration loading from subdirectories. |
| `RouteServiceProvider.php` | Loads `routes/web.php` with `web` middleware and `routes/api.php` with `api` middleware and `/api` prefix. |

---

## Routes

### `application/routes/web.php`

Blade interface routes without the `/api` prefix.

| Route | Type | Responsibility |
| --- | --- | --- |
| `GET /` | Web view | Renders `home.blade.php`. |
| `GET /contact` | Web view | Renders `contact.blade.php`. |
| `GET /register/{id?}` | Web view | Renders the registration form. |
| `POST /login` | Web action | Redirects to the `ubs.auth.login` route, delegating login to Keycloak. |

### `application/routes/api.php`

JSON routes loaded with the `/api` prefix. Only `GET /api/auth/ubs/login` and `GET /api/auth/ubs/callback` are open; every other API route uses `auth:keycloak`.

| Route | Type | Responsibility |
| --- | --- | --- |
| `GET /api/auth/ubs/login` | Auth | Redirects to Keycloak login. |
| `GET /api/auth/ubs/callback` | Auth | Receives the Keycloak callback and returns token/active UBS data. |
| `GET /api/auth/ubs/me` | Auth | Returns the UBS authenticated by the Bearer token. |
| `GET /api/auth/ubs/logout` | Auth | Returns the Keycloak logout URL. |
| `apiResource` | REST JSON | CRUD for `districts`, `ubs`, `users`, `patients`, `assessments`, `risks`, `reports`. |
| `DELETE /api/{resource}/{id}/delete-self` | REST JSON | Alternative deletion route for each resource. |

---

## Database

### `application/database/migrations/`

| File | Created tables |
| --- | --- |
| `district-migrations/2026_01_23_143000_create_districts_table.php` | `districts` |
| `ubs-migrations/2026_01_23_143100_create_ubs_table.php` | `ubs` |
| `ubs-migrations/2026_05_21_000000_add_auth_fields_to_ubs_table.php` | Adjusts existing databases with `ubs.password`, `ubs.keycloak_id`, and nullable `users.password`. |
| `user-migrations/2026_01_23_143151_create_users_table.php` | `users` |
| `patient-migrations/2026_01_23_143200_create_patients_table.php` | `patients` |
| `assessment-migrations/2026_01_23_143300_create_assessments_table.php` | `assessments` |
| `risk-migrations/2026_01_23_143400_create_risks_table.php` | `risks` |
| `report-migrations/2026_01_23_143500_create_reports_table.php` | `reports` |
| `2026_01_23_150700_password_reset_tokens.php` | `password_reset_tokens` |
| `2026_04_27_135537_create_sessions_table.php` | `sessions` |
| `2026_04_27_145038_create_cache_table.php` | `cache`, `cache_locks` |

Entity migrations use UUIDs and are separated by entity folder.

### `application/database/seeders/`

| File | Responsibility |
| --- | --- |
| `DatabaseSeeder.php` | Creates a district, a UBS with `keycloak_id`, and an operational test user. |

### `application/database/factories/`

| File | Responsibility |
| --- | --- |
| `UserFactory.php` | Default user factory for tests and seeders. |

---

## Web Interface and Assets

### `application/resources/views/`

| File | Responsibility |
| --- | --- |
| `layouts/main.blade.php` | Base HTML layout with Bootstrap CDN, Google Fonts Roboto, `public/css/styles.css`, and `public/js/scripts.js`. |
| `home.blade.php` | Simple "Sistema UBS" home screen. |
| `register.blade.php` | Registration form displaying the "Glicodata" name. |
| `contact.blade.php` | Simple contact page. |

### `application/public/`

| Path | Responsibility |
| --- | --- |
| `public/index.php` | Laravel front controller. |
| `public/css/styles.css` | Simple global style for font and `h1` color. |
| `public/js/scripts.js` | Current public script with a functioning log. |

### `application/resources/css` and `application/resources/js`

Vite entry files configured in `vite.config.js`: `resources/css/app.css` and `resources/js/app.js`. The file `resources/css/register.styles.css` also exists and contains registration form styles, but the current view references `/css/register.styles.css`, a path that would point to `public/css/register.styles.css`.

---

## Tests

| Path | Responsibility |
| --- | --- |
| `tests/Feature/ExampleTest.php` | Tests that `GET /` returns HTTP 200. |
| `tests/Feature/ApiValidationTest.php` | Covers basic API validations; needs review for the `keycloak` guard. |
| `tests/Unit/ExampleTest.php` | Basic `assertTrue(true)` unit test. |
| `phpunit.xml` | Configures Unit and Feature suites with in-memory SQLite for tests. |
