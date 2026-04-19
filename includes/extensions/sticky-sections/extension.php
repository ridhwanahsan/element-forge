<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Sticky_Sections_Extension extends ElementForge_Extension_Base {

	protected function get_slug() {
		return 'sticky-sections';
	}

	protected function supports_widget_controls() {
		return true;
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'Sticky Sections', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Sticky Section', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'top_offset' ),
			[
				'label'     => esc_html__( 'Top Offset', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 300,
				'default'   => 24,
				'condition' => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->add_control(
			$this->get_setting_key( 'z_index' ),
			[
				'label'     => esc_html__( 'Z-Index', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 999,
				'default'   => 10,
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
			[ 'efx-sticky-section' ],
			[],
			[
				'--efx-sticky-top' => $this->get_int_setting( $settings, $this->get_setting_key( 'top_offset' ), 24, 0, 300 ) . 'px',
				'--efx-sticky-z'   => $this->get_int_setting( $settings, $this->get_setting_key( 'z_index' ), 10, 1, 999 ),
			]
		);
	}
}
