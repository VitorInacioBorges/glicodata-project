# Technologies

| Area | Technology |
| --- | --- |
| Runtime/framework | PHP 8.2+, Laravel 12 |
| API/auth | JSON Resources, Form Requests, Sanctum 4 |
| Database | PostgreSQL; in-memory SQLite for tests |
| Password hashing | Argon2id |
| Frontend | Blade, Vite 7, Bootstrap 5.3 |
| Tests | PHPUnit 11 |

Eloquent uses UUIDs, soft deletes for clinical records, enums for statuses/classifications, and JSON arrays for versioned answers. Vite port 5173 is development-only; production assets live in `public/build`.
