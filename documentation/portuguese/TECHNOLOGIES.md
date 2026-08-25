# Metodologias e Tecnologias

## Stack Principal

### Backend

| Tecnologia | Versao | Funcao |
| --- | --- | --- |
| **PHP** | `^8.2` no Composer; `8.3.6` observado localmente | Runtime da aplicacao Laravel. |
| **Laravel Framework** | `^12.0`; `12.67.0` instalado | Framework MVC, roteamento, container, Eloquent, migrations e testes. |
| **Eloquent ORM** | Incluso no Laravel | Models, relacionamentos, casts, fillable e consultas. |
| **Laravel Sanctum** | `^4.0`; `4.3.3` instalado | Tokens Bearer de UBS e administradores para a API. |
| **PostgreSQL** | Default em `.env.example` e `config/database.php` | Banco padrao do projeto conforme PDS-UEPG. |
| **SQLite** | Configurado em `phpunit.xml` | Banco em memoria para testes automatizados locais. |
| **Laravel Tinker** | `^2.10.1` | REPL para inspecao e operacoes locais. |

### Interface Web e Assets

| Tecnologia | Versao | Funcao |
| --- | --- | --- |
| **Blade** | Incluso no Laravel | Templates server-side em `resources/views`. |
| **Vite** | `^7.0.7`; `7.3.2` instalado | Build de assets e dev server. |
| **laravel-vite-plugin** | `^2.0.0`; `2.1.0` instalado | Integracao entre Laravel e Vite. |
| **Axios** | `^1.11.0`; `1.15.2` instalado | Cliente HTTP exposto em `resources/js/bootstrap.js`. |
| **Bootstrap** | `^5.3.8`; `5.3.8` instalado | Componentes e estilos importados por Vite em `resources/css/app.css` e `resources/js/app.js`. |

### Ferramentas de Desenvolvimento

| Ferramenta | Versao | Funcao |
| --- | --- | --- |
| **Composer** | `2.7.1` observado localmente | Gerenciamento de dependencias PHP. |
| **Node.js** | `24.14.0` observado localmente | Runtime para Vite e ferramentas JS. |
| **npm** | `11.9.0` observado localmente | Gerenciamento de dependencias JS. |
| **PHPUnit** | `^11.5.3`; `11.5.55` instalado | Testes unitarios e feature tests. |
| **Laravel Pint** | `^1.24`; `1.25.1` instalado | Formatacao de codigo PHP. |
| **Laravel Sail** | `^1.41`; `1.47.0` instalado | Ambiente Docker opcional para Laravel. |
| **Laravel Pail** | `^1.2.2`; `1.2.3` instalado | Inspecao de logs no script de desenvolvimento. |
| **concurrently** | `^9.0.1`; `9.2.1` instalado | Executa servidor, queue, logs e Vite em paralelo no script `composer dev`. |

---

## Metodologia de Desenvolvimento

### Arquitetura Laravel em Camadas

O backend esta dividido em quatro camadas principais:

- **Controllers / Form Requests**: entrada HTTP, validacao de payload e serializacao JSON.
- **Policies**: autorizacao pelo principal Sanctum/sessao, com isolamento por UBS e privilegios administrativos separados.
- **Services**: regras de aplicacao, transacoes e auditoria de alteracoes.
- **Repositories**: consultas Eloquent e criacao de registros.
- **Models**: mapeamento de tabelas, casts, fillable e relacionamentos.

Essa estrutura nao e uma Clean Architecture estrita, porque os services dependem de repositories concretos e os repositories dependem diretamente de Eloquent. Ainda assim, ela melhora a separacao de responsabilidades frente a controllers com logica embutida.

### Conventional Commits

O historico Git mostra uso de Conventional Commits em portugues brasileiro:

```text
feat(services): valida ids e emails nas buscas
refactor(routes): aplica prefixo api pelo provider
feat(audit): registra eventos das operacoes persistidas
```

### CRUD REST por Recurso

Os recursos operacionais usam `Route::apiResource` com CRUD protegido por `auth:sanctum`. Distritos sao somente leitura; UBS e auditoria aceitam UBS no proprio escopo e administradores globais nas operacoes institucionais. Recursos clinicos aceitam somente tokens com identidade/ability de UBS.

---

## Gerenciamento de Estado e Dados

### Backend — Persistencia

| Aspecto | Implementacao |
| --- | --- |
| **ORM** | Eloquent Models em `glicodata/app/Models`. |
| **IDs nos models** | Models usam `HasUuids` e migrations das entidades usam colunas UUID. |
| **Paginacao** | `PaginationRequest` aceita `per_page` apenas no intervalo de 1 a 20. |
| **Casts** | `boolean`, `date`, `array`, `float`, enums nativos PHP e idade calculada a partir de `birth`. |
| **Exclusao logica** | Usuarios, pacientes, avaliacoes, riscos e relatorios usam `SoftDeletes`. |
| **Auditoria** | `audit_events` armazena snapshots `jsonb` e registro permanente de redacao. |
| **Migrations** | Consolidadas para instalacao PostgreSQL nova, incluindo catalogo institucional inicial e filas em banco. |
| **Banco de teste** | SQLite em memoria configurado em `phpunit.xml`. |

### Interface Web — Estado

As views atuais sao renderizadas no servidor com Blade para login UBS, lobby, listagens e telas de detalhe. Nao existe estado global front-end, roteamento SPA ou autenticacao client-side implementada no codigo versionado.

### Comunicacao Cliente ↔ Backend

| Aspecto | Implementacao |
| --- | --- |
| **Formato da API** | JSON para controllers REST. |
| **Autenticacao** | `Authorization: Bearer <token>` validado localmente por Laravel Sanctum; Blade usa guards de sessao. |
| **Credenciais** | Senhas em hash Argon2id, sem descriptografia; tokens expiram em 24 horas e sao armazenados como SHA-256. |
| **Paginacao** | Query string `?per_page=N`, com limite maximo efetivo de 20. |
| **Validacao de payload** | Form Requests entregam somente `$request->validated()` aos controllers. |
| **Validacao de ID** | UUID validado por `ValidateUtils::validateId()` nas buscas, updates e deletes por ID. |
| **Identidade UBS** | CNES numerico unico de sete digitos; email permanece dado opcional do perfil. |
| **Autorizacao** | Policies restringem dados por UBS; `AdministratorModel` gerencia UBS e auditoria global sem acesso clinico automatico. |
