<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Info_Box_Widget extends ElementForge_Widget_Base {

	public function get_name() {
		return 'elementforge_info_box';
	}

	public function get_title() {
		return esc_html__( 'Info Box', 'element-forge' );
	}

	public function get_icon() {
		return 'eicon-info-box';
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-info-box-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Box Content', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'box_icon',
			[
				'label'            => esc_html__( 'Icon', 'element-forge' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'default'          => [
					'value'   => 'fas fa-rocket',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'box_title',
			[
				'label'   => esc_html__( 'Title', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Fast Performance', 'element-forge' ),
			]
		);

		$this->add_control(
			'box_description',
			[
				'label'   => esc_html__( 'Description', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Experience lightning-fast load times with our optimized components.', 'element-forge' ),
			]
		);

		$this->add_control(
			'box_link',
			[
				'label'       => esc_html__( 'Link', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'element-forge' ),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$target   = $settings['box_link']['is_external'] ? ' target="_blank"' : '';
		$nofollow = $settings['box_link']['nofollow'] ? ' rel="nofollow"' : '';
		$url      = ! empty( $settings['box_link']['url'] ) ? $settings['box_link']['url'] : '';

		$has_link = ! empty( $url );
		$wrapper_tag = $has_link ? 'a' : 'div';
		$wrapper_href = $has_link ? ' href="' . esc_url( $url ) . '"' . $target . $nofollow : '';
		?>
		<<?php echo esc_html( $wrapper_tag ); ?> class="ef-info-box" <?php echo wp_kses_post( $wrapper_href ); ?>>
			<div class="ef-info-box-icon">
				<?php \Elementor\Icons_Manager::render_icon( $settings['box_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</div>
			<h3 class="ef-info-box-title"><?php echo esc_html( $settings['box_title'] ); ?></h3>
			<p class="ef-info-box-desc"><?php echo esc_html( $settings['box_description'] ); ?></p>
		</<?php echo esc_html( $wrapper_tag ); ?>>
		<?php
	}
}
