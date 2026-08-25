# Aplicação GlicoData

Aplicação Laravel 12 do GlicoData. A referência operacional está no [README principal](../README.md) e no [guia de execução](../documentation/portuguese/EXECUTION.md).

## Comandos

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan glicodata:admin-create ADMIN_001
composer run dev
```

- `composer run dev`: Laravel em 8000 e Vite em 5173 para hot reload.
- `composer run homolog`: build estático e aplicação inteira acessível em 8000.
- `php artisan test`: suíte isolada em SQLite na memória.
- `npm run build`: gera `public/build`.

A API usa Sanctum. UBS autenticam por CNES; administradores por `admin_code`; profissionais não possuem credenciais nem login. O banco PostgreSQL esperado é `glicodata_db`.
