# Guia de Execucao

## Setup Local

### 1. Clonar o Repositorio

```bash
git clone <url-do-repositorio> ubs-system
cd ubs-system/application
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
KEYCLOAK_BASE_URL=https://keycloak.example
KEYCLOAK_REALM=seu_realm
```

### 4. Executar Migrations

```bash
php artisan migrate
```

Observacao: SQLite segue configurado em `phpunit.xml` apenas para testes automatizados em memoria.

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

### PHP / Composer (`application/composer.json`)

| Script | Comando | Descricao |
| --- | --- | --- |
| `setup` | `composer install`, copia `.env`, gera chave, roda migrate, instala npm e build | Setup automatizado inicial. |
| `dev` | `concurrently` com Laravel server, queue, pail e Vite | Ambiente de desenvolvimento completo. |
| `test` | `php artisan config:clear --ansi` e `php artisan test` | Executa testes Laravel. |

### JavaScript (`application/package.json`)

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
| `POST` | `/districts` | `DistrictController@store` |
| `GET` | `/districts/{id}` | `DistrictController@show` |
| `PUT/PATCH` | `/districts/{id}` | `DistrictController@update` |
| `DELETE` | `/districts/{id}` | `DistrictController@destroy` |
| `DELETE` | `/districts/{id}/delete-self` | `DistrictController@deleteSelf` |

O mesmo padrao se repete para:

- `/api/ubs`
- `/api/users`
- `/api/patients`
- `/api/assessments`
- `/api/risks`
- `/api/reports`

Todas as rotas acima, exceto `/api/auth/ubs/login` e `/api/auth/ubs/callback`, exigem:

```http
Authorization: Bearer <token_keycloak>
```

Rotas web ficam fora do prefixo `/api`:

| Metodo | Rota | Descricao |
| --- | --- | --- |
| `GET` | `/` | Renderiza home. |
| `GET` | `/contact` | Renderiza pagina de contato. |
| `GET` | `/register/{id?}` | Renderiza formulario de registro. |
| `POST` | `/login` | Redireciona para o login Keycloak da UBS. |

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

### Recriar Banco Local

```bash
php artisan migrate:fresh --seed
```

Use `migrate:fresh` apenas em ambiente local ou bancos descartaveis, pois ele apaga tabelas existentes.

---

## Testes e Validacao

### Executar Testes

```bash
php artisan test
```

ou:

```bash
composer test
```

Resultado historico observado durante documentacao anterior:

```text
Tests: 1 risky, 1 passed (2 assertions)
```

O teste risky e `Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response`; o PHPUnit informou que o codigo de teste ou codigo testado nao fechou seus proprios output buffers. O comando terminou com exit code 0.

### Validar Rotas

```bash
php artisan route:list
```

Resultado observado apos a reorganizacao de auth e pastas:

```text
Showing [53] routes
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
cd /var/www/ubs-system/application
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
application/public
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
- Garantir permissao de escrita em `storage/` e `bootstrap/cache/`.
- Nao versionar `.env`, logs, caches, `vendor/` ou `node_modules/`.
