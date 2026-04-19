<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __DIR__ ) . '/class-elementforge-woocommerce-widget-base.php';

class ElementForge_Sticky_Add_To_Cart_Widget extends ElementForge_WooCommerce_Widget_Base {

	public function get_name() {
		return 'elementforge_sticky_add_to_cart';
	}

	public function get_title() {
		return esc_html__( 'Sticky Add To Cart', 'element-forge' );
	}

	public function get_icon() {
		return parent::get_icon();
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'product', 'sticky', 'cart' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-sticky-add-to-cart-style' ];
	}

	public function get_script_depends() {
		return [ 'elementforge-sticky-add-to-cart-script' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Sticky Add To Cart', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_product_controls();

		$this->add_control(
			'show_image',
			[
				'label'        => esc_html__( 'Show Image', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'        => esc_html__( 'Show Title', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_price',
			[
				'label'        => esc_html__( 'Show Price', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_stock',
			[
				'label'        => esc_html__( 'Show Stock Status', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'show_quantity',
			[
				'label'        => esc_html__( 'Show Quantity', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_variation_summary',
			[
				'label'        => esc_html__( 'Show Variation Summary', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'       => esc_html__( 'Button Text', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave empty to use the WooCommerce default', 'element-forge' ),
				'default'     => '',
			]
		);

		$this->add_control(
			'sticky_position',
			[
				'label'   => esc_html__( 'Position', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'bottom',
				'options' => [
					'bottom' => esc_html__( 'Bottom', 'element-forge' ),
					'top'    => esc_html__( 'Top', 'element-forge' ),
				],
			]
		);

		$this->add_control(
			'show_on_mobile',
			[
				'label'        => esc_html__( 'Show On Mobile', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'offset_top',
			[
				'label'   => esc_html__( 'Offset', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 120,
				'default' => 16,
			]
		);

		$this->add_control(
			'bar_radius',
			[
				'label'   => esc_html__( 'Corner Radius', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 32,
				'default' => 18,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => esc_html__( 'Style', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'panel_background',
			[
				'label'     => esc_html__( 'Panel Background', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .ef-sticky-add-to-cart' => '--ef-sticky-panel-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'panel_border',
			[
				'label'     => esc_html__( 'Panel Border', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .ef-sticky-add-to-cart' => '--ef-sticky-panel-border: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#f8fafc',
				'selectors' => [
					'{{WRAPPER}} .ef-sticky-add-to-cart' => '--ef-sticky-text-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background',
			[
				'label'     => esc_html__( 'Button Background', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#22c55e',
				'selectors' => [
					'{{WRAPPER}} .ef-sticky-add-to-cart' => '--ef-sticky-button-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => esc_html__( 'Button Text Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#052e16',
				'selectors' => [
					'{{WRAPPER}} .ef-sticky-add-to-cart' => '--ef-sticky-button-color: {{VALUE}};',
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

		$current_product    = $this->get_current_product();
		$is_current_context = $current_product instanceof \WC_Product && (int) $current_product->get_id() === (int) $product->get_id();
		$is_single_context  = function_exists( 'is_product' ) && is_product() && $is_current_context;
		$product_type       = (string) $product->get_type();
		$image_url          = '';
		$availability       = $product->get_availability();
		$availability_text  = isset( $availability['availability'] ) ? wp_strip_all_tags( $availability['availability'] ) : '';
		$button_text        = ! empty( $settings['button_text'] )
			? (string) $settings['button_text']
			: ( method_exists( $product, 'single_add_to_cart_text' ) ? $product->single_add_to_cart_text() : $product->add_to_cart_text() );
		$fallback_url       = $product->add_to_cart_url();
		$position           = isset( $settings['sticky_position'] ) && 'top' === $settings['sticky_position'] ? 'top' : 'bottom';
		$offset             = isset( $settings['offset_top'] ) ? max( 0, min( 120, (int) $settings['offset_top'] ) ) : 16;
		$radius             = isset( $settings['bar_radius'] ) ? max( 0, min( 32, (int) $settings['bar_radius'] ) ) : 18;
		$show_quantity      = 'yes' === $settings['show_quantity'] && ! $product->is_sold_individually();
		$show_mobile        = 'yes' === $settings['show_on_mobile'];
		$price_html         = $product->get_price_html();
		$image_id           = $product->get_image_id();
		$wrapper_classes    = [
			'ef-sticky-add-to-cart',
			'ef-sticky-add-to-cart--' . $position,
		];
		$button_classes     = [ 'ef-sticky-add-to-cart__button' ];
		$button_label       = wp_strip_all_tags( $button_text );

		if ( ! $show_mobile ) {
			$wrapper_classes[] = 'ef-sticky-add-to-cart--desktop-only';
		}

		if ( $image_id ) {
			$image_url = wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' );
		}

		if ( ! $image_url && function_exists( 'wc_placeholder_img_src' ) ) {
			$image_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}

		if ( ! $product->is_in_stock() ) {
			$button_classes[] = 'is-disabled';
		} elseif ( ! $is_single_context && $product->supports( 'ajax_add_to_cart' ) && 'simple' === $product_type ) {
			$button_classes[] = 'add_to_cart_button';
			$button_classes[] = 'ajax_add_to_cart';
		}
		?>
		<div
			class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
			data-context="<?php echo esc_attr( $is_single_context ? 'single' : 'fallback' ); ?>"
			data-product-type="<?php echo esc_attr( $product_type ); ?>"
			data-position="<?php echo esc_attr( $position ); ?>"
			data-fallback-url="<?php echo esc_url( $fallback_url ); ?>"
			style="<?php echo esc_attr( '--ef-sticky-offset:' . $offset . 'px;--ef-sticky-radius:' . $radius . 'px;' ); ?>"
		>
			<div class="ef-sticky-add-to-cart__inner">
				<div class="ef-sticky-add-to-cart__meta">
					<?php if ( 'yes' === $settings['show_image'] && ! empty( $image_url ) ) : ?>
						<div class="ef-sticky-add-to-cart__thumb">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
						</div>
					<?php endif; ?>

					<div class="ef-sticky-add-to-cart__content">
						<?php if ( 'yes' === $settings['show_title'] ) : ?>
							<div class="ef-sticky-add-to-cart__title"><?php echo esc_html( $product->get_name() ); ?></div>
						<?php endif; ?>

						<div class="ef-sticky-add-to-cart__details">
							<?php if ( 'yes' === $settings['show_price'] && '' !== $price_html ) : ?>
								<div class="ef-sticky-add-to-cart__price">
									<?php echo wp_kses_post( $price_html ); ?>
								</div>
							<?php endif; ?>

							<?php if ( 'yes' === $settings['show_stock'] && '' !== $availability_text ) : ?>
								<div class="ef-sticky-add-to-cart__stock"><?php echo esc_html( $availability_text ); ?></div>
							<?php endif; ?>

							<?php if ( 'yes' === $settings['show_variation_summary'] ) : ?>
								<div class="ef-sticky-add-to-cart__variation" hidden></div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="ef-sticky-add-to-cart__actions">
					<?php if ( $show_quantity ) : ?>
						<label class="screen-reader-text" for="<?php echo esc_attr( $this->get_id() . '-sticky-qty' ); ?>">
							<?php esc_html_e( 'Quantity', 'element-forge' ); ?>
						</label>
						<input
							id="<?php echo esc_attr( $this->get_id() . '-sticky-qty' ); ?>"
							class="ef-sticky-add-to-cart__qty"
							type="number"
							min="1"
							step="1"
							value="1"
							inputmode="numeric"
						>
					<?php endif; ?>

					<?php if ( $is_single_context ) : ?>
						<button
							type="button"
							class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>"
							data-default-label="<?php echo esc_attr( $button_label ); ?>"
							data-select-options-label="<?php echo esc_attr__( 'Select options', 'element-forge' ); ?>"
							<?php disabled( ! $product->is_in_stock() ); ?>
						>
							<?php echo esc_html( $button_text ); ?>
						</button>
					<?php else : ?>
						<a
							href="<?php echo esc_url( $fallback_url ); ?>"
							class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>"
							data-product_id="<?php echo esc_attr( (string) $product->get_id() ); ?>"
							data-product_sku="<?php echo esc_attr( (string) $product->get_sku() ); ?>"
							data-quantity="1"
							aria-label="<?php echo esc_attr( wp_strip_all_tags( $product->add_to_cart_description() ) ); ?>"
							rel="nofollow"
						>
							<?php echo esc_html( $button_text ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
