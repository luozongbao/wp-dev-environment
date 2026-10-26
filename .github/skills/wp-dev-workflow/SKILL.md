---
name: wp-dev-workflow
description: 'Run and debug the local WordPress Docker stack in wp-dev-environment: start/stop services, lint PHP inside the container, read Apache/PHP error logs, query MySQL, and verify theme/plugin changes in the browser. Use when any WordPress, theme, or plugin change needs local verification, or when the site on port 8081 must be brought up.'
argument-hint: 'optional stage: up | lint | logs | db | verify | reset'
---

# WordPress Dev Workflow

All PHP executes **inside the container**. The host has no `php`, no `node`, and no WP-CLI (`wp` is not
in PATH), so a bare `php -l file.php` on the host exits 127 and silently looks like a pass.

Run every command from the repository root (the folder containing `docker-compose.yml`).

---

## Stage 1 — `up`: bring the stack running

```bash
docker compose up -d
docker compose ps
```

Wait until `database` reports `healthy` before loading the site.

| URL | Service |
| --- | --- |
| <http://localhost:8081> | WordPress |
| <http://localhost:8082> | phpMyAdmin |

On a fresh volume the site returns `302` to `/wp-admin/install.php` — complete the install wizard once.
Subsequent starts reuse the `wordpress_files` and `db_data` volumes and go straight to the site.

Containers are named `${PROJECT_NAME}-wordpress-app` / `-db` / `-phpmyadmin` (currently `dev-…`).
Use `docker compose exec <service>` rather than hardcoded names.

---

## Stage 2 — `lint`: syntax-check after every edit

Single file:

```bash
docker compose exec -T wordpress php -l /var/www/html/wp-content/themes/MyTheme/functions.php
docker compose exec -T wordpress php -l /var/www/html/wp-content/plugins/MyPlugin/myplugin.php
```

Every project PHP file at once (fails fast on the first error):

```bash
docker compose exec -T wordpress sh -lc 'for f in $(find /var/www/html/wp-content/themes/MyTheme /var/www/html/wp-content/plugins/MyPlugin -name "*.php"); do php -l "$f" || exit 1; done'
```

> `-T` disables TTY allocation; omit it only for interactive shells.
> Container paths differ from host paths: host `mytheme/` is `/var/www/html/wp-content/themes/MyTheme`
> and host `myplugin/` is `/var/www/html/wp-content/plugins/MyPlugin`.

---

## Stage 3 — `logs`: diagnose a broken page

```bash
docker compose logs -f wordpress          # container stdout (Apache + entrypoint)
docker compose exec -T wordpress tail -50 /var/log/apache2/error.log
docker compose exec -T wordpress tail -50 /var/log/apache2/access.log
docker compose logs -f database           # MySQL startup / crash logs
```

Common causes of a blank or 500 page: a PHP fatal in the child theme or plugin (check `error.log`),
or a missing parent theme (see Stage 5).

---

## Stage 4 — `db`: inspect the database

The db container already exports `MYSQL_DATABASE`, `MYSQL_USER`, and `MYSQL_PASSWORD` from `.env`:

```bash
docker compose exec -T database sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'
```

One-off query:

```bash
docker compose exec -T database sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SHOW TABLES;"'
```

phpMyAdmin (<http://localhost:8082>) is the friendlier option for browsing rows.

---

## Stage 5 — `verify`: confirm the change in the browser

1. **Plugin** → Plugins screen → activate *MyPlugin*. Confirm the sample shortcode renders:
   `[myplugin_greeting message="Hi"]`.
2. **Theme** → the parent **Kadence** is *not* installed by default. Install it via
   Appearance → Themes → Add New → search *Kadence* → Install → Activate, then activate *MyTheme*.
   MyTheme cannot be activated before its parent exists.
3. **CSS overrides** → view source on the front end and look for
   `wp-content/themes/MyTheme/style/override.css`. If the request 404s, check the enqueue path in
   `mytheme/functions.php` — it currently points at `/styles/overrides.css` (plural) while the file on
   disk is `style/override.css` (singular).
4. Also confirm the stylesheet is printed at all: it depends on the `kadence-global` handle, so it is
   silently dropped while the Kadence parent is inactive.

No rebuild or restart is needed for theme/plugin edits — both folders are bind-mounted read-write.

---

## Stage 6 — `reset`: start over (destructive)

```bash
docker compose down -v     # ⚠️ deletes the database, uploads, and WordPress core volume
docker compose up -d
```

Confirm with the user before running `down -v` — it is not recoverable without a backup.
