# Diretórios

```text
glicodata/
├── app/
│   ├── Http/Controllers       # API e controllers Web
│   ├── Http/Requests          # validação e contratos proibidos
│   ├── Http/Resources         # allowlists JSON
│   ├── Models                 # Eloquent
│   ├── Policies               # autorização e isolamento por UBS
│   ├── Repositories           # consultas escopadas
│   ├── Services               # regras/transações/auditoria
│   └── Support/TenantContext.php
├── config/                    # auth, Sanctum, hashing e frontend
├── database/
│   ├── migrations            # schema PostgreSQL/SQLite
│   ├── factories             # dados descartáveis de teste
│   └── seeders               # distritos e questionário, sem contas
├── resources/
│   ├── views                 # Blade de UBS e administrador
│   ├── css/app.css           # Bootstrap + estilo do produto
│   └── js/app.js             # Bootstrap + busca de profissional
├── routes/api.php            # API Sanctum
├── routes/web.php            # sessões Blade
├── tests/                    # PHPUnit
└── deploy/                   # build e serviços de produção
```

Os recursos `Professional`, `Patient`, `Assessment`, `Risk` e `Report` ficam em pastas próprias. Não existem `UserModel`, login profissional, regra de CPF ou requests de escrita de risco.
