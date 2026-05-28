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
| Autenticacao | Guard `keycloak` com UBS autenticada via Laravel Socialite e SocialiteProviders Keycloak |
| Views | Blade em `resources/views` |
| Assets | Vite, Bootstrap 5.3.8 via npm, Axios e CSS proprio em `resources/css/app.css` |
| Testes | PHPUnit via `php artisan test` |

## Autenticacao e Desenvolvimento Visual

A autenticacao principal continua sendo Keycloak/OpenID pela conta institucional da UBS. Para trabalho local de design e chamadas de API sem token, o projeto possui um bypass temporario controlado por ambiente:

```env
KEYCLOAK_WEB_REDIRECT_URI="${APP_URL}/auth/ubs/callback"
GLICODATA_AUTH_DISABLED=false
GLICODATA_AUTH_BYPASS_UBS_EMAIL=
```

Use `GLICODATA_AUTH_DISABLED=true` somente em ambiente local. Nesse modo, as rotas web `/ubs/*` ficam acessiveis sem sessao e o guard `keycloak` resolve uma UBS ativa local para que policies e escopo por UBS continuem funcionando nas APIs.

## Comandos Rapidos

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Executar testes:

```bash
php artisan test
```

Listar rotas:

```bash
php artisan route:list
```
