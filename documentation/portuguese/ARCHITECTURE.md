# Arquitetura do Projeto

## Justificativa da Arquitetura

O projeto adota uma arquitetura Laravel em camadas, organizada em **Controllers**, **Services**, **Repositories** e **Eloquent Models**. Essa separacao reduz o acoplamento entre HTTP, regras de aplicacao e persistencia sem abandonar os recursos nativos do framework.

Essa escolha resolve tres necessidades centrais deste projeto:

1. **Organizacao por recurso**: distritos, UBS, usuarios, pacientes, avaliacoes, riscos e relatorios seguem o mesmo fluxo de controller, service, repository e model.
2. **Contrato HTTP explicito**: Form Requests validam entrada, normalizam emails/CPF e limitam paginacao antes dos controllers.
3. **Persistencia rastreavel**: services executam mutacoes e eventos de auditoria na mesma transacao.
4. **Controle de acesso por UBS**: a API usa Keycloak/OpenID; a client role `audit-admin` administra cadastro institucional e auditoria global.

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
│                    Auth Keycloak / Policies                 │
│  Guard keycloak resolve UBS e Gates autorizam por entidade  │
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
│  Layout base, navegacao UBS e aviso de bypass local         │
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
2. O guard keycloak valida o token no Keycloak e resolve a UBS ativa.
3. Laravel roteia para PatientControllers\PatientController@store.
4. Controller autoriza a operacao com PatientPolicy.
5. StorePatientRequest valida CPF formatado e nascimento, normalizando endereco/telefone vazio para `null`; o controller usa `validated()`.
6. O controller define `ubs_id` pelo token, sem aceitar escopo arbitrario do payload.
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

### API: Login da UBS pelo Keycloak

```text
1. Cliente acessa GET /api/auth/ubs/login.
2. UbsAuthController redireciona para o provider Keycloak via Socialite.
3. Keycloak autentica a conta institucional da UBS.
4. Callback GET /api/auth/ubs/callback recebe o usuario autenticado.
5. KeycloakUbsAuthService localiza UBS ativa por `keycloak_id` ou email institucional.
6. Se ainda nao houver vinculo, grava `keycloak_id` e auditoria na primeira autenticacao.
7. O token validado pode conceder a client role `audit-admin`.
8. API retorna access_token, refresh_token, expires_in e dados da UBS.
```

### Web: Login e Navegacao UBS

```text
1. Cliente acessa GET /login.
2. A rota renderiza resources/views/ubs/auth/login.blade.php ou redireciona para /ubs/lobby se ja houver sessao.
3. O link institucional chama GET /auth/ubs/redirect.
4. UbsAuthController usa `KEYCLOAK_WEB_REDIRECT_URI` para autenticar no Keycloak e criar sessao `auth:ubs`.
5. As paginas /ubs/lobby, /ubs/pacientes, /ubs/profissionais e /ubs/avaliacoes usam o layout Blade compartilhado.
6. Em desenvolvimento local, `GLICODATA_AUTH_DISABLED=true` remove temporariamente o middleware `auth:ubs` dessas paginas.
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
| `Ubs`        | Catalogo institucional; alteracao/ativacao somente por `audit-admin`.                                        |
| `User`       | Perfis `professional` (medicos/enfermeiros) ou `admin` vinculados a UBS, com contato opcional e exclusao logica. |
| `Patient`    | Pacientes vinculados a UBS, com contato opcional, `birth`, idade derivada e exclusao logica.                |
| `Assessment` | Avaliacao da UBS vinculada por `user_id` ao executor da mesma unidade; aceita `professional` ou `admin`.    |
| `Risk`       | Registro de risco associado a avaliacao, com percentual, score e classificacao `low`, `moderate` ou `high`. |
| `Report`     | Relatorio associado a uma avaliacao, com titulo, descricao e comentario.                                    |
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
Assessment 1 ── 1 Risk
Assessment 1 ── 1 Report
Ubs      1 ── N AuditEvent
```

Os relacionamentos acima estao declarados nos models em `glicodata/app/Models`. As migrations consolidadas criam o schema PostgreSQL para instalacao limpa, carregam o catalogo inicial de Ponta Grossa e sao carregadas pelo `AppServiceProvider`.
