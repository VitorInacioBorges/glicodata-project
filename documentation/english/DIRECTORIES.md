# Directory Mapping

## Full Structure

```bash
ubs-system/
├── application/
│   ├── app/
│   │   ├── Enums/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Policies/
│   │   ├── Providers/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   ├── Rules/
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

HTTP controllers for the API. They receive validated Form Requests, apply authorization through `Gate`, inject authenticated ownership when necessary, delegate to services, and return `JsonResponse`.

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
| `AuditEventControllers/AuditEventController.php` | `/api/audit-events` |

Operational entity controllers expose CRUD through `Route::apiResource`. District is read-only, UBS only supports read and administrative update, and audit events use read and redaction routes.

### `application/app/Http/Requests/`

Form Requests normalize and validate incoming data before the controller calls a service. Resource requests prevent clients from supplying ownership or identity-provider fields, `PaginationRequest` caps `per_page` at 20, and `RedactAuditEventRequest` validates the audit redaction reason.

### `application/app/Services/`

Application layer. Services are separated by entity folder and centralize UUID/email lookup checks, tenant invariants, transactions, logical deletions, and audit recording.

| Path | Responsibility |
| --- | --- |
| `DistrictServices/DistrictService.php` | Read-only district lookup and pagination. |
| `UbsServices/UbsService.php` | UBS lookup and administrative update/activation rules. |
| `UbsServices/KeycloakUbsAuthService.php` | Resolves active UBS identities, first official-email binding, and the verified `audit-admin` role. |
| `UserServices/UserService.php` | User CRUD, logical deletion, email lookup, and audited transactions. |
| `PatientServices/PatientService.php` | Patient CRUD, logical deletion, and audited transactions. |
| `AssessmentServices/AssessmentService.php` | Assessment CRUD with tenant consistency and transactional logical deletion of associated risk/report. |
| `RiskServices/RiskService.php` | Risk CRUD, logical deletion, and audit. |
| `ReportServices/ReportService.php` | Report CRUD, logical deletion, and audit. |
| `AuditEventServices/AuditEventService.php` | Scoped audit listing and audited payload redaction. |

### `application/app/Repositories/`

Data access layer. Repositories are separated by entity folder, use `newQuery()` on Eloquent models, and encapsulate queries reused by services.

| Path | Defined operations |
| --- | --- |
| `DistrictRepositories/DistrictRepository.php` | `paginateDistricts`, `findDistrictById` |
| `UbsRepositories/UbsRepository.php` | `paginateUbs`, `paginateAuthenticatedUbs`, `findUbsById`, `findUbsByEmail`, `findUbsByKeycloakId` |
| `UserRepositories/UserRepository.php` | `paginateUsers`, `paginateUsersForUbs`, `findUserById`, `findUserByEmail`, `createUser` |
| `PatientRepositories/PatientRepository.php` | `paginatePatients`, `paginatePatientsForUbs`, `findPatientById`, `createPatient` |
| `AssessmentRepositories/AssessmentRepository.php` | `paginateAssessments`, `paginateAssessmentsForUbs`, `findAssessmentById`, `createAssessment` |
| `RiskRepositories/RiskRepository.php` | `paginateRisks`, `paginateRisksForUbs`, `findRiskById`, `createRisk` |
| `ReportRepositories/ReportRepository.php` | `paginateReports`, `paginateReportsForUbs`, `findReportById`, `createReport` |
| `AuditEventRepositories/AuditEventRepository.php` | `paginateAuditEvents`, `paginateAuditEventsForUbs`, `findAuditEventById`, `createAuditEvent` |

### `application/app/Policies/`

Entity policies registered in `AppServiceProvider`. They authorize the UBS authenticated by the `keycloak` guard to access only data linked to its own UBS, except districts, which are read-only.

| Path | Responsibility |
| --- | --- |
| `DistrictPolicies/DistrictPolicy.php` | Allows listing/lookup for active UBS accounts and blocks writes. |
| `UbsPolicies/UbsPolicy.php` | Allows reads in scope and reserves UBS update to active `audit-admin` identities; deletion is blocked. |
| `UserPolicies/UserPolicy.php` | Restricts users to the authenticated UBS `ubs_id`. |
| `PatientPolicies/PatientPolicy.php` | Restricts patients to the authenticated UBS `ubs_id`. |
| `AssessmentPolicies/AssessmentPolicy.php` | Restricts assessments to the authenticated UBS `ubs_id`. |
| `RiskPolicies/RiskPolicy.php` | Restricts risks through the assessment linked to the authenticated UBS. |
| `ReportPolicies/ReportPolicy.php` | Restricts reports through the assessment linked to the authenticated UBS. |
| `AuditEventPolicies/AuditEventPolicy.php` | Restricts ordinary UBS to its own events and allows global read/redaction to `audit-admin`. |

### `application/app/Models/`

Eloquent models with `fillable`, casts, explicit table names, and relationships.

| File | Table | Main relationships |
| --- | --- | --- |
| `DistrictModel.php` | `districts` | `hasMany(UbsModel)` |
| `UbsModel.php` | `ubs` | `belongsTo(DistrictModel)`, operational collections, and audit events; also acts as the authenticatable UBS entity. |
| `UserModel.php` | `users` | UBS professional (doctor/nurse) or administrator; `belongsTo(UbsModel)`, `hasMany(AssessmentModel)`; soft-deleted with calculated age. |
| `PatientModel.php` | `patients` | UBS-linked patient; `belongsTo(UbsModel)`, `hasMany(AssessmentModel)`; soft-deleted with calculated age. |
| `AssessmentModel.php` | `assessments` | `belongsTo(PatientModel)`, `belongsTo(UserModel)`, `belongsTo(UbsModel)`, `hasOne(RiskModel)`, `hasOne(ReportModel)` |
| `RiskModel.php` | `risks` | `belongsTo(AssessmentModel)` |
| `ReportModel.php` | `reports` | `belongsTo(AssessmentModel)` |
| `AuditEventModel.php` | `audit_events` | `belongsTo(UbsModel)` as actor and owner |

### `application/app/Enums/`

Native PHP enums used as model casts.

| File | Values |
| --- | --- |
| `UserRole.php` | `admin`, `professional` |
| `RiskClassification.php` | `low`, `moderate`, `high` |

### `application/app/Utils/`

| File | Responsibility |
| --- | --- |
| `ValidateUtils.php` | Trait with UUID and RFC email validation used by service lookup methods. |

### `application/app/Rules/`

| File | Responsibility |
| --- | --- |
| `CpfRules/ValidCpf.php` | Validates formatted Brazilian CPF values and verifying digits for HTTP requests. |

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
| `GET /api/districts*` | REST JSON | Read-only institutional district catalog. |
| `GET/PUT/PATCH /api/ubs*` | REST JSON | UBS read and `audit-admin` managed update. |
| `apiResource` | REST JSON | CRUD for `users`, `patients`, `assessments`, `risks`, and `reports`; deletes are logical. |
| `GET/POST /api/audit-events*` | REST JSON | Scoped audit reading and administrative payload redaction. |

---

## Database

### `application/database/migrations/`

| File | Created tables |
| --- | --- |
| `district-migrations/2026_01_23_143000_create_districts_table.php` | `districts` |
| `ubs-migrations/2026_01_23_143100_create_ubs_table.php` | `ubs` |
| `ubs-migrations/2026_01_23_143150_seed_ponta_grossa_catalog.php` | Inserts the initial district/UBS institutional catalog; provisional units remain inactive. |
| `user-migrations/2026_01_23_143151_create_users_table.php` | `users`, with `professional`/`admin` roles and optional contact fields |
| `patient-migrations/2026_01_23_143200_create_patients_table.php` | `patients`, with optional address and phone fields |
| `assessment-migrations/2026_01_23_143300_create_assessments_table.php` | `assessments` |
| `risk-migrations/2026_01_23_143400_create_risks_table.php` | `risks` |
| `report-migrations/2026_01_23_143500_create_reports_table.php` | `reports` |
| `audit-event-migrations/2026_01_23_143600_create_audit_events_table.php` | `audit_events` |
| `2026_01_23_150800_create_jobs_tables.php` | `jobs`, `job_batches`, `failed_jobs` |
| `2026_01_23_150700_password_reset_tokens.php` | `password_reset_tokens` |
| `2026_04_27_135537_create_sessions_table.php` | `sessions` |
| `2026_04_27_145038_create_cache_table.php` | `cache`, `cache_locks` |

Entity migrations use UUIDs, PostgreSQL integrity constraints, soft-delete columns for operational records, and are separated by entity folder. For users, `professional` represents doctors and nurses; `admin` may also be recorded as the assessment executor. User and patient address/phone fields may be `NULL` when unavailable. This consolidated schema targets a fresh database; applying it over an already-migrated production database requires a separate migration strategy.

### `application/database/seeders/`

| File | Responsibility |
| --- | --- |
| `DatabaseSeeder.php` | Creates a district, a UBS with `keycloak_id`, and an operational `professional` test profile. |

### `application/database/factories/`

| File | Responsibility |
| --- | --- |
| `UserFactory.php` | Default `professional`/`admin` user-profile factory for tests and seeders. |

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
| `tests/Feature/ApiValidationTest.php` | Existing API validations; requires a later update for Form Requests, `birth`, logical deletion, audit, and Keycloak authorization. |
| `tests/Unit/ExampleTest.php` | Basic `assertTrue(true)` unit test. |
| `phpunit.xml` | Configures Unit and Feature suites with in-memory SQLite for tests. |
