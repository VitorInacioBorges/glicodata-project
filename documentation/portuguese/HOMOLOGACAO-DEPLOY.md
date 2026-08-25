# Homologação de segurança e deploy

## Escopo automatizado

O checkout de homologação cobre autenticação institucional, individual e global; papéis; expiração e revogação de tokens; CSRF; isolamento entre UBS; CRUDs clínicos; questionário versionado; conclusão imutável; cálculo de risco no servidor; auditoria; exportação agregada; headers defensivos e migrations PostgreSQL.

```bash
cd glicodata
vendor/bin/pint --test
php artisan test
DB_PORT=5432 vendor/bin/phpunit --configuration phpunit.postgresql.xml
npm run build
composer audit
npm audit
php artisan route:list --except-vendor
```

O banco configurado em `phpunit.postgresql.xml` deve ser exclusivo de testes: `RefreshDatabase` recria seu schema. Nunca aponte essa configuração para homologação compartilhada ou produção.

## Smoke test administrativo real

Crie o administrador por entrada interativa; a senha não é argumento nem conteúdo versionado:

```bash
php artisan glicodata:admin-create \
  --name="Administrador de Homologação" \
  --email="admin@example.test"
```

Depois execute, somente em banco descartável de homologação:

```bash
HOMOLOG_ADMIN_EMAIL=admin@example.test \
HOMOLOG_ADMIN_PASSWORD='senha-lida-de-um-secret-manager' \
vendor/bin/phpunit --configuration phpunit.postgresql.xml \
  --filter PostgresqlHomologationSmokeTest
```

Esse teste autentica o administrador, cria uma UBS pendente, completa o perfil, ativa a conta e confirma a auditoria. Ele não deve ser incluído na rotina de produção.

## Preparação de produção

1. Validar restauração de backup PostgreSQL antes das migrations.
2. Copiar `.env.production.example` para um secret store ou arquivo fora do release e preencher chaves e credenciais.
3. Ajustar domínio, certificado, caminho do release e socket PHP-FPM em `deploy/nginx-glicodata.conf`.
4. Instalar e habilitar `glicodata-worker.service` e `glicodata-scheduler.timer`.
5. Publicar o código e executar:

```bash
GLICODATA_BACKUP_CONFIRMED=yes deploy/deploy.sh /var/www/glicodata/current/glicodata
```

6. Confirmar HTTPS, `APP_DEBUG=false`, cookies `Secure`/`HttpOnly`, login dos três tipos, criação e ativação de UBS, isolamento entre duas UBS, worker, scheduler, logs e restauração do backup.
7. Manter `HASH_VERIFY=false` durante a transição de hashes legados e ativar `true` somente após todas as credenciais terem sido regravadas em Argon2id.

## Pendências de aceite humano

- Aprovação formal da regra clínica e das faixas de risco pelo responsável médico.
- Teste de intrusão e revisão de infraestrutura pelo NTI.
- DPIA/RIPD, política de retenção, base legal e processo de atendimento aos titulares conforme LGPD.
- Definição do domínio definitivo, certificados, backup criptografado e monitoração.
