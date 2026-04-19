<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Ajax_Load_More_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'ajax-load-more';
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'AJAX Load More', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Load More', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'initial_items' ),
			[
				'label'     => esc_html__( 'Initial Items', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'default'   => 3,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'step' ),
			[
				'label'     => esc_html__( 'Items Per Click', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'default'   => 3,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'button_text' ),
			[
				'label'     => esc_html__( 'Button Text', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Load More', 'element-forge' ),
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	protected function apply_render_attributes( $element, $settings ) {
		$this->add_wrapper_attributes(
			$element,
			[ 'efx-load-more' ],
			[
				'initial-items' => $this->get_int_setting( $settings, $this->get_setting_key( 'initial_items' ), 3, 1, 24 ),
				'step'          => $this->get_int_setting( $settings, $this->get_setting_key( 'step' ), 3, 1, 24 ),
				'button-text'   => $this->get_text_setting( $settings, $this->get_setting_key( 'button_text' ), esc_html__( 'Load More', 'element-forge' ) ),
			]
		);
	}
}
