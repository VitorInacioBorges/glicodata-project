# Mapeamento de Diretorios

## Estrutura Completa

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

Diretorios ignorados por `.gitignore`, como `application/vendor/`, `application/node_modules/`, `application/.env`, caches, logs e arquivos gerados em `storage/`, nao fazem parte da documentacao operacional.

---

## Backend — Detalhamento por Diretorio

### `application/app/Http/Controllers/`

Controllers HTTP da API. Eles recebem `Illuminate\Http\Request`, aplicam autorizacao via `Gate`, extraem `per_page` quando necessario, delegam para services e retornam `JsonResponse`.

| Caminho | Rotas base |
| --- | --- |
| `DistrictControllers/DistrictController.php` | `/api/districts` |
| `UbsControllers/UbsController.php` | `/api/ubs` |
| `UbsControllers/UbsAuthController.php` | `/api/auth/ubs/*` |
| `UserControllers/UserController.php` | `/api/users` |
| `PatientControllers/PatientController.php` | `/api/patients` |
| `AssessmentControllers/AssessmentController.php` | `/api/assessments` |
| `RiskControllers/RiskController.php` | `/api/risks` |
| `ReportControllers/ReportController.php` | `/api/reports` |

Cada controller expõe o CRUD padrao do `Route::apiResource` e uma rota adicional `DELETE /api/{resource}/{id}/delete-self`.

### `application/app/Services/`

Camada de aplicacao. Os services ficam separados por pasta de entidade e concentram validacao de UUID, validacao de email quando existe busca por email, normalizacao de paginacao e orquestracao de update/delete.

| Caminho | Responsabilidade |
| --- | --- |
| `DistrictServices/DistrictService.php` | CRUD de distritos e paginacao limitada entre 1 e 20 itens. |
| `UbsServices/UbsService.php` | CRUD de UBS, busca por email e busca por `keycloak_id`. |
| `UbsServices/KeycloakUbsAuthService.php` | Resolve UBS autenticada a partir do token Bearer ou do usuario Socialite retornado pelo Keycloak. |
| `UserServices/UserService.php` | CRUD de usuarios e busca por email. |
| `PatientServices/PatientService.php` | CRUD de pacientes. |
| `AssessmentServices/AssessmentService.php` | CRUD de avaliacoes. |
| `RiskServices/RiskService.php` | CRUD de riscos. |
| `ReportServices/ReportService.php` | CRUD de relatorios. |

### `application/app/Repositories/`

Camada de acesso a dados. Repositories ficam separados por pasta de entidade, usam `newQuery()` sobre os models Eloquent e encapsulam as consultas reutilizadas pelos services.

| Caminho | Operacoes definidas |
| --- | --- |
| `DistrictRepositories/DistrictRepository.php` | `paginateDistricts`, `findDistrictById`, `createDistrict` |
| `UbsRepositories/UbsRepository.php` | `paginateUbs`, `paginateAuthenticatedUbs`, `findUbsById`, `findUbsByEmail`, `findUbsByKeycloakId`, `createUbs` |
| `UserRepositories/UserRepository.php` | `paginateUsers`, `paginateUsersForUbs`, `findUserById`, `findUserByEmail`, `createUser` |
| `PatientRepositories/PatientRepository.php` | `paginatePatients`, `paginatePatientsForUbs`, `findPatientById`, `createPatient` |
| `AssessmentRepositories/AssessmentRepository.php` | `paginateAssessments`, `paginateAssessmentsForUbs`, `findAssessmentById`, `createAssessment` |
| `RiskRepositories/RiskRepository.php` | `paginateRisks`, `paginateRisksForUbs`, `findRiskById`, `createRisk` |
| `ReportRepositories/ReportRepository.php` | `paginateReports`, `paginateReportsForUbs`, `findReportById`, `createReport` |

### `application/app/Policies/`

Policies por entidade registradas em `AppServiceProvider`. Elas autorizam a UBS autenticada pelo guard `keycloak` a acessar apenas dados vinculados a sua propria UBS, exceto distritos, que ficam somente para leitura.

| Caminho | Responsabilidade |
| --- | --- |
| `DistrictPolicies/DistrictPolicy.php` | Permite listagem/consulta para UBS ativa e bloqueia escrita. |
| `UbsPolicies/UbsPolicy.php` | Permite leitura, update e delete apenas da propria UBS autenticada. |
| `UserPolicies/UserPolicy.php` | Restringe usuarios ao mesmo `ubs_id` da UBS autenticada. |
| `PatientPolicies/PatientPolicy.php` | Restringe pacientes ao mesmo `ubs_id` da UBS autenticada. |
| `AssessmentPolicies/AssessmentPolicy.php` | Restringe avaliacoes ao mesmo `ubs_id` da UBS autenticada. |
| `RiskPolicies/RiskPolicy.php` | Restringe riscos pela avaliacao vinculada a UBS autenticada. |
| `ReportPolicies/ReportPolicy.php` | Restringe relatorios pela avaliacao vinculada a UBS autenticada. |

### `application/app/Models/`

Models Eloquent com `fillable`, casts, tabela explicita e relacionamentos.

| Arquivo | Tabela | Relacionamentos principais |
| --- | --- | --- |
| `DistrictModel.php` | `districts` | `hasMany(UbsModel)` |
| `UbsModel.php` | `ubs` | `belongsTo(DistrictModel)`, `hasMany(UserModel)`, `hasMany(PatientModel)`, `hasMany(AssessmentModel)`; tambem atua como entidade autenticavel da UBS. |
| `UserModel.php` | `users` | `belongsTo(UbsModel)`, `hasMany(AssessmentModel)` |
| `PatientModel.php` | `patients` | `belongsTo(UbsModel)`, `hasMany(AssessmentModel)` |
| `AssessmentModel.php` | `assessments` | `belongsTo(PatientModel)`, `belongsTo(UserModel)`, `belongsTo(UbsModel)`, `hasOne(RiskModel)`, `hasOne(ReportModel)` |
| `RiskModel.php` | `risks` | `belongsTo(AssessmentModel)` |
| `ReportModel.php` | `reports` | `belongsTo(AssessmentModel)` |

### `application/app/Enums/`

Enums nativos do PHP usados como casts nos models.

| Arquivo | Valores |
| --- | --- |
| `UserRole.php` | `admin`, `user` |
| `RiskClassification.php` | `low`, `moderate`, `high` |

### `application/app/Utils/`

| Arquivo | Responsabilidade |
| --- | --- |
| `ValidateUtils.php` | Trait com validacoes de UUID, email RFC e payloads de create/update por entidade. |

### `application/app/Providers/`

| Arquivo | Responsabilidade |
| --- | --- |
| `AppServiceProvider.php` | Registra Socialite Keycloak, guard `keycloak`, policies e carregamento de migrations em subdiretorios. |
| `RouteServiceProvider.php` | Carrega `routes/web.php` com middleware `web` e `routes/api.php` com middleware `api` e prefixo `/api`. |

---

## Rotas

### `application/routes/web.php`

Rotas de interface Blade, sem prefixo `/api`.

| Rota | Tipo | Responsabilidade |
| --- | --- | --- |
| `GET /` | Web view | Renderiza `home.blade.php`. |
| `GET /contact` | Web view | Renderiza `contact.blade.php`. |
| `GET /register/{id?}` | Web view | Renderiza o formulario de registro. |
| `POST /login` | Web action | Redireciona para a rota `ubs.auth.login`, delegando login ao Keycloak. |

### `application/routes/api.php`

Rotas JSON carregadas com prefixo `/api`. Apenas `GET /api/auth/ubs/login` e `GET /api/auth/ubs/callback` ficam abertas; as demais rotas usam middleware `auth:keycloak`.

| Rota | Tipo | Responsabilidade |
| --- | --- | --- |
| `GET /api/auth/ubs/login` | Auth | Redireciona para o login Keycloak. |
| `GET /api/auth/ubs/callback` | Auth | Recebe retorno do Keycloak e retorna token/dados da UBS ativa. |
| `GET /api/auth/ubs/me` | Auth | Retorna a UBS autenticada pelo Bearer token. |
| `GET /api/auth/ubs/logout` | Auth | Retorna URL de logout do Keycloak. |
| `apiResource` | REST JSON | CRUD para `districts`, `ubs`, `users`, `patients`, `assessments`, `risks`, `reports`. |
| `DELETE /api/{resource}/{id}/delete-self` | REST JSON | Delecao alternativa para cada recurso. |

---

## Banco de Dados

### `application/database/migrations/`

| Arquivo | Tabelas criadas |
| --- | --- |
| `district-migrations/2026_01_23_143000_create_districts_table.php` | `districts` |
| `ubs-migrations/2026_01_23_143100_create_ubs_table.php` | `ubs` |
| `ubs-migrations/2026_05_21_000000_add_auth_fields_to_ubs_table.php` | Ajusta bancos existentes com `ubs.password`, `ubs.keycloak_id` e `users.password` nullable. |
| `user-migrations/2026_01_23_143151_create_users_table.php` | `users` |
| `patient-migrations/2026_01_23_143200_create_patients_table.php` | `patients` |
| `assessment-migrations/2026_01_23_143300_create_assessments_table.php` | `assessments` |
| `risk-migrations/2026_01_23_143400_create_risks_table.php` | `risks` |
| `report-migrations/2026_01_23_143500_create_reports_table.php` | `reports` |
| `2026_01_23_150700_password_reset_tokens.php` | `password_reset_tokens` |
| `2026_04_27_135537_create_sessions_table.php` | `sessions` |
| `2026_04_27_145038_create_cache_table.php` | `cache`, `cache_locks` |

As migrations das entidades usam UUID e ficam separadas por pasta de entidade.

### `application/database/seeders/`

| Arquivo | Responsabilidade |
| --- | --- |
| `DatabaseSeeder.php` | Cria distrito, UBS com `keycloak_id` e um usuario operacional de teste. |

### `application/database/factories/`

| Arquivo | Responsabilidade |
| --- | --- |
| `UserFactory.php` | Factory padrao de usuarios para testes e seeders. |

---

## Interface Web e Assets

### `application/resources/views/`

| Arquivo | Responsabilidade |
| --- | --- |
| `layouts/main.blade.php` | Layout HTML base com Bootstrap via CDN, Roboto via Google Fonts, `public/css/styles.css` e `public/js/scripts.js`. |
| `home.blade.php` | Tela inicial simples do "Sistema UBS". |
| `register.blade.php` | Formulario de cadastro exibindo o nome "Glicodata". |
| `contact.blade.php` | Pagina simples de contatos. |

### `application/public/`

| Caminho | Responsabilidade |
| --- | --- |
| `public/index.php` | Front controller do Laravel. |
| `public/css/styles.css` | Estilo global simples para fonte e cor de `h1`. |
| `public/js/scripts.js` | Script publico atual com log de funcionamento. |

### `application/resources/css` e `application/resources/js`

Arquivos de entrada do Vite configurados em `vite.config.js`: `resources/css/app.css` e `resources/js/app.js`. O arquivo `resources/css/register.styles.css` tambem existe e contem estilos do formulario de registro, mas a view atual referencia `/css/register.styles.css`, caminho que apontaria para `public/css/register.styles.css`.

---

## Testes

| Caminho | Responsabilidade |
| --- | --- |
| `tests/Feature/ExampleTest.php` | Testa se `GET /` retorna status 200. |
| `tests/Feature/ApiValidationTest.php` | Cobre validacoes basicas de API; precisa ser revisado para o guard `keycloak`. |
| `tests/Unit/ExampleTest.php` | Teste unitario basico `assertTrue(true)`. |
| `phpunit.xml` | Configura suite Unit e Feature com SQLite em memoria no ambiente de teste. |
