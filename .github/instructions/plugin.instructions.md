---
description: "WordPress plugin conventions for myplugin/. Use when editing plugin PHP, adding hooks, shortcodes, admin pages, settings, or uninstall cleanup."
applyTo: "myplugin/**"
---

# MyPlugin — plugin starter

## Structure

| Path | Purpose |
| --- | --- |
| `myplugin/myplugin.php` | Plugin header, `MYPLUGIN_*` constants, hooks, shortcode, admin page |
| `myplugin/uninstall.php` | Cleanup — runs **only** on plugin delete, never on deactivate |
| `myplugin/languages/` | `.mo` / `.po` translation files |
| `myplugin/README.md` | User-facing plugin docs |

## Conventions

- **Prefix every global symbol** with `myplugin_`: functions (`myplugin_activate`),
  shortcodes (`[myplugin_greeting]`), options (`myplugin_settings`), and settings groups
  (`myplugin_settings_group`).
- **Constants live in one block** at the top of `myplugin.php`: `MYPLUGIN_VERSION`, `MYPLUGIN_FILE`,
  `MYPLUGIN_DIR`, `MYPLUGIN_URL`, `MYPLUGIN_BASENAME`, `MYPLUGIN_TEXTDOMAIN`. Add new ones there.
- **Hook order matters:**
  - `plugins_loaded` → `load_plugin_textdomain`
  - `init` → register shortcodes, post types, taxonomies, block types
  - `admin_menu` → add admin pages
  - `admin_init` → `register_setting` / `add_settings_section`
  - activation / deactivation → `register_activation_hook` / `register_deactivation_hook`
- **Capability-check admin pages** (`current_user_can( 'manage_options' )`) and **sanitize on save**
  (`sanitize_text_field`, `sanitize_email`, …) inside the `sanitize_callback`.
- **Escape on output** (`esc_html`, `esc_attr`, `esc_url`) and translate with the `myplugin` text domain.
- **Guard direct file access:** `if ( ! defined( 'ABSPATH' ) ) { exit; }` in `myplugin.php`,
  `if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }` in `uninstall.php`.
- **Keep `Version:` in the plugin header and `MYPLUGIN_VERSION` in sync.**
- Split larger features into extra files inside `myplugin/` and `require_once` them from `myplugin.php`.

## Gotchas

- **Never delete options, custom tables, post meta, or scheduled events in the deactivation hook.**
  Destructive cleanup belongs in `uninstall.php`, which WordPress only runs on full deletion.
- The container path is `/wp-content/plugins/MyPlugin` (capital M, capital P) while the host folder is
  `myplugin/` — the rename table in the root README maps between them.
- There is no build step, no Composer autoloader, and no test suite for this plugin — do not assume one
  exists or add one uninvited.
