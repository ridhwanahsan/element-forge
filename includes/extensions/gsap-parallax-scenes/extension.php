<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Gsap_Parallax_Scenes_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'gsap-parallax-scenes';
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'GSAP Parallax Scenes', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Parallax Scene', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'strength' ),
			[
				'label'     => esc_html__( 'Parallax Strength', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 4,
				'max'       => 80,
				'default'   => 18,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'reverse' ),
			[
				'label'        => esc_html__( 'Reverse Motion', 'element-forge' ),
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
			[ 'efx-parallax-scene' ],
			[
				'strength' => $this->get_int_setting( $settings, $this->get_setting_key( 'strength' ), 18, 4, 80 ),
				'reverse'  => $this->get_text_setting( $settings, $this->get_setting_key( 'reverse' ), '' ),
			]
		);
	}
}
