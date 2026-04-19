<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Spotlight_Hover_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'spotlight-hover';
	}

	protected function supports_widget_controls() {
		return true;
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'Spotlight Hover', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Spotlight Hover', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'color' ),
			[
				'label'     => esc_html__( 'Glow Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'size' ),
			[
				'label'     => esc_html__( 'Glow Size', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 80,
				'max'       => 480,
				'default'   => 220,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'opacity' ),
			[
				'label'     => esc_html__( 'Glow Opacity', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0.05,
				'max'       => 1,
				'step'      => 0.05,
				'default'   => 0.18,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'target_selector' ),
			[
				'label'       => esc_html__( 'Target Selector', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( '.your-card', 'element-forge' ),
				'description' => esc_html__( 'Optional. Match a specific inner element for the spotlight area.', 'element-forge' ),
				'condition'   => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	protected function apply_render_attributes( $element, $settings ) {
		$element_type = is_callable( [ $element, 'get_type' ] ) ? (string) $element->get_type() : '';
		$layer       = 'widget' === $element_type ? 'foreground' : 'background';

		$this->add_wrapper_attributes(
			$element,
			[ 'efx-spotlight-hover' ],
			[
				'layer'           => $layer,
				'target-selector' => $this->get_text_setting( $settings, $this->get_setting_key( 'target_selector' ), '' ),
			],
			[
				'--efx-spotlight-color'   => $this->get_color_setting( $settings, $this->get_setting_key( 'color' ), '#ffffff' ),
				'--efx-spotlight-size'    => $this->get_int_setting( $settings, $this->get_setting_key( 'size' ), 220, 80, 480 ) . 'px',
				'--efx-spotlight-opacity' => $this->get_float_setting( $settings, $this->get_setting_key( 'opacity' ), 0.18, 0.05, 1 ) . '',
			]
		);
	}
}
