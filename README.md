# GlicoData

Sistema Laravel para gerenciamento institucional de UBS e avaliação de risco de Diabetes Mellitus tipo 2 com minimização de dados.

## Contrato de privacidade

- Pacientes guardam somente primeiro nome, sexo, bairro e nome do logradouro sem número. A faixa etária é informada em cada avaliação; CPF, data de nascimento, telefone e endereço completo não existem no schema final.
- Profissionais são referências clínicas sem login: primeiro nome, especialidade, UBS e status ativo. Não há e-mail, senha, CPF, nascimento, contato ou conselho profissional.
- A UBS autentica pelo CNES e executa o fluxo clínico. O profissional responsável é selecionado por busca dentro da própria UBS.
- Administradores autenticam com `admin_code` e senha; não possuem nome nem e-mail.
- Relatórios guardam a anamnese e a descrição. Título e comentário interno foram removidos.
- Auditoria guarda ator institucional, ação e nomes dos campos alterados, nunca cópias dos valores pessoais ou clínicos.

## Stack

- PHP 8.2+, Laravel 12 e Laravel Sanctum
- PostgreSQL (`glicodata_db`) no ambiente da aplicação
- Blade, Vite 7 e Bootstrap 5.3
- PHPUnit com SQLite em memória

## Início rápido

```bash
cd glicodata
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan glicodata:admin-create
composer run dev
```

`composer run dev` usa Laravel em `127.0.0.1:8000` e Vite em `127.0.0.1:5173`. A porta 5173 serve somente CSS/JavaScript com hot reload; não é uma segunda aplicação nem outro banco.

Para homologação em uma única porta de navegador, com assets compilados no mesmo host:

```bash
composer run homolog
```

Esse fluxo remove o marcador `public/hot`, compila os assets, aplica migrations, otimiza o Laravel e abre somente a aplicação em `http://127.0.0.1:8000`.

## Autenticação

- UBS: `/login/ubs`, identificador CNES e senha.
- Administrador global: `/login/admin`, ID administrativo e senha.
- Não existe login de profissional.

Novas UBS solicitam acesso em `/cadastro/ubs` e permanecem inativas até aprovação administrativa. Senhas são definidas interativamente:

```bash
php artisan glicodata:admin-create ADMIN_001
php artisan glicodata:ubs-password 1234567
```

## Verificação

```bash
php artisan test
npm run build
php artisan route:list --except-vendor
```

## Documentação

- [Arquitetura](./documentation/portuguese/ARCHITECTURE.md)
- [Execução e diagnóstico de portas/assets](./documentation/portuguese/EXECUTION.md)
- [Homologação e deploy](./documentation/portuguese/HOMOLOGACAO-DEPLOY.md)
- [Documentação em inglês](./documentation/english/ARCHITECTURE.md)

Credenciais e segredos nunca devem ser registrados no repositório.

---

# GlicoData (English)

Laravel application for UBS institutional management and type 2 diabetes risk assessment with data minimization.

- Patients contain only first name, sex, neighborhood, and street name without a house number. Age range is captured per assessment.
- Professionals are non-login clinical references containing only first name, specialty, UBS, and active status.
- UBS accounts own clinical sessions; global administrators authenticate using an administrator ID, without name or email.
- `composer run dev` uses application port 8000 plus Vite asset port 5173. `composer run homolog` serves compiled assets and the application from port 8000 only.
- PostgreSQL uses `glicodata_db`; SQLite in memory is reserved for automated tests.

See the [English execution guide](./documentation/english/EXECUTION.md).

codex resume 01a03f50-b51b-7bf3-9631-69eb4a0225c3
codex resume 01a03f50-b51b-7bf3-9631-69eb4a0225c3
