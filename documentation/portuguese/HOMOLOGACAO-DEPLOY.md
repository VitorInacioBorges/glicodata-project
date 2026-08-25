# Homologação e deploy

## Homologação local em uma porta

Use o mesmo PostgreSQL `glicodata_db` e não mantenha uma segunda aplicação em 8001:

```bash
cd glicodata
composer run homolog
```

A aplicação fica em `http://127.0.0.1:8000`. Os assets compilados são entregues em `/build/...` nessa mesma origem. O comando remove `public/hot`, portanto não há dependência da porta 5173 nesse modo.

## Checklist automatizado

```bash
vendor/bin/pint --test
php artisan test
npm run build
composer audit
npm audit
php artisan route:list --except-vendor
php artisan migrate:status
```

O teste padrão usa SQLite em memória. Nunca aponte uma configuração com `RefreshDatabase` para um PostgreSQL compartilhado.

## Smoke PostgreSQL opcional

Crie um administrador interativamente:

```bash
php artisan glicodata:admin-create ADMIN_HOMOLOG
```

Em banco descartável e explicitamente autorizado:

```bash
HOMOLOG_ADMIN_CODE=ADMIN_HOMOLOG \
HOMOLOG_ADMIN_PASSWORD='senha-do-secret-manager' \
vendor/bin/phpunit --configuration phpunit.postgresql.xml \
  --filter PostgresqlHomologationSmokeTest
```

O smoke autentica por ID, cria uma UBS pendente, completa os dados institucionais, ativa a unidade e verifica a auditoria. Ele não deve rodar automaticamente em produção.

## Deploy

1. Valide um backup PostgreSQL restaurável.
2. Preencha `.env.production.example` fora do repositório.
3. Confirme `APP_DEBUG=false`, HTTPS, cookies seguros e `DB_DATABASE=glicodata_db`.
4. Execute:

```bash
GLICODATA_BACKUP_CONFIRMED=yes deploy/deploy.sh /var/www/glicodata/current/glicodata
```

O script instala dependências, remove `public/hot`, compila assets, aplica migrations, semeia o questionário, otimiza caches e reinicia a fila. Nginx/PHP-FPM expõem somente a origem pública da aplicação; o Vite não deve rodar no servidor.

## Aceite humano

- revisão clínica das regras FINDRISC;
- teste de intrusão e revisão de infraestrutura;
- política de retenção, RIPD/DPIA e processo LGPD;
- backups criptografados, monitoração e teste de restauração.
