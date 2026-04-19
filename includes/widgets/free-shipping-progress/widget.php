<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __DIR__ ) . '/class-elementforge-woocommerce-widget-base.php';

class ElementForge_Free_Shipping_Progress_Widget extends ElementForge_WooCommerce_Widget_Base {

	/**
	 * Prevents the same localized payload from being printed repeatedly.
	 *
	 * @var bool
	 */
	private static $script_localized = false;

	public function get_name() {
		return 'elementforge_free_shipping_progress';
	}

	public function get_title() {
		return esc_html__( 'Free Shipping Progress', 'element-forge' );
	}

	public function get_icon() {
		return parent::get_icon();
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'cart', 'shipping', 'progress', 'checkout' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-free-shipping-progress-style' ];
	}

	public function get_script_depends() {
		return [ 'elementforge-free-shipping-progress-script' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Free Shipping Progress', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'goal_amount',
			[
				'label'   => esc_html__( 'Free Shipping Goal', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'step'    => 1,
				'default' => 100,
			]
		);

		$this->add_control(
			'amount_basis',
			[
				'label'   => esc_html__( 'Calculation Basis', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'subtotal',
				'options' => [
					'subtotal'            => esc_html__( 'Subtotal Before Discounts', 'element-forge' ),
					'discounted_subtotal' => esc_html__( 'Subtotal After Discounts', 'element-forge' ),
				],
			]
		);

		$this->add_control(
			'show_icon',
			[
				'label'        => esc_html__( 'Show Icon', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'before_text',
			[
				'label'       => esc_html__( 'Progress Message', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Add {remaining} more to unlock free shipping.', 'element-forge' ),
				'description' => esc_html__( 'Use {remaining} to insert the remaining amount.', 'element-forge' ),
			]
		);

		$this->add_control(
			'success_text',
			[
				'label'   => esc_html__( 'Success Message', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Nice work. Free shipping is unlocked.', 'element-forge' ),
			]
		);

		$this->add_control(
			'empty_text',
			[
				'label'   => esc_html__( 'Empty Cart Message', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Add products to your cart to start tracking free shipping.', 'element-forge' ),
			]
		);

		$this->add_responsive_control(
			'label_alignment',
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
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .ef-free-shipping-progress' => 'text-align: {{VALUE}};',
				],
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
			'track_color',
			[
				'label'     => esc_html__( 'Track Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => [
					'{{WRAPPER}} .ef-free-shipping-progress' => '--ef-fsp-track: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'fill_color',
			[
				'label'     => esc_html__( 'Fill Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#16a34a',
				'selectors' => [
					'{{WRAPPER}} .ef-free-shipping-progress' => '--ef-fsp-fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'success_color',
			[
				'label'     => esc_html__( 'Success Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#15803d',
				'selectors' => [
					'{{WRAPPER}} .ef-free-shipping-progress' => '--ef-fsp-success: {{VALUE}};',
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
					'{{WRAPPER}} .ef-free-shipping-progress' => '--ef-fsp-text: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_height',
			[
				'label'   => esc_html__( 'Progress Height', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 4,
				'max'     => 24,
				'default' => 12,
				'selectors'=> [
					'{{WRAPPER}} .ef-free-shipping-progress' => '--ef-fsp-height: {{VALUE}}px;',
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

		$settings    = $this->get_settings_for_display();
		$goal_amount = isset( $settings['goal_amount'] ) ? max( 1, (float) $settings['goal_amount'] ) : 100.0;
		$basis       = $this->get_amount_basis( $settings );
		$state       = $this->get_progress_state( $goal_amount, $basis, $settings );
		$handle      = 'elementforge-free-shipping-progress-script';
		$goal_text   = sprintf(
			esc_html__( 'Goal: %s', 'element-forge' ),
			wp_strip_all_tags( wc_price( $goal_amount ) )
		);

		$this->localize_script( $handle );
		?>
		<div
			class="ef-free-shipping-progress<?php echo esc_attr( ' ' . $this->get_state_class( $state['state'] ) ); ?>"
			data-threshold="<?php echo esc_attr( (string) $goal_amount ); ?>"
			data-basis="<?php echo esc_attr( $basis ); ?>"
			data-before-text="<?php echo esc_attr( $this->get_message_template( $settings, 'before_text', esc_html__( 'Add {remaining} more to unlock free shipping.', 'element-forge' ) ) ); ?>"
			data-success-text="<?php echo esc_attr( $this->get_message_template( $settings, 'success_text', esc_html__( 'Nice work. Free shipping is unlocked.', 'element-forge' ) ) ); ?>"
			data-empty-text="<?php echo esc_attr( $this->get_message_template( $settings, 'empty_text', esc_html__( 'Add products to your cart to start tracking free shipping.', 'element-forge' ) ) ); ?>"
			data-subtotal="<?php echo esc_attr( (string) $state['subtotal'] ); ?>"
			data-discounted-subtotal="<?php echo esc_attr( (string) $state['discounted_subtotal'] ); ?>"
		>
			<div class="ef-free-shipping-progress__inner">
				<?php if ( isset( $settings['show_icon'] ) && 'yes' === $settings['show_icon'] ) : ?>
					<div class="ef-free-shipping-progress__icon" aria-hidden="true">
						<span class="ef-free-shipping-progress__icon-ring"></span>
					</div>
				<?php endif; ?>

				<div class="ef-free-shipping-progress__content">
					<div class="ef-free-shipping-progress__message"><?php echo esc_html( $state['message'] ); ?></div>
					<div class="ef-free-shipping-progress__meta">
						<span class="ef-free-shipping-progress__remaining"><?php echo esc_html( $state['remaining_formatted'] ); ?></span>
						<span class="ef-free-shipping-progress__goal"><?php echo esc_html( $goal_text ); ?></span>
					</div>
					<div class="ef-free-shipping-progress__bar" aria-hidden="true">
						<span class="ef-free-shipping-progress__fill" style="width: <?php echo esc_attr( (string) $state['percent'] ); ?>%;"></span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param string $handle Script handle.
	 * @return void
	 */
	private function localize_script( $handle ) {
		if ( self::$script_localized || ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}

		wp_localize_script(
			$handle,
			'elementForgeWooWidgets',
			[
				'cartSummaryUrl'  => esc_url_raw( rest_url( 'element-forge/v1/cart-summary' ) ),
				'currencySymbol' => get_woocommerce_currency_symbol(),
				'currencyPos'    => get_option( 'woocommerce_currency_pos', 'left' ),
				'decimals'       => wc_get_price_decimals(),
				'decimalSep'     => wc_get_price_decimal_separator(),
				'thousandSep'    => wc_get_price_thousand_separator(),
				'goalLabel'      => esc_html__( 'Goal: %s', 'element-forge' ),
			]
		);

		self::$script_localized = true;
	}

	/**
	 * @param array  $settings Widget settings.
	 * @param string $key      Setting key.
	 * @param string $default  Default message.
	 * @return string
	 */
	private function get_message_template( $settings, $key, $default ) {
		$message = isset( $settings[ $key ] ) ? sanitize_text_field( (string) $settings[ $key ] ) : $default;

		return '' !== $message ? $message : $default;
	}

	/**
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_amount_basis( $settings ) {
		$basis = isset( $settings['amount_basis'] ) ? sanitize_key( (string) $settings['amount_basis'] ) : 'subtotal';

		return in_array( $basis, [ 'subtotal', 'discounted_subtotal' ], true ) ? $basis : 'subtotal';
	}

	/**
	 * @param float  $goal_amount Free shipping target.
	 * @param string $basis       Calculation basis.
	 * @return array<string, float|string>
	 */
	private function get_progress_state( $goal_amount, $basis, $settings = [] ) {
		$summary   = $this->get_cart_summary();
		$subtotal  = 'discounted_subtotal' === $basis ? $summary['discounted_subtotal'] : $summary['subtotal'];
		$remaining = max( 0, $goal_amount - $subtotal );
		$percent   = min( 100, max( 0, ( $subtotal / $goal_amount ) * 100 ) );
		$state     = 'progress';

		if ( $subtotal <= 0 ) {
			$state = 'empty';
		} elseif ( $remaining <= 0 ) {
			$state = 'success';
		}

		return [
			'subtotal'            => $subtotal,
			'discounted_subtotal' => $summary['discounted_subtotal'],
			'remaining'           => $remaining,
			'remaining_formatted' => wc_price( $remaining ),
			'percent'             => $percent,
			'state'               => $state,
			'message'             => $this->build_state_message( $state, $remaining, $settings ),
		];
	}

	/**
	 * @return array<string, float>
	 */
	private function get_cart_summary() {
		$cart = function_exists( 'WC' ) ? WC()->cart : null;

		if ( ! $cart && function_exists( 'wc_load_cart' ) && did_action( 'woocommerce_init' ) ) {
			wc_load_cart();
			$cart = WC()->cart;
		}

		if ( ! $cart ) {
			return [
				'subtotal'            => 0.0,
				'discounted_subtotal' => 0.0,
			];
		}

		$subtotal            = (float) $cart->get_displayed_subtotal();
		$discounted_subtotal = $subtotal - (float) $cart->get_discount_total();

		if ( $cart->display_prices_including_tax() ) {
			$discounted_subtotal -= (float) $cart->get_discount_tax();
		}

		return [
			'subtotal'            => max( 0, $subtotal ),
			'discounted_subtotal' => max( 0, $discounted_subtotal ),
		];
	}

	/**
	 * @param string $state     Current state.
	 * @param float  $remaining Remaining amount.
	 * @return string
	 */
	private function build_state_message( $state, $remaining, $settings = [] ) {
		$remaining_amount = wp_strip_all_tags( wc_price( $remaining ) );

		if ( 'empty' === $state ) {
			return $this->get_message_template(
				$settings,
				'empty_text',
				esc_html__( 'Add products to your cart to start tracking free shipping.', 'element-forge' )
			);
		}

		if ( 'success' === $state ) {
			return $this->get_message_template(
				$settings,
				'success_text',
				esc_html__( 'Nice work. Free shipping is unlocked.', 'element-forge' )
			);
		}

		return str_replace(
			'{remaining}',
			$remaining_amount,
			$this->get_message_template(
				$settings,
				'before_text',
				esc_html__( 'Add {remaining} more to unlock free shipping.', 'element-forge' )
			)
		);
	}

	/**
	 * @param string $state Current state.
	 * @return string
	 */
	private function get_state_class( $state ) {
		switch ( $state ) {
			case 'success':
				return 'is-success';
			case 'empty':
				return 'is-empty';
			default:
				return 'is-progress';
		}
	}
}
