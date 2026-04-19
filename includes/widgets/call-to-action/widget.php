<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Call_To_Action_Widget extends ElementForge_Widget_Base {

	public function get_name() {
		return 'elementforge_call_to_action';
	}

	public function get_title() {
		return esc_html__( 'Call To Action', 'element-forge' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-call-to-action-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'CTA Content', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'cta_title',
			[
				'label'   => esc_html__( 'Title', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Ready to boost your productivity?', 'element-forge' ),
			]
		);

		$this->add_control(
			'cta_description',
			[
				'label'   => esc_html__( 'Description', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Join thousands of active users and take your website to the next level.', 'element-forge' ),
			]
		);

		$this->add_control(
			'primary_button_text',
			[
				'label'   => esc_html__( 'Primary Button', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Get Started Now', 'element-forge' ),
			]
		);

		$this->add_control(
			'primary_button_link',
			[
				'label'       => esc_html__( 'Primary Link', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'element-forge' ),
			]
		);

		$this->add_control(
			'secondary_button_text',
			[
				'label'   => esc_html__( 'Secondary Button', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Read Documentation', 'element-forge' ),
			]
		);

		$this->add_control(
			'secondary_button_link',
			[
				'label'       => esc_html__( 'Secondary Link', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://docs-link.com', 'element-forge' ),
			]
		);

		$this->add_control(
			'cta_alignment',
			[
				'label'     => esc_html__( 'Alignment', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
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
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .ef-cta-wrapper' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .ef-cta-buttons' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="ef-cta-wrapper">
			<h2 class="ef-cta-title"><?php echo esc_html( $settings['cta_title'] ); ?></h2>
			<p class="ef-cta-desc"><?php echo esc_html( $settings['cta_description'] ); ?></p>
			
			<div class="ef-cta-buttons">
				<?php if ( ! empty( $settings['primary_button_text'] ) ) : 
					$p_target   = $settings['primary_button_link']['is_external'] ? ' target="_blank"' : '';
					$p_nofollow = $settings['primary_button_link']['nofollow'] ? ' rel="nofollow"' : '';
					$p_url      = ! empty( $settings['primary_button_link']['url'] ) ? $settings['primary_button_link']['url'] : '#';
				?>
					<a href="<?php echo esc_url( $p_url ); ?>" class="ef-btn ef-btn-primary"<?php echo wp_kses_post( $p_target . $p_nofollow ); ?>>
						<?php echo esc_html( $settings['primary_button_text'] ); ?>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $settings['secondary_button_text'] ) ) : 
					$s_target   = $settings['secondary_button_link']['is_external'] ? ' target="_blank"' : '';
					$s_nofollow = $settings['secondary_button_link']['nofollow'] ? ' rel="nofollow"' : '';
					$s_url      = ! empty( $settings['secondary_button_link']['url'] ) ? $settings['secondary_button_link']['url'] : '#';
				?>
					<a href="<?php echo esc_url( $s_url ); ?>" class="ef-btn ef-btn-secondary"<?php echo wp_kses_post( $s_target . $s_nofollow ); ?>>
						<?php echo esc_html( $settings['secondary_button_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
