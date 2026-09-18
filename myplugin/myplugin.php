<?php
/**
 * Plugin Name:       MyPlugin
 * Plugin URI:        https://example.com/myplugin
 * Description:       A minimal, reusable WordPress plugin starter.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       myplugin
 * Domain Path:       /languages
 *
 * @package MyPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* --------------------------------------------------------------------- */
/* Constants                                                              */
/* --------------------------------------------------------------------- */

define( 'MYPLUGIN_VERSION',    '1.0.0' );
define( 'MYPLUGIN_FILE',       __FILE__ );
define( 'MYPLUGIN_DIR',        plugin_dir_path( __FILE__ ) );
define( 'MYPLUGIN_URL',        plugin_dir_url( __FILE__ ) );
define( 'MYPLUGIN_BASENAME',   plugin_basename( __FILE__ ) );
define( 'MYPLUGIN_TEXTDOMAIN', 'myplugin' );

/* --------------------------------------------------------------------- */
/* Lifecycle hooks                                                        */
/* --------------------------------------------------------------------- */

register_activation_hook( __FILE__, 'myplugin_activate' );
register_deactivation_hook( __FILE__, 'myplugin_deactivate' );

/**
 * Activation handler. Set up default options, flush rewrite rules, etc.
 */
function myplugin_activate(): void {
    if ( false === get_option( 'myplugin_settings' ) ) {
        add_option( 'myplugin_settings', array(
            'enabled' => 1,
            'message' => 'Hello from MyPlugin!',
        ) );
    }

    flush_rewrite_rules();
}

/**
 * Deactivation handler. Note: do NOT delete options/tables here —
 * use a separate uninstall.php for destructive cleanup.
 */
function myplugin_deactivate(): void {
    flush_rewrite_rules();
}

/* --------------------------------------------------------------------- */
/* Load textdomain                                                        */
/* --------------------------------------------------------------------- */

add_action( 'plugins_loaded', function () {
    load_plugin_textdomain(
        MYPLUGIN_TEXTDOMAIN,
        false,
        dirname( MYPLUGIN_BASENAME ) . '/languages'
    );
} );

/* --------------------------------------------------------------------- */
/* Bootstrap                                                              */
/* --------------------------------------------------------------------- */

add_action( 'init', function () {
    // Register shortcodes, post types, taxonomies, block types here.
    // Example shortcode: [myplugin_greeting]
    add_shortcode( 'myplugin_greeting', 'myplugin_render_greeting' );
} );

/**
 * Sample shortcode renderer.
 *
 * Usage: [myplugin_greeting message="Custom hello"]
 */
function myplugin_render_greeting( $atts = array() ): string {
    $defaults = array(
        'message' => get_option( 'myplugin_settings', array() )['message'] ?? 'Hello from MyPlugin!',
    );
    $atts     = shortcode_atts( $defaults, $atts, 'myplugin_greeting' );

    return sprintf(
        '<div class="myplugin-greeting">%s</div>',
        esc_html( (string) $atts['message'] )
    );
}

/* --------------------------------------------------------------------- */
/* Admin UI (optional sample)                                             */
/* --------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
    add_submenu_page(
        'options-general.php',
        __( 'MyPlugin Settings', MYPLUGIN_TEXTDOMAIN ),
        __( 'MyPlugin', MYPLUGIN_TEXTDOMAIN ),
        'manage_options',
        'myplugin',
        'myplugin_render_settings_page'
    );
} );

/**
 * Render the settings page.
 */
function myplugin_render_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $settings = get_option( 'myplugin_settings', array() );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'MyPlugin Settings', MYPLUGIN_TEXTDOMAIN ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'myplugin_settings_group' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="myplugin_message"><?php esc_html_e( 'Greeting message', MYPLUGIN_TEXTDOMAIN ); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="myplugin_message"
                            name="myplugin_settings[message]"
                            value="<?php echo esc_attr( $settings['message'] ?? '' ); ?>"
                            class="regular-text"
                        />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init', function () {
    register_setting( 'myplugin_settings_group', 'myplugin_settings', array(
        'type'              => 'array',
        'sanitize_callback' => function ( $input ) {
            $out          = array();
            $out['message'] = isset( $input['message'] ) ? sanitize_text_field( $input['message'] ) : '';

            return $out;
        },
    ) );
} );
