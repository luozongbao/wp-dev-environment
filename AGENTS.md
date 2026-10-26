# AGENTS.md — WordPress Dev Environment

Docker Compose WordPress stack (WordPress 6 / PHP 8.3 / MySQL 8.0 / phpMyAdmin) with a live-mounted
Kadence child theme (`mytheme/`) and a plugin starter (`myplugin/`).

Full service, volume, and credential documentation: [README.md](README.md)

---

## Environment rules (read before running anything)

**The host has no `php`, no `node`, and no WP-CLI.** `php -l file.php` on the host fails with exit 127
("command not found"). Every PHP command must run inside the container:

```bash
docker compose exec -T wordpress php -l /var/www/html/wp-content/plugins/MyPlugin/myplugin.php
```

Use `-T` for non-interactive calls. The WordPress image ships **no `wp` binary** — use the WP Admin UI
for activation/settings, and `docker compose exec database mysql ...` for data work.

## Common commands

| Purpose | Command |
| --- | --- |
| Start / stop | `docker compose up -d` / `docker compose down` |
| Status | `docker compose ps` |
| Site | <http://localhost:8081> (first run → install wizard) |
| phpMyAdmin | <http://localhost:8082> |
| Tail Apache/PHP errors | `docker compose logs -f wordpress` |
| Restart one service | `docker compose restart wordpress` |
| Lint all project PHP | `docker compose exec -T wordpress sh -lc 'for f in $(find /var/www/html/wp-content/themes/MyTheme /var/www/html/wp-content/plugins/MyPlugin -name "*.php"); do php -l "$f" || exit 1; done'` |

The repeatable version of this workflow lives in the skill: [`.github/skills/wp-dev-workflow/SKILL.md`](.github/skills/wp-dev-workflow/SKILL.md).

## Layout: host path → container path

| Host path | Container path | Service |
| --- | --- | --- |
| `mytheme/` | `/var/www/html/wp-content/themes/MyTheme` | `wordpress` |
| `myplugin/` | `/var/www/html/wp-content/plugins/MyPlugin` | `wordpress` |
| `php.ini` | `/usr/local/etc/php/conf.d/uploads.ini` (read-only) | `wordpress` |

Mounts are `:rw` and live — edits appear in the container immediately. **Never rebuild or restart to
pick up a PHP/CSS change.**

Container/volume/network names are prefixed with `PROJECT_NAME` from `.env` (currently `dev` →
`dev-wordpress-app`). Prefer `docker compose exec <service>` over hardcoded container names so
commands keep working when `PROJECT_NAME` changes.

## No build step, no test suite

There is no `package.json`, `composer.json`, PHPUnit, or any test runner in this repo — and none is
expected. Do not add one unless asked. Verification means: `php -l` inside the container, then check
the rendered page in a browser.

## Known pitfalls

- **The Kadence parent theme is not installed.** `mytheme/` is a child theme (`Template: kadence`),
  so MyTheme cannot be activated until Kadence is installed via WP Admin → Appearance → Themes →
  Add New. The container ships only the bundled `twentytwenty*` themes.
- **The override stylesheet path does not match the file on disk.** `mytheme/functions.php` enqueues
  `get_stylesheet_directory_uri() . '/styles/overrides.css'`, but the file is `mytheme/style/override.css`
  (singular directory *and* singular filename). The enqueue 404s, so the child CSS never loads. Fix the
  enqueue path to `/style/override.css`.
- **The child stylesheet depends on the `kadence-global` handle**, so it is not printed at all while
  the Kadence parent is inactive — dependency resolution drops it, no error is shown.
- **Doc drift:** the root README still says `./myplugins/` (the real folder is `myplugin/`) and
  `mytheme/README.md` says `style/overide.css` (the real file is `style/override.css`). Treat the code
  as source of truth and fix the docs when working in those areas.
- **`.env` is gitignored.** Never commit it or paste real credentials into tracked files; use
  [`.env.example`](.env.example) as the template.
- **`docker compose down -v` destroys the database and uploads.** Confirm with the user before running it.

## Conventions

- The active branch is `dev` (`main` is the upstream default per `origin/HEAD`); `release` also exists.
  Commit to `dev` unless told otherwise.
- `PROJECT_NAME` must match `^[a-zA-Z0-9_-]+$`. `./scripts/init-env.sh [name]` regenerates `.env` from
  the folder name; run it from the repo root.
- Generic identifiers `MyTheme` / `MyPlugin` are placeholders. Renaming them means changing the tokens,
  the folder names, the `docker-compose.yml` mount targets, the `Template:` line in `mytheme/style.css`,
  and the text domains — see the "Renaming the theme / plugin" section of [README.md](README.md).
- Follow WordPress Coding Standards: escape on output (`esc_html`, `esc_attr`, `esc_url`), translate
  with the `mytheme` / `myplugin` text domain, and guard direct file access with
  `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- Keep `Version:` in `mytheme/style.css` and `MYTHEME_VERSION` in `functions.php` in sync; likewise
  `Version:` in the `myplugin.php` header and `MYPLUGIN_VERSION`.

## Scoped guidance

Load these for specific paths — they are not repeated in this file:

| File | Applies to |
| --- | --- |
| [`.github/instructions/theme.instructions.md`](.github/instructions/theme.instructions.md) | `mytheme/**` |
| [`.github/instructions/plugin.instructions.md`](.github/instructions/plugin.instructions.md) | `myplugin/**` |
| [`.github/instructions/compose.instructions.md`](.github/instructions/compose.instructions.md) | `docker-compose.yml`, `.env*`, `scripts/**` |
