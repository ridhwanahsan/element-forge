<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Gsap_Horizontal_Panels_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'gsap-horizontal-panels';
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'GSAP Horizontal Panels', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Horizontal Panels', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'panel_width' ),
			[
				'label'     => esc_html__( 'Panel Width (vw)', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 40,
				'max'       => 100,
				'default'   => 80,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'gap' ),
			[
				'label'     => esc_html__( 'Panel Gap', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 120,
				'default'   => 24,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'wheel_scroll' ),
			[
				'label'        => esc_html__( 'Use Mouse Wheel', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	protected function apply_render_attributes( $element, $settings ) {
		$this->add_wrapper_attributes(
			$element,
			[ 'efx-horizontal-panels' ],
			[
				'wheel-scroll' => $this->get_text_setting( $settings, $this->get_setting_key( 'wheel_scroll' ), 'yes' ),
			],
			[
				'--efx-horizontal-panel-width' => $this->get_int_setting( $settings, $this->get_setting_key( 'panel_width' ), 80, 40, 100 ) . 'vw',
				'--efx-horizontal-panel-gap'   => $this->get_int_setting( $settings, $this->get_setting_key( 'gap' ), 24, 0, 120 ) . 'px',
			]
		);
	}
}
