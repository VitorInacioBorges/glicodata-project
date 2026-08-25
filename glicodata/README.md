# UBS System / Glicodata Application

Esta pasta contem a aplicacao Laravel do projeto UBS System/Glicodata.

Use a documentacao principal na raiz do repositorio:

- [README raiz](../README.md)
- [Documentacao em portugues](../documentation/portuguese/ARCHITECTURE.md)
- [English documentation](../documentation/english/ARCHITECTURE.md)

## Resumo Tecnico

| Area | Implementacao |
| --- | --- |
| Framework | Laravel 12 |
| Runtime | PHP 8.2+ |
| Camadas | Controllers, Policies, Services, Repositories e Eloquent Models |
| Rotas | `routes/web.php` para Blade e `routes/api.php` carregado com prefixo `/api` |
| Autenticacao | Laravel Sanctum na API e guards de sessao separados para UBS e administradores |
| Views | Blade em `resources/views` |
| Assets | Vite, Bootstrap 5.3.8 via npm, Axios e CSS proprio em `resources/css/app.css` |
| Testes | PHPUnit via `php artisan test` |

## Autenticacao

A API usa tokens Bearer do Laravel Sanctum com validade de 24 horas. UBS autenticam pelo CNES e administradores pelo email. As telas Blade usam sessoes seguras e credenciais armazenadas somente como hashes Argon2id. Nao existe bypass de autenticacao.

```bash
php artisan glicodata:admin-create
php artisan glicodata:ubs-password 1234567
```

O primeiro comando cria uma identidade administrativa global. O segundo define ou redefine pelo CNES a senha de uma UBS sem expor a senha na linha de comando.

O cadastro inicial de UBS e publico em `/cadastro/ubs`, recebe somente CNES, senha e confirmacao e cria uma conta pendente. Administradores revisam e ativam contas em `/admin/ubs`; depois, cada UBS mantem o proprio perfil.

## Comandos Rapidos

```bash
cd glicodata
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan glicodata:admin-create
composer run dev
```

Antes disso, confirme PHP 8.2+ com as extensoes `dom`, `xml`,
`mbstring`, `pdo_pgsql` e `pdo_sqlite`, Composer 2, Node.js 20+ e um
PostgreSQL acessivel. O `pdo_sqlite` e usado pelos testes automatizados.

O fluxo completo, incluindo pacotes para Ubuntu, criacao do banco e provisionamento
de credenciais, esta no [Guia de Execucao](../documentation/portuguese/EXECUTION.md).

Executar testes:

```bash
php artisan test
```

Listar rotas:

```bash
php artisan route:list
```
