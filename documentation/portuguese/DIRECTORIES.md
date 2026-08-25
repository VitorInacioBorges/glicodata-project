# Mapeamento de Diretorios

## Estrutura Completa

```bash
ubs-system/
├── glicodata/
│   ├── app/
│   │   ├── Enums/
│   │   ├── Console/Commands/
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
| `AuthControllers/ApiAuthController.php` | `/api/auth/*` |
| `AuthControllers/WebAuthController.php` | `/login`, logouts e troca de senha web |
| `UserControllers/UserController.php` | `/api/users` |
| `PatientControllers/PatientController.php` | `/api/patients` |
| `AssessmentControllers/AssessmentController.php` | `/api/assessments` |
| `QuestionnaireControllers/QuestionnaireController.php` | `/api/questionnaires/current`, `/api/questionnaire-versions/{id}` |
| `RiskControllers/RiskController.php` | `/api/risks` (somente leitura) |
| `ReportControllers/ReportController.php` | `/api/reports` |
| `AuditEventControllers/AuditEventController.php` | `/api/audit-events` |

`users`, `patients`, `assessments` e `reports` expoem CRUD com delete logico. `risks` e questionarios expõem leitura; risco e criado somente pela conclusao no servidor. `districts` expõe leitura; `ubs` expõe leitura e update administrativo; auditoria expõe leitura e redacao registrada.

### `glicodata/app/Http/Requests/`

Form Requests por recurso validam payloads de store/update e `PaginationRequest` limita `per_page` entre 1 e 20. `ApiFormRequest` fornece normalizacao comum; email e persistido em lowercase e somente dados validados seguem para os services.

### `glicodata/app/Services/`

Camada de aplicacao. Os services ficam separados por pasta de entidade e concentram verificacoes de consulta, invariantes por UBS, transacoes de mutacao, exclusao logica e auditoria.

| Caminho | Responsabilidade |
| --- | --- |
| `DistrictServices/DistrictService.php` | Consultas do catalogo institucional e paginacao limitada. |
| `UbsServices/UbsService.php` | Consulta e atualizacao auditada de UBS; bloqueia ativacao com dados provisórios. |
| `AuthServices/AuthenticationService.php` | Valida hashes locais, rehash, emissao/revogacao Sanctum e limite de tokens. |
| `UserServices/UserService.php` | CRUD com soft delete, email por busca e auditoria transacional. |
| `PatientServices/PatientService.php` | CRUD com soft delete e auditoria transacional. |
| `AssessmentServices/AssessmentService.php` | Rascunho/conclusao imutavel, versao do questionario, consistencia de tenant e auditoria transacional. |
| `QuestionnaireServices/*` | Versao publicada, validacao dinamica das respostas e schema. |
| `RiskServices/RiskCalculator.php` | Pontua respostas versionadas e classifica o risco exclusivamente no servidor. |
| `ReportServices/ReportService.php` | CRUD e exportacao agregada com supressao de grupos menores que cinco. |
| `AuditEventServices/AuditEventService.php` | Consulta por escopo, registro de snapshots e redacao auditada. |

### `glicodata/app/Repositories/`

Camada de acesso a dados. Repositories ficam separados por pasta de entidade, usam `newQuery()` sobre os models Eloquent e encapsulam as consultas reutilizadas pelos services.

| Caminho | Operacoes definidas |
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

Policies por entidade registradas em `AppServiceProvider`. Elas autorizam a UBS autenticada a acessar apenas dados vinculados a sua unidade e tratam separadamente o administrador global.

| Caminho | Responsabilidade |
| --- | --- |
| `DistrictPolicies/DistrictPolicy.php` | Permite listagem/consulta para UBS ativa e bloqueia escrita. |
| `UbsPolicies/UbsPolicy.php` | Permite leitura propria; administrador global le/atualiza cadastros; delete e bloqueado. |
| `UserPolicies/UserPolicy.php` | Restringe usuarios ao mesmo `ubs_id` da UBS autenticada. |
| `PatientPolicies/PatientPolicy.php` | Restringe pacientes ao mesmo `ubs_id` da UBS autenticada. |
| `AssessmentPolicies/AssessmentPolicy.php` | Restringe avaliacoes ao mesmo `ubs_id` da UBS autenticada. |
| `RiskPolicies/RiskPolicy.php` | Restringe riscos pela avaliacao vinculada a UBS autenticada. |
| `ReportPolicies/ReportPolicy.php` | Restringe relatorios pela avaliacao vinculada a UBS autenticada. |
| `AuditEventPolicies/AuditEventPolicy.php` | Restringe consulta ao escopo proprio e redacao/consulta global ao administrador. |

### `glicodata/app/Models/`

Models Eloquent com `fillable`, casts, tabela explicita e relacionamentos.

| Arquivo | Tabela | Relacionamentos principais |
| --- | --- | --- |
| `DistrictModel.php` | `districts` | `hasMany(UbsModel)` |
| `UbsModel.php` | `ubs` | `belongsTo(DistrictModel)`, `hasMany(UserModel)`, `hasMany(PatientModel)`, `hasMany(AssessmentModel)`; tambem atua como entidade autenticavel da UBS. |
| `AdministratorModel.php` | `administrators` | Identidade administrativa autenticavel e emissora de tokens Sanctum. |
| `UserModel.php` | `users` | Identidade individual autenticavel, papel, CRM/COREN, UF, especialidade, `belongsTo(UbsModel)` e `hasMany(AssessmentModel)` |
| `PatientModel.php` | `patients` | Paciente vinculado a UBS; `belongsTo(UbsModel)`, `hasMany(AssessmentModel)` |
| `QuestionnaireModel.php` / `QuestionnaireVersionModel.php` | `questionnaires`, `questionnaire_versions` | Schema e regras versionados; versao pertence ao questionario. |
| `AssessmentModel.php` | `assessments` | Paciente, usuario, UBS, versao, status, conclusao, risco e relatorio. |
| `RiskModel.php` | `risks` | `belongsTo(AssessmentModel)` |
| `ReportModel.php` | `reports` | `belongsTo(AssessmentModel)` |
| `AuditEventModel.php` | `audit_events` | Ator UBS ou administrador e UBS proprietaria. |

`UserModel`, `PatientModel`, `AssessmentModel`, `RiskModel` e `ReportModel` usam `SoftDeletes`. Usuarios e pacientes persistem `birth`, expõem `age` calculada e aceitam endereco/telefone nulos quando a informacao nao estiver disponivel.

### `glicodata/app/Enums/`

Enums nativos do PHP usados como casts nos models.

| Arquivo | Valores |
| --- | --- |
| `UserRole.php` | `admin`, `professional` |
| `AccountType.php` | `ubs`, `admin` |
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
| `AppServiceProvider.php` | Registra policy, regra de senha, rate limiter de login e migrations em subdiretorios. |
| `RouteServiceProvider.php` | Carrega `routes/web.php` com middleware `web` e `routes/api.php` com middleware `api` e prefixo `/api`. |

---

## Rotas

### `glicodata/routes/web.php`

Rotas de interface Blade, sem prefixo `/api`.

| Rota | Tipo | Responsabilidade |
| --- | --- | --- |
| `GET /` | Redirect | Redireciona para `/login/ubs`. |
| `GET /login/ubs` | Web view | Renderiza o login da UBS. |
| `GET /login/admin` | Web view | Renderiza o login administrativo. |
| `POST /login` | Web auth | Valida credenciais e cria sessao no guard UBS/admin. |
| `GET/POST /cadastro/ubs` | Cadastro publico | Cria UBS pendente com CNES e senha confirmada. |
| `GET /ubs/lobby` | Web view | Renderiza o lobby operacional do GlicoData. |
| `GET/PUT /ubs/conta/perfil` | Perfil UBS | Atualiza somente os dados da UBS autenticada. |
| `/ubs/pacientes*` | Web CRUD | CRUD real de pacientes escopado ao usuario/UBS. |
| `/ubs/profissionais*` | Web CRUD | CRUD real de identidades e papeis da equipe. |
| `/ubs/avaliacoes*` | Web CRUD | Rascunho, preenchimento e conclusao da anamnese versionada. |
| `/ubs/relatorios*` | Web CRUD/export | Relatorios reais e CSV agregado anonimizado. |
| `POST /ubs/logout` | Web auth | Encerra a sessao local da UBS. |
| `GET /admin` | Web view | Painel administrativo global. |
| `GET/PUT /admin/ubs*` | Gestao UBS | Revisa, edita, ativa e desativa UBS. |

### `glicodata/routes/api.php`

Rotas JSON carregadas com prefixo `/api`. Apenas `POST /api/auth/login` fica aberta; as demais rotas usam `auth:sanctum` e controle de tipo de conta.

| Rota | Tipo | Responsabilidade |
| --- | --- | --- |
| `POST /api/auth/login` | Auth | Valida UBS/admin e retorna token Sanctum de 24 horas. |
| `GET /api/auth/me` | Auth | Retorna a identidade autenticada pelo Bearer token. |
| `POST /api/auth/logout` | Auth | Revoga o token atual. |
| `PUT /api/auth/password` | Auth | Troca senha e revoga todos os tokens da conta. |
| `GET /api/districts*` | REST JSON | Consulta ao catalogo institucional de distritos. |
| `POST/GET/PUT/PATCH /api/ubs*` | REST JSON | Criacao pendente por admin, consulta e manutencao global/propria. |
| `apiResource` | REST JSON | CRUD com delete logico para `users`, `patients`, `assessments`, `risks`, `reports`. |
| `GET /api/audit-events*` | Auditoria | Consulta propria, ou global para administrador. |
| `POST /api/audit-events/{id}/redact` | Auditoria | Redacao de snapshots sensiveis com novo evento permanente. |

---

## Banco de Dados

### `glicodata/database/migrations/`

| Arquivo | Tabelas criadas |
| --- | --- |
| `district-migrations/2026_01_23_143000_create_districts_table.php` | `districts` |
| `ubs-migrations/2026_01_23_143100_create_ubs_table.php` | `ubs` |
| `ubs-migrations/2026_01_23_143150_seed_ponta_grossa_catalog.php` | Carga institucional somente dos 5 distritos de Ponta Grossa. |
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
| `2026_08_18_181400_add_cnes_and_optional_profile_to_ubs.php` | Adiciona CNES e torna o perfil inicial opcional. |
| `2026_08_18_181500_remove_automatically_seeded_ubs.php` | Remove irreversivelmente as 42 UBS automáticas e seus vínculos. |

As migrations usam UUID interno, CNES unico de sete digitos, timestamps com timezone, constraints PostgreSQL, soft delete operacional e auditoria. O perfil da UBS e opcional no cadastro inicial; toda conta nasce inativa ate aprovacao administrativa. A limpeza incremental exclui as UBS deterministicas do catalogo antigo e seus vinculos.

### `glicodata/database/seeders/`

| Arquivo | Responsabilidade |
| --- | --- |
| `DatabaseSeeder.php` | Nao cria dados automaticamente; testes usam factories isoladas. |

### `glicodata/database/factories/`

| Arquivo | Responsabilidade |
| --- | --- |
| `UserFactory.php` | Factory padrao de perfis com role `professional` ou `admin` para testes e seeders. |

---

## Interface Web e Assets

### `glicodata/resources/views/`

| Arquivo | Responsabilidade |
| --- | --- |
| `layouts/app.blade.php` | Layout base com Vite e navegacao protegida da UBS. |
| `ubs/auth/login.blade.php` | Tela publica de acesso institucional por CNES. |
| `ubs/auth/register.blade.php` | Cadastro publico CNES/senha com aprovacao posterior. |
| `ubs/profile/edit.blade.php` | Autoedicao do perfil da UBS autenticada. |
| `admin/ubs/*.blade.php` | Listagem, revisao e ativacao administrativa de UBS. |
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
| `tests/Feature/ApiValidationTest.php` | Fronteira de autenticacao e validacoes da API. |
| `tests/Feature/AuthenticationTest.php` | Sanctum, expiracao, limites, permissoes e isolamento por UBS. |
| `tests/Feature/WebAuthenticationTest.php` | Sessoes UBS/admin e troca de senha. |
| `tests/Unit/ExampleTest.php` | Teste unitario basico `assertTrue(true)`. |
| `phpunit.xml` | Configura suite Unit e Feature com SQLite em memoria no ambiente de teste. |
