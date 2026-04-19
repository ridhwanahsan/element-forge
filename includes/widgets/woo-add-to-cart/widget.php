<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __DIR__ ) . '/class-elementforge-woocommerce-widget-base.php';

class ElementForge_Woo_Add_To_Cart_Widget extends ElementForge_WooCommerce_Widget_Base {

	public function get_name() {
		return 'elementforge_woo_add_to_cart';
	}

	public function get_title() {
		return esc_html__( 'Add To Cart', 'element-forge' );
	}

	public function get_icon() {
		return parent::get_icon();
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'product', 'cart', 'buy' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-woo-add-to-cart-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Add To Cart', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_product_controls();

		$this->add_control(
			'button_text',
			[
				'label'       => esc_html__( 'Custom Button Text', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave empty to use the product default', 'element-forge' ),
				'default'     => '',
			]
		);

		$this->add_control(
			'show_price',
			[
				'label'        => esc_html__( 'Show Product Price', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'element-forge' ),
				'label_off'    => esc_html__( 'Hide', 'element-forge' ),
				'return_value' => 'yes',
				'default'      => '',
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
					'{{WRAPPER}} .ef-woo-add-to-cart' => 'align-items: {{VALUE}};',
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

		$button_text = ! empty( $settings['button_text'] ) ? $settings['button_text'] : $product->add_to_cart_text();
		$button_url  = $product->add_to_cart_url();
		$classes     = [
			'ef-woo-add-to-cart-button',
			'product_type_' . sanitize_html_class( $product->get_type() ),
		];

		if ( $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() && 'simple' === $product->get_type() ) {
			$classes[] = 'add_to_cart_button';
			$classes[] = 'ajax_add_to_cart';
		}

		$attributes = [
			'href'          => esc_url( $button_url ),
			'data-product_id' => (string) $product->get_id(),
			'data-product_sku' => $product->get_sku(),
			'data-quantity' => '1',
			'aria-label'    => wp_strip_all_tags( $product->add_to_cart_description() ),
			'rel'           => 'nofollow',
			'class'         => implode( ' ', array_map( 'sanitize_html_class', $classes ) ),
		];
		?>
		<div class="ef-woo-add-to-cart">
			<a
				<?php foreach ( $attributes as $attribute_name => $attribute_value ) : ?>
					<?php if ( '' !== $attribute_value ) : ?>
						<?php echo esc_attr( $attribute_name ); ?>="<?php echo esc_attr( $attribute_value ); ?>"
					<?php endif; ?>
				<?php endforeach; ?>
			>
				<?php echo esc_html( $button_text ); ?>
			</a>

			<?php if ( 'yes' === $settings['show_price'] ) : ?>
				<div class="ef-woo-add-to-cart-price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
