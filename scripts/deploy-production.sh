#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

# Fixed server paths: no caller-controlled deployment path or shell fragments.
readonly APP=/var/www/moeen_app
readonly STATE=/var/backups/moeen-deploy
readonly TARGET=${1:-}
readonly MIGRATE=${2:-false}
[[ "$TARGET" =~ ^[0-9a-f]{40}$ ]] || { echo 'Invalid commit.' >&2; exit 1; }
[[ "$MIGRATE" == true || "$MIGRATE" == false ]] || exit 1
(( EUID != 0 )) || { echo 'Deployment must not run as root.' >&2; exit 1; }
cd "$APP"
[[ "$(git rev-parse --show-toplevel)" == "$APP" ]] || exit 1
[[ "$(git branch --show-current)" == main ]] || exit 1
test -r .env && test -w storage && test -w bootstrap/cache
test -d "$STATE" && test -w "$STATE"
exec 9>"$STATE/deploy.lock"
flock -n 9 || { echo 'Another server deployment is running.' >&2; exit 1; }
test ! -e storage/framework/down || { echo 'Application is already in maintenance.' >&2; exit 1; }

git fetch --no-tags origin main
[[ "$(git rev-parse origin/main)" == "$TARGET" ]] || {
  echo 'Superseded deployment: target is no longer the latest main commit.' >&2; exit 1;
}
readonly PREVIOUS=$(git rev-parse HEAD)
git merge-base --is-ancestor "$PREVIOUS" "$TARGET"
if [[ "$PREVIOUS" == "$TARGET" ]]; then
  echo "Already deployed: $TARGET"
  php artisan flashcards:check-import --no-ansi
  exit 0
fi

# Ignore no local changes: fail on overlap, preserve unrelated server files.
changed=$(git diff --name-only "$PREVIOUS" "$TARGET")
while IFS= read -r path; do
  [[ -z "$path" ]] && continue
  if grep -Fxq -- "$path" <<< "$changed"; then
    echo "Local change overlaps deployment: $path" >&2; exit 1
  fi
done < <({ git diff --name-only; git diff --cached --name-only; git ls-files --others --exclude-standard; } | sort -u)

# The existing server builds its UI separately. Never publish stale Vite assets.
if git diff --name-only "$PREVIOUS" "$TARGET" -- package.json package-lock.json vite.config.js resources/css resources/js | grep -q .; then
  echo 'Web asset changes require a reviewed asset build; automated PHP deployment stopped.' >&2
  exit 1
fi
mapfile -t pending < <(git diff --name-only --diff-filter=A "$PREVIOUS" "$TARGET" -- database/migrations)
if (( ${#pending[@]} > 0 )) && [[ "$MIGRATE" != true ]]; then
  echo 'New migrations require manual dispatch with run_migrations after database backup.' >&2
  exit 1
fi
composer --no-interaction check-platform-reqs --lock --no-dev
sudo -n -l /usr/bin/systemctl reload php8.2-fpm >/dev/null

readonly SNAPSHOT="$STATE/$(date -u +%Y%m%dT%H%M%SZ)-${PREVIOUS:0:12}"
mkdir "$SNAPSHOT"
printf '%s\n' "$PREVIOUS" > "$SNAPSHOT/previous-commit"
git archive "$PREVIOUS" | gzip > "$SNAPSHOT/source.tar.gz"
git diff --binary HEAD > "$SNAPSHOT/local-changes.patch"
test ! -d vendor || tar -czf "$SNAPSHOT/vendor.tar.gz" vendor
maintenance=false
mutating=false
failed() {
  local status=$?
  trap - ERR HUP INT TERM
  if [[ "$maintenance" == true && "$mutating" == false ]]; then
    php artisan up || true
  fi
  echo "Deployment failed (exit $status). Recovery snapshot: $SNAPSHOT" >&2
  if [[ "$mutating" == true ]]; then
    echo 'Maintenance is intentionally retained: inspect and repair/revert the release before artisan up.' >&2
  fi
  exit "$status"
}
trap failed ERR HUP INT TERM
php artisan down --retry=60
maintenance=true
mutating=true
git merge --ff-only "$TARGET"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
php artisan flashcards:check-import --no-ansi
if [[ "$MIGRATE" == true ]]; then php artisan migrate --force --isolated; fi
php artisan optimize:clear
php artisan config:cache
sudo -n /usr/bin/systemctl reload php8.2-fpm
php artisan up
maintenance=false
php artisan queue:restart
curl --fail --silent --show-error --retry 3 --retry-delay 3 --max-time 30 https://moeen.tech/up
printf '\nDEPLOYED %s (previous %s)\n' "$TARGET" "$PREVIOUS"
