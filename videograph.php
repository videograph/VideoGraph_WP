<?php
/*
 * Plugin Name: videograph
 * Plugin URI: https://github.com/videograph/VideoGrpah_WP
 * Description: <a target="_blank" href="https://videograph.ai">videograph.ai</a> provides video infrastructure for product builders. Use <a target="_blank" href="https://videograph.ai">videograph.ai</a> for integrating, scaling, and managing on-demand & low latency live streaming features in your WordPress site.
 * Version: 1.0
 * Author: videograph.ai
 * Author URI: https://videograph.ai/
 * License: GPL-2.0-or-later
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: videograph
 * Domain Path: /languages
 */

// Ensure this file is not accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'VIDEOGRAPH_DIR', plugin_dir_path( __FILE__ ) );
define( 'VIDEOGRAPH_URL', plugin_dir_url( __FILE__ ) );
define( 'VIDEOGRAPH_PLUGIN_PREFIX', 'videograph' );

// Load plugin text domain for translations
function videograph_load_textdomain() {
    load_plugin_textdomain( 'videograph', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'videograph_load_textdomain' );

// Include necessary files
require_once( VIDEOGRAPH_DIR . 'includes/vg-library.php' );
require_once( VIDEOGRAPH_DIR . 'includes/vg-add-new-video.php' );
require_once( VIDEOGRAPH_DIR . 'includes/vg-settings.php' );
require_once( VIDEOGRAPH_DIR . 'includes/vg-live-stream.php' );
require_once( VIDEOGRAPH_DIR . 'includes/vg-live-stream-videos.php' );
require_once( VIDEOGRAPH_DIR . 'includes/vg-live-recording-videos.php' );

// Set default options on plugin activation
register_activation_hook( __FILE__, 'videograph_set_default_options' );
function videograph_set_default_options() {
    if ( false === get_option( 'videograph_access_token' ) ) {
        add_option( 'videograph_access_token', '' );
    }
    if ( false === get_option( 'videograph_secret_key' ) ) {
        add_option( 'videograph_secret_key', '' );
    }
}

// Register the plugin menu
function videograph_add_menu() {
    add_menu_page(
        __( 'Videograph AI', 'videograph' ),
        __( 'Videograph AI', 'videograph' ),
        'manage_options',
        'videograph-video-library',
        'videograph_video_library',
        plugin_dir_url( __FILE__ ) . 'assets/wp-vg-icon-new.svg',
        25
    );

    add_submenu_page(
        'videograph-video-library',
        __( 'Videos', 'videograph' ),
        __( 'Videos', 'videograph' ),
        'manage_options',
        'videograph-video-library',
        'videograph_video_library'
    );

    add_submenu_page(
        'videograph-video-library',
        __( 'Add New Video', 'videograph' ),
        __( 'Add New Video', 'videograph' ),
        'manage_options',
        'videograph-add-new-video',
        'videograph_add_new_video'
    );

    add_submenu_page(
        'videograph-video-library',
        __( 'Create Live Stream', 'videograph' ),
        __( 'Create Live Stream', 'videograph' ),
        'manage_options',
        'videograph-create-livestream',
        'videograph_create_livestream'
    );

    add_submenu_page(
        'videograph-video-library',
        __( 'Live Stream', 'videograph' ),
        __( 'Live Stream', 'videograph' ),
        'manage_options',
        'videograph-livestreams',
        'videograph_livestreams'
    );

    add_submenu_page(
        'videograph-video-library',
        __( 'Live Recording', 'videograph' ),
        __( 'Live Recording', 'videograph' ),
        'manage_options',
        'videograph-live-recordings',
        'videograph_live_recordings'
    );

    add_submenu_page(
        'videograph-video-library',
        __( 'Settings', 'videograph' ),
        __( 'Settings', 'videograph' ),
        'manage_options',
        'videograph-settings',
        'videograph_settings'
    );
}
add_action( 'admin_menu', 'videograph_add_menu' );

// Handle form submissions
add_action( 'admin_post_create_post_action', 'videograph_add_new_video_callback' );
add_action( 'admin_post_create_post_action', 'videograph_library_pagination' );

// Enqueue styles and scripts for plugin pages only
function videograph_enqueue_scripts( $hook ) {
    if ( strpos( $hook, 'videograph' ) === false ) return;

    wp_enqueue_style( VIDEOGRAPH_PLUGIN_PREFIX . '-style', plugin_dir_url( __FILE__ ) . 'assets/css/vg-style.css', [], '1.0.1' );
    wp_enqueue_script( VIDEOGRAPH_PLUGIN_PREFIX . '-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/vg-scripts.js', [ 'jquery' ], '1.0.1', true );
}
add_action( 'admin_enqueue_scripts', 'videograph_enqueue_scripts' );

// Enqueue scripts for the live stream page
function videograph_enqueue_live_stream_scripts( $hook ) {
    if ( $hook === 'videograph-video-library_page_videograph-create-livestream' ) {
        wp_enqueue_script(
            VIDEOGRAPH_PLUGIN_PREFIX . '-livestream-js',
            plugin_dir_url( __FILE__ ) . 'assets/js/create-livestream.js',
            [ 'jquery' ],
            '1.0',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'videograph_enqueue_live_stream_scripts' );

function videograph_enqueue_frontend_styles() {
    if (!is_admin()) {
        wp_enqueue_style('videograph-global-styles', plugin_dir_url(__FILE__) . 'assets/css/vg-global-style.css', [], '1.0.1');
    }
}
add_action('wp_enqueue_scripts', 'videograph_enqueue_frontend_styles');
