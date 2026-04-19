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

$settings = get_option( 'element_forge_settings', [] );

if ( is_array( $settings ) && isset( $settings['remove_data_on_uninstall'] ) && 'yes' === $settings['remove_data_on_uninstall'] ) {
	delete_option( 'element_forge_settings' );
}
