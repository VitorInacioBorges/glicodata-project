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
| Assets | Vite, Tailwind CSS, Axios e Bootstrap CDN |
| Testes | PHPUnit via `php artisan test` |

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
