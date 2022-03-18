<?php

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load Give file.
include_once GIVE_PLUGIN_DIR . 'give.php';

global $wpdb;

if ( give_is_setting_enabled( give_get_option( 'uninstall_on_delete' ) ) ) {

	//
}
