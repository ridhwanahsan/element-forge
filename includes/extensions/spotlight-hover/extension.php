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
			$this->get_setting_key( 'layer_mode' ),
			[
				'label'     => esc_html__( 'Layer Mode', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'auto',
				'options'   => [
					'auto'       => esc_html__( 'Auto', 'element-forge' ),
					'background' => esc_html__( 'Background', 'element-forge' ),
					'foreground' => esc_html__( 'Foreground', 'element-forge' ),
				],
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'z_index' ),
			[
				'label'     => esc_html__( 'Glow Z-Index', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 999,
				'default'   => 2,
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
		$default_layer = 'widget' === $element_type ? 'foreground' : 'background';
		$layer_mode    = $this->get_text_setting( $settings, $this->get_setting_key( 'layer_mode' ), 'auto' );
		$layer         = in_array( $layer_mode, [ 'background', 'foreground' ], true ) ? $layer_mode : $default_layer;
		$default_z     = 'foreground' === $layer ? 2 : 0;

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
				'--efx-spotlight-z'       => $this->get_int_setting( $settings, $this->get_setting_key( 'z_index' ), $default_z, 0, 999 ),
			]
		);
	}
}
