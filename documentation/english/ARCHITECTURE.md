# GlicoData architecture

## Identities and tenancy

Only UBS and global administrator records authenticate. UBS accounts use CNES and own every clinical action. Administrators use `admin_code` and manage institutional UBS records without clinical access.

Professionals are non-login clinical references containing `first_name`, `specialty`, `ubs_id`, and `is_active`. An assessment selects one active professional from the authenticated UBS.

```text
routes -> guards/middleware -> Form Request -> controller/policy
       -> service/transaction -> repository/Eloquent -> PostgreSQL
       -> API Resource or Blade
```

Controllers derive clinical `ubs_id` from the authenticated account. Policies and tenant-scoped repository queries prevent cross-UBS access. Explicit JSON Resources prevent accidental model serialization.

## Minimal data model

| Entity | Relevant stored data |
| --- | --- |
| Administrator | internal ID, administrator code, password hash, active status |
| UBS | CNES, institutional profile, password hash, active status |
| Professional | UBS, first name, specialty, active status |
| Patient | UBS, first name, sex, neighborhood, normalized neighborhood, street name |
| Assessment | UBS, patient, professional, questionnaire version, structured answers, status/timestamps |
| Risk | server-calculated score, percentage, classification |
| Report | assessment and description |
| Audit event | institutional actor, owner, subject, action, changed field names |

Age range is captured per assessment instead of storing birth date. Audit processing discards before/after values and stores no personal or clinical snapshots.

## Asset delivery

Development serves Blade from Laravel on port 8000 and hot-reload assets from Vite on 5173. Homologation and production serve compiled `public/build` assets from the same origin as Laravel, with a self-only CSP.
