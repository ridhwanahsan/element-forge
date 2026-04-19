<?php
/**
 * Plugin Name: ElementForge
 * Description: A lightweight, modular Elementor widgets suite with a React-powered dashboard to control exactly which widgets load on your site.
 * Plugin URI: https://wordpress.org/plugins/element-forge/
 * Version: 1.0.0
 * Author: ridhwanahsan
 * Author URI: https://profiles.wordpress.org/ridhwanahsan/
 * Text Domain: element-forge
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Elementor tested up to: 3.20.0
 * Elementor Pro tested up to: 3.20.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define core constants.
define( 'ELEMENT_FORGE_VERSION', '1.0.0' );
define( 'ELEMENT_FORGE_FILE', __FILE__ );
define( 'ELEMENT_FORGE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ELEMENT_FORGE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main ElementForge Class
 */
final class ElementForge {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init() {
		// Include required files
		$this->includes();

		// Initialize hooks
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	private function includes() {
		// Admin Dashboard Page (React)
		require_once ELEMENT_FORGE_PATH . 'includes/class-elementforge-admin-page.php';

		// REST API Routes
		require_once ELEMENT_FORGE_PATH . 'includes/class-elementforge-rest-api.php';

		// Elementor Extension (Loaded later)
		require_once ELEMENT_FORGE_PATH . 'includes/class-elementforge-elementor.php';

		// Elementor Template Library Sync
		require_once ELEMENT_FORGE_PATH . 'includes/template-library/class-elementforge-library-manager.php';

		// Theme Builder (CPT and display logic)
		require_once ELEMENT_FORGE_PATH . 'includes/class-elementforge-theme-builder.php';
	}

	public function on_plugins_loaded() {
		// Initialize the admin page which loads the React App in the backend
		if ( is_admin() ) {
			new ElementForge_Admin_Page();
		}

		// Initialize REST API
		new ElementForge_REST_API();

		// Theme Builder CPT must always be registered (regardless of Elementor)
		// so the admin menu link and CPT are always available.
		ElementForge_Theme_Builder::get_instance();

		// Check if Elementor is installed and activated
		if ( did_action( 'elementor/loaded' ) ) {
			// Initialize Elementor widgets and controls
			ElementForge_Elementor::get_instance();
			// Initialize Template Library Sync
			ElementForge_Library_Manager::instance();
		} else {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
		}
	}

	public function admin_notice_missing_elementor() {
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'element-forge' ),
			'<strong>' . esc_html__( 'ElementForge', 'element-forge' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'element-forge' ) . '</strong>'
		);
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			wp_kses_post( $message )
		);
	}
}

// Initialize the plugin.
ElementForge::instance();

