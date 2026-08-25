# Conventions

- PHP classes use `PascalCase`; methods use `camelCase`; database identifiers use `snake_case`.
- Project models keep the `Model` suffix, such as `ProfessionalModel`.
- JSON routes use `/api/professionals`, `/api/patients`, and `/api/assessments`; Blade routes use Portuguese paths under `/ubs`.
- Domain IDs are UUIDs, CNES has seven digits, and administrator codes contain letters, numbers, `_`, or `-`.
- `professional_id` identifies an assessment's selected professional. Legacy `user_id` input is prohibited.
- Audit metadata uses `changed_fields`; before/after snapshots do not exist.
- Conventional Commit messages must not include secrets, dumps, `.env`, or `public/hot`.
