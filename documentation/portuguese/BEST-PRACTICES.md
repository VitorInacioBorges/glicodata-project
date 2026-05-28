# Boas Praticas

## Principios SOLID

### Single Responsibility (SRP)

Cada classe tende a ter uma responsabilidade principal:

- **Form Requests**: validam e normalizam o contrato HTTP antes da orquestracao.
- **Controllers**: autorizam a acao, aplicam o escopo autenticado e retornam JSON.
- **Policies**: autorizam acesso conforme a UBS autenticada.
- **Services**: validam buscas e invariantes de negocio e coordenam mutacoes/auditoria transacional.
- **Repositories**: encapsulam consultas Eloquent.
- **Models**: declaram tabela, fillable, casts e relacionamentos.
- **Enums**: isolam valores permitidos para roles e classificacao de risco.

### Open/Closed (OCP)

Novos recursos podem ser adicionados replicando o padrao de controller, service, repository e model sem alterar recursos existentes. Para evoluir essa pratica, novos comportamentos compartilhados devem entrar em traits, policies, form requests ou classes dedicadas em vez de condicionais grandes nos controllers.

### Liskov Substitution (LSP)

Como os services dependem de repositories concretos, a substituicao por implementacoes alternativas ainda nao e automatica. Se a aplicacao passar a exigir mocks frequentes ou multiplos mecanismos de persistencia, introduza contratos e bindings no container.

### Interface Segregation (ISP)

Nao ha interfaces no desenho atual. A segregacao pratica ocorre por classes pequenas por recurso. Caso contratos sejam adicionados, mantenha uma interface por recurso e evite repositories genericos com metodos que nem todos os modelos usam.

### Dependency Inversion (DIP)

O container do Laravel injeta controllers, services, repositories e models. A dependencia ainda aponta para classes concretas, o que e pragmatico para o tamanho atual do projeto. Para regras mais complexas, use interfaces em `app/Contracts` ou `app/Repositories/Contracts`.

---

## Tratamento de Erros

### Backend

| Camada | Estrategia atual |
| --- | --- |
| **Controllers** | Retornam `JsonResponse` e delegam erros para o handler padrao do Laravel. |
| **Services** | Lancam `ValidationException` para UUID/email invalidos e `ModelNotFoundException` para registros inexistentes. |
| **Repositories** | Propagam erros de Eloquent e banco de dados. |
| **Models** | Usam casts para normalizar tipos ao serializar e persistir. |

### Pontos a Fortalecer

| Area | Recomendacao |
| --- | --- |
| **Validacao de entrada** | Form Requests substituem `$request->all()`; manter novas regras HTTP nessa camada. |
| **Formato de erro** | Padronizar respostas JSON de validacao, nao encontrado e conflito. |
| **Autenticacao** | Manter Keycloak como fonte principal e documentar configuracao de realm/client por ambiente. |
| **Autorizacao** | A client role Keycloak `audit-admin` governa cadastro de UBS e redacao/consulta global de auditoria. |
| **Transacoes** | Services gravam mutacoes e `audit_events` na mesma `DB::transaction()`. |

---

## Testes

### Tipos de Teste Configurados

| Tipo | Framework | Configuracao |
| --- | --- | --- |
| **Unitarios** | PHPUnit 11 | Suite `tests/Unit` em `phpunit.xml`. |
| **Feature** | Laravel TestCase + PHPUnit | Suite `tests/Feature` com SQLite em memoria. |

### Estrutura de Teste

```bash
glicodata/tests/
├── Feature/
│   ├── ApiValidationTest.php
│   └── ExampleTest.php
├── Unit/
│   └── ExampleTest.php
└── TestCase.php
```

### Cobertura Atual

O checkout atual possui testes de exemplo:

- `GET /` deve retornar status 200.
- Validacoes basicas de API existem no checkout, mas precisam ser revisadas para Form Requests, `birth`, delete logico, auditoria e o guard `keycloak`.
- Um teste unitario simples garante que `true` e verdadeiro.

Os testes existentes ainda nao foram atualizados nesta etapa por decisao de planejamento. Para a fase de testes e homologacao NTI, priorize:

1. Feature tests dos CRUDs por recurso.
2. Testes de validacao para UUID invalido e email invalido.
3. Testes de paginacao para `per_page` abaixo de 1 e acima de 20.
4. Testes de serializacao de enums e casts.
5. Testes de erro para `ModelNotFoundException`.

---

## Seguranca

### Autenticacao

`UbsModel` estende `Authenticatable`, oculta `password` e possui cast `password => hashed`. A API nao aceita senha em payload de UBS ou usuario: a autenticacao principal usa Keycloak/OpenID via Laravel Socialite e guard `keycloak`. No primeiro acesso de uma UBS ativa sem `keycloak_id`, o identificador e vinculado pelo email institucional e a alteracao e auditada.

Para desenvolvimento visual local existe `GLICODATA_AUTH_DISABLED`. Quando ativado, o guard `keycloak` resolve uma UBS ativa do banco e as rotas web da UBS ficam temporariamente sem `auth:ubs`. Essa flag deve permanecer `false` em homologacao e producao.

### Autorizacao

Policies por entidade sao registradas em `AppServiceProvider` e chamadas pelos controllers via `Gate::authorize()`. Dados operacionais permanecem no escopo da UBS autenticada; distritos sao somente leitura; cadastro de UBS e redacao/consulta global de auditorias exigem a client role Keycloak `audit-admin`.

### Validacao e Sanitizacao

| Aspecto | Implementacao atual |
| --- | --- |
| **UUID** | `ValidateUtils::validateId()` usa `Str::isUuid()`. |
| **Payload HTTP** | Form Requests entregam apenas `$request->validated()` aos services. |
| **Email** | Requests convertem email para lowercase; PostgreSQL aplica check e indice unico por `LOWER(email)`. |
| **CPF** | Regra `ValidCpf` exige formato `000.000.000-00` e digitos verificadores validos. |
| **Nascimento** | `User` e `Patient` persistem `birth`; `age` e calculada na serializacao. |
| **Contato opcional** | Endereco e telefone de `User` e `Patient` aceitam `NULL`; strings vazias sao normalizadas para `null` nos Form Requests. |
| **Papel profissional** | `UserRole::Professional` identifica medicos e enfermeiros; `admin` tambem pode ser o executor associado a uma avaliacao. |
| **Mass assignment** | Models usam `fillable`, reduzindo exposicao de campos nao permitidos. |
| **Senha** | `UserModel` e `UbsModel` ocultam `password` e aplicam cast `hashed`. |
| **Token Bearer** | `KeycloakUbsAuthService` consulta o endpoint `userinfo` do Keycloak para resolver a UBS ativa. |
| **CSRF** | Formulario Blade usa `@csrf`. |

### Variaveis de Ambiente

`.env` nao e versionado. O template `.env.example` define:

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
KEYCLOAK_BASE_URL=
KEYCLOAK_REALM=
KEYCLOAK_WEB_REDIRECT_URI="${APP_URL}/auth/ubs/callback"
GLICODATA_AUTH_DISABLED=false
GLICODATA_AUTH_BYPASS_UBS_EMAIL=
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Em producao:

- `APP_DEBUG` deve ser `false`.
- `APP_KEY` deve estar gerado e protegido.
- Credenciais de banco devem ficar somente em `.env`.
- Definir `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` nos ambientes da aplicacao.

---

## Persistencia e Integridade

### Pontos Fortes

- Relacionamentos Eloquent estao declarados nos models.
- Enums nativos reduzem valores invalidos para `role` e `classification`.
- Services fazem validacao de UUID antes de buscar por ID.
- Paginacao tem limite superior para reduzir respostas grandes.
- Constraints compostas impedem avaliacoes entre paciente, usuario e UBS de unidades diferentes.
- `SoftDeletes` preserva historico de usuarios, pacientes, avaliacoes, riscos e relatorios.
- Mutacoes geram snapshots em `audit_events`; redacao administrativa permanece registrada.

### Riscos Atuais

| Risco | Impacto |
| --- | --- |
| Dependencia de Keycloak mal configurado | Tokens nao serao validados e rotas protegidas retornarao 401. |
| Bypass local ativado fora de desenvolvimento | Rotas web e chamadas API poderiam operar sem token institucional. |
| Snapshots completos de auditoria em `jsonb` | CPF, endereco e informacao clinica duplicados exigem controle restrito do banco e backups. |
| Catalogo inicial provisório | UBS com `@seed.local`, telefone/endereco pendente entram inativas ate regularizacao. |
| Migrations consolidadas para banco novo | Bancos antigos nao devem receber este conjunto sem estrategia especifica de migracao. |

---

## Boas Praticas Recomendadas para Proximas Alteracoes

1. Adicionar API Resources para estabilizar o formato JSON, especialmente dados pessoais e auditoria.
2. Definir retencao/expurgo de `audit_events` e controles de backup com seguranca/infraestrutura do NTI.
3. Cobrir Form Requests, policies, soft delete, auditoria e carga inicial com testes de feature na etapa reservada.
4. Executar homologacao e aceite formal antes de publicar dados clinicos em producao, conforme PDS-UEPG.
5. Avaliar cache ou validacao JWT/JWKS se o endpoint `userinfo` se tornar gargalo.
6. Garantir `GLICODATA_AUTH_DISABLED=false` em qualquer ambiente compartilhado ou institucional.
