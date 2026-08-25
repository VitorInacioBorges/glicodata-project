# Directories

```text
glicodata/
├── app/Http/Controllers   # JSON and web controllers
├── app/Http/Requests      # validation and prohibited legacy fields
├── app/Http/Resources     # JSON allowlists
├── app/Models             # Eloquent models
├── app/Policies           # authorization and UBS isolation
├── app/Repositories       # scoped persistence queries
├── app/Services           # domain rules, transactions, auditing
├── database               # migrations, test factories, safe seeders
├── resources/views        # Blade UI
├── resources/css          # Bootstrap and product styling
├── resources/js           # Bootstrap and professional search
├── routes                 # web and API routes
└── tests                  # PHPUnit feature/unit tests
```

There is no `UserModel`, professional authentication view, CPF validation rule, or risk write request.
