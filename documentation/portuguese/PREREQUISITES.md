# Pré-requisitos

- PHP 8.2+ e Composer 2
- Extensões PHP `curl`, `dom`, `intl`, `mbstring`, `pdo_pgsql`, `pdo_sqlite`, `xml` e `zip`
- Node.js 20+ e npm compatível com o lockfile
- PostgreSQL acessível em TCP ou socket
- Git e shell Bash para scripts de deploy

Verificação:

```bash
php -v
composer --version
node --version
php -m | grep -E 'dom|mbstring|pdo_pgsql|pdo_sqlite|xml'
pg_isready -h 127.0.0.1 -p 5432
```

O banco esperado é `glicodata_db`. O usuário PostgreSQL deve possuir somente as permissões necessárias sobre esse banco. Credenciais devem permanecer em `.env` não versionado ou secret manager.

Não existe bypass de autenticação: provisione administrador com `glicodata:admin-create`, cadastre uma UBS e aprove-a. Profissionais não são identidades HTTP.
