<?php
/*
 * Plugin Name: videograph
 * Plugin URI: https://videograph.ai/
 * Description: Accessing videos from videograph.ai
 * Version: 1.0
 * Author: videograph
 * Author URI: https://videograph.ai/
 * 
 * 
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: videograph
 * Domain Path: where to find the translation files (see How to Internationalize Your Plugin)
 */
// Define constants
define('VIDEOGRAPH_DIR', plugin_dir_path(__FILE__));
define('VIDEOGRAPH_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once(VIDEOGRAPH_DIR . 'includes/vg-library.php');
require_once(VIDEOGRAPH_DIR . 'includes/vg-add-new-video.php');
require_once(VIDEOGRAPH_DIR . 'includes/vg-settings.php');
require_once(VIDEOGRAPH_DIR . 'includes/vg-live-stream.php');
require_once(VIDEOGRAPH_DIR . 'includes/vg-live-stream-videos.php');
require_once(VIDEOGRAPH_DIR . 'includes/vg-live-recording-videos.php');
require_once(VIDEOGRAPH_DIR . 'includes/vg-upload-new-video.php');


register_activation_hook(__FILE__, 'vg_set_default_options');

function vg_set_default_options()
{
    if (false === get_option('vg_access_token')) {
        add_option('vg_access_token', '');
    }
    if (false === get_option('vg_secret_key')) {
        add_option('vg_secret_key', '');
    }
}
// Add the plugin to the WordPress menu
function videograph_menu()
{
    add_menu_page(
        'Videograph AI',
        'Videograph AI',
        'manage_options',
        'vg-library',
        'vg_library',
        plugin_dir_url(__FILE__) . 'assets/wp-vg-icon-new.svg',
        25
    );

    add_submenu_page(
        'vg-library',
        'Videos',
        'Videos',
        'manage_options',
        'vg-library',
        'vg_library',
    );

    add_submenu_page(
        'vg-library',
        'Add New Video',
        'Add New Video',
        'manage_options',
        'vg-add-new-video',
        'vg_add_new_video'
    );

    add_submenu_page(
        'vg-library',
        'Upload New Video',
        'Upload New Video',
        'manage_options',
        'vg-upload-new-video',
        'vg_upload_new_video'
    );

    add_submenu_page(
        'vg-library',
        'Create Live Stream',
        'Create Live Stream',
        'manage_options',
        'vg-live-stream',
        'vg_live_stream'
    );
    add_submenu_page(
        'vg-library',
        'Live Stream',
        'Live Stream',
        'manage_options',
        'vg-live-stream-videos',
        'vg_live_stream_videos'
    );

    add_submenu_page(
        'vg-library',
        'Live Recording',
        'Live Recording',
        'manage_options',
        'vg-live-recording-videos',
        'vg_live_recording_videos'
    );

    add_submenu_page(
        'vg-library',
        'Settings',
        'Settings',
        'manage_options',
        'vg-settings',
        'vg_settings'
    );
}

add_action('admin_menu', 'videograph_menu');
add_action('admin_post_create_post_action', 'vg_add_new_video_callback');
add_action('admin_post_create_post_action', 'vg_upload_video_callback');
add_action('admin_post_create_post_action', 'vg_library_pagination');

function vg_enqueue_scripts() {
    // Enqueue CSS
    wp_enqueue_style( 'vg-style', plugin_dir_url( __FILE__ ) . 'assets/css/vg-style.css', array(), '6.4.1' );

    // Enqueue JS
    wp_enqueue_script( 'vg-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/vg-scripts.js', array(), '1.0.1', true );
    //wp_enqueue_script( 'jquery-script', 'https://code.jquery.com/jquery-3.6.0.min.js', array(), '3.6.0', true );
    wp_enqueue_style( 'custom-svg-icon', plugin_dir_url( __FILE__ ) . 'assets/wp-vg-icon-new.svg', array(), '1.0' );

    //wp_enqueue_script('videograph-progress', plugin_dir_url( __FILE__ ) . 'assets/js/progress.js', array('jquery'), '1.0', true);
}
add_action( 'admin_enqueue_scripts', 'vg_enqueue_scripts' );

function vg_enqueue_jquery_ui() {
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'vg_enqueue_jquery_ui');

function enqueue_custom_scripts() {
    wp_enqueue_script('custom-upload-script', plugin_dir_url(__FILE__) . 'assets/js/custom-upload.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'vg_enqueue_custom_scripts');

