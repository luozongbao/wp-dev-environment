# MyPlugin

A minimal, reusable WordPress plugin starter. Lives under `/wp-content/plugins/MyPlugin/` in this dev environment.

## Features

- Standard WordPress plugin header (PHP 8.0+, WP 6.0+)
- Activation / deactivation hooks
- Uninstall script for clean option removal
- Example shortcode: `[myplugin_greeting message="Hello!"]`
- Example settings page under **Settings → MyPlugin**

## Folder layout

```
myplugin/
├── myplugin.php        # Main plugin file (header + bootstrap)
├── uninstall.php       # Runs only on plugin deletion
├── languages/          # Translation .mo/.po files (optional)
└── README.md           # This file
```

## Extending

Add new functionality by:

1. Defining additional constants alongside the existing `MYPLUGIN_*` block in `myplugin.php`.
2. Registering hooks on `init`, `admin_init`, `plugins_loaded`, etc.
3. Splitting larger features into files inside `myplugin/` and requiring them from `myplugin.php`.

## Uninstall

When the plugin is deleted via the Plugins screen, `uninstall.php` removes the `myplugin_settings` option. Add other cleanup here (custom tables, post meta, scheduled events).
