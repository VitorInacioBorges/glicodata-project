# 💉​ Projeto Glicodata

[Português](#projeto-glicodata-portuguese) 🇧🇷 / 🇵🇹 | [English](#projeto-glicodata-english) 🇺🇸 / 🇬🇧 / 🇨🇦 / 🇦🇺

Sistema Laravel para apoiar o cadastro e a organizacao de dados de UBS, usuarios, pacientes, avaliacoes, riscos e relatorios relacionados ao acompanhamento de risco de Diabetes Mellitus II.

## Propósito

Oferecer uma base de API e interface web simples para registrar unidades basicas de saude, pacientes, profissionais, avaliacoes clinicas, classificacoes de risco e relatorios. O projeto tambem funciona como base de estudo para arquitetura Laravel com controllers, services, repositories, Eloquent models, Blade, Vite e testes PHPUnit.

## Objetivos

- **Gestao de UBS**: Cadastro de distritos e unidades basicas de saude com dados de contato, endereco, bairro de referencia e status ativo.
- **Cadastro Operacional**: Registro de usuarios do sistema e pacientes vinculados a uma UBS.
- **Autenticacao UBS**: Login da UBS via Keycloak/OpenID, com token Bearer usado nas rotas protegidas da API.
- **Avaliacoes e Risco**: Modelagem de avaliacoes, respostas, sintomas e classificacao de risco em `low`, `moderate` ou `high`.
- **Relatorios**: Registro de titulos, descricoes e comentarios associados a uma avaliacao.
- **Base Laravel Evolutiva**: Separacao pragmatica entre controllers, services, repositories e models para evoluir validacao, autenticacao, migrations e testes.

## Servicos

| Servico             | Descricao                                                                                                        |
| ------------------- | ---------------------------------------------------------------------------------------------------------------- |
| **Backend API**     | API REST em **Laravel 12** e **PHP 8.2+**, organizada por controllers, services, repositories e Eloquent models. |
| **Autenticacao**    | Integracao com **Laravel Socialite** e **SocialiteProviders Keycloak** para autenticar UBS via OpenID Connect.   |
| **Interface Blade** | Views server-side simples para home, contato e formulario de registro, com Bootstrap via CDN e assets publicos.  |
| **Assets**          | Build com **Vite 7**, **Tailwind CSS 4**, `laravel-vite-plugin` e Axios inicializado no bootstrap JS.            |
| **Banco de Dados**  | PostgreSQL como banco padrao do projeto; SQLite fica restrito a testes automatizados quando configurado.         |

## Documentacao Tecnica

| Documento                                                         | Descricao                                                           |
| ----------------------------------------------------------------- | ------------------------------------------------------------------- |
| [ARCHITECTURE.md](./documentation/portuguese/ARCHITECTURE.md)     | Fundacao arquitetural, camadas, fluxo de dados e modulos do sistema |
| [DIRECTORIES.md](./documentation/portuguese/DIRECTORIES.md)       | Mapeamento completo de diretorios e responsabilidades               |
| [TECHNOLOGIES.md](./documentation/portuguese/TECHNOLOGIES.md)     | Stack, metodologias, dependencias e gerenciamento de dados          |
| [CONVENTIONS.md](./documentation/portuguese/CONVENTIONS.md)       | Padroes de nomeacao, organizacao e design patterns                  |
| [BEST-PRACTICES.md](./documentation/portuguese/BEST-PRACTICES.md) | SOLID, tratamento de erros, testes, seguranca e riscos atuais       |
| [PREREQUISITES.md](./documentation/portuguese/PREREQUISITES.md)   | Dependencias de sistema, ferramentas, banco e hardware              |
| [EXECUTION.md](./documentation/portuguese/EXECUTION.md)           | Setup local, variaveis de ambiente, migrations, endpoints e deploy  |

## Estrutura Geral

```text
ubs-system/
├── application/          # Aplicacao Laravel 12
│   ├── app/              # Controllers, policies, services, repositories, models, providers e utils
│   ├── database/         # Migrations, factories e seeders
│   ├── resources/        # Views Blade e entradas Vite
│   ├── routes/           # Rotas web e rotas API; API usa prefixo /api
│   └── tests/            # Testes Feature e Unit
├── documentation/
│   ├── english/          # Documentacao em ingles
│   └── portuguese/       # Documentacao em portugues
└── README.md             # Este arquivo
```

---

# 💉​ Glicodata Project

[Português](#projeto-glicodata-portuguese) 🇧🇷 / 🇵🇹 | [English](#projeto-glicodata-english) 🇺🇸 / 🇬🇧 / 🇨🇦 / 🇦🇺

Laravel system to support UBS unit, user, patient, assessment, risk, and report data related to Diabetes Mellitus II risk tracking.

## Purpose

To provide an API and simple web interface foundation for registering basic health units, patients, professionals, clinical assessments, risk classifications, and reports. The project also serves as a study base for Laravel architecture with controllers, services, repositories, Eloquent models, Blade, Vite, and PHPUnit tests.

## Objectives

- **UBS Management**: Register districts and basic health units with contact data, address, reference neighborhood, and active status.
- **Operational Registration**: Register system users and patients linked to a UBS unit.
- **UBS Authentication**: UBS login through Keycloak/OpenID, with Bearer tokens used on protected API routes.
- **Assessments and Risk**: Model assessments, answers, symptoms, and risk classification as `low`, `moderate`, or `high`.
- **Reports**: Register titles, descriptions, and comments associated with an assessment.
- **Evolvable Laravel Base**: Pragmatic separation between controllers, services, repositories, and models to evolve validation, authentication, migrations, and tests.

## Services

| Service             | Description                                                                                                                  |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| **Backend API**     | REST API built with **Laravel 12** and **PHP 8.2+**, organized by controllers, services, repositories, and Eloquent models.  |
| **Authentication**  | Integration with **Laravel Socialite** and **SocialiteProviders Keycloak** to authenticate UBS accounts with OpenID Connect. |
| **Blade Interface** | Simple server-side views for home, contact, and registration form, with Bootstrap CDN and public assets.                     |
| **Assets**          | Build with **Vite 7**, **Tailwind CSS 4**, `laravel-vite-plugin`, and Axios initialized in the JS bootstrap.                 |
| **Database**        | PostgreSQL as the project default database; SQLite is limited to automated tests when configured.                            |

## Technical Documentation

| Document                                                       | Description                                                           |
| -------------------------------------------------------------- | --------------------------------------------------------------------- |
| [ARCHITECTURE.md](./documentation/english/ARCHITECTURE.md)     | Architectural foundation, layers, data flow, and system modules       |
| [DIRECTORIES.md](./documentation/english/DIRECTORIES.md)       | Complete mapping of directories and responsibilities                  |
| [TECHNOLOGIES.md](./documentation/english/TECHNOLOGIES.md)     | Stack, methodology, dependencies, and data management                 |
| [CONVENTIONS.md](./documentation/english/CONVENTIONS.md)       | Naming patterns, organization, and design patterns                    |
| [BEST-PRACTICES.md](./documentation/english/BEST-PRACTICES.md) | SOLID, error handling, tests, security, and current risks             |
| [PREREQUISITES.md](./documentation/english/PREREQUISITES.md)   | System dependencies, tools, database, and hardware                    |
| [EXECUTION.md](./documentation/english/EXECUTION.md)           | Local setup, environment variables, migrations, endpoints, and deploy |

## General Structure

```text
ubs-system/
├── application/          # Laravel 12 application
│   ├── app/              # Controllers, policies, services, repositories, models, providers, and utils
│   ├── database/         # Migrations, factories, and seeders
│   ├── resources/        # Blade views and Vite entries
│   ├── routes/           # Web routes and API routes; API uses /api prefix
│   └── tests/            # Feature and Unit tests
├── documentation/
│   ├── english/          # English documentation
│   └── portuguese/       # Portuguese documentation
└── README.md             # This file
```

## Sessions

codex resume 019e171c-5e24-7371-b4cb-30138e1839c2
019e1f24-9624-7022-bf2d-8ab1571cb629
