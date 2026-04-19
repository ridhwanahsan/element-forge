<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __DIR__ ) . '/class-elementforge-woocommerce-widget-base.php';

class ElementForge_Review_Summary_Widget extends ElementForge_WooCommerce_Widget_Base {

	public function get_name() {
		return 'elementforge_review_summary';
	}

	public function get_title() {
		return esc_html__( 'Review Summary', 'element-forge' );
	}

	public function get_icon() {
		return parent::get_icon();
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'reviews', 'rating', 'summary', 'product' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-review-summary-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Review Summary', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_product_controls();

		$this->add_control(
			'show_average_rating',
			[
				'label'        => esc_html__( 'Show Average Rating', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_total_reviews',
			[
				'label'        => esc_html__( 'Show Review Count', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_star_visual',
			[
				'label'        => esc_html__( 'Show Star Visual', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_breakdown',
			[
				'label'        => esc_html__( 'Show Rating Breakdown', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'layout_style',
			[
				'label'   => esc_html__( 'Layout', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'card',
				'options' => [
					'card'    => esc_html__( 'Card', 'element-forge' ),
					'compact' => esc_html__( 'Compact', 'element-forge' ),
				],
			]
		);

		$this->add_control(
			'empty_state_text',
			[
				'label'   => esc_html__( 'Empty State Text', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'No reviews yet. Be the first to review this product.', 'element-forge' ),
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
			'accent_color',
			[
				'label'     => esc_html__( 'Accent Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#f59e0b',
				'selectors' => [
					'{{WRAPPER}} .ef-review-summary' => '--ef-review-accent: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'track_color',
			[
				'label'     => esc_html__( 'Track Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => [
					'{{WRAPPER}} .ef-review-summary' => '--ef-review-track: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .ef-review-summary' => '--ef-review-text: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$product  = $this->get_product_from_settings( $settings );

		if ( ! $this->is_woocommerce_active() ) {
			$this->render_editor_notice( esc_html__( 'WooCommerce is required for this widget.', 'element-forge' ) );
			return;
		}

		if ( ! $product ) {
			$this->render_editor_notice( esc_html__( 'Select a product or use this widget on a single product page.', 'element-forge' ) );
			return;
		}

		$average       = (float) $product->get_average_rating();
		$review_count  = (int) $product->get_review_count();
		$rating_counts = array_map( 'intval', (array) $product->get_rating_counts() );
		$layout        = isset( $settings['layout_style'] ) && 'compact' === $settings['layout_style'] ? 'compact' : 'card';

		if ( $review_count <= 0 || $average <= 0 ) {
			?>
			<div class="ef-review-summary ef-review-summary--empty ef-review-summary--<?php echo esc_attr( $layout ); ?>">
				<div class="ef-review-summary__empty"><?php echo esc_html( $settings['empty_state_text'] ); ?></div>
			</div>
			<?php
			return;
		}

		$rating_percent = min( 100, max( 0, ( $average / 5 ) * 100 ) );
		?>
		<div class="ef-review-summary ef-review-summary--<?php echo esc_attr( $layout ); ?>">
			<div class="ef-review-summary__header">
				<?php if ( 'yes' === $settings['show_average_rating'] ) : ?>
					<div class="ef-review-summary__average"><?php echo esc_html( wc_format_decimal( $average, 1 ) ); ?></div>
				<?php endif; ?>

				<div class="ef-review-summary__summary">
					<?php if ( 'yes' === $settings['show_star_visual'] ) : ?>
						<div class="ef-review-summary__stars" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %1$s out of 5', 'element-forge' ), wc_format_decimal( $average, 1 ) ) ); ?>">
							<span class="ef-review-summary__stars-base">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
							<span class="ef-review-summary__stars-fill" style="width: <?php echo esc_attr( (string) $rating_percent ); ?>%;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
						</div>
					<?php endif; ?>

					<?php if ( 'yes' === $settings['show_total_reviews'] ) : ?>
						<div class="ef-review-summary__count">
							<?php
							echo esc_html(
								sprintf(
									_n( '%s review', '%s reviews', $review_count, 'element-forge' ),
									number_format_i18n( $review_count )
								)
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( 'yes' === $settings['show_breakdown'] ) : ?>
				<div class="ef-review-summary__breakdown">
					<?php for ( $star = 5; $star >= 1; $star-- ) : ?>
						<?php
						$count   = isset( $rating_counts[ $star ] ) ? (int) $rating_counts[ $star ] : 0;
						$percent = $review_count > 0 ? min( 100, max( 0, ( $count / $review_count ) * 100 ) ) : 0;
						?>
						<div class="ef-review-summary__row">
							<span class="ef-review-summary__label"><?php echo esc_html( sprintf( _x( '%s star', 'review rating breakdown label', 'element-forge' ), $star ) ); ?></span>
							<span class="ef-review-summary__track">
								<span class="ef-review-summary__row-fill" style="width: <?php echo esc_attr( (string) $percent ); ?>%;"></span>
							</span>
							<span class="ef-review-summary__value"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
						</div>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
