# Best practices

- Derive clinical tenant IDs from the authenticated UBS.
- Pass only Form Request `validated()` data and reject removed fields with `prohibited`.
- Return explicit API Resources instead of raw models.
- Keep professionals non-authenticatable and store passwords only for UBS/admin accounts.
- Never copy personal or clinical values into logs or audit events.
- Keep published questionnaire versions immutable and calculate risk on the server.
- Restrict professionals, patients, assessments, reports, and risks to their UBS.
- Use compiled same-origin assets for homologation/production and keep `public/hot` development-only.

Run `php artisan test`, `npm run build`, `vendor/bin/pint --test`, `composer audit`, and `npm audit` before delivery.
