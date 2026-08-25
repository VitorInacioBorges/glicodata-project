# Guia de execução

## Preparação

Requisitos: PHP 8.2+, Composer 2, Node.js 20+, PostgreSQL e extensões `dom`, `xml`, `mbstring`, `pdo_pgsql` e `pdo_sqlite`.

```bash
cd glicodata
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Configure somente o banco original da aplicação:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=glicodata_db
DB_USERNAME=glicodata_user
DB_PASSWORD=<segredo-local>
```

Não registre a senha no Git. Confirme a conexão e as migrations sem imprimir o `.env`:

```bash
pg_isready -h 127.0.0.1 -p 5432
php artisan migrate:status
php artisan migrate --seed
```

O seeder padrão cria somente o catálogo de distritos e o questionário versionado. Ele não cria UBS, profissionais, pacientes nem contas administrativas.

## Credenciais institucionais

```bash
php artisan glicodata:admin-create ADMIN_001
php artisan glicodata:ubs-password 1234567
```

Os comandos pedem as senhas de modo interativo. O administrador entra com `admin_code`; a UBS entra com CNES. Não existe login de profissional.

## Desenvolvimento: por que existem duas portas

```bash
composer run dev
```

O script inicia processos diferentes:

- `127.0.0.1:8000`: Laravel, rotas Blade e API;
- `127.0.0.1:5173`: Vite, somente CSS/JavaScript com hot reload;
- worker de filas e visualização de logs, sem portas HTTP da aplicação.

A porta 5173 não representa outro frontend, outro Laravel ou outro banco. O Blade gerado em 8000 referencia temporariamente os assets do Vite em 5173. Em ambiente local, a CSP libera exatamente a origem configurada em `VITE_DEV_SERVER_ORIGIN` somente quando `public/hot` existe.

`php artisan serve` inicia apenas o servidor PHP. Sem `--port`, ele tenta 8000 e pode avançar para 8001 quando 8000 já está ocupada. Portanto, portas 8000 e 8001 abertas significam normalmente que dois processos Laravel foram iniciados. O script do projeto fixa `--port=8000`, fazendo a ocupação aparecer como erro em vez de abrir uma cópia silenciosa em 8001.

## Homologação: uma única porta

```bash
composer run homolog
```

Esse comando:

1. remove somente o marcador `public/hot`;
2. executa `npm run build`;
3. aplica `php artisan migrate --force` no banco configurado;
4. executa `php artisan optimize`;
5. abre Laravel em `http://127.0.0.1:8000`.

CSS e JavaScript são servidos de `public/build` pelo mesmo host e porta da aplicação. A CSP de homologação/produção permanece em `'self'` e não depende do Vite.

Se o HTML aparecer sem estilo, verifique:

```bash
test -f public/hot && sed -n '1p' public/hot
npm run build
php artisan optimize:clear
```

Um `public/hot` antigo faz o Laravel procurar o Vite mesmo quando ele não está rodando. Para homologação, prefira sempre `composer run homolog`.

## Contratos principais

- `/login/ubs`: sessão institucional por CNES.
- `/login/admin`: sessão global por ID administrativo.
- `/api/professionals`: primeiro nome, especialidade e status; acesso somente pela UBS.
- `/api/patients`: primeiro nome, sexo, bairro e nome do logradouro.
- `/api/assessments`: paciente, profissional da mesma UBS, respostas estruturadas e versão do questionário.
- `/api/risks`: somente leitura; o resultado é calculado pelo servidor ao concluir a avaliação.
- `/api/reports`: anamnese e descrição.

CPF, nascimento, telefone e endereço completo de pacientes; dados pessoais, credenciais e conselho de profissionais; e nome/e-mail de administradores são rejeitados pelos Form Requests e não aparecem nos Resources JSON.

## Testes e build

```bash
php artisan test
npm run build
vendor/bin/pint --test
composer audit
npm audit
```

Os testes usam SQLite em memória e não apagam o PostgreSQL.

## Reset de desenvolvimento

Antes de apagar dados, confirme que a conexão aponta exatamente para `glicodata_db`:

```bash
php artisan db:show
php artisan migrate:status
```

Somente em desenvolvimento e com o alvo confirmado:

```bash
php artisan migrate:fresh --seed --force
```

Esse comando remove todas as tabelas do banco conectado e as recria. Ele não deve ser apontado para produção nem para outro banco. Após o reset não há contas; crie o administrador de forma interativa e cadastre/aprove a UBS.
