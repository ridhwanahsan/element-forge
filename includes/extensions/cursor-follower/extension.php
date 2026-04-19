<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Cursor_Follower_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'cursor-follower';
	}

	protected function supports_widget_controls() {
		return true;
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'Cursor Follower', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Cursor Follower', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'label' ),
			[
				'label'     => esc_html__( 'Follower Label', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Move', 'element-forge' ),
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'size' ),
			[
				'label'     => esc_html__( 'Follower Size', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 18,
				'max'       => 120,
				'default'   => 32,
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
			[ 'efx-cursor-follower' ],
			[
				'label' => $this->get_text_setting( $settings, $this->get_setting_key( 'label' ), esc_html__( 'Move', 'element-forge' ) ),
			],
			[
				'--efx-cursor-size' => $this->get_int_setting( $settings, $this->get_setting_key( 'size' ), 32, 18, 120 ) . 'px',
			]
		);
	}
}
