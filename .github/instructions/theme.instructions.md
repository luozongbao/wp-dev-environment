---
description: "WordPress child theme conventions for mytheme/ (Kadence child). Use when editing functions.php, style.css, the override stylesheet, theme supports, or enqueueing."
applyTo: "mytheme/**"
---

# MyTheme — Kadence child theme

## Structure

| Path | Purpose |
| --- | --- |
| `mytheme/style.css` | WordPress theme header (metadata) + minimal in-file styles |
| `mytheme/functions.php` | Theme supports, textdomain, enqueues — the only PHP file today |
| `mytheme/style/override.css` | All site-specific CSS overrides |
| `mytheme/languages/` | `.mo` / `.po` translation files (created by `load_child_theme_textdomain`) |
| `mytheme/screenshot.png` | 1200×900 theme preview (currently only a `.placeholder` exists) |

## Conventions

- **Keep `functions.php` under ~100 lines.** Move page-specific logic into `mytheme/inc/*.php` and
  `require_once` those files from `functions.php`.
- **Register everything via hooks** (`after_setup_theme`, `wp_enqueue_scripts`). At file scope only
  constants are allowed — no side effects, no output.
- **Use the child-theme helpers:** `get_stylesheet_directory()` and `get_stylesheet_directory_uri()`.
  `get_template_directory()` points at the Kadence parent and is almost never what you want.
- **Enqueue the child stylesheet at priority 20 or later** so it loads after Kadence's global CSS.
  Depend on the `kadence-global` handle.
- **Bump `MYTHEME_VERSION` in `functions.php` and `Version:` in `style.css` together.**
- Escape all output (`esc_html`, `esc_attr`, `esc_url`) and translate strings with the `mytheme` text domain.
- Group CSS in `style/override.css` under short comment headers per section/brand rule, as the file
  already does.

## Gotchas

- **Broken enqueue path.** `functions.php` registers
  `get_stylesheet_directory_uri() . '/styles/overrides.css'`, but the file on disk is
  `mytheme/style/override.css`. The request 404s and the override CSS never applies. Point the enqueue
  at `/style/override.css` (keep `filemtime( get_stylesheet_directory() . '/style/override.css' )` for
  cache busting).
- **The child stylesheet will not print while Kadence is inactive** — the `kadence-global` dependency
  is unregistered, so the handle is dropped silently. Install Kadence first
  (Appearance → Themes → Add New → Kadence), then activate MyTheme.
- The parent theme (`Template: kadence`) is **not** in this repo and is not mounted — it must be installed
  through WP Admin.
- `mytheme/README.md` refers to `style/overide.css`; the real file is `style/override.css`.
