<?php
if ( !defined('WP_UNINSTALL_PLUGIN')){
    exit;
}

if (false === get_option( 'plugin_videograph_access_token_id')) {
    delete_option('plugin_videograph_access_token_id');
}
if (false === get_option('plugin_videograph_secret_id')) {
    delete_option('plugin_videograph_secret_id');
}