# Boas Praticas

## Principios SOLID

### Single Responsibility (SRP)

Cada classe tende a ter uma responsabilidade principal:

- **Controllers**: recebem requisicoes e retornam JSON.
- **Policies**: autorizam acesso conforme a UBS autenticada.
- **Services**: validam IDs, emails, paginacao e coordenam create/update/delete.
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
| **Validacao de entrada** | Criar Form Requests por recurso para substituir `$request->all()` nos controllers. |
| **Formato de erro** | Padronizar respostas JSON de validacao, nao encontrado e conflito. |
| **Autenticacao** | Manter Keycloak como fonte principal e documentar configuracao de realm/client por ambiente. |
| **Autorizacao** | Evoluir policies para papeis mais granulares quando houver perfis alem da UBS autenticada. |
| **Transacoes** | Usar `DB::transaction()` quando uma operacao gravar multiplas tabelas. |

---

## Testes

### Tipos de Teste Configurados

| Tipo | Framework | Configuracao |
| --- | --- | --- |
| **Unitarios** | PHPUnit 11 | Suite `tests/Unit` em `phpunit.xml`. |
| **Feature** | Laravel TestCase + PHPUnit | Suite `tests/Feature` com SQLite em memoria. |

### Estrutura de Teste

```bash
application/tests/
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
- Validacoes basicas de email invalido em rotas de usuarios existem no checkout, mas precisam ser revisadas para o novo guard `keycloak`.
- Um teste unitario simples garante que `true` e verdadeiro.

Para cobrir a API real, priorize:

1. Feature tests dos CRUDs por recurso.
2. Testes de validacao para UUID invalido e email invalido.
3. Testes de paginacao para `per_page` abaixo de 1 e acima de 20.
4. Testes de serializacao de enums e casts.
5. Testes de erro para `ModelNotFoundException`.

---

## Seguranca

### Autenticacao

`UbsModel` estende `Authenticatable`, oculta `password` e possui cast `password => hashed`. A autenticacao principal da API usa Keycloak/OpenID via Laravel Socialite e guard `keycloak`. O login local por usuario/senha deixou de ser a fonte principal.

### Autorizacao

Policies por entidade sao registradas em `AppServiceProvider` e chamadas pelos controllers via `Gate::authorize()`. Elas restringem listagens e acoes ao escopo da UBS autenticada, com distritos em modo somente leitura.

### Validacao e Sanitizacao

| Aspecto | Implementacao atual |
| --- | --- |
| **UUID** | `ValidateUtils::validateId()` usa `Str::isUuid()`. |
| **Email** | `ValidateUtils::validateEmail()` usa validator Laravel com regra `email:rfc` e `max:255`. |
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

### Riscos Atuais

| Risco | Impacto |
| --- | --- |
| Ausencia de Form Requests | Campos invalidos podem chegar aos models ate serem rejeitados pelo banco ou casts. |
| Dependencia de Keycloak mal configurado | Tokens nao serao validados e rotas protegidas retornarao 401. |
| Hard delete padrao | Exclusoes removem registros sem lixeira logica. |

---

## Boas Praticas Recomendadas para Proximas Alteracoes

1. Introduzir Form Requests para `store` e `update` de cada recurso.
2. Adicionar API Resources para controlar o formato de resposta.
3. Definir papeis e regras adicionais quando houver mais perfis alem da UBS.
4. Cobrir services, policies e controllers com testes de feature.
5. Avaliar cache ou introspection de token se o endpoint `userinfo` se tornar gargalo.
