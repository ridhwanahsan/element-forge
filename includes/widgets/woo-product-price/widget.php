<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __DIR__ ) . '/class-elementforge-woocommerce-widget-base.php';

class ElementForge_Woo_Product_Price_Widget extends ElementForge_WooCommerce_Widget_Base {

	public function get_name() {
		return 'elementforge_woo_product_price';
	}

	public function get_title() {
		return esc_html__( 'Product Price', 'element-forge' );
	}

	public function get_icon() {
		return 'eicon-product-price';
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'product', 'price', 'sale' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-woo-product-price-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Product Price', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_product_controls();

		$this->add_control(
			'show_sale_badge',
			[
				'label'        => esc_html__( 'Show Sale Badge', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'element-forge' ),
				'label_off'    => esc_html__( 'Hide', 'element-forge' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'sale_badge_text',
			[
				'label'     => esc_html__( 'Sale Badge Text', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Sale', 'element-forge' ),
				'condition' => [
					'show_sale_badge' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label'   => esc_html__( 'Alignment', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => esc_html__( 'Left', 'element-forge' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'element-forge' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'element-forge' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'              => 'left',
				'selectors_dictionary' => [
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				],
				'selectors'            => [
					'{{WRAPPER}} .ef-woo-product-price' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->is_woocommerce_active() ) {
			$this->render_editor_notice( esc_html__( 'WooCommerce is required for this widget.', 'element-forge' ) );
			return;
		}

		$settings = $this->get_settings_for_display();
		$product  = $this->get_product_from_settings( $settings );

		if ( ! $product ) {
			$this->render_editor_notice( esc_html__( 'Select a product or use this widget on a single product page.', 'element-forge' ) );
			return;
		}
		?>
		<div class="ef-woo-product-price">
			<?php if ( 'yes' === $settings['show_sale_badge'] && $product->is_on_sale() ) : ?>
				<span class="ef-woo-product-price-badge"><?php echo esc_html( $settings['sale_badge_text'] ); ?></span>
			<?php endif; ?>

			<div class="ef-woo-product-price-value">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>
		</div>
		<?php
	}
}
