# WordPress Development Environment

Local WordPress development environment using **Docker Compose** — with WordPress, MySQL, and phpMyAdmin preconfigured for quick setup.

Names of containers, network, and volumes are automatically prefixed with `PROJECT_NAME` (defaulting to the directory name) so **multiple WordPress environments can coexist on a single machine** without name clashes.

---

## 🚀 Services

| Service     | Port              | Container Name                       | Description                          |
| ----------- | ----------------- | ------------------------------------ | ------------------------------------ |
| WordPress   | `8081`            | `${PROJECT_NAME}-wordpress-app`      | WordPress 6 (PHP 8.3 / Apache)       |
| MySQL 8.0   | `3306` (internal) | `${PROJECT_NAME}-wordpress-db`       | Database server                      |
| phpMyAdmin  | `8082`            | `${PROJECT_NAME}-wordpress-phpmyadmin` | Web-based DB management UI        |

---

## 📋 Prerequisites

- [Docker](https://docs.docker.com/get-docker/) (>= 20.10)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2)

---

## ⚙️ Setup

### 1. Configure environment

Copy the example env file and adjust values if needed:

```bash
cp .env.example .env
```

Or use the helper script, which will auto-fill `PROJECT_NAME` from the current directory name:

```bash
./scripts/init-env.sh
```

Default values:

```env
# Used to namespace containers, network, and volumes.
# Defaults to the current directory name; override here if you want
# multiple projects on the same machine to have different prefixes.
PROJECT_NAME=wp-dev-environment

DOMAIN=web.local
PORT=8081

# Database Configuration for Docker Compose
DATABASENAME=databasename
DATABASEUSER=databaseuser
DATABASEPASS=databasepass
MYSQL_ROOT_PASSWORD=r00tP@ssw0rd
```

> 💡 The database variables (`DATABASENAME`, `DATABASEUSER`, `DATABASEPASS`, `MYSQL_ROOT_PASSWORD`) are placeholders for documenting what `docker-compose.yml` expects — change them in `.env` if you want to override the default credentials.
>
> 💡 `PROJECT_NAME` can be anything that matches `^[a-zA-Z0-9_-]+$`. Docker will create resources like `wp-dev-environment_network`, `wp-dev-environment_wordpress_data`, and containers prefixed with `wp-dev-environment-`.

### 2. Prepare custom code folders

The compose file mounts `./mytheme/` and `./myplugins/` into WordPress. Make sure these folders exist (they will be created automatically by Docker on first run if missing).

### 3. Start the stack

```bash
docker compose up -d
```

This will start WordPress, MySQL, and phpMyAdmin in the background.

### 4. Access the services

| URL                      | Service       |
| ------------------------ | ------------- |
| `http://localhost:8081`  | WordPress     |
| `http://localhost:8082`  | phpMyAdmin    |

> 💡 First-time setup: open WordPress at port `8081` and complete the installation wizard.

---

## 🗂️ Project Structure

```
.
├── docker-compose.yml      # Docker services definition
├── .env.example            # Environment variables template
├── .gitignore              # Git ignore rules
├── php.ini                 # PHP upload/size overrides (mounted into container)
├── mytheme/                # Mounted to wp-content/themes/MyTheme
├── myplugin/               # Mounted to wp-content/plugins/MyPlugin
└── docs/                   # Project documentation
```

### Volume mounts

| Host path       | Container path                                  | Purpose                          |
| --------------- | ----------------------------------------------- | -------------------------------- |
| (named volume)  | `/var/www/html`                                 | WordPress core + uploads         |
| `./mytheme/`    | `/var/www/html/wp-content/themes/MyTheme`       | Custom theme development         |
| `./myplugin/`   | `/var/www/html/wp-content/plugins/MyPlugin`     | Custom plugin development        |
| `./php.ini`     | `/usr/local/etc/php/conf.d/uploads.ini`         | PHP upload limits override       |

---

## 🔧 Useful Commands

```bash
# Start all services
docker compose up -d

# View running containers
docker compose ps

# View logs (follow mode)
docker compose logs -f

# View logs for a specific service
docker compose logs -f wordpress

# Stop all services
docker compose down

# Stop and remove volumes (⚠️ deletes DB + uploads)
docker compose down -v

# Restart a single service
docker compose restart wordpress

# Execute commands inside the WordPress container
docker compose exec wordpress bash

# Access MySQL CLI
docker compose exec db mysql -uwordpress_user -pwordpress_password wordpress_db
```

---

## 🗄️ Database Credentials

Database credentials are sourced from your `.env` file. The default fallback values (used when `.env` is missing or the variable is empty) are:

| User              | Password           | Database       |
| ----------------- | ------------------ | -------------- |
| `wordpress_user`  | `wordpress_password` | `wordpress_db` |
| `root`            | `rootpassword`     | (all)          |

To override, edit your `.env`:

```env
DATABASENAME=your_db_name
DATABASEUSER=your_db_user
DATABASEPASS=your_db_password
MYSQL_ROOT_PASSWORD=your_root_password
```

The compose file uses `${VAR:-default}` syntax, so unset variables fall back to the defaults above.

> ⚠️ These are **development-only** credentials. Change them before any production deployment.

---

## ⚙️ PHP Configuration

Custom PHP settings (mounted via `./php.ini`) increase the upload limits for media-heavy sites:

```ini
upload_max_filesize = 256M
post_max_size       = 256M
max_execution_time  = 300
max_input_time      = 300
```

Additional WordPress constants are set via `WORDPRESS_CONFIG_EXTRA`:

- `WP_HOME` / `WP_SITEURL` — derived from `.env`
- `WP_MEMORY_LIMIT` = 256M
- `WP_MAX_MEMORY_LIMIT` = 512M

---

## 🧰 Developing Theme & Plugin

This repo ships with **generic starter templates** for both the theme and the plugin. Edit them freely — they're meant to be customized.

- `mytheme/` — generic **Kadence child theme** starter (see [mytheme/README.md](mytheme/README.md))
- `myplugin/` — generic **WordPress plugin** starter (see [myplugin/README.md](myplugin/README.md))

Changes appear immediately inside the container (live mount with `:rw`). No rebuild required.

### Renaming the theme / plugin

Both starters use generic identifiers (`MyTheme`, `MyPlugin`). To rename one, do a project-wide search-and-replace for these tokens:

| Old | New |
| --- | --- |
| `MyTheme` | `YourThemeName` |
| `mytheme` | `your-theme-slug` |
| `MYTHEME_*` | `YOURTHEME_*` |
| `MyPlugin` | `YourPluginName` |
| `myplugin` | `your-plugin-slug` |
| `MYPLUGIN_*` | `YOURPLUGIN_*` |

Also update the folder names, the WordPress folder mount targets in `docker-compose.yml`, and the `Template:` line in `mytheme/style.css` if you switch parent themes.

---

## 📝 Notes

- All persistent data (WordPress files + uploads, MySQL data) lives in **named Docker volumes** (`${PROJECT_NAME}_wordpress_data`, `${PROJECT_NAME}_db_data`). These survive `docker compose down` but are removed with `docker compose down -v`. The `PROJECT_NAME` prefix prevents collisions when running multiple projects on the same machine.

### Running multiple projects side-by-side

Each project directory uses its own folder name (or any value you set in `.env`) as `PROJECT_NAME`, which Docker Compose uses to prefix:

- Containers → `${PROJECT_NAME}-wordpress-app`, `${PROJECT_NAME}-wordpress-db`, …
- Network → `${PROJECT_NAME}_network`
- Volumes → `${PROJECT_NAME}_wordpress_data`, `${PROJECT_NAME}_db_data`

To run a second WordPress site on the same host, clone the repo into a different directory (e.g. `wp-dev-environment-blog`) and run `./scripts/init-env.sh` there — Docker Compose will automatically use `wp-dev-environment-blog` as the prefix and nothing will clash.
- The repo uses a `.gitignore` that excludes `.env`, logs, and IDE/OS files.

---

## 📄 License

This project is for local development use. WordPress is licensed under the [GPL v2 or later](https://wordpress.org/about/license/).