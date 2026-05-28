# Guia de Execucao

## Setup Local

### 1. Clonar o Repositorio

```bash
git clone <url-do-repositorio> ubs-system
cd ubs-system/glicodata
```

### 2. Instalar Dependencias

```bash
composer install
npm install
```

### 3. Configurar Variaveis de Ambiente

Copie o template:

```bash
cp .env.example .env
php artisan key:generate
```

Configurar banco local no `.env`.

#### PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ubs_system
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Crie o banco antes de rodar migrations:

```bash
createdb ubs_system
```

#### Keycloak / OpenID

Configure o provider de autenticacao da UBS:

```env
KEYCLOAK_CLIENT_ID=seu_client_id
KEYCLOAK_CLIENT_SECRET=seu_client_secret
KEYCLOAK_REDIRECT_URI="${APP_URL}/api/auth/ubs/callback"
KEYCLOAK_WEB_REDIRECT_URI="${APP_URL}/auth/ubs/callback"
KEYCLOAK_BASE_URL=https://keycloak.example
KEYCLOAK_REALM=seu_realm
```

Configure no client Keycloak a role `audit-admin` em `resource_access.<KEYCLOAK_CLIENT_ID>.roles` para a conta institucional que podera corrigir/ativar UBS e administrar redacao de auditoria.

Para trabalhar temporariamente em design e chamadas API sem o Keycloak configurado localmente:

```env
GLICODATA_AUTH_DISABLED=true
GLICODATA_AUTH_BYPASS_UBS_EMAIL=
```

Esse modo libera as rotas web da UBS e faz o guard `keycloak` resolver uma UBS ativa local. Nunca use essa flag ativada em homologacao ou producao.

### 4. Executar Migrations

```bash
php artisan migrate
```

As migrations atuais destinam-se a uma instalacao PostgreSQL limpa. Elas criam schema, tabelas de fila/auditoria e carregam o catalogo inicial de Ponta Grossa com 5 distritos e 42 UBS. UBS com dados provisórios (`@seed.local`, `A validar` ou `Não informado`) entram inativas.

Observacao: SQLite segue configurado em `phpunit.xml` apenas para testes automatizados em memoria; os testes precisam ser atualizados na etapa reservada antes de serem executados contra o novo contrato.

### 5. Executar Seeders

```bash
php artisan db:seed
```

O seeder atual cria distrito, UBS de teste com `keycloak_id = ubs-teste-keycloak-id` e um usuario operacional com `test@example.com`.

### 6. Iniciar em Modo de Desenvolvimento

#### Laravel

```bash
php artisan serve
```

Servidor padrao:

```text
http://127.0.0.1:8000
```

As telas Blade ficam nas rotas web, enquanto os endpoints JSON ficam sob `/api`.

```text
http://127.0.0.1:8000
http://127.0.0.1:8000/api/users
```

#### Vite

```bash
npm run dev
```

Dev server padrao:

```text
http://127.0.0.1:5173
```

#### Script combinado do Composer

```bash
composer run dev
```

Esse script executa em paralelo:

- `php artisan serve`
- `php artisan queue:listen --tries=1`
- `php artisan pail --timeout=0`
- `npm run dev`

---

## Scripts Disponiveis

### PHP / Composer (`glicodata/composer.json`)

| Script | Comando | Descricao |
| --- | --- | --- |
| `setup` | `composer install`, copia `.env`, gera chave, roda migrate, instala npm e build | Setup automatizado inicial. |
| `dev` | `concurrently` com Laravel server, queue, pail e Vite | Ambiente de desenvolvimento completo. |
| `test` | `php artisan config:clear --ansi` e `php artisan test` | Executa testes Laravel. |

### JavaScript (`glicodata/package.json`)

| Script | Comando | Descricao |
| --- | --- | --- |
| `dev` | `vite` | Inicia dev server de assets. |
| `build` | `vite build` | Gera build de producao. |

### Artisan

| Comando | Descricao |
| --- | --- |
| `php artisan route:list` | Lista rotas registradas. |
| `php artisan migrate` | Executa migrations pendentes. |
| `php artisan db:seed` | Executa seeders. |
| `php artisan test` | Executa testes. |
| `php artisan tinker` | Abre REPL Laravel. |

---

## Endpoints Principais

Todos os endpoints abaixo usam prefixo `/api`.

| Metodo | Rota | Controller |
| --- | --- | --- |
| `GET` | `/auth/ubs/login` | `UbsAuthController@redirect` |
| `GET` | `/auth/ubs/callback` | `UbsAuthController@callback` |
| `GET` | `/auth/ubs/me` | `UbsAuthController@me` |
| `GET` | `/auth/ubs/logout` | `UbsAuthController@logout` |
| `GET` | `/districts` | `DistrictController@index` |
| `GET` | `/districts/{id}` | `DistrictController@show` |
| `GET` | `/ubs` e `/ubs/{id}` | Consulta da propria UBS; `audit-admin` lista/consulta todas. |
| `PUT/PATCH` | `/ubs/{id}` | Atualizacao/ativacao exclusiva de `audit-admin`. |
| CRUD | `/users`, `/patients`, `/assessments`, `/risks`, `/reports` | Dados no escopo da UBS; `DELETE` e logico e auditado. |
| `GET` | `/audit-events` e `/audit-events/{id}` | Consulta de auditoria propria ou global para `audit-admin`. |
| `POST` | `/audit-events/{id}/redact` | Redacao administrativa auditada de snapshots sensiveis. |

Todas as rotas acima, exceto `/api/auth/ubs/login` e `/api/auth/ubs/callback`, exigem:

```http
Authorization: Bearer <token_keycloak>
```

Com `GLICODATA_AUTH_DISABLED=true` em ambiente local, o middleware continua registrado, mas o guard `keycloak` aceita chamadas sem Bearer token para facilitar desenvolvimento.

Rotas web ficam fora do prefixo `/api`:

| Metodo | Rota | Descricao |
| --- | --- | --- |
| `GET` | `/` | Redireciona para `/login`. |
| `GET` | `/login` | Renderiza login da UBS ou redireciona para o lobby. |
| `GET` | `/auth/ubs/redirect` | Inicia login Keycloak web. |
| `GET` | `/auth/ubs/callback` | Recebe callback web e cria sessao `auth:ubs`. |
| `GET` | `/ubs/lobby` | Renderiza lobby operacional. |
| `GET` | `/ubs/pacientes*` | Renderiza listagem e detalhe visual de pacientes. |
| `GET` | `/ubs/profissionais*` | Renderiza listagem e detalhe visual de profissionais. |
| `GET` | `/ubs/avaliacoes*` | Renderiza listagem e detalhe visual de avaliacoes. |
| `POST` | `/ubs/logout` | Encerra sessao web da UBS. |

---

## Workflow de Banco

### Criar Nova Migration

```bash
php artisan make:migration create_districts_table
```

### Rodar Migrations

```bash
php artisan migrate
```

### Reverter Ultimo Lote

```bash
php artisan migrate:rollback
```

### Criar Banco Novo para Este Schema

```bash
php artisan migrate:fresh --seed
```

Use `migrate:fresh` apenas em ambiente local ou bancos descartaveis, pois ele apaga tabelas existentes. As migrations consolidadas e a carga institucional foram desenhadas para um PostgreSQL novo; banco de producao que ja possui migrations executadas exige plano de transicao separado.

---

## Testes e Validacao

### Status de Testes

Os testes existentes nao foram alterados nem executados nesta etapa. Eles precisam de uma fase posterior para cobrir Form Requests, `birth`, delete logico, auditoria e autorizacao Keycloak.

### Validar Rotas

```bash
php artisan route:list
```

Resultado observado apos Form Requests, auditoria e restricao do catalogo:

```text
Showing [37] routes
```

### Validar Versao do Framework

```bash
php artisan --version
```

Resultado observado:

```text
Laravel Framework 12.60.2
```

---

## Estrategia de Deploy (Producao)

O repositorio nao possui configuracao de deploy versionada. Um fluxo minimo para VPS com Nginx/Apache e PHP-FPM seria:

```bash
cd /var/www/ubs-system/glicodata
git pull
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Garanta que o servidor web aponte para:

```text
glicodata/public
```

### Checklist Pos-deploy

```bash
php artisan route:list
php artisan migrate:status
curl -i https://seu-dominio.example/api
```

### Cuidados de Producao

- Definir `APP_ENV=production`.
- Definir `APP_DEBUG=false`.
- Configurar `APP_KEY`.
- Usar banco persistente PostgreSQL.
- Publicar somente em banco novo ou preparar migracao especifica para ambiente que ja executou migrations antigas.
- Provisionar uma UBS ativa e a client role Keycloak `audit-admin` antes da manutencao do catalogo.
- Garantir `GLICODATA_AUTH_DISABLED=false` antes de cachear configuracoes.
- Restringir acesso e backups de `audit_events`, pois os snapshots `jsonb` podem conter dados pessoais e clinicos.
- Regularizar e ativar UBS provisórias apenas depois da confirmacao de email, telefone e endereco.
- Concluir testes de aceitacao/homologacao e avaliacao de seguranca/infraestrutura antes de producao, conforme PDS-UEPG.
- Garantir permissao de escrita em `storage/` e `bootstrap/cache/`.
- Nao versionar `.env`, logs, caches, `vendor/` ou `node_modules/`.
