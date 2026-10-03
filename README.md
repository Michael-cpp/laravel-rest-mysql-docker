# Laravel REST API in Docker Compose

A small token-authenticated REST API (Laravel 13, Sanctum 4, PHP 8.5, MySQL 9.7 LTS, nginx 1.30).

## Setup

```sh
# Optional: override ports, DB credentials, Xdebug (see "Configuration" below)
printf 'UID=%s\nGID=%s\n' "$(id -u)" "$(id -g)" > .env

docker compose up -d --build

cp app/laravel/.env.example app/laravel/.env
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --seed
```

The API is now available at http://localhost:8080/api.

## Endpoints

All requests should send `Accept: application/json`. Everything except `login` requires an
`Authorization: Bearer <token>` header.

| Method | Path                | Description                                                         |
|--------|---------------------|---------------------------------------------------------------------|
| POST   | `/api/login`        | Body: `email`, `password`, optional `device_name`. Returns a token. Rate limited to 5/min. |
| POST   | `/api/logout`       | Revokes the current token.                                          |
| GET    | `/api/user`         | The authenticated user.                                             |
| GET    | `/api/goods`        | Paginated list. Query: `search` (name substring), `per_page` (1–100), `page`. |
| GET    | `/api/goods/{id}`   | A single item, or 404.                                              |

Tokens expire after `SANCTUM_EXPIRATION` minutes (default 1440). Expired tokens are pruned by the
scheduler (`php artisan schedule:run`).

Example:

```sh
TOKEN=$(curl -s -H 'Accept: application/json' \
  -d email=test@test.com -d password=admin \
  http://localhost:8080/api/login | jq -r .token)

curl -s -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" \
  'http://localhost:8080/api/goods?search=red&per_page=10'
```

## Development credentials

`php artisan db:seed` creates the user `test@test.com` / `admin` and 50 sample goods.
These credentials, and the default database passwords below, are for local development only.

## Configuration

Docker Compose reads an optional `.env` file in the repository root:

| Variable           | Default   | Purpose                                         |
|--------------------|-----------|-------------------------------------------------|
| `APP_PORT`         | `8080`    | Host port for nginx                             |
| `UID` / `GID`      | `1000`    | Host user the PHP container runs as             |
| `DB_DATABASE`      | `laravel` | MySQL database (must match `app/laravel/.env`)  |
| `DB_USERNAME`      | `laravel` | MySQL user (must match `app/laravel/.env`)      |
| `DB_PASSWORD`      | `secret`  | MySQL password (must match `app/laravel/.env`)  |
| `DB_ROOT_PASSWORD` | `root`    | MySQL root password                             |
| `XDEBUG_MODE`      | `off`     | Set to `debug` to enable Xdebug (port 9003, trigger mode) |

Changes to `UID`/`GID` require `docker compose build php`.
