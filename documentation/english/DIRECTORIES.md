# Directory Mapping

## Full Structure

```bash
ubs-system/
├── glicodata/
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
│   │   ├── images/
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

Directories ignored by `.gitignore`, such as `glicodata/vendor/`, `glicodata/node_modules/`, `glicodata/.env`, caches, logs, and generated files under `storage/`, are not part of the operational documentation.

---

## Backend — Directory Details

### `glicodata/app/Http/Controllers/`

HTTP controllers for the API. They receive validated Form Requests, apply authorization through `Gate`, inject authenticated ownership when necessary, delegate to services, and return `JsonResponse`.

| Path | Base routes |
| --- | --- |
| `DistrictControllers/DistrictController.php` | `/api/districts` |
| `UbsControllers/UbsController.php` | `/api/ubs` |
| `AuthControllers/ApiAuthController.php` | `/api/auth/*` |
| `AuthControllers/WebAuthController.php` | `/login`, web logout, and password change |
| `UserControllers/UserController.php` | `/api/users` |
| `PatientControllers/PatientController.php` | `/api/patients` |
| `AssessmentControllers/AssessmentController.php` | `/api/assessments` |
| `RiskControllers/RiskController.php` | `/api/risks` |
| `ReportControllers/ReportController.php` | `/api/reports` |
| `AuditEventControllers/AuditEventController.php` | `/api/audit-events` |

Operational entity controllers expose CRUD through `Route::apiResource`. District is read-only, UBS only supports read and administrative update, and audit events use read and redaction routes.

### `glicodata/app/Http/Requests/`

Form Requests normalize and validate incoming data before the controller calls a service. Resource requests prevent clients from supplying ownership or identity-provider fields, `PaginationRequest` caps `per_page` at 20, and `RedactAuditEventRequest` validates the audit redaction reason.

### `glicodata/app/Services/`

Application layer. Services are separated by entity folder and centralize UUID/email lookup checks, tenant invariants, transactions, logical deletions, and audit recording.

| Path | Responsibility |
| --- | --- |
| `DistrictServices/DistrictService.php` | Read-only district lookup and pagination. |
| `UbsServices/UbsService.php` | UBS lookup and administrative update/activation rules. |
| `AuthServices/AuthenticationService.php` | Validates local hashes and manages Sanctum issue, revocation, expiration, and token limits. |
| `UserServices/UserService.php` | User CRUD, logical deletion, email lookup, and audited transactions. |
| `PatientServices/PatientService.php` | Patient CRUD, logical deletion, and audited transactions. |
| `AssessmentServices/AssessmentService.php` | Assessment CRUD with tenant consistency and transactional logical deletion of associated risk/report. |
| `RiskServices/RiskService.php` | Risk CRUD, logical deletion, and audit. |
| `ReportServices/ReportService.php` | Report CRUD, logical deletion, and audit. |
| `AuditEventServices/AuditEventService.php` | Scoped audit listing and audited payload redaction. |

### `glicodata/app/Repositories/`

Data access layer. Repositories are separated by entity folder, use `newQuery()` on Eloquent models, and encapsulate queries reused by services.

| Path | Defined operations |
| --- | --- |
| `DistrictRepositories/DistrictRepository.php` | `paginateDistricts`, `findDistrictById` |
| `UbsRepositories/UbsRepository.php` | `paginateUbs`, `paginateAuthenticatedUbs`, `findUbsById`, `findUbsByEmail` |
| `UserRepositories/UserRepository.php` | `paginateUsers`, `paginateUsersForUbs`, `findUserById`, `findUserByEmail`, `createUser` |
| `PatientRepositories/PatientRepository.php` | `paginatePatients`, `paginatePatientsForUbs`, `findPatientById`, `createPatient` |
| `AssessmentRepositories/AssessmentRepository.php` | `paginateAssessments`, `paginateAssessmentsForUbs`, `findAssessmentById`, `createAssessment` |
| `RiskRepositories/RiskRepository.php` | `paginateRisks`, `paginateRisksForUbs`, `findRiskById`, `createRisk` |
| `ReportRepositories/ReportRepository.php` | `paginateReports`, `paginateReportsForUbs`, `findReportById`, `createReport` |
| `AuditEventRepositories/AuditEventRepository.php` | `paginateAuditEvents`, `paginateAuditEventsForUbs`, `findAuditEventById`, `createAuditEvent` |

### `glicodata/app/Policies/`

Entity policies registered in `AppServiceProvider` authorize each UBS only in its own scope and handle global administrators separately.

| Path | Responsibility |
| --- | --- |
| `DistrictPolicies/DistrictPolicy.php` | Allows listing/lookup for active UBS accounts and blocks writes. |
| `UbsPolicies/UbsPolicy.php` | Allows self-read and reserves UBS maintenance to active global administrators. |
| `UserPolicies/UserPolicy.php` | Restricts users to the authenticated UBS `ubs_id`. |
| `PatientPolicies/PatientPolicy.php` | Restricts patients to the authenticated UBS `ubs_id`. |
| `AssessmentPolicies/AssessmentPolicy.php` | Restricts assessments to the authenticated UBS `ubs_id`. |
| `RiskPolicies/RiskPolicy.php` | Restricts risks through the assessment linked to the authenticated UBS. |
| `ReportPolicies/ReportPolicy.php` | Restricts reports through the assessment linked to the authenticated UBS. |
| `AuditEventPolicies/AuditEventPolicy.php` | Restricts UBS to its own events and allows global read/redaction to administrators. |

### `glicodata/app/Models/`

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

### `glicodata/app/Enums/`

Native PHP enums used as model casts.

| File | Values |
| --- | --- |
| `UserRole.php` | `admin`, `professional` |
| `RiskClassification.php` | `low`, `moderate`, `high` |

### `glicodata/app/Utils/`

| File | Responsibility |
| --- | --- |
| `ValidateUtils.php` | Trait with UUID and RFC email validation used by service lookup methods. |

### `glicodata/app/Rules/`

| File | Responsibility |
| --- | --- |
| `CpfRules/ValidCpf.php` | Validates formatted Brazilian CPF values and verifying digits for HTTP requests. |

### `glicodata/app/Providers/`

| File | Responsibility |
| --- | --- |
| `AppServiceProvider.php` | Registers policies, the password rule, login rate limiter, and migration loading. |
| `RouteServiceProvider.php` | Loads `routes/web.php` with `web` middleware and `routes/api.php` with `api` middleware and `/api` prefix. |

---

## Routes

### `glicodata/routes/web.php`

Blade interface routes without the `/api` prefix.

| Route | Type | Responsibility |
| --- | --- | --- |
| `GET /` | Redirect | Redirects to `/login/ubs`. |
| `GET /login/ubs` | Web view | Renders the UBS login page. |
| `GET /login/admin` | Web view | Renders the administrator login page. |
| `POST /login` | Web auth | Creates a session for the explicit account type. |
| `GET/POST /cadastro/ubs` | Public registration | Creates a pending UBS with CNES and confirmed password. |
| `GET /ubs/lobby` | Web view | Renders the GlicoData operational lobby. |
| `GET/PUT /ubs/conta/perfil` | UBS profile | Updates only the authenticated UBS institutional profile. |
| `GET /ubs/pacientes*` | Web view | Renders patient listing and demonstration detail screens. |
| `GET /ubs/profissionais*` | Web view | Renders professional listing and demonstration detail screens. |
| `GET /ubs/avaliacoes*` | Web view | Renders assessment listing and demonstration detail screens. |
| `POST /ubs/logout` | Web auth | Ends the UBS session. |
| `GET /admin` | Web view | Global administrator dashboard. |
| `GET/PUT /admin/ubs*` | UBS management | Reviews, edits, activates, and deactivates UBS accounts. |

### `glicodata/routes/api.php`

JSON routes use the `/api` prefix. Only `POST /api/auth/login` is open; every other API route uses `auth:sanctum` and account-type enforcement.

| Route | Type | Responsibility |
| --- | --- | --- |
| `POST /api/auth/login` | Auth | Validates UBS/admin credentials and returns a 24-hour Sanctum token. |
| `GET /api/auth/me` | Auth | Returns the Bearer-token identity. |
| `POST /api/auth/logout` | Auth | Revokes the current token. |
| `PUT /api/auth/password` | Auth | Changes the password and revokes every token. |
| `GET /api/districts*` | REST JSON | Read-only institutional district catalog. |
| `POST/GET/PUT/PATCH /api/ubs*` | REST JSON | Pending admin creation, scoped read, and global/self profile update. |
| `apiResource` | REST JSON | CRUD for `users`, `patients`, `assessments`, `risks`, and `reports`; deletes are logical. |
| `GET/POST /api/audit-events*` | REST JSON | Scoped audit reading and administrative payload redaction. |

---

## Database

### `glicodata/database/migrations/`

| File | Created tables |
| --- | --- |
| `district-migrations/2026_01_23_143000_create_districts_table.php` | `districts` |
| `ubs-migrations/2026_01_23_143100_create_ubs_table.php` | `ubs` |
| `ubs-migrations/2026_01_23_143150_seed_ponta_grossa_catalog.php` | Inserts only the five institutional Ponta Grossa districts. |
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
| `2026_08_18_181400_add_cnes_and_optional_profile_to_ubs.php` | Adds CNES and makes the initial UBS profile optional. |
| `2026_08_18_181500_remove_automatically_seeded_ubs.php` | Irreversibly removes the 42 automatic UBS records and dependencies. |

Entity migrations use internal UUIDs, unique seven-digit CNES, PostgreSQL integrity constraints, and soft-delete columns for operational records. A UBS profile is optional at public registration and remains inactive until approval. The incremental cleanup migration deletes the deterministic legacy catalog UBS records and all linked data; its rollback cannot restore deleted data.

### `glicodata/database/seeders/`

| File | Responsibility |
| --- | --- |
| `DatabaseSeeder.php` | Creates no automatic data; automated tests use isolated factories. |

### `glicodata/database/factories/`

| File | Responsibility |
| --- | --- |
| `UserFactory.php` | Default `professional`/`admin` user-profile factory for tests and seeders. |

---

## Web Interface and Assets

### `glicodata/resources/views/`

| File | Responsibility |
| --- | --- |
| `layouts/app.blade.php` | Base layout with Vite and UBS navigation. |
| `ubs/auth/login.blade.php` | Public UBS CNES/password login screen. |
| `ubs/auth/register.blade.php` | Public CNES/password registration pending approval. |
| `ubs/profile/edit.blade.php` | Authenticated UBS self-service profile editor. |
| `admin/auth/login.blade.php` | Public administrator password-login screen. |
| `admin/ubs/*.blade.php` | Administrator listing, review, and activation screens. |
| `ubs/lobby.blade.php` | GlicoData lobby with shortcuts to patients, professionals, and assessments. |
| `ubs/patients/*.blade.php` | Patient listing and visual detail screens. |
| `ubs/professionals/*.blade.php` | Professional listing and visual detail screens. |
| `ubs/assessments/*.blade.php` | Assessment listing and visual detail screens. |

### `glicodata/public/`

| Path | Responsibility |
| --- | --- |
| `public/index.php` | Laravel front controller. |
| `public/images/*.svg` | GlicoData mark and module illustrations displayed in the lobby. |
| `public/css/styles.css` | Simple global style for font and `h1` color. |
| `public/js/scripts.js` | Current public script with a functioning log. |

### `glicodata/resources/css` and `glicodata/resources/js`

Vite entry files configured in `vite.config.js`: `resources/css/app.css` and `resources/js/app.js`. The main CSS imports Bootstrap and contains the current Blade screen styles; the JavaScript entry imports the Bootstrap bundle.

---

## Tests

| Path | Responsibility |
| --- | --- |
| `tests/Feature/ExampleTest.php` | Tests the root redirect to UBS login. |
| `tests/Feature/ApiValidationTest.php` | Authentication boundary and API validation tests. |
| `tests/Feature/AuthenticationTest.php` | Sanctum, expiration, limits, permissions, and tenant isolation. |
| `tests/Feature/WebAuthenticationTest.php` | UBS/admin session authentication and password change. |
| `tests/Unit/ExampleTest.php` | Basic `assertTrue(true)` unit test. |
| `phpunit.xml` | Configures Unit and Feature suites with in-memory SQLite for tests. |
