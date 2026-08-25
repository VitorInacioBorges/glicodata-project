# Pre-requisitos e Desempenho

## Dependencias de Sistema

### Runtime

| Dependencia | Versao minima | Verificacao |
| --- | --- | --- |
| **PHP** | `>=8.2` | `php -v` |
| **Composer** | `>=2.x` | `composer --version` |
| **Node.js** | Recomendado `>=20 LTS` para Vite moderno | `node --version` |
| **npm** | Compativel com a versao do Node | `npm --version` |

Versoes observadas durante a geracao desta documentacao:

| Ferramenta | Versao observada |
| --- | --- |
| PHP | `8.3.6` |
| Composer | `2.7.1` |
| Laravel | `12.67.0` |
| Node.js | `24.14.0` |
| npm | `11.9.0` |

### Banco de Dados

| Banco | Status no projeto | Verificacao |
| --- | --- | --- |
| **PostgreSQL** | Banco padrao em `.env.example` e em `config/database.php`, conforme PDS-UEPG. | `psql --version` |
| **SQLite** | Usado apenas em testes automatizados via `phpunit.xml`, quando configurado. | `php -m | grep sqlite` |
| **MySQL/MariaDB/SQL Server** | Conexoes padrao do Laravel mantidas em `config/database.php`. | Cliente correspondente |

Para novos sistemas no contexto NTI/UEPG, PostgreSQL e o banco padrao do projeto. SQLite permanece apenas como banco em memoria para testes automatizados locais.

### Extensoes PHP Relevantes

| Extensao | Motivo |
| --- | --- |
| `pdo` | Acesso a banco de dados via Laravel. |
| `pdo_pgsql` | Necessario para a conexao padrao PostgreSQL. |
| `pdo_sqlite` | Necessario apenas para SQLite em memoria nos testes automatizados. |
| `mbstring` | Dependencia comum do Laravel e Symfony. |
| `openssl` | Criptografia, chaves e operacoes seguras. |
| `fileinfo` | Validacao e manipulacao de arquivos. |
| `dom` / `xml` | Exigidas pelo Laravel, Pint e PHPUnit durante `composer install`. |
| `curl` | Comunicacao HTTP usada pelo Composer e integracoes. |
| `intl` | Internacionalizacao e utilitarios Symfony/Laravel. |
| `zip` | Instalacao eficiente dos pacotes Composer. |

### Autenticacao

Nao ha servico externo de identidade obrigatorio. Credenciais ficam no PostgreSQL como hashes Argon2id; Laravel Sanctum emite os tokens Bearer da API.

---

## Dependencias do Projeto

### PHP — Dependencias Diretas

| Pacote | Versao instalada | Categoria |
| --- | --- | --- |
| `laravel/framework` | `v12.67.0` | Framework principal |
| `laravel/sanctum` | `v4.3.3` | Tokens pessoais Bearer com abilities e expiracao. |
| `laravel/tinker` | `v2.10.1` | REPL |
| `fakerphp/faker` | `v1.24.1` | Dados falsos para testes/factories |
| `laravel/pail` | `v1.2.3` | Logs em desenvolvimento |
| `laravel/pint` | `v1.25.1` | Formatacao |
| `laravel/sail` | `v1.47.0` | Docker opcional |
| `mockery/mockery` | `1.6.12` | Test doubles |
| `nunomaduro/collision` | `v8.8.2` | Erros CLI |
| `phpunit/phpunit` | `11.5.55` | Testes |

### JavaScript — Dependencias Diretas

| Pacote | Versao instalada | Categoria |
| --- | --- | --- |
| `vite` | `7.3.2` | Build/dev server |
| `laravel-vite-plugin` | `2.1.0` | Integracao Laravel/Vite |
| `bootstrap` | `5.3.8` | Componentes e estilos da interface Blade compilados pelo Vite |
| `axios` | `1.15.2` | Cliente HTTP |
| `concurrently` | `9.2.1` | Execucao paralela de processos |

---

## Hardware Sugerido

### Desenvolvimento Local

| Recurso | Minimo | Recomendado |
| --- | --- | --- |
| **RAM** | 4 GB | 8 GB |
| **CPU** | 2 cores | 4 cores |
| **Disco** | 2 GB livres sem `vendor`/`node_modules`; 5 GB com dependencias | 10 GB |
| **SO** | Linux, macOS ou Windows com WSL2 | Ubuntu 22.04+ |

### Servidor de Producao

| Recurso | Minimo | Recomendado |
| --- | --- | --- |
| **RAM** | 1 GB para API pequena | 2 GB+ |
| **CPU** | 1 vCPU | 2 vCPU |
| **Disco** | 10 GB SSD | 20 GB SSD+ |
| **SO** | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |

### Portas Utilizadas

| Porta | Servico | Ambiente |
| --- | --- | --- |
| `8000` | `php artisan serve` | Desenvolvimento |
| `5173` | Vite dev server | Desenvolvimento |
| `5432` | PostgreSQL | Desenvolvimento/producao |
| `80` | HTTP via Nginx/Apache | Producao |
| `443` | HTTPS via Nginx/Apache | Producao |

---

## Requisitos de Ambiente

Antes de executar a aplicacao:

1. Instale dependencias PHP com Composer.
2. Instale dependencias JS com npm.
3. Copie `.env.example` para `.env`.
4. Gere `APP_KEY`.
5. Configure as credenciais PostgreSQL.
6. Configure Argon2id, sessao segura e expiracao Sanctum de 1.440 minutos.
7. Rode as migrations incrementais no PostgreSQL.
8. Crie o primeiro administrador com `php artisan glicodata:admin-create`.
9. Cadastre UBS em `/cadastro/ubs`, revise-as em `/admin/ubs` e use `php artisan glicodata:ubs-password 1234567` apenas para redefinicao operacional.

Nao existe bypass de autenticacao. Contas administrativas sao separadas das UBS e profissionais em `users` nao sao principais HTTP.

As migrations carregam somente os cinco distritos de Ponta Grossa. UBS nao sao criadas automaticamente: o cadastro publico por CNES nasce inativo e exige aprovacao administrativa. A migration de remocao do catalogo antigo e irreversivel e requer backup previo.

Para testes automatizados, `phpunit.xml` ja define SQLite em memoria:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Esse banco em memoria e adequado para testes rapidos, mas depende de migrations coerentes com os models usados nos testes.
