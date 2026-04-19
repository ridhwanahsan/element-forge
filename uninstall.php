<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file is executed when a user clicks "Delete" in the WordPress plugins area.
 * It is required by WordPress.org to clean up any orphaned datatables or wp_options settings.
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clear out our saved core settings so we don't leave ghost data in their database
delete_option( 'element_forge_settings' );
