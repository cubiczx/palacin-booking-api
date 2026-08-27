# 📅 Palacin Booking API

REST API to manage experiences, sessions and seat reservations with strict business invariants and concurrency-safe booking. Implements capacity control, same-day session uniqueness, 24h cancellation policy, past-date guards, and email notification hooks (`null://null` transport as required). Built with Symfony 8.1, DDD + Hexagonal Architecture for long-term maintainability, REST principles, SQLite for local dev and MySQL for Docker, with optimistic locking (`UPDATE ... WHERE available_seats >= :seats`) to handle high-contention sell-outs.

## 🚀 Live Demo

The API is deployed on Render (free tier - it may take ~30s to wake up on first request):

**Swagger UI / OpenAPI Docs:** https://palacin-booking-api.onrender.com/api/doc

**Base URL:** `https://palacin-booking-api.onrender.com/api`

### Production Stack on Render
- **PHP 8.4 + Symfony 8 + API Platform**
- **PostgreSQL 16** (Render managed PostgreSQL)
- Local dev uses SQLite / MySQL via `docker compose`. For Render, MySQL migrations (`LONGTEXT`, `DATETIME`) are automatically converted to Postgres-compatible types (`TEXT`, `TIMESTAMP`) during deploy via `render/migrations/` override in the Dockerfile.

> Note: Free tier spins down after inactivity. If you get a 500/timeout on first hit, wait 30s and reload.

## ✨ Features

- Create experiences with provider ID
- Create sessions per experience (validates: no past dates, no duplicate same-day session per experience)
- Reserve seats (validates: session not started, atomic decrement of `available_seats`)
- Cancel reservations (validates: cannot double-cancel, cannot cancel <24h before start, restores capacity)
- Email hook on create/cancel via `ReservationNotifierInterface` / `MailerReservationNotifier`
- Concurrency-safe: `UPDATE sessions SET available_seats = available_seats - :seats WHERE available_seats >= :seats`

## 💻 Local setup (without Docker)

If you prefer running the application natively without Docker, the repository uses SQLite by default via `.env.local`:

### 📋 Requirements

- PHP 8.4+
- `pdo_sqlite` extension enabled
- Composer
- Symfony CLI (optional, for `symfony server:start`)

1. Copy the local environment configuration (sets up SQLite `var/data.db`):

```bash
cp .env.local.example .env.local
```

2. Install dependencies:
 
```bash
composer install
```

3. Run migrations:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

4. Start local development server:

```bash
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

## ✉ Email notifications - local verification

Per the requirements, `MAILER_DSN=null://null` in `.env`, so no real email is sent.
The implementation is still wired: `ReserveSeatsHandler` calls
`ReservationNotifierInterface::notifyReservationCreated()` which is
implemented by `MailerReservationNotifier`. Symfony autowiring binds the interface
to that single implementation.

> **Note**: `log://` and `file://` mailer schemes are not supported in Symfony 8.1,
> so `null://null` is kept for the final version.

If you want to verify locally that the notifier is called on `POST /api/sessions/{id}/reservations`,
temporarily replace the notifier with this debug version that writes to `var/mails/notifier.log`:

```php
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class MailerReservationNotifier implements ReservationNotifierInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {}

    private function send(string $to, string $subject, string $body): void
    {
        // Local debug - writes email to log file
        file_put_contents(
            $this->projectDir . '/var/mails/notifier.log',
            sprintf("[%s] to=%s subject=%s body=%s\n", date('c'), $to, $subject, $body),
            FILE_APPEND
        );

        $this->mailer->send(new Email()->from('no-reply@...')->to($to)->subject($subject)->text($body));
    }
}
```

### Then

```bash
   mkdir -p var/mails
   rm var/cache/* -R
   php bin/console cache:clear
   # POST /api/sessions/{id}/reservations
   cat var/mails/notifier.log
```

Revert to the clean version before committing.

## 🐳 Deployment with Docker (production/staging)

The multi-stage containerized setup (PHP 8.4 FPM + Nginx + MySQL 8.0) has been fully tested and verified using GitHub Codespaces, providing a seamless, zero-dependency environment end-to-end.

### Simply run

```bash
docker compose up -d --build
```

- API Base: `http://localhost:8080/api`
- Swagger Documentation: `http://localhost:8080/api/doc`

> **Note**: Database migrations (doctrine:migrations:migrate) are executed automatically on container boot before launching PHP-FPM.

The container stack uses MySQL via `docker-compose`, while local native development uses SQLite. The domain and application layers are identical in both cases; only `DATABASE_URL` changes. The seat-availability control mechanism (`UPDATE ... WHERE available_seats >= :seats`) is fully compatible with both database engines.

## ☁️ Deployment on Render (PostgreSQL)

This repo includes `render.yaml` and `render/Dockerfile` for one-click deploy on Render.

The `render/Dockerfile` swaps `migrations/*.php` with `render/migrations/*.php` at boot time to convert MySQL-specific types (`LONGTEXT` -> `TEXT`) to PostgreSQL 16 compatible types, keeping local MySQL untouched.

Environment variables required on Render:

- `DATABASE_URL` (PostgreSQL internal URL with `?serverVersion=16.0.0`)
- `APP_ENV=prod`, `APP_SECRET`, `DEFAULT_URI=https://palacin-booking-api.onrender.com`, `APP_SHARE_DIR=var/share`, `MAILER_DSN=null://null`

## 👨💻 Autor

**Xavier Palacín Ayuso**
Email: [cubiczx@hotmail.com](cubiczx@hotmail.com)
GitHub: [@cubiczx](https://github.com/cubiczx)
