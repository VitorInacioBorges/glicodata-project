# Mapeamento de Diretorios

## Estrutura Completa

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
│   │   ├── Rules/
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

Diretorios ignorados por `.gitignore`, como `glicodata/vendor/`, `glicodata/node_modules/`, `glicodata/.env`, caches, logs e arquivos gerados em `storage/`, nao fazem parte da documentacao operacional.

---

## Backend — Detalhamento por Diretorio

### `glicodata/app/Http/Controllers/`

Controllers HTTP da API. Eles recebem Form Requests tipados, aplicam autorizacao via `Gate`, delegam somente `$request->validated()` para services e retornam `JsonResponse`.

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
| `AuditEventControllers/AuditEventController.php` | `/api/audit-events` |

`users`, `patients`, `assessments`, `risks` e `reports` expoem CRUD com delete logico. `districts` expõe apenas leitura; `ubs` expõe leitura e update administrativo; auditoria expõe leitura e redacao registrada.

### `glicodata/app/Http/Requests/`

Form Requests por recurso validam payloads de store/update e `PaginationRequest` limita `per_page` entre 1 e 20. `ApiFormRequest` fornece normalizacao comum; email e persistido em lowercase e somente dados validados seguem para os services.

### `glicodata/app/Services/`

Camada de aplicacao. Os services ficam separados por pasta de entidade e concentram verificacoes de consulta, invariantes por UBS, transacoes de mutacao, exclusao logica e auditoria.

| Caminho | Responsabilidade |
| --- | --- |
| `DistrictServices/DistrictService.php` | Consultas do catalogo institucional e paginacao limitada. |
| `UbsServices/UbsService.php` | Consulta e atualizacao auditada de UBS; bloqueia ativacao com dados provisórios. |
| `UbsServices/KeycloakUbsAuthService.php` | Resolve UBS ativa, vincula `keycloak_id` no primeiro acesso e identifica `audit-admin`. |
| `UserServices/UserService.php` | CRUD com soft delete, email por busca e auditoria transacional. |
| `PatientServices/PatientService.php` | CRUD com soft delete e auditoria transacional. |
| `AssessmentServices/AssessmentService.php` | CRUD, consistencia UBS/paciente/usuario e delete logico transacional do risco/relatorio associado. |
| `RiskServices/RiskService.php` | CRUD com soft delete e auditoria transacional. |
| `ReportServices/ReportService.php` | CRUD com soft delete e auditoria transacional. |
| `AuditEventServices/AuditEventService.php` | Consulta por escopo, registro de snapshots e redacao auditada. |

### `glicodata/app/Repositories/`

Camada de acesso a dados. Repositories ficam separados por pasta de entidade, usam `newQuery()` sobre os models Eloquent e encapsulam as consultas reutilizadas pelos services.

| Caminho | Operacoes definidas |
| --- | --- |
| `DistrictRepositories/DistrictRepository.php` | `paginateDistricts`, `findDistrictById` |
| `UbsRepositories/UbsRepository.php` | `paginateUbs`, `paginateAuthenticatedUbs`, `findUbsById`, `findUbsByEmail`, `findUbsByKeycloakId` |
| `UserRepositories/UserRepository.php` | `paginateUsers`, `paginateUsersForUbs`, `findUserById`, `findUserByEmail`, `createUser` |
| `PatientRepositories/PatientRepository.php` | `paginatePatients`, `paginatePatientsForUbs`, `findPatientById`, `createPatient` |
| `AssessmentRepositories/AssessmentRepository.php` | `paginateAssessments`, `paginateAssessmentsForUbs`, `findAssessmentById`, `createAssessment` |
| `RiskRepositories/RiskRepository.php` | `paginateRisks`, `paginateRisksForUbs`, `findRiskById`, `createRisk` |
| `ReportRepositories/ReportRepository.php` | `paginateReports`, `paginateReportsForUbs`, `findReportById`, `createReport` |
| `AuditEventRepositories/AuditEventRepository.php` | `paginateAuditEvents`, `paginateAuditEventsForUbs`, `findAuditEventById`, `createAuditEvent` |

### `glicodata/app/Policies/`

Policies por entidade registradas em `AppServiceProvider`. Elas autorizam a UBS autenticada pelo guard `keycloak` a acessar apenas dados vinculados a sua propria UBS, exceto distritos, que ficam somente para leitura.

| Caminho | Responsabilidade |
| --- | --- |
| `DistrictPolicies/DistrictPolicy.php` | Permite listagem/consulta para UBS ativa e bloqueia escrita. |
| `UbsPolicies/UbsPolicy.php` | Permite leitura propria; `audit-admin` le/atualiza cadastros; delete e bloqueado. |
| `UserPolicies/UserPolicy.php` | Restringe usuarios ao mesmo `ubs_id` da UBS autenticada. |
| `PatientPolicies/PatientPolicy.php` | Restringe pacientes ao mesmo `ubs_id` da UBS autenticada. |
| `AssessmentPolicies/AssessmentPolicy.php` | Restringe avaliacoes ao mesmo `ubs_id` da UBS autenticada. |
| `RiskPolicies/RiskPolicy.php` | Restringe riscos pela avaliacao vinculada a UBS autenticada. |
| `ReportPolicies/ReportPolicy.php` | Restringe relatorios pela avaliacao vinculada a UBS autenticada. |
| `AuditEventPolicies/AuditEventPolicy.php` | Restringe consulta ao escopo proprio e redacao/consulta global a `audit-admin`. |

### `glicodata/app/Models/`

Models Eloquent com `fillable`, casts, tabela explicita e relacionamentos.

| Arquivo | Tabela | Relacionamentos principais |
| --- | --- | --- |
| `DistrictModel.php` | `districts` | `hasMany(UbsModel)` |
| `UbsModel.php` | `ubs` | `belongsTo(DistrictModel)`, `hasMany(UserModel)`, `hasMany(PatientModel)`, `hasMany(AssessmentModel)`; tambem atua como entidade autenticavel da UBS. |
| `UserModel.php` | `users` | Perfil profissional (medico/enfermeiro) ou administrador vinculado a UBS; `belongsTo(UbsModel)`, `hasMany(AssessmentModel)` |
| `PatientModel.php` | `patients` | Paciente vinculado a UBS; `belongsTo(UbsModel)`, `hasMany(AssessmentModel)` |
| `AssessmentModel.php` | `assessments` | `belongsTo(PatientModel)`, `belongsTo(UserModel)`, `belongsTo(UbsModel)`, `hasOne(RiskModel)`, `hasOne(ReportModel)` |
| `RiskModel.php` | `risks` | `belongsTo(AssessmentModel)` |
| `ReportModel.php` | `reports` | `belongsTo(AssessmentModel)` |
| `AuditEventModel.php` | `audit_events` | `belongsTo(UbsModel)` para ator e UBS proprietaria |

`UserModel`, `PatientModel`, `AssessmentModel`, `RiskModel` e `ReportModel` usam `SoftDeletes`. Usuarios e pacientes persistem `birth`, expõem `age` calculada e aceitam endereco/telefone nulos quando a informacao nao estiver disponivel.

### `glicodata/app/Enums/`

Enums nativos do PHP usados como casts nos models.

| Arquivo | Valores |
| --- | --- |
| `UserRole.php` | `admin`, `professional` |
| `RiskClassification.php` | `low`, `moderate`, `high` |

### `glicodata/app/Utils/`

| Arquivo | Responsabilidade |
| --- | --- |
| `ValidateUtils.php` | Trait com validacoes de UUID e email usadas em buscas dos services. |

### `glicodata/app/Rules/`

| Arquivo | Responsabilidade |
| --- | --- |
| `CpfRules/ValidCpf.php` | Valida formato e digitos verificadores do CPF recebido por Form Requests. |

### `glicodata/app/Providers/`

| Arquivo | Responsabilidade |
| --- | --- |
| `AppServiceProvider.php` | Registra Socialite Keycloak, guard `keycloak`, bypass local opcional, policies e carregamento de migrations em subdiretorios. |
| `RouteServiceProvider.php` | Carrega `routes/web.php` com middleware `web` e `routes/api.php` com middleware `api` e prefixo `/api`. |

---

## Rotas

### `glicodata/routes/web.php`

Rotas de interface Blade, sem prefixo `/api`.

| Rota | Tipo | Responsabilidade |
| --- | --- | --- |
| `GET /` | Redirect | Redireciona para `/login`. |
| `GET /login` | Web view | Renderiza o login da UBS ou redireciona para o lobby quando autenticado. |
| `GET /auth/ubs/redirect` | Web auth | Inicia login institucional via Keycloak. |
| `GET /auth/ubs/callback` | Web auth | Recebe callback Keycloak, cria sessao `auth:ubs` e redireciona para o lobby. |
| `GET /ubs/lobby` | Web view | Renderiza o lobby operacional do GlicoData. |
| `GET /ubs/pacientes*` | Web view | Renderiza listagem e detalhe demonstrativo de pacientes. |
| `GET /ubs/profissionais*` | Web view | Renderiza listagem e detalhe demonstrativo de profissionais. |
| `GET /ubs/avaliacoes*` | Web view | Renderiza listagem e detalhe demonstrativo de avaliacoes. |
| `POST /ubs/logout` | Web auth | Encerra a sessao local e redireciona para logout Keycloak quando configurado. |

### `glicodata/routes/api.php`

Rotas JSON carregadas com prefixo `/api`. Apenas `GET /api/auth/ubs/login` e `GET /api/auth/ubs/callback` ficam abertas; as demais rotas usam middleware `auth:keycloak`. Em desenvolvimento local, `GLICODATA_AUTH_DISABLED=true` faz esse guard resolver uma UBS local sem token.

| Rota | Tipo | Responsabilidade |
| --- | --- | --- |
| `GET /api/auth/ubs/login` | Auth | Redireciona para o login Keycloak. |
| `GET /api/auth/ubs/callback` | Auth | Recebe retorno do Keycloak e retorna token/dados da UBS ativa. |
| `GET /api/auth/ubs/me` | Auth | Retorna a UBS autenticada pelo Bearer token. |
| `GET /api/auth/ubs/logout` | Auth | Retorna URL de logout do Keycloak. |
| `GET /api/districts*` | REST JSON | Consulta ao catalogo institucional de distritos. |
| `GET/PUT/PATCH /api/ubs*` | REST JSON | Consulta de UBS e manutencao por `audit-admin`. |
| `apiResource` | REST JSON | CRUD com delete logico para `users`, `patients`, `assessments`, `risks`, `reports`. |
| `GET /api/audit-events*` | Auditoria | Consulta propria, ou global para `audit-admin`. |
| `POST /api/audit-events/{id}/redact` | Auditoria | Redacao de snapshots sensiveis com novo evento permanente. |

---

## Banco de Dados

### `glicodata/database/migrations/`

| Arquivo | Tabelas criadas |
| --- | --- |
| `district-migrations/2026_01_23_143000_create_districts_table.php` | `districts` |
| `ubs-migrations/2026_01_23_143100_create_ubs_table.php` | `ubs` |
| `ubs-migrations/2026_01_23_143150_seed_ponta_grossa_catalog.php` | Carga inicial de 5 distritos e 42 UBS de Ponta Grossa. |
| `user-migrations/2026_01_23_143151_create_users_table.php` | `users`, com role `professional`/`admin` e contato opcional |
| `patient-migrations/2026_01_23_143200_create_patients_table.php` | `patients`, com endereco e telefone opcionais |
| `assessment-migrations/2026_01_23_143300_create_assessments_table.php` | `assessments` |
| `risk-migrations/2026_01_23_143400_create_risks_table.php` | `risks` |
| `report-migrations/2026_01_23_143500_create_reports_table.php` | `reports` |
| `audit-event-migrations/2026_01_23_143600_create_audit_events_table.php` | `audit_events` |
| `2026_01_23_150700_password_reset_tokens.php` | `password_reset_tokens` |
| `2026_01_23_150800_create_jobs_tables.php` | `jobs`, `job_batches`, `failed_jobs` |
| `2026_04_27_135537_create_sessions_table.php` | `sessions` |
| `2026_04_27_145038_create_cache_table.php` | `cache`, `cache_locks` |

As migrations foram consolidadas para instalacao limpa: usam UUID, timestamps com timezone, constraints PostgreSQL, soft delete operacional e auditoria. Em `users`, `professional` representa medicos e enfermeiros; `admin` tambem pode ser referenciado como executor de avaliacao. Em `users` e `patients`, endereco e telefone podem ser `NULL`. UBS com email `@seed.local` ou contato/endereco pendente sao inseridas inativas.

### `glicodata/database/seeders/`

| Arquivo | Responsabilidade |
| --- | --- |
| `DatabaseSeeder.php` | Cria distrito, UBS com `keycloak_id` e um perfil `professional` operacional de teste. |

### `glicodata/database/factories/`

| Arquivo | Responsabilidade |
| --- | --- |
| `UserFactory.php` | Factory padrao de perfis com role `professional` ou `admin` para testes e seeders. |

---

## Interface Web e Assets

### `glicodata/resources/views/`

| Arquivo | Responsabilidade |
| --- | --- |
| `layouts/app.blade.php` | Layout base com Vite, navegacao protegida da UBS e aviso visual quando o bypass local esta ativo. |
| `ubs/auth/login.blade.php` | Tela publica de acesso institucional da UBS. |
| `ubs/lobby.blade.php` | Lobby do GlicoData com atalhos para pacientes, profissionais e avaliacoes. |
| `ubs/patients/*.blade.php` | Listagem e detalhe visual de pacientes. |
| `ubs/professionals/*.blade.php` | Listagem e detalhe visual de profissionais. |
| `ubs/assessments/*.blade.php` | Listagem e detalhe visual de avaliacoes. |

### `glicodata/public/`

| Caminho | Responsabilidade |
| --- | --- |
| `public/index.php` | Front controller do Laravel. |
| `public/images/*.svg` | Marca GlicoData e ilustrações dos modulos exibidos no lobby. |
| `public/css/styles.css` | Estilo global simples para fonte e cor de `h1`. |
| `public/js/scripts.js` | Script publico atual com log de funcionamento. |

### `glicodata/resources/css` e `glicodata/resources/js`

Arquivos de entrada do Vite configurados em `vite.config.js`: `resources/css/app.css` e `resources/js/app.js`. O CSS principal importa Bootstrap e concentra os estilos das telas Blade atuais; o JavaScript importa o bundle Bootstrap.

---

## Testes

| Caminho | Responsabilidade |
| --- | --- |
| `tests/Feature/ExampleTest.php` | Testa se `GET /` retorna status 200. |
| `tests/Feature/ApiValidationTest.php` | Testes existentes de API; precisam ser atualizados posteriormente para Form Requests, `birth`, delete logico, auditoria e Keycloak. |
| `tests/Unit/ExampleTest.php` | Teste unitario basico `assertTrue(true)`. |
| `phpunit.xml` | Configura suite Unit e Feature com SQLite em memoria no ambiente de teste. |
