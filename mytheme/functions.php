<?php
/**
 * MyTheme — generic Kadence child theme bootstrap.
 *
 * This file is intentionally minimal: theme supports, textdomain, and
 * enqueueing of the override stylesheet. Add page-specific code below
 * or in dedicated files inside /inc/ as the site grows.
 *
 * @package MyTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MYTHEME_VERSION', '1.0.0' );
define( 'MYTHEME_TEXTDOMAIN', 'mytheme' );

/**
 * Theme setup: textdomain + sensible supports for a block-theme child.
 */
add_action( 'after_setup_theme', function () {
    load_child_theme_textdomain(
        MYTHEME_TEXTDOMAIN,
        get_stylesheet_directory() . '/languages'
    );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
} );

/**
 * Enqueue parent + child styles.
 *
 * Kadence enqueues its own stylesheet on the `wp_enqueue_scripts` hook.
 * We depend on it so the child overrides.css loads AFTER it.
 */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'mytheme-overrides',
        get_stylesheet_directory_uri() . '/styles/overrides.css',
        array( 'kadence-global' ),
        filemtime( get_stylesheet_directory() . '/style/override.css' )
    );
}, 20 );
