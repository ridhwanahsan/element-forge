<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Pricing_Table_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'elementforge_pricing_table';
	}

	public function get_title() {
		return esc_html__( 'Pricing Table', 'element-forge' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-pricing-table-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Pricing Header', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'is_featured',
			[
				'label'        => esc_html__( 'Featured/Popular', 'element-forge' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'element-forge' ),
				'label_off'    => esc_html__( 'No', 'element-forge' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'featured_text',
			[
				'label'     => esc_html__( 'Ribbon Text', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Most Popular', 'element-forge' ),
				'condition' => [
					'is_featured' => 'yes',
				],
			]
		);

		$this->add_control(
			'plan_name',
			[
				'label'   => esc_html__( 'Plan Name', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Pro Plan', 'element-forge' ),
			]
		);

		$this->add_control(
			'plan_price',
			[
				'label'   => esc_html__( 'Price', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( '$99', 'element-forge' ),
			]
		);

		$this->add_control(
			'plan_duration',
			[
				'label'   => esc_html__( 'Duration', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( '/ month', 'element-forge' ),
			]
		);

		$this->add_control(
			'plan_description',
			[
				'label'   => esc_html__( 'Description', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Perfect for growing businesses and agencies.', 'element-forge' ),
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'features_section',
			[
				'label' => esc_html__( 'Features List', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'feature_text',
			[
				'label'       => esc_html__( 'Feature', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'New Feature', 'element-forge' ),
				'label_block' => true,
			]
		);
		$repeater->add_control(
			'feature_icon',
			[
				'label'            => esc_html__( 'Icon', 'element-forge' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'default'          => [
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'features_list',
			[
				'label'       => esc_html__( 'Features', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[ 'feature_text' => esc_html__( '10 Websites', 'element-forge' ) ],
					[ 'feature_text' => esc_html__( 'Priority Support', 'element-forge' ) ],
					[ 'feature_text' => esc_html__( 'Unlimited Bandwidth', 'element-forge' ) ],
				],
				'title_field' => '{{{ feature_text }}}',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'button_section',
			[
				'label' => esc_html__( 'Action Button', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'btn_text',
			[
				'label'   => esc_html__( 'Button Text', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Get Started', 'element-forge' ),
			]
		);

		$this->add_control(
			'btn_link',
			[
				'label' => esc_html__( 'Link', 'element-forge' ),
				'type'  => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'element-forge' ),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$is_featured = ( 'yes' === $settings['is_featured'] );
		$wrap_classes = 'ef-pricing-table' . ( $is_featured ? ' ef-pricing-featured' : '' );

		$target = $settings['btn_link']['is_external'] ? ' target="_blank"' : '';
		$nofollow = $settings['btn_link']['nofollow'] ? ' rel="nofollow"' : '';
		$url = ! empty( $settings['btn_link']['url'] ) ? $settings['btn_link']['url'] : '#';
		?>
		<div class="<?php echo esc_attr( $wrap_classes ); ?>">
			<?php if ( $is_featured && ! empty( $settings['featured_text'] ) ) : ?>
				<div class="ef-pricing-ribbon">
					<span><?php echo esc_html( $settings['featured_text'] ); ?></span>
				</div>
			<?php endif; ?>

			<div class="ef-pricing-header">
				<h3 class="ef-plan-name"><?php echo esc_html( $settings['plan_name'] ); ?></h3>
				<p class="ef-plan-desc"><?php echo esc_html( $settings['plan_description'] ); ?></p>
				<h2 class="ef-plan-price">
					<?php echo esc_html( $settings['plan_price'] ); ?>
					<span class="ef-plan-duration"><?php echo esc_html( $settings['plan_duration'] ); ?></span>
				</h2>
			</div>

			<div class="ef-pricing-features">
				<ul>
					<?php foreach ( $settings['features_list'] as $item ) : ?>
						<li>
							<?php \Elementor\Icons_Manager::render_icon( $item['feature_icon'], [ 'aria-hidden' => 'true' ] ); ?>
							<span><?php echo esc_html( $item['feature_text'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="ef-pricing-footer">
				<a href="<?php echo esc_url( $url ); ?>" class="ef-pricing-btn"<?php echo wp_kses_post( $target . $nofollow ); ?>>
					<?php echo esc_html( $settings['btn_text'] ); ?>
				</a>
			</div>
		</div>
		<?php
	}
}


