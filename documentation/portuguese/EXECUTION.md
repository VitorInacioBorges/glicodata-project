# Guia de Execucao

## Setup Local

### 1. Clonar o Repositorio

```bash
git clone <url-do-repositorio> ubs-system
cd ubs-system/glicodata
```

### 2. Instalar Dependencias

No Ubuntu, instale os runtimes, o PostgreSQL e as extensoes PHP exigidas pelo
Composer, pela aplicacao e pelos testes:

```bash
sudo apt update
sudo apt install php-cli php-curl php-intl php-mbstring php-pgsql \
  php-sqlite3 php-xml php-zip composer \
  nodejs npm postgresql postgresql-client
```

Confira o ambiente antes de continuar:

```bash
php -v
composer --version
node --version
php -m | grep -E 'dom|mbstring|pdo_pgsql|pdo_sqlite|xml'
pg_isready
```

Instale exatamente as versoes registradas nos lockfiles:

```bash
composer install
npm ci
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
sudo systemctl enable --now postgresql
createdb -h 127.0.0.1 -U postgres ubs_system
```

Se a sua instalacao PostgreSQL usa autenticacao local por `peer`, crie o banco
com `sudo -u postgres createdb ubs_system` e defina uma senha para o usuario que
sera informado no `.env`.

#### Autenticacao local e Sanctum

Configure hashes Argon2id, sessoes e tokens de 24 horas:

```env
HASH_DRIVER=argon2id
HASH_VERIFY=false
SANCTUM_EXPIRATION=1440
SANCTUM_TOKEN_PREFIX=glicodata_
SESSION_DRIVER=database
SESSION_LIFETIME=1440
SESSION_ENCRYPT=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=true
```

Use `SESSION_SECURE_COOKIE=false` somente no desenvolvimento local sem HTTPS. `HASH_VERIFY=false` permite validar hashes bcrypt preexistentes e regrava-los como Argon2id no proximo login bem-sucedido.

### 4. Executar Migrations

```bash
php artisan migrate
```

As migrations atuais destinam-se a uma instalacao PostgreSQL limpa. Elas criam schema, tabelas de fila/auditoria e carregam somente os 5 distritos institucionais. Nenhuma UBS ou credencial e criada automaticamente. A migration incremental de limpeza remove de forma irreversivel as 42 UBS do catalogo antigo e todos os dados vinculados; faca backup validado antes de aplica-la em PostgreSQL existente.

SQLite segue configurado em `phpunit.xml` apenas para testes automatizados em memoria.

### 5. Executar Seeders

```bash
php artisan db:seed
```

O seeder padrao nao cria UBS, usuarios nem credenciais. Dados de teste sao criados exclusivamente por factories na suite automatizada. Para redefinir a senha de uma UBS existente pelo CNES:

```bash
php artisan glicodata:ubs-password 1234567
```

Crie a primeira conta administrativa sem senha padrao:

```bash
php artisan glicodata:admin-create
```

### 6. Iniciar em Modo de Desenvolvimento

Nao existe bypass local de autenticacao. Use os comandos interativos acima para provisionar credenciais de desenvolvimento.

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

| Script  | Comando                                                                         | Descricao                             |
| ------- | ------------------------------------------------------------------------------- | ------------------------------------- |
| `setup` | `composer install`, copia `.env`, gera chave, roda migrate, instala npm e build | Setup automatizado inicial.           |
| `dev`   | `concurrently` com Laravel server, queue, pail e Vite                           | Ambiente de desenvolvimento completo. |
| `test`  | `php artisan config:clear --ansi` e `php artisan test`                          | Executa testes Laravel.               |

O script `composer setup` pressupoe que o PostgreSQL e o `.env` ja estejam
configurados, pois ele executa migrations. Para uma primeira instalacao, prefira
seguir os passos deste guia na ordem.

### JavaScript (`glicodata/package.json`)

| Script  | Comando      | Descricao                    |
| ------- | ------------ | ---------------------------- |
| `dev`   | `vite`       | Inicia dev server de assets. |
| `build` | `vite build` | Gera build de producao.      |

### Artisan

| Comando                  | Descricao                     |
| ------------------------ | ----------------------------- |
| `php artisan route:list` | Lista rotas registradas.      |
| `php artisan migrate`    | Executa migrations pendentes. |
| `php artisan db:seed`    | Executa seeders.              |
| `php artisan test`       | Executa testes.               |
| `php artisan tinker`     | Abre REPL Laravel.            |

---

## Endpoints Principais

Todos os endpoints abaixo usam prefixo `/api`.

| Metodo      | Rota                                                        | Controller                                                   |
| ----------- | ----------------------------------------------------------- | ------------------------------------------------------------ |
| `POST`      | `/auth/login`                                               | Login UBS/admin e emissao de token Sanctum.                  |
| `GET`       | `/auth/me`                                                  | Retorna a identidade do token.                               |
| `POST`      | `/auth/logout`                                              | Revoga o token atual.                                        |
| `PUT`       | `/auth/password`                                            | Troca senha e revoga todos os tokens.                        |
| `GET`       | `/districts`                                                | `DistrictController@index`                                   |
| `GET`       | `/districts/{id}`                                           | `DistrictController@show`                                    |
| `POST`      | `/ubs`                                                       | Administrador cria uma UBS pendente por CNES.                 |
| `GET`       | `/ubs` e `/ubs/{id}`                                        | UBS consulta a si; administrador consulta todas.             |
| `PUT/PATCH` | `/ubs/{id}`                                                 | Admin gerencia; UBS altera somente o proprio perfil.         |
| CRUD        | `/users`, `/patients`, `/assessments`, `/risks`, `/reports` | Dados no escopo da UBS; `DELETE` e logico e auditado.        |
| `GET`       | `/audit-events` e `/audit-events/{id}`                      | Consulta propria da UBS ou global para administrador.        |
| `POST`      | `/audit-events/{id}/redact`                                 | Redacao administrativa auditada de snapshots sensiveis.      |

Todas as rotas acima, exceto `POST /api/auth/login`, exigem:

```http
Authorization: Bearer <token_sanctum>
```

O login recebe `account_type` (`ubs` ou `admin`), `identifier`, `password` e `device_name`. `identifier` e o CNES de sete digitos para UBS e o email para administrador. Cada token expira em 24 horas e cada conta mantem no maximo 20 tokens ativos.

Rotas web ficam fora do prefixo `/api`:

| Metodo | Rota                  | Descricao                                             |
| ------ | --------------------- | ----------------------------------------------------- |
| `GET`  | `/`                   | Redireciona para `/login/ubs`.                        |
| `GET`  | `/login/ubs`          | Renderiza o login da UBS.                             |
| `GET`  | `/login/admin`        | Renderiza o login administrativo.                     |
| `POST` | `/login`              | Cria sessao no guard indicado por `account_type`.     |
| `GET/POST` | `/cadastro/ubs`     | Solicita cadastro publico por CNES; conta fica pendente. |
| `GET`  | `/ubs/lobby`          | Renderiza lobby operacional.                          |
| `GET/PUT` | `/ubs/conta/perfil` | Autoedicao dos dados institucionais da propria UBS.   |
| `GET`  | `/ubs/pacientes*`     | Renderiza listagem e detalhe visual de pacientes.     |
| `GET`  | `/ubs/profissionais*` | Renderiza listagem e detalhe visual de profissionais. |
| `GET`  | `/ubs/avaliacoes*`    | Renderiza listagem e detalhe visual de avaliacoes.    |
| `POST` | `/ubs/logout`         | Encerra sessao web da UBS.                            |
| `GET`  | `/admin`              | Painel administrativo global.                         |
| `GET/PUT` | `/admin/ubs*`      | Lista, revisa, edita e ativa UBS.                      |
| `POST` | `/admin/logout`       | Encerra sessao administrativa.                        |

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

### Executar Testes

```bash
composer test
```

Os testes usam SQLite em memoria e exigem a extensao `pdo_sqlite`.

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
Laravel Framework 12.67.0
```

---

## Estrategia de Deploy (Producao)

O repositorio nao possui configuracao de deploy versionada. Um fluxo minimo para VPS com Nginx/Apache e PHP-FPM seria:

```bash
cd /var/www/ubs-system/glicodata
git pull
composer install --no-dev --optimize-autoloader
npm ci
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
- Criar o primeiro administrador e definir senhas das UBS pelos comandos interativos.
- Usar HTTPS, `SESSION_SECURE_COOKIE=true`, Argon2id e executar o scheduler para `sanctum:prune-expired`.
- Restringir acesso e backups de `audit_events`, pois os snapshots `jsonb` podem conter dados pessoais e clinicos.
- Regularizar e ativar UBS provisórias apenas depois da confirmacao de email, telefone e endereco.
- Concluir testes de aceitacao/homologacao e avaliacao de seguranca/infraestrutura antes de producao, conforme PDS-UEPG.
- Garantir permissao de escrita em `storage/` e `bootstrap/cache/`.
- Nao versionar `.env`, logs, caches, `vendor/` ou `node_modules/`.
