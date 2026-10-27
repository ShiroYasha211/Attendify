# Backend Deployment Through GitHub Actions

## Scope

This workflow belongs to `ShiroYasha211/Attendify`, not the Flutter repository.
Pushes and pull requests to `main` run dependency validation, PHP syntax checks,
the existing flashcard regression suites and Excel-runtime validation. Deployment
uses the exact tested SHA, only from `main`, only after validation succeeds.
No new secrets, users or server permissions are created by committing this file.

## One-Time Activation

1. Create a dedicated Unix deployment account (not root) with write access to the
   existing `/var/www/moeen_app` checkout, dependencies, storage and bootstrap
   cache, and read access to its existing `.env`. Preserve the PHP-FPM user's
   access to application files. Use a shared application group and setgid/ACLs,
   not world-writable permissions or a blanket `chown` of unrelated directories.
2. Provision `/var/backups/moeen-deploy` writable only by that account and the
   administrator. Backups include source and vendor, not database contents or
   uploaded files. Monitor capacity and retain a reviewed backup policy.
3. Allow only this sudo command, using `visudo` and validating the actual binary
   path: `/usr/bin/systemctl reload php8.2-fpm`. Never grant unrestricted sudo.
4. Give this account a read-only GitHub deploy key for this repository, so it can
   fetch `origin/main` without interactive authentication. Pin GitHub's SSH host
   key after verifying it against GitHub's published fingerprints.
5. Generate a separate Ed25519 SSH key for GitHub Actions to access this account.
   Add its public half to authorized_keys with `restrict` (no port forwarding,
   agent forwarding, X11 or PTY). This key can execute application deployment
   code and read the application's environment: keep access tightly controlled.
6. Create a GitHub Environment named `production`, restricted to `main`. Configure
   required reviewers when the repository plan supports them. Add its secrets:

   | Secret | Value |
   | --- | --- |
   | `DEPLOY_HOST` | Production hostname or IP |
   | `DEPLOY_PORT` | SSH port, usually `22` |
   | `DEPLOY_USER` | Dedicated non-root deployment account |
   | `DEPLOY_SSH_KEY` | Private half of the Actions-to-server key |
   | `DEPLOY_KNOWN_HOSTS` | Verified OpenSSH known_hosts entry for this host/port |

   Obtain the host public key from the trusted server console and compare its
   SHA-256 fingerprint. Do not trust `ssh-keyscan` output without verification.
   Do not paste private keys into chat, commits, logs or workflow YAML.
7. After reviewing account permissions and backups, set repository variable
   `DEPLOY_ENABLED` to `true`, then run the workflow manually on `main` once.
   Leave it unset/false until setup is complete. Review logs and `/up` before
   treating automated deployment as operational.

## Normal Use

Push the backend to `main`. After checks pass and any Environment approval, one
deployment runs at a time; the server also takes a filesystem lock. A stale run
cannot deploy over a newer `main`. Existing local changes are preserved, and an
overlap causes deployment to stop rather than overwrite them.

Dependencies come from `composer.lock`, never `composer update`. This in-place
deployment uses a short maintenance window. Source and vendor snapshots are
created before changing live code. PHP-FPM is reloaded, queues are signalled to
restart and public health is checked. Existing cron/Supervisor configuration is
not modified; operators must ensure their workers are already supervised.

## Explicit Safety Stops

- New migrations stop automatic deployment. Review them, take a verified database
  backup, then use **Run workflow** with `run_migrations` enabled. Existing pending
  migrations also need operator review before manually enabling this option.
- Changes to Vite inputs/package locks stop PHP deployment instead of serving
  stale web assets. A reproducible Vite build/artifact stage is a separate next
  step: this checkout currently has no committed npm lockfile. The workflow does
  not silently run an unpinned `npm install` on production.
- The workflow does not install OS packages, upgrade PHP, change `.env`, run
  seeders, reset Git, delete uploaded files or disable host-key verification.

## Failed Deployment / Recovery

Failures before maintenance do not touch live code. A failure after live mutation
keeps maintenance enabled to avoid serving mixed code/dependencies. The failed
run prints its recovery snapshot directory. This is **not** an atomic-release
system and does not promise zero downtime or automatic database rollback.

An administrator must inspect the error, compare `previous-commit`, recover the
reviewed code/dependencies from the snapshot or push a corrective/revert commit,
check Excel/platform requirements, reload PHP-FPM, then run `php artisan up` and
verify `/up`. Never blindly reset local changes or roll migrations back. If
maintenance is already active, the next automated run stops for operator review.

## Current Activation Status

Activated on 2026-10-27. The `moeen-deploy` account uses application-scoped ACLs,
an independent read-only repository key and only the PHP-FPM reload sudo command.
The verified server host key and encrypted credentials are stored in the
`production` Environment, restricted to `main`; `DEPLOY_ENABLED` is `true`.

The first live manual run passed both validation and deployment:
[40446176061](https://github.com/ShiroYasha211/Attendify/actions/runs/40446176061),
deploying commit `fcf91754225660916476556bfcdde867aa6db271`. This confirms the
connection and deployment path, not every application feature. No database
migrations were requested by this activation.
