#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

# Fixed server paths: no caller-controlled deployment path or shell fragments.
readonly APP=/var/www/moeen_app
readonly STATE=/var/backups/moeen-deploy
readonly TARGET=${1:-}
readonly MIGRATE=${2:-false}
readonly ASSET_SHA=${3:-}
[[ "$TARGET" =~ ^[0-9a-f]{40}$ ]] || { echo 'Invalid commit.' >&2; exit 1; }
[[ "$MIGRATE" == true || "$MIGRATE" == false ]] || exit 1
[[ "$ASSET_SHA" =~ ^[0-9a-f]{64}$ ]] || { echo 'Invalid asset checksum.' >&2; exit 1; }
readonly ASSET_ARCHIVE="$STATE/incoming/$TARGET.tar.gz"
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
  php scripts/check-web-build.php public/build
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

test ! -e public/hot || { echo 'Remove the development Vite hot file after operator review.' >&2; exit 1; }
printf '%s  %s\n' "$ASSET_SHA" "$ASSET_ARCHIVE" | sha256sum --check
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
test ! -d public/build || tar -czf "$SNAPSHOT/web-build.tar.gz" public/build
readonly STAGED="$APP/public/.deploy-$TARGET"
test ! -e "$STAGED" || { echo 'Asset staging path already exists; inspect it before retrying.' >&2; exit 1; }
mkdir "$STAGED"
tar -xzf "$ASSET_ARCHIVE" -C "$STAGED" --no-same-owner --no-same-permissions
find "$STAGED" -type d -exec chmod 750 {} +
find "$STAGED" -type f -exec chmod 640 {} +
# Validate using the exact target's checker before entering maintenance.
git show "$TARGET:scripts/check-web-build.php" | php /dev/stdin "$STAGED"
maintenance=false
mutating=false
failed() {
  local status=$?
  trap - ERR HUP INT TERM
  if [[ "$maintenance" == true && "$mutating" == false ]]; then
    php artisan up || true
  fi
  echo "Deployment failed (exit $status). Recovery snapshot: $SNAPSHOT" >&2
  if [[ "$mutating" == true && "$maintenance" == true ]]; then
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
test ! -d public/build || mv public/build "$SNAPSHOT/previous-web-build"
mv "$STAGED" public/build
php scripts/check-web-build.php public/build
if [[ "$MIGRATE" == true ]]; then php artisan migrate --force --isolated; fi
php artisan optimize:clear
php artisan config:cache
sudo -n /usr/bin/systemctl reload php8.2-fpm
php artisan up
maintenance=false
php artisan queue:restart
curl --fail --silent --show-error --retry 3 --retry-delay 3 --max-time 30 https://moeen.tech/up
php -r '$manifest = json_decode(file_get_contents("public/build/manifest.json"), true, 512, JSON_THROW_ON_ERROR); foreach ($manifest as $entry) { echo $entry["file"], PHP_EOL; }' |
  while IFS= read -r asset; do
    curl --fail --silent --show-error --output /dev/null --retry 2 --max-time 30 "https://moeen.tech/build/$asset"
  done
printf '\nDEPLOYED %s (previous %s)\n' "$TARGET" "$PREVIOUS"
