# Padroes de Organizacao e Nomeacao

## Naming Conventions

### Backend (PHP / Laravel)

| Elemento | Convencao atual | Exemplo |
| --- | --- | --- |
| **Namespaces** | PSR-4 sob `App\`, com subpastas por entidade nas camadas principais | `App\Services\UserServices\UserService` |
| **Classes** | `PascalCase` | `UserService`, `PatientRepository` |
| **Controllers** | Recurso singular + `Controller` | `RiskController` |
| **Services** | Recurso singular + `Service` | `AssessmentService` |
| **Repositories** | Recurso singular + `Repository` | `DistrictRepository` |
| **Models** | Recurso singular + `Model` | `UbsModel`, `ReportModel` |
| **Enums** | `PascalCase` | `UserRole`, `RiskClassification` |
| **Valores de Enum** | Valores persistidos em `lowercase` | `admin`, `user`, `low` |
| **Metodos** | `camelCase` | `getUserById()`, `createRisk()` |
| **Variaveis** | `camelCase` | `$perPage`, `$assessment` |
| **Tabelas** | `snake_case` plural | `users`, `assessments` |
| **Colunas** | Predominantemente `snake_case` | `ubs_id`, `assessment_id` |

### Views e Assets

| Elemento | Convencao atual | Exemplo |
| --- | --- | --- |
| **Views Blade** | `kebab` ou nome simples em minusculas | `home.blade.php`, `register.blade.php` |
| **Layouts Blade** | Subdiretorio `layouts/` | `layouts/main.blade.php` |
| **CSS de tela** | Nome descritivo com pontos | `register.styles.css` |
| **JS publico** | Nome simples em minusculas | `scripts.js` |
| **Entradas Vite** | `resources/css/app.css`, `resources/js/app.js` | Configuradas em `vite.config.js` |

---

## Padrao de Sufixos por Tipo de Arquivo

| Sufixo / Padrao | Tipo | Camada |
| --- | --- | --- |
| `*Controller.php` | Controller HTTP | Entrada |
| `*Request.php` | Form Request Laravel | Validacao da entrada HTTP |
| `*Policy.php` | Policy Laravel | Autorizacao |
| `*Service.php` | Service de aplicacao | Regras e orquestracao |
| `*Repository.php` | Repository Eloquent | Persistencia |
| `*Model.php` | Model Eloquent | Dados e relacionamentos |
| `*.blade.php` | Template Blade | Interface server-side |
| `*.css` | Estilos | Assets |
| `*.js` | JavaScript | Assets |
| `*Test.php` | Teste PHPUnit | Testes |

---

## Design Patterns Utilizados

### Service Layer

Os services encapsulam regras que nao pertencem diretamente ao transporte HTTP. Exemplos:

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

Repositories encapsulam consultas Eloquent e criacao de registros. O padrao atual usa classes concretas, sem interfaces:

```text
UserServices/UserService -> UserRepositories/UserRepository -> UserModel
```

### Policy / Gate

Controllers usam `Gate::authorize()` antes de responder ou alterar recursos. As policies ficam em subpastas por entidade, como `app/Policies/UserPolicies/UserPolicy.php`, e recebem a UBS autenticada como usuario do guard `keycloak`.

### Trait de Validacao Compartilhada

`ValidateUtils` centraliza validacao de identificadores e emails usados em consultas dos services. A validacao de payload HTTP pertence aos Form Requests e os controllers repassam apenas `$request->validated()`.

### Form Requests

`Http/Requests` possui requests por recurso para `store`, `update`, paginacao e redacao de auditoria. Campos controlados pelo servidor, como `ubs_id`, `keycloak_id` e senha, nao sao aceitos do corpo da API.

### Active Record / Eloquent Model

Os models concentram fillable, casts e relacionamentos. Essa e a abordagem nativa do Laravel e e usada em todos os recursos principais.

### Resource Routing

`Route::apiResource()` gera rotas REST previsiveis. Recursos operacionais (`users`, `patients`, `assessments`, `risks` e `reports`) possuem CRUD; `districts` e catalogo somente leitura e `ubs` permite consulta e atualizacao administrativa. Eventos de auditoria possuem rotas especificas de leitura e redacao.

### Provider Pattern

`RouteServiceProvider` e `AppServiceProvider` customizam bootstrapping do framework: carregamento de rotas com prefixo `/api` e carregamento de migrations em subdiretorios.

---

## Organizacao por Recurso

Cada recurso principal possui arquivos paralelos nas camadas, separados por pasta de entidade:

```text
app/Http/Controllers/UserControllers/UserController.php
app/Services/UserServices/UserService.php
app/Repositories/UserRepositories/UserRepository.php
app/Policies/UserPolicies/UserPolicy.php
app/Models/UserModel.php
```

O mesmo padrao existe para:

| Recurso | Controller | Service | Repository | Policy | Model |
| --- | --- | --- | --- | --- | --- |
| Distrito | `DistrictControllers/DistrictController` | `DistrictServices/DistrictService` | `DistrictRepositories/DistrictRepository` | `DistrictPolicies/DistrictPolicy` | `DistrictModel` |
| UBS | `UbsControllers/UbsController` | `UbsServices/UbsService` | `UbsRepositories/UbsRepository` | `UbsPolicies/UbsPolicy` | `UbsModel` |
| Usuario | `UserControllers/UserController` | `UserServices/UserService` | `UserRepositories/UserRepository` | `UserPolicies/UserPolicy` | `UserModel` |
| Paciente | `PatientControllers/PatientController` | `PatientServices/PatientService` | `PatientRepositories/PatientRepository` | `PatientPolicies/PatientPolicy` | `PatientModel` |
| Avaliacao | `AssessmentControllers/AssessmentController` | `AssessmentServices/AssessmentService` | `AssessmentRepositories/AssessmentRepository` | `AssessmentPolicies/AssessmentPolicy` | `AssessmentModel` |
| Risco | `RiskControllers/RiskController` | `RiskServices/RiskService` | `RiskRepositories/RiskRepository` | `RiskPolicies/RiskPolicy` | `RiskModel` |
| Relatorio | `ReportControllers/ReportController` | `ReportServices/ReportService` | `ReportRepositories/ReportRepository` | `ReportPolicies/ReportPolicy` | `ReportModel` |
| Auditoria | `AuditEventControllers/AuditEventController` | `AuditEventServices/AuditEventService` | `AuditEventRepositories/AuditEventRepository` | `AuditEventPolicies/AuditEventPolicy` | `AuditEventModel` |

---

## Convencoes Operacionais

| Area | Convencao |
| --- | --- |
| **Paginacao** | `PaginationRequest` valida `per_page`; o limite permitido e de 1 a 20. |
| **Delecao** | `User`, `Patient`, `Assessment`, `Risk` e `Report` usam `SoftDeletes`; distritos e UBS nao expoem delecao pela API. |
| **Rotas** | `routes/api.php` recebe prefixo `/api`; `routes/web.php` permanece sem prefixo API. |
| **Respostas** | Controllers retornam JSON para API; `store` usa status 201 e delete usa 204. |
| **Validacao HTTP** | Form Requests normalizam e validam entrada; controllers repassam `$request->validated()`. |
| **Autenticacao** | API usa guard `keycloak`; login/callback de UBS sao as unicas rotas abertas. |
| **Autorizacao** | Controllers usam `Gate::authorize()`; a role Keycloak `audit-admin` administra UBS e auditoria global. |
| **Auditoria** | Escritas operacionais e vinculacao Keycloak registram snapshots `jsonb`; redacao e auditada. |

---

## Inconsistencias Conhecidas

- O layout referencia `/css/register.styles.css` em `register.blade.php`, mas esse arquivo esta em `resources/css/register.styles.css`; em `public/css` existe apenas `styles.css` no checkout versionado.
- Os testes existentes ainda precisam ser atualizados em uma etapa propria para os contratos `birth`, `SoftDeletes`, Keycloak e Form Requests.
