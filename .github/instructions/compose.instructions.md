---
description: "Docker Compose and environment conventions for wp-dev-environment. Use when editing docker-compose.yml, .env / .env.example, php.ini, or scripts/init-env.sh."
applyTo: ["docker-compose.yml", ".env*", "scripts/**"]
---

# Docker Compose & environment configuration

## Services

`wordpress` (WordPress 6 / PHP 8.3 / Apache), `database` (MySQL 8.0), `phpmyadmin`.
All three live on the `app_network` bridge network; `wordpress` waits for `database` to be `healthy`.

## Rules

- **Never hardcode names, ports, or credentials in `docker-compose.yml`.** Every one is templated from
  `.env` via `${VAR:-default}`:
  `PROJECT_NAME`, `DOMAIN`, `PORT`, `PHPMYADMIN_PORT`, `DATABASENAME`, `DATABASEUSER`, `DATABASEPASS`,
  `MYSQL_ROOT_PASSWORD`.
- **`PROJECT_NAME` must match `^[a-zA-Z0-9_-]+$`.** It prefixes the container names
  (`${PROJECT_NAME}-wordpress-app`), the network (`${PROJECT_NAME}_network`), and the volumes
  (`${PROJECT_NAME}_wordpress_files`, `${PROJECT_NAME}_db_data`). This is what lets several WordPress
  environments coexist on one machine.
- **Adding a new variable means updating both `.env.example` and `.env`.** `.env` is gitignored and
  never committed; `.env.example` is the tracked template. Keep the same ordering and comment style.
- **Changing a mount target means changing the host folder too.** The current mapping is
  `./mytheme/` → `.../themes/MyTheme` and `./myplugin/` → `.../plugins/MyPlugin`.
- **Keep `WORDPRESS_CONFIG_EXTRA` and `php.ini` in sync** — the upload limits appear in both
  (`WORDPRESS_CONFIG_EXTRA` sets `upload_max_filesize`/`post_max_size`/`max_execution_time`/
  `max_input_time`, and `php.ini` is mounted at `/usr/local/etc/php/conf.d/uploads.ini`).
- **Restart vs rebuild:** `docker compose up -d` recreates containers but the WordPress core lives in a
  named volume, so core files are preserved. `docker compose down -v` deletes the database and uploads —
  confirm with the user first.
- `scripts/init-env.sh` derives `PROJECT_NAME` from the repository directory name (lowercased, non
  `[a-z0-9_-]` characters replaced with `-`). It updates `PROJECT_NAME` in place in `.env` if the file
  already exists. Keep `set -euo pipefail` and the `derive_name()` helper intact when editing it.

## Verifying a change

```bash
docker compose config --services          # service list
docker compose config | grep container_name  # resolved container names for the current PROJECT_NAME
docker compose up -d && docker compose ps
docker compose logs -f wordpress
```
