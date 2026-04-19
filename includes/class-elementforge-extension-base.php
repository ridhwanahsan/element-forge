<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class ElementForge_Extension_Base {

	public function __construct() {
		$this->register_assets();
		$this->register_hooks();
	}

	abstract protected function get_slug();

	abstract public function register_extension_controls( $element, $args );

	abstract protected function apply_render_attributes( $element, $settings );

	protected function register_hooks() {
		foreach ( $this->get_control_hooks() as $hook ) {
			add_action( $hook, [ $this, 'register_extension_controls' ], 10, 2 );
		}

		foreach ( $this->get_render_hooks() as $hook ) {
			add_action( $hook, [ $this, 'maybe_render_extension' ] );
		}
	}

	protected function get_control_hooks() {
		$hooks = [
			'elementor/element/section/section_advanced/after_section_end',
			'elementor/element/container/section_layout/after_section_end',
		];

		if ( $this->supports_widget_controls() ) {
			$hooks[] = 'elementor/element/common/_section_style/after_section_end';
		}

		return $hooks;
	}

	protected function get_render_hooks() {
		$hooks = [
			'elementor/frontend/section/before_render',
			'elementor/frontend/container/before_render',
		];

		if ( $this->supports_widget_render() ) {
			$hooks[] = 'elementor/frontend/widget/before_render';
		}

		return $hooks;
	}

	protected function supports_widget_controls() {
		return false;
	}

	protected function supports_widget_render() {
		return $this->supports_widget_controls();
	}

	public function maybe_render_extension( $element ) {
		$settings   = $element->get_settings_for_display();
		$enable_key = $this->get_setting_key( 'enable' );

		if ( empty( $settings[ $enable_key ] ) || 'yes' !== $settings[ $enable_key ] ) {
			return;
		}

		$this->enqueue_assets();
		$this->apply_render_attributes( $element, $settings );
	}

	protected function register_assets() {
		$style_path = $this->get_extension_dir() . 'style.css';
		if ( file_exists( $style_path ) ) {
			wp_register_style(
				$this->get_asset_handle( 'style' ),
				$this->get_extension_url() . 'style.css',
				[],
				$this->get_asset_version( $style_path )
			);
		}

		$script_path = $this->get_extension_dir() . 'script.js';
		if ( file_exists( $script_path ) ) {
			wp_register_script(
				$this->get_asset_handle( 'script' ),
				$this->get_extension_url() . 'script.js',
				[],
				$this->get_asset_version( $script_path ),
				true
			);
		}
	}

	protected function enqueue_assets() {
		$style_handle  = $this->get_asset_handle( 'style' );
		$script_handle = $this->get_asset_handle( 'script' );

		if ( wp_style_is( $style_handle, 'registered' ) ) {
			wp_enqueue_style( $style_handle );
		}

		if ( wp_script_is( $script_handle, 'registered' ) ) {
			wp_enqueue_script( $script_handle );
		}
	}

	protected function get_asset_handle( $type ) {
		return 'elementforge-extension-' . $this->get_slug() . '-' . $type;
	}

	protected function get_asset_version( $asset_path ) {
		$modified_time = filemtime( $asset_path );

		if ( false === $modified_time ) {
			return ELEMENT_FORGE_VERSION;
		}

		return (string) $modified_time;
	}

	protected function get_extension_dir() {
		return ELEMENT_FORGE_PATH . 'includes/extensions/' . $this->get_slug() . '/';
	}

	protected function get_extension_url() {
		return ELEMENT_FORGE_URL . 'includes/extensions/' . $this->get_slug() . '/';
	}

	protected function get_setting_key( $suffix ) {
		return 'efx_' . str_replace( '-', '_', $this->get_slug() ) . '_' . $suffix;
	}

	protected function start_extension_section( $element, $label ) {
		$element->start_controls_section(
			$this->get_setting_key( 'section' ),
			[
				'label' => $label,
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);
	}

	protected function add_wrapper_attributes( $element, $classes = [], $data = [], $styles = [] ) {
		$attributes = [];

		$classes = array_values( array_filter( array_map( 'sanitize_html_class', (array) $classes ) ) );
		if ( ! empty( $classes ) ) {
			$attributes['class'] = $classes;
		}

		foreach ( $data as $key => $value ) {
			if ( null === $value || '' === $value ) {
				continue;
			}

			$attributes[ 'data-' . sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
		}

		$style_attribute = $this->build_style_attribute( $styles );
		if ( '' !== $style_attribute ) {
			$attributes['style'] = $style_attribute;
		}

		if ( ! empty( $attributes ) ) {
			$element->add_render_attribute( '_wrapper', $attributes );
		}
	}

	protected function build_style_attribute( $styles ) {
		$output = [];

		foreach ( (array) $styles as $property => $value ) {
			if ( null === $value || '' === $value ) {
				continue;
			}

			$property = preg_replace( '/[^a-zA-Z0-9\-_]/', '', (string) $property );
			if ( '' === $property ) {
				continue;
			}

			$output[] = $property . ':' . sanitize_text_field( (string) $value );
		}

		if ( empty( $output ) ) {
			return '';
		}

		return implode( ';', $output ) . ';';
	}

	protected function get_text_setting( $settings, $key, $default = '' ) {
		if ( ! isset( $settings[ $key ] ) ) {
			return $default;
		}

		$value = sanitize_text_field( (string) $settings[ $key ] );

		return '' !== $value ? $value : $default;
	}

	protected function get_int_setting( $settings, $key, $default = 0, $min = 0, $max = null ) {
		$value = isset( $settings[ $key ] ) ? (int) $settings[ $key ] : (int) $default;
		$value = max( (int) $min, $value );

		if ( null !== $max ) {
			$value = min( (int) $max, $value );
		}

		return $value;
	}

	protected function get_float_setting( $settings, $key, $default = 0.0, $min = 0.0, $max = null ) {
		$value = isset( $settings[ $key ] ) ? (float) $settings[ $key ] : (float) $default;
		$value = max( (float) $min, $value );

		if ( null !== $max ) {
			$value = min( (float) $max, $value );
		}

		return $value;
	}

	protected function get_color_setting( $settings, $key, $default = '#ffffff' ) {
		if ( empty( $settings[ $key ] ) ) {
			return $default;
		}

		$color = sanitize_hex_color( $settings[ $key ] );

		return $color ? $color : $default;
	}
}
