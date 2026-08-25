# Prerequisites

- PHP 8.2+ and Composer 2
- PHP extensions `curl`, `dom`, `intl`, `mbstring`, `pdo_pgsql`, `pdo_sqlite`, `xml`, and `zip`
- Node.js 20+ and npm
- PostgreSQL
- Git and Bash for deployment scripts

The expected application database is `glicodata_db`. Grant the PostgreSQL role only the permissions it needs on that database and keep credentials in an untracked `.env` or secret manager.

There is no authentication bypass. Create an administrator interactively, register and approve a UBS, and use that UBS session for clinical work. Professionals are not HTTP principals.
