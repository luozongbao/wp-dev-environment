# MyTheme

A generic **Kadence child theme** starter. Drop site-specific CSS into `style/overide.css` and PHP hooks into `functions.php` (or new files in `inc/`).

## Parent

- **Parent theme:** Kadence (`Template: kadence`)

## Files

| File | Purpose |
| --- | --- |
| `style.css` | Theme metadata + minimal in-file styles |
| `functions.php` | Theme supports, textdomain, enqueue hooks |
| `style/overide.css` | Site-specific CSS overrides |
| `languages/` | Translation `.mo` / `.po` files |

## Quick start

1. Make sure the **Kadence** parent theme is installed and active.
2. Activate **MyTheme** from `Appearance → Themes`.
3. Add your CSS overrides to `style/overide.css`.
4. Add PHP hooks to `functions.php` (or include files under `inc/`).
5. Replace `screenshot.png` with a 1200×900 preview image of your site.

> Tip: keep page-specific logic in `inc/` once `functions.php` starts growing past ~100 lines.
