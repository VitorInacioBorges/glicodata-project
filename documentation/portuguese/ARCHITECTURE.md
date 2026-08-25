# Arquitetura do Projeto

## Justificativa da Arquitetura

O projeto adota uma arquitetura Laravel em camadas, organizada em **Controllers**, **Services**, **Repositories** e **Eloquent Models**. Essa separacao reduz o acoplamento entre HTTP, regras de aplicacao e persistencia sem abandonar os recursos nativos do framework.

Essa escolha resolve tres necessidades centrais deste projeto:

1. **Organizacao por recurso**: distritos, UBS, usuarios, pacientes, avaliacoes, riscos e relatorios seguem o mesmo fluxo de controller, service, repository e model.
2. **Contrato HTTP explicito**: Form Requests validam entrada, normalizam emails/CPF e limitam paginacao antes dos controllers.
3. **Persistencia rastreavel**: services executam mutacoes e eventos de auditoria na mesma transacao.
4. **Identidade individual e tenant**: a API usa Sanctum; `UbsModel` delimita a unidade, `UserModel` identifica o ator clinico e `AdministratorModel` administra cadastros institucionais sem acesso clinico.

Na interface web, a arquitetura usa **Blade templates** com um layout base, telas da UBS e assets compilados pelo Vite. O Bootstrap e importado por npm em `resources/css/app.css` e `resources/js/app.js`, e os SVGs de navegacao ficam em `public/images`.

---

## Visualizacao da Arquitetura (Backend)

```text
┌─────────────────────────────────────────────────────────────┐
│                         HTTP / API                          │
│  routes/web.php e api.php -> RouteServiceProvider           │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                     Sanctum / Policies                      │
│  Bearer resolve UBS/user/admin e Gates autorizam por papel  │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                        Controllers                          │
│  Recebem validated(), aplicam Gate e coordenam JSON         │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                          Services                           │
│  Aplicam invariantes e transacoes com auditoria             │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                        Repositories                         │
│  Encapsulam consultas Eloquent e criacao de registros       │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                       Eloquent Models                       │
│  Tabelas, fillable, casts e relacionamentos                 │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                           Banco                             │
│  PostgreSQL como padrao; SQLite somente em testes           │
└─────────────────────────────────────────────────────────────┘
```

## Visualizacao da Arquitetura (Interface Web)

```text
┌─────────────────────────────────────────────────────────────┐
│                      resources/views                        │
│  ubs/auth, ubs/lobby, listagens e detalhes por entidade      │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                  resources/views/layouts/app.blade.php      │
│  Layout base, navegacao UBS e sessoes Laravel seguras       │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│               resources/css, resources/js e public/images   │
│  Bootstrap via Vite, CSS do produto e SVGs dos modulos      │
└─────────────────────────────────────────────────────────────┘
```

---

## Fluxo de Dados — Requisicao Tipica

### API: Criacao de Paciente

```text
1. Cliente envia POST /api/patients com body JSON e Authorization: Bearer <token>.
2. O guard Sanctum valida o hash e a expiracao do token e resolve o usuario individual e sua UBS ativa.
3. Laravel roteia para PatientControllers\PatientController@store.
4. Controller autoriza a operacao com PatientPolicy.
5. StorePatientRequest valida CPF formatado e nascimento, normalizando endereco/telefone vazio para `null`; o controller usa `validated()`.
6. O controller define `ubs_id` por `user.ubs_id`, sem aceitar escopo arbitrario do payload.
7. Service cria paciente e `audit_events` dentro da mesma transacao.
8. Eloquent persiste `birth`; a resposta serializada calcula `age`.
9. Controller retorna JSON com status 201.
```

### API: Consulta por ID

```text
1. Cliente envia GET /api/users/{id} com Authorization: Bearer <token>.
2. UserControllers\UserController@show chama UserService::getUserById($id).
3. ValidateUtils::validateId() exige UUID valido.
4. UserRepository::findUserById($id) busca o registro via Eloquent.
5. Se nao encontrar, o service lanca ModelNotFoundException.
6. Se encontrar, o controller autoriza acesso com UserPolicy.
7. Se autorizado, o model e serializado em JSON.
```

### API: Login por senha e emissao Sanctum

```text
1. Cliente envia POST /api/auth/login com account_type, identifier, password e device_name; identifier e CNES para UBS e email para usuario/administrador.
2. AuthenticationService busca a identidade somente na tabela indicada por account_type.
3. Hash::check compara a senha com o hash local e rejeita contas inativas.
4. Sanctum grava apenas SHA-256 do token, com ability ubs/user/admin e expires_at em 24 horas.
5. O texto do token e retornado uma unica vez; cada identidade mantem no maximo 20 tokens.
6. Policies e EnsureAccountType separam operacoes de UBS e administrador global.
```

### Web: Login e Navegacao UBS

```text
1. Cliente acessa GET /login/ubs, GET /login/profissional ou GET /login/admin.
2. As telas enviam POST /login com account_type explicito, identifier, senha e CSRF.
3. WebAuthController usa o mesmo AuthenticationService e cria sessao no guard ubs, user ou admin.
4. O ID da sessao e regenerado; auth.session invalida sessoes quando o hash da senha muda.
5. Perfil institucional usa auth:ubs, area clinica usa auth:user e painel global usa auth:admin.
6. Logout invalida a sessao e regenera o token CSRF.
```

---

## Inversao de Dependencia

O projeto usa injecao de dependencia do container do Laravel por construtor:

```php
class UserController extends Controller
{
    public function __construct(
        protected \App\Services\UserServices\UserService $service,
    ) {
    }
}
```

Cada service recebe seu repository correspondente, e cada repository recebe o model Eloquent correspondente:

```php
class UserService
{
    public function __construct(
        protected \App\Repositories\UserRepositories\UserRepository $repository,
    ) {
    }
}
```

Nao ha interfaces formais para repositories neste momento. A separacao atual ainda ajuda a trocar ou especializar consultas sem mover logica para controllers, mas a substituicao por mocks exige binding manual ou doubles nos testes.

---

## Modulos do Sistema

| Modulo       | Responsabilidade                                                                                            |
| ------------ | ----------------------------------------------------------------------------------------------------------- |
| `District`   | Consulta do catalogo institucional fixo de distritos.                                                        |
| `Ubs`        | Catalogo institucional e principal tenant; alteracao/ativacao somente por administrador global.             |
| `Administrator` | Identidade global separada, autorizada para UBS e auditoria institucional.                                 |
| `User`       | Identidade HTTP individual `professional` ou gestor `admin` da UBS, com conselho/UF/especialidade quando profissional. |
| `Patient`    | Pacientes vinculados a UBS, com contato opcional, `birth`, idade derivada e exclusao logica.                |
| `Questionnaire` | Questionario global com versoes imutaveis de schema e regras de risco publicadas.                         |
| `Assessment` | Anamnese em rascunho/concluida, vinculada a versao e ao usuario individual autenticado.                     |
| `Risk`       | Resultado calculado no servidor ao concluir a anamnese; a API oferece somente leitura.                      |
| `Report`     | Relatorio associado a uma conclusao, com CRUD e exportacao somente agregada/anonimizada.                     |
| `AuditEvent` | Trilha de alteracoes com snapshots e redacao registrada sob autorizacao administrativa.                     |

---

## Relacionamentos de Dados

```text
District 1 ── N Ubs
Ubs      1 ── N User
Ubs      1 ── N Patient
Ubs      1 ── N Assessment
User     1 ── N Assessment
Patient  1 ── N Assessment
Questionnaire 1 ── N QuestionnaireVersion
QuestionnaireVersion 1 ── N Assessment
Assessment 1 ── 1 Risk
Assessment 1 ── 1 Report
Ubs      1 ── N AuditEvent
Administrator 1 ── N AuditEvent
User     1 ── N AuditEvent
```

Os relacionamentos acima estao declarados nos models em `glicodata/app/Models`. As migrations consolidadas criam o schema PostgreSQL para instalacao limpa, carregam o catalogo inicial de Ponta Grossa e sao carregadas pelo `AppServiceProvider`.
