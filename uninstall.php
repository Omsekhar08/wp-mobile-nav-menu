<?php
// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete plugin settings & uploaded icons.
 */
$settings = get_option( 'wp_mnb_settings', array() );

// 1. Delete main option.
delete_option( 'wp_mnb_settings' );

// 2. Remove uploaded icon directory.
$upload_dir = wp_get_upload_dir();
$icons_dir  = trailingslashit( $upload_dir['basedir'] ) . 'wp-mobile-nav-buttons';

if ( is_dir( $icons_dir ) ) {
	wp_delete_directory( $icons_dir );
}
