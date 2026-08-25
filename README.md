## Puesta en marcha en local (sin Docker)

Requisitos: PHP 8.3+, Composer, extensión `pdo_sqlite` habilitada.

```bash
composer install

php bin/console doctrine:migrations:migrate --no-interaction

symfony server:start
# o alternativamente: php -S 127.0.0.1:8000 -t public
```

- API disponible en `http://127.0.0.1:8000/api`
- Swagger UI en `http://127.0.0.1:8000/api/doc`
- Swagger JSON en `http://127.0.0.1:8000/api/doc.json`

Tests:
```bash
php bin/phpunit
```

No requiere Docker ni servicios externos: usa SQLite (`var/data.db`) por defecto.

## Despliegue con Docker (producción/staging)

> **Nota**: esta configuración es una referencia de despliegue estándar
> (PHP-FPM + Nginx + MySQL, build multi-stage), pero no ha sido verificada
> en el entorno de desarrollo de este repositorio. El desarrollo local usa
> SQLite (ver sección "Puesta en marcha") precisamente para no depender de
> Docker. Antes de usar esta configuración en un entorno real, valida el
> arranque completo (`docker compose up --build`), las migraciones y las
> variables de entorno en un entorno de staging.

1. Copia `.env.prod.example` a `.env.prod` y rellena los valores.
2. Levanta los servicios:
```bash
   docker compose --env-file .env.prod up --build -d
```
3. Ejecuta las migraciones dentro del contenedor `php`:
```bash
   docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```
4. La API queda expuesta en `http://localhost:8080`, y Swagger UI en
   `http://localhost:8080/api/doc`.

La base de datos en producción es **MySQL** (vía `docker-compose`), mientras
que en desarrollo local se usa **SQLite** — el dominio y la capa de
aplicación son idénticos en ambos casos; sólo cambia `DATABASE_URL`. El
mecanismo de control de aforo (`UPDATE ... WHERE available_seats >= :seats`)
es compatible con ambos motores.