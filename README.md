# HyperLine API Server

Standalone Laravel 12 / PHP 8.3 source export of `services/api` from `/opt/hyperline-ott` (2026-11-10). PostgreSQL/Redis production support; SQLite in-memory automated tests.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure a disposable local database in `.env`, then run `php artisan migrate` and `php artisan serve`. Never point development migrations or test fixtures at production. Generate a key only for a new local environment; do not rotate an existing deployed key.

```bash
php vendor/bin/phpunit --do-not-cache-result
```

The test bootstrap forces testing mode, in-memory SQLite, array cache/sessions and synchronous queues. The exported suite passed 178 tests / 780 assertions before upload.

The Dockerfile builds from this repository root and runs PHP-FPM on port 9000. Worker and scheduler probes/scripts are in `docker/`. This source repository does not automatically create containers, publish ports or migrate a production database.

## API / security

Routes under `/api/v1` include public health/catalog/search, viewer auth/watchlist/progress/playback, and admin auth/dashboard/movie CRUD/audit logs. Administrator accounts are provisioned explicitly by `php artisan hyperline:admin`; no production passwords are included. The `--password` option can appear in process arguments/history; use the documented bootstrap configuration through an operator-controlled secure mechanism rather than committing secrets.

Bearer login tokens currently have no configured server-side TTL; a bounded expiry policy remains an acceptance item. Signed playback is required in production; the unconfigured CDN authorizer fails closed with 503. Do not enable unsigned production playback. No CDN signing keys, account tokens, certificates, production environments, databases or deployment evidence are included.

Companions: [CMS](https://github.com/AungMyoNyein/hyperline-cms), [web](https://github.com/AungMyoNyein/hyperline-web), [mobile](https://github.com/AungMyoNyein/hyperline-mobile).
