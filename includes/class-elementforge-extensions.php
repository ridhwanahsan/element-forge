<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Extensions {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Disabled extension slugs.
	 *
	 * @var string[]
	 */
	private $disabled_extensions = [];

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$settings = get_option( 'element_forge_settings', [] );

		$this->disabled_extensions = isset( $settings['disabled_extensions'] ) && is_array( $settings['disabled_extensions'] )
			? $settings['disabled_extensions']
			: [];

		$this->load_extensions();
	}

	private function load_extensions() {
		require_once ELEMENT_FORGE_PATH . 'includes/class-elementforge-extension-base.php';

		foreach ( glob( ELEMENT_FORGE_PATH . 'includes/extensions/*', GLOB_ONLYDIR ) as $folder ) {
			$slug = basename( $folder );

			if ( in_array( $slug, $this->disabled_extensions, true ) ) {
				continue;
			}

			$extension_file = $folder . '/extension.php';
			if ( ! file_exists( $extension_file ) ) {
				continue;
			}

			require_once $extension_file;

			$class_name = 'ElementForge_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $slug ) ) ) . '_Extension';
			if ( class_exists( $class_name ) ) {
				new $class_name();
			}
		}
	}
}
