# 📅 Palacin Booking API

REST API to manage experiences, sessions and seat reservations with strict business invariants and concurrency-safe booking. Implements capacity control, same-day session uniqueness, 24h cancellation policy, past-date guards, and email notification hooks (`null://null` transport as required). Built with Symfony 8.1, DDD + Hexagonal Architecture for long-term maintainability, REST principles, SQLite for local dev and MySQL for Docker, with optimistic locking (`UPDATE ... WHERE available_seats >= :seats`) to handle high-contention sell-outs.

## ✨ Features

- Create experiences with provider ID
- Create sessions per experience (validates: no past dates, no duplicate same-day session per experience)
- Reserve seats (validates: session not started, atomic decrement of `available_seats`)
- Cancel reservations (validates: cannot double-cancel, cannot cancel <24h before start, restores capacity)
- Email hook on create/cancel via `ReservationNotifierInterface` / `MailerReservationNotifier`
- Concurrency-safe: `UPDATE sessions SET available_seats = available_seats - :seats WHERE available_seats >= :seats`

## 💻 Local setup (without Docker)

### 📋 Requirements

- PHP 8.3+
- `pdo_sqlite` extension enabled.
- Composer
- Symfony CLI (optional, for `symfony server:start`)

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

## ✉️ Email notifications - local verification

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

Then:

```bash
   mkdir -p var/mails
   rm var/cache/* -R
   php bin/console cache:clear
   # POST /api/sessions/{id}/reservations
   cat var/mails/notifier.log
```

Revert to the clean version before committing.

## 🐳 Deployment with Docker (production/staging)

### 📋 Requirements for Docker

- Docker
- Docker Compose v2

> **Note**: this configuration is a standard deployment reference
> (PHP-FPM + Nginx + MySQL, multi-stage build), but it has not been verified
> in the development environment of this repository. Local development uses
> SQLite (see "Local setup" section) precisely to avoid depending on
> Docker. Before using this configuration in a real environment, validate
> the full boot (`docker compose up --build`), migrations and environment
> variables in a staging environment.

1. Copy `.env.prod.example` to `.env.prod` and fill in the values.
2. Bring up the services:

```bash
   docker compose --env-file .env.prod up --build -d
```

1. Run migrations inside the `php` container:

```bash
   docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

1. The API is exposed at `http://localhost:8080`, and Swagger UI at
   `http://localhost:8080/api/doc`.

The production database is MySQL (via docker-compose), while
local development uses SQLite — the domain and application layers are
identical in both cases; only DATABASE_URL changes. The seat-availability
control mechanism (UPDATE ... WHERE available_seats >= :seats)
is compatible with both engines.

## 👨‍💻 Autor

**Xavier Palacín Ayuso**
Email: [cubiczx@hotmail.com](cubiczx@hotmail.com)
GitHub: [@cubiczx](https://github.com/cubiczx)