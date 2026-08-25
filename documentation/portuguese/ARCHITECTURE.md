# Arquitetura do GlicoData

## Identidades e tenant

O sistema possui duas identidades autenticáveis:

- `UbsModel`: conta institucional, identificada por CNES, proprietária de todo fluxo clínico;
- `AdministratorModel`: conta global, identificada por `admin_code`, autorizada a revisar UBS e auditoria institucional.

`ProfessionalModel` não é uma conta. É uma referência selecionável dentro da UBS, com `first_name`, `specialty` e `is_active`. Não usa guard, sessão, token ou senha.

```text
Administrador ── gerencia ──> UBS
                              │
                              ├── Profissionais
                              ├── Pacientes
                              └── Avaliações ──> Risco
                                      └───────> Relatório
```

As rotas clínicas exigem `auth:ubs`, `auth.session` e `account:ubs`. O `TenantContext`, as Policies e as consultas por `ubs_id` impedem que uma UBS opere registros de outra. Administradores não acessam dados clínicos.

## Camadas

```text
routes -> middleware/guards -> Form Request -> controller/policy
       -> service/transação -> repository/Eloquent -> PostgreSQL
       -> API Resource ou Blade
```

- Form Requests normalizam a entrada, rejeitam campos removidos e validam UUIDs/relações.
- Controllers nunca aceitam `ubs_id` clínico do cliente; derivam o tenant da UBS autenticada.
- Services aplicam invariantes e registram auditoria na mesma transação.
- Repositories clínicos paginam sempre por UBS.
- Resources JSON usam listas explícitas de campos, evitando serialização acidental do model.

## Minimização de dados

| Entidade | Dados persistidos relevantes |
| --- | --- |
| Administrador | ID interno, `admin_code`, hash de senha, status |
| UBS | CNES, dados institucionais, hash de senha, status |
| Profissional | UBS, primeiro nome, especialidade, status |
| Paciente | UBS, primeiro nome, sexo, bairro, bairro normalizado, nome do logradouro |
| Avaliação | UBS, paciente, profissional, versão, respostas estruturadas, status e datas |
| Risco | avaliação, pontuação, percentual e classificação calculados |
| Relatório | avaliação e descrição |
| Auditoria | ator institucional, proprietário, assunto, ação e nomes de campos alterados |

A faixa etária faz parte das respostas imutáveis de cada avaliação. Data de nascimento não é necessária. O nome do logradouro rejeita número de imóvel e complemento; o bairro normalizado permite pesquisa posterior sem duplicar identificadores.

## Fluxo da avaliação

1. A UBS seleciona um paciente do próprio tenant.
2. A busca dinâmica retorna somente profissionais ativos da mesma UBS e expõe apenas primeiro nome e especialidade.
3. O rascunho fixa `professional_id` e a versão publicada do questionário.
4. O servidor valida respostas estruturadas e calcula o FINDRISC; score enviado pelo cliente é ignorado.
5. Ao concluir, respostas e risco tornam-se imutáveis.
6. O relatório herda paciente e profissional pela avaliação e guarda somente a descrição.

## Auditoria

`AuditEventService` descarta os valores recebidos em estados anterior/posterior. O registro contém apenas os nomes dos campos envolvidos. Assim, respostas clínicas, descrição do relatório, nomes e outros valores não são copiados para snapshots.

## Frontend e assets

Blade renderiza a interface no Laravel. `resources/css/app.css` importa Bootstrap e os estilos do produto; `resources/js/app.js` importa Bootstrap e implementa a busca de profissionais.

- Desenvolvimento: Blade em 8000 busca assets no Vite em 5173; a CSP local libera essa origem apenas com `public/hot`.
- Homologação/produção: Vite gera `public/build`; HTML, CSS e JS vêm da mesma origem e porta, com CSP `'self'`.

## Relações

```text
District 1 ── N Ubs
Ubs 1 ── N Professional
Ubs 1 ── N Patient
Ubs 1 ── N Assessment
Professional 1 ── N Assessment
Patient 1 ── N Assessment
Questionnaire 1 ── N QuestionnaireVersion
QuestionnaireVersion 1 ── N Assessment
Assessment 1 ── 1 Risk
Assessment 1 ── 1 Report
Ubs/Administrator 1 ── N AuditEvent
```
