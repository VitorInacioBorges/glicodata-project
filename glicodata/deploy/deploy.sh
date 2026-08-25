#!/usr/bin/env bash
set -Eeuo pipefail

app_dir="${1:-}"

if [[ -z "$app_dir" || "$app_dir" == "/" || ! -f "$app_dir/artisan" ]]; then
    echo "Uso: GLICODATA_BACKUP_CONFIRMED=yes deploy/deploy.sh /caminho/absoluto/glicodata" >&2
    exit 2
fi

if [[ "${GLICODATA_BACKUP_CONFIRMED:-}" != "yes" ]]; then
    echo "Confirme um backup PostgreSQL restaurável com GLICODATA_BACKUP_CONFIRMED=yes." >&2
    exit 3
fi

cd "$app_dir"

php artisan down --retry=60
restore_application() {
    php artisan up || true
}
trap restore_application EXIT

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\QuestionnaireSeeder --force
php artisan optimize
php artisan queue:restart || true

php artisan up
trap - EXIT

echo "Deploy concluído. Execute os smoke tests e confirme worker, scheduler e HTTPS."
