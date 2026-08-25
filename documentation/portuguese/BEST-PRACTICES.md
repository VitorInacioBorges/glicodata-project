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
| **Autenticacao** | Manter Sanctum, expiracao de 24 horas, limite de tokens e Argon2id cobertos por testes. |
| **Autorizacao** | Administradores globais ficam separados de UBS e nao recebem acesso clinico automatico. |
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
│   ├── AuthenticationTest.php
│   ├── CredentialCommandsTest.php
│   └── WebAuthenticationTest.php
├── Unit/
│   └── ExampleTest.php
└── TestCase.php
```

### Cobertura Atual

O checkout cobre login API/web, expiracao, rate limit, limite de 20 tokens, revogacao, separacao UBS/admin, BOLA basico, comandos de credencial e validacoes de API. Para ampliar a homologacao, priorize:

1. Feature tests dos CRUDs por recurso.
2. Testes de validacao para UUID invalido e email invalido.
3. Testes de paginacao para `per_page` abaixo de 1 e acima de 20.
4. Testes de serializacao de enums e casts.
5. Testes de erro para `ModelNotFoundException`.

---

## Seguranca

### Autenticacao

`UbsModel` e `AdministratorModel` estendem `Authenticatable`, ocultam `password`, usam cast `hashed` e `HasApiTokens`. Senhas sao comparadas por `Hash::check` e nunca descriptografadas. A API usa Sanctum Bearer; Blade usa guards de sessao separados. Nao existe bypass de autenticacao.

### Autorizacao

Policies por entidade sao registradas em `AppServiceProvider` e chamadas pelos controllers via `Gate::authorize()`. Dados operacionais permanecem no escopo da UBS autenticada; distritos sao somente leitura; cadastro de UBS e redacao/consulta global de auditorias exigem `AdministratorModel` ativo.

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
| **Token Bearer** | Sanctum armazena SHA-256, aplica abilities, expiracao de 24 horas e limite de 20 por conta. |
| **CSRF** | Formulario Blade usa `@csrf`. |

### Variaveis de Ambiente

`.env` nao e versionado. O template `.env.example` define:

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
HASH_DRIVER=argon2id
HASH_VERIFY=false
SANCTUM_EXPIRATION=1440
SANCTUM_TOKEN_PREFIX=glicodata_
SESSION_DRIVER=database
SESSION_ENCRYPT=true
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
| Reivindicacao publica de CNES sem revisao | Um atacante poderia reivindicar um CNES real; por isso a conta permanece inativa ate aprovacao administrativa. |
| Scheduler nao executado | Tokens expirados deixam de autenticar, mas linhas antigas se acumulam ate a limpeza. |
| Snapshots completos de auditoria em `jsonb` | CPF, endereco e informacao clinica duplicados exigem controle restrito do banco e backups. |
| Limpeza legada irreversivel | A remocao do catalogo apaga dados clinicos/auditoria vinculados e exige backup PostgreSQL validado. |

---

## Boas Praticas Recomendadas para Proximas Alteracoes

1. Adicionar API Resources para estabilizar o formato JSON, especialmente dados pessoais e auditoria.
2. Definir retencao/expurgo de `audit_events` e controles de backup com seguranca/infraestrutura do NTI.
3. Cobrir Form Requests, policies, soft delete, auditoria e carga inicial com testes de feature na etapa reservada.
4. Executar homologacao e aceite formal antes de publicar dados clinicos em producao, conforme PDS-UEPG.
5. Executar `sanctum:prune-expired --hours=24` diariamente pelo scheduler.
6. Ativar `HASH_VERIFY=true` depois que todos os hashes legados tiverem sido regravados como Argon2id.
