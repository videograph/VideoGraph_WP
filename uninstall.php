<?php
if ( !defined('WP_UNINSTALL_PLUGIN')){
    exit;
}

if (false === get_option( 'videograph_access_token')) {
    delete_option('videograph_access_token');
}
if (false === get_option('videograph_secret_key')) {
    delete_option('videograph_secret_key');
}
