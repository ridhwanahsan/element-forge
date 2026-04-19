<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __DIR__ ) . '/class-elementforge-woocommerce-widget-base.php';

class ElementForge_Woo_Product_Rating_Widget extends ElementForge_WooCommerce_Widget_Base {

	public function get_name() {
		return 'elementforge_woo_product_rating';
	}

	public function get_title() {
		return esc_html__( 'Product Rating', 'element-forge' );
	}

	public function get_icon() {
		return 'eicon-star';
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'product', 'rating', 'reviews' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-woo-product-rating-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Product Rating', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_product_controls();

		$this->add_control(
			'show_review_count',
			[
				'label'        => esc_html__( 'Show Review Count', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'element-forge' ),
				'label_off'    => esc_html__( 'Hide', 'element-forge' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'empty_text',
			[
				'label'   => esc_html__( 'Empty State Text', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'No reviews yet', 'element-forge' ),
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
					'{{WRAPPER}} .ef-woo-product-rating' => 'align-items: {{VALUE}};',
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

		$average      = (float) $product->get_average_rating();
		$review_count = (int) $product->get_review_count();

		if ( $average <= 0 ) {
			if ( ! empty( $settings['empty_text'] ) ) {
				printf(
					'<div class="ef-woo-product-rating-empty">%s</div>',
					esc_html( $settings['empty_text'] )
				);
			}
			return;
		}

		$rating_percent = min( 100, max( 0, ( $average / 5 ) * 100 ) );
		?>
		<div class="ef-woo-product-rating">
			<div class="ef-woo-product-rating-stars" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %1$s out of 5', 'element-forge' ), wc_format_decimal( $average, 1 ) ) ); ?>">
				<span class="ef-woo-product-rating-base">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
				<span class="ef-woo-product-rating-fill" style="width: <?php echo esc_attr( $rating_percent ); ?>%;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
			</div>

			<div class="ef-woo-product-rating-summary">
				<span class="ef-woo-product-rating-average"><?php echo esc_html( wc_format_decimal( $average, 1 ) ); ?>/5</span>
				<?php if ( 'yes' === $settings['show_review_count'] ) : ?>
					<span class="ef-woo-product-rating-count">
						<?php
						echo esc_html(
							sprintf(
								_n( '%s review', '%s reviews', $review_count, 'element-forge' ),
								number_format_i18n( $review_count )
							)
						);
						?>
					</span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
