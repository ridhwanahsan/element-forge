<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/class-elementforge-widget-base.php';

abstract class ElementForge_WooCommerce_Widget_Base extends ElementForge_Widget_Base {

	protected function is_woocommerce_active() {
		return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_product' );
	}

	protected function register_product_controls() {
		$this->add_control(
			'product_source',
			[
				'label'   => esc_html__( 'Product Source', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'current',
				'options' => [
					'current' => esc_html__( 'Current Product', 'element-forge' ),
					'custom'  => esc_html__( 'Select Product', 'element-forge' ),
				],
			]
		);

		$this->add_control(
			'product_id',
			[
				'label'       => esc_html__( 'Product', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_product_options(),
				'label_block' => true,
				'condition'   => [
					'product_source' => 'custom',
				],
			]
		);
	}

	protected function get_product_from_settings( $settings ) {
		if ( ! $this->is_woocommerce_active() ) {
			return false;
		}

		$source        = isset( $settings['product_source'] ) ? $settings['product_source'] : 'current';
		$selected_id   = isset( $settings['product_id'] ) ? absint( $settings['product_id'] ) : 0;
		$current_product = $this->get_current_product();

		if ( 'custom' === $source && $selected_id > 0 ) {
			$product = wc_get_product( $selected_id );
			if ( $product instanceof \WC_Product ) {
				return $product;
			}
		}

		if ( $current_product instanceof \WC_Product ) {
			return $current_product;
		}

		if ( $selected_id > 0 ) {
			$product = wc_get_product( $selected_id );
			if ( $product instanceof \WC_Product ) {
				return $product;
			}
		}

		return false;
	}

	protected function get_current_product() {
		if ( ! $this->is_woocommerce_active() ) {
			return false;
		}

		global $product;

		if ( $product instanceof \WC_Product ) {
			return $product;
		}

		$post_id = get_the_ID();
		if ( $post_id && 'product' === get_post_type( $post_id ) ) {
			$resolved_product = wc_get_product( $post_id );
			if ( $resolved_product instanceof \WC_Product ) {
				return $resolved_product;
			}
		}

		return false;
	}

	protected function render_editor_notice( $message ) {
		if ( ! $this->is_editor_context() ) {
			return;
		}

		printf(
			'<div class="ef-woo-widget-notice">%s</div>',
			esc_html( $message )
		);
	}

	protected function is_editor_context() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		return \Elementor\Plugin::$instance->preview->is_preview_mode()
			|| \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	private function get_product_options() {
		$options = [];

		if ( ! $this->is_woocommerce_active() ) {
			return $options;
		}

		$products = get_posts(
			[
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);

		foreach ( $products as $product_post ) {
			$title = get_the_title( $product_post );
			if ( '' === $title ) {
				continue;
			}

			$options[ $product_post->ID ] = $title;
		}

		return $options;
	}
}
