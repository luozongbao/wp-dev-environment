<?php
/**
 * MyPlugin uninstall script.
 *
 * Fired when the plugin is deleted in WP Admin → Plugins. Removes any
 * options the plugin created. NOTE: this runs only on full delete,
 * NOT on simple deactivation.
 *
 * @package MyPlugin
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'myplugin_settings' );
