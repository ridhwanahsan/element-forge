<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class ElementForge_Widget_Base extends \Elementor\Widget_Base {

	public function get_style_depends() {
		return $this->get_widget_asset_depends( 'style.css', 'style' );
	}

	public function get_script_depends() {
		return $this->get_widget_asset_depends( 'script.js', 'script' );
	}

	private function get_widget_asset_depends( $asset_file, $asset_type ) {
		$widget_slug = $this->get_widget_folder_name();

		if ( '' === $widget_slug ) {
			return [];
		}

		$asset_path = ELEMENT_FORGE_PATH . 'includes/widgets/' . $widget_slug . '/' . $asset_file;

		if ( ! file_exists( $asset_path ) ) {
			return [];
		}

		return [ 'elementforge-' . $widget_slug . '-' . $asset_type ];
	}

	private function get_widget_folder_name() {
		$reflection = new \ReflectionClass( $this );
		$file_name  = $reflection->getFileName();

		if ( ! is_string( $file_name ) || '' === $file_name ) {
			return '';
		}

		return basename( dirname( $file_name ) );
	}
}
