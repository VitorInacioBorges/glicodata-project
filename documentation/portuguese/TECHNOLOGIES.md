# Tecnologias

| Área | Tecnologia |
| --- | --- |
| Runtime | PHP 8.2+ |
| Framework | Laravel 12 |
| API | JSON, Form Requests, Resources e Sanctum 4 |
| Banco | PostgreSQL; SQLite em memória para testes |
| Autenticação | Guards UBS/admin, tokens Bearer de 24 h |
| Senhas | Argon2id |
| Frontend | Blade, Vite 7, Bootstrap 5.3 e JavaScript modular |
| Testes | PHPUnit 11 |

Eloquent usa UUIDs, soft delete nos registros clínicos, enums para status/classificação e arrays JSON para respostas versionadas. Auditoria armazena somente metadados e nomes de campos alterados.

Vite roda na porta 5173 apenas no desenvolvimento com hot reload. Builds de homologação/produção ficam em `public/build` e são servidos pelo Laravel/web server na mesma origem.
