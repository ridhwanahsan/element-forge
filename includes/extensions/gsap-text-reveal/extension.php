<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Gsap_Text_Reveal_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'gsap-text-reveal';
	}

	protected function supports_widget_controls() {
		return true;
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'GSAP Text Reveal', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Text Reveal', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'selector' ),
			[
				'label'     => esc_html__( 'Target Selector', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '.elementor-heading-title,h1,h2,h3,h4,p',
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'distance' ),
			[
				'label'     => esc_html__( 'Reveal Distance', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 6,
				'max'       => 80,
				'default'   => 24,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'duration' ),
			[
				'label'     => esc_html__( 'Transition Duration', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0.2,
				'max'       => 2,
				'step'      => 0.1,
				'default'   => 0.7,
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
			[ 'efx-text-reveal' ],
			[
				'selector' => $this->get_text_setting( $settings, $this->get_setting_key( 'selector' ), '.elementor-heading-title,h1,h2,h3,h4,p' ),
			],
			[
				'--efx-text-reveal-distance' => $this->get_int_setting( $settings, $this->get_setting_key( 'distance' ), 24, 6, 80 ) . 'px',
				'--efx-text-reveal-duration' => $this->get_float_setting( $settings, $this->get_setting_key( 'duration' ), 0.7, 0.2, 2 ) . 's',
			]
		);
	}
}
