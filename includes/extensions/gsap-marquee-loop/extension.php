<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Gsap_Marquee_Loop_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'gsap-marquee-loop';
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'GSAP Marquee Loop', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Marquee Loop', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'duration' ),
			[
				'label'     => esc_html__( 'Animation Duration', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 6,
				'max'       => 60,
				'default'   => 18,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'gap' ),
			[
				'label'     => esc_html__( 'Item Gap', 'element-forge' ),
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
			$this->get_setting_key( 'reverse' ),
			[
				'label'        => esc_html__( 'Reverse Direction', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
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
			[ 'efx-marquee-loop' ],
			[
				'reverse' => $this->get_text_setting( $settings, $this->get_setting_key( 'reverse' ), '' ),
			],
			[
				'--efx-marquee-duration' => $this->get_int_setting( $settings, $this->get_setting_key( 'duration' ), 18, 6, 60 ) . 's',
				'--efx-marquee-gap'      => $this->get_int_setting( $settings, $this->get_setting_key( 'gap' ), 24, 0, 120 ) . 'px',
			]
		);
	}
}
