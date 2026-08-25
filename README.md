## Local setup (without Docker)

Requirements: PHP 8.3+, Composer, `pdo_sqlite` extension enabled.

```bash
composer install

php bin/console doctrine:migrations:migrate --no-interaction

symfony server:start
# or alternatively: php -S 127.0.0.1:8000 -t public
```

- API available at `http://127.0.0.1:8000/api`
- Swagger UI at `http://127.0.0.1:8000/api/doc`
- Swagger JSON at `http://127.0.0.1:8000/api/doc.json`

Tests:
```bash
php bin/phpunit
```

No Docker or external services required: uses SQLite (var/data.db) by default.

## Deployment with Docker (production/staging)

> **Note**: this configuration is a standard deployment reference
> (PHP-FPM + Nginx + MySQL, multi-stage build), but it has not been verified
> in the development environment of this repository. Local development uses
> SQLite (see "Local setup" section) precisely to avoid depending on
> Docker. Before using this configuration in a real environment, validate
> the full boot (docker compose up --build), migrations and environment
> variables in a staging environment.

1. Copy `.env.prod.example` to `.env.prod` and fill in the values.
2. Bring up the services:
```bash
   docker compose --env-file .env.prod up --build -d
```
3. Run migrations inside the `php` container:
```bash
   docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```
4. The API is exposed at `http://localhost:8080`, and Swagger UI at
   `http://localhost:8080/api/doc`.

The production database is MySQL (via docker-compose), while
local development uses SQLite — the domain and application layers are
identical in both cases; only DATABASE_URL changes. The seat-availability
control mechanism (UPDATE ... WHERE available_seats >= :seats)
is compatible with both engines.