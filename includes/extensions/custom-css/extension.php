<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Custom_Css_Extension extends ElementForge_Extension_Base {

	/**
	 * Tracks inline CSS payloads already printed on the current request.
	 *
	 * @var array<string, bool>
	 */
	private $printed_styles = [];

	protected function get_slug() {
		return 'custom-css';
	}

	protected function supports_widget_controls() {
		return true;
	}

	public function register_extension_controls( $element, $args ) {
		$this->start_extension_section( $element, esc_html__( 'Custom CSS', 'element-forge' ) );

		$element->add_control(
			$this->get_setting_key( 'enable' ),
			[
				'label'        => esc_html__( 'Enable Custom CSS', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$element->add_control(
			$this->get_setting_key( 'rules' ),
			[
				'label'       => esc_html__( 'CSS Rules', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 10,
				'placeholder' => "selector {\n    border-radius: 20px;\n}\n\nselector:hover {\n    transform: translateY(-4px);\n}",
				'description' => esc_html__( 'Use "selector" to target the current element. Plain declarations without selectors are also supported.', 'element-forge' ),
				'condition'   => [
					$this->get_setting_key( 'enable' ) => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	protected function apply_render_attributes( $element, $settings ) {
		$rules_key = $this->get_setting_key( 'rules' );
		$raw_css   = isset( $settings[ $rules_key ] ) ? (string) $settings[ $rules_key ] : '';
		$element_id = method_exists( $element, 'get_id' ) ? (string) $element->get_id() : '';

		if ( '' === trim( $raw_css ) || '' === $element_id ) {
			return;
		}

		$scope_class = 'efx-custom-css-' . sanitize_html_class( $element_id );
		$compiled_css = $this->compile_scoped_css( $raw_css, '.' . $scope_class );

		if ( '' === $compiled_css ) {
			return;
		}

		$this->add_wrapper_attributes(
			$element,
			[
				'efx-custom-css',
				$scope_class,
			]
		);

		$hash = md5( $compiled_css );
		if ( isset( $this->printed_styles[ $hash ] ) ) {
			return;
		}

		$this->printed_styles[ $hash ] = true;
		wp_add_inline_style( $this->get_asset_handle( 'style' ), $compiled_css );
	}

	/**
	 * Turns the saved textarea content into wrapper-scoped CSS.
	 *
	 * @param string $css   Raw textarea content.
	 * @param string $scope Wrapper selector.
	 *
	 * @return string
	 */
	private function compile_scoped_css( $css, $scope ) {
		$css = wp_check_invalid_utf8( (string) $css );
		$css = str_replace( [ "\0", "\r", '<' ], '', $css );
		$css = trim( $css );

		if ( '' === $css ) {
			return '';
		}

		if ( strlen( $css ) > 5000 ) {
			$css = substr( $css, 0, 5000 );
		}

		if ( preg_match( '/(?:expression\s*\(|javascript\s*:|vbscript\s*:|behavior\s*:|-moz-binding|@import|@charset|@namespace|<\/style|<style|<script)/i', $css ) ) {
			return '';
		}

		if ( preg_match( '/@[a-z]/i', $css ) ) {
			return '';
		}

		if ( false === strpos( $css, '{' ) ) {
			$declarations = $this->normalize_declaration_block( $css );

			return '' !== $declarations ? $scope . '{' . $declarations . '}' : '';
		}

		$blocks = explode( '}', $css );
		$output = [];

		foreach ( $blocks as $block ) {
			if ( false === strpos( $block, '{' ) ) {
				continue;
			}

			list( $selectors, $declarations ) = explode( '{', $block, 2 );

			$selectors    = trim( $selectors );
			$declarations = $this->normalize_declaration_block( $declarations );

			if ( '' === $selectors || '' === $declarations ) {
				continue;
			}

			$prefixed_selectors = $this->prefix_selector_list( $selectors, $scope );
			if ( '' === $prefixed_selectors ) {
				continue;
			}

			$output[] = $prefixed_selectors . '{' . $declarations . '}';
		}

		return implode( "\n", $output );
	}

	/**
	 * Normalizes a declaration block so multiple extension styles merge safely.
	 *
	 * @param string $declarations CSS declarations only.
	 *
	 * @return string
	 */
	private function normalize_declaration_block( $declarations ) {
		$declarations = trim( (string) $declarations );

		if ( '' === $declarations ) {
			return '';
		}

		return rtrim( $declarations, "; \n\t" ) . ';';
	}

	/**
	 * Prefixes selectors so they only affect the current element wrapper.
	 *
	 * @param string $selector_list Raw selector list.
	 * @param string $scope         Scoped wrapper selector.
	 *
	 * @return string
	 */
	private function prefix_selector_list( $selector_list, $scope ) {
		$selectors = array_filter( array_map( 'trim', explode( ',', (string) $selector_list ) ) );
		$output    = [];

		foreach ( $selectors as $selector ) {
			$selector = str_replace( 'selector', $scope, $selector );

			if ( false !== strpos( $selector, $scope ) ) {
				$output[] = $selector;
				continue;
			}

			if ( 0 === strpos( $selector, ':' ) || 0 === strpos( $selector, '[' ) ) {
				$output[] = $scope . $selector;
				continue;
			}

			$output[] = $scope . ' ' . $selector;
		}

		return implode( ', ', $output );
	}
}
