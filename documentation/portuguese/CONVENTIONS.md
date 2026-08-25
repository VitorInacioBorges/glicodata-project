# Convenções

## Código

- Classes em `PascalCase`; métodos e variáveis em `camelCase`; tabelas e colunas em `snake_case`.
- Models usam o sufixo `Model` adotado pelo projeto: `ProfessionalModel`, `PatientModel`.
- Pastas de recurso seguem `ProfessionalControllers`, `ProfessionalServices` e `ProfessionalRepositories`.
- Form Requests distinguem store/update; Resources definem a resposta JSON.
- IDs de domínio são UUIDs; CNES tem sete dígitos; `admin_code` aceita letras, números, `_` e `-`.

## HTTP

- Rotas JSON usam nomes em inglês: `/api/professionals`, `/api/patients`, `/api/assessments`.
- Rotas Blade usam português sob `/ubs`.
- Parâmetros de IDs usam `whereUuid` e Policies validam o tenant.
- Falha de validação retorna 422; autenticação 401; autorização 403; ausência 404.

## Dados mínimos

- `first_name` representa apenas um primeiro nome.
- `street_name` representa nome do logradouro sem número/complemento.
- `neighborhood_normalized` é derivado no service, nunca informado pelo cliente.
- Profissional selecionado é `professional_id`; o termo legado `user_id` é proibido no contrato clínico.
- Auditoria usa `changed_fields`; snapshots anterior/posterior não existem.

## Git

Use Conventional Commits com ação no imperativo e escopo quando útil, sem registrar credenciais, arquivos `.env`, dumps ou `public/hot`.
