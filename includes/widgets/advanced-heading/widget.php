<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Advanced_Heading_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'elementforge_advanced_heading';
	}

	public function get_title() {
		return esc_html__( 'Advanced Heading', 'element-forge' );
	}

	public function get_icon() {
		return 'eicon-heading';
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_keywords() {
		return [ 'heading', 'title', 'advanced', 'elementforge' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-advanced-heading-style' ];
	}

	protected function register_controls() {
		// ================= Content Section =================
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Heading Content', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'sub_heading_text',
			[
				'label'       => esc_html__( 'Sub Heading', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Welcome to the future', 'element-forge' ),
				'placeholder' => esc_html__( 'Type your sub-heading', 'element-forge' ),
			]
		);

		$this->add_control(
			'main_heading_prefix',
			[
				'label'       => esc_html__( 'Prefix Text', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Build', 'element-forge' ),
			]
		);

		$this->add_control(
			'main_heading_highlight',
			[
				'label'       => esc_html__( 'Highlighted Text', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Better', 'element-forge' ),
				'description' => esc_html__( 'This text gets the gradient/highlight treatment.', 'element-forge' ),
			]
		);

		$this->add_control(
			'main_heading_suffix',
			[
				'label'       => esc_html__( 'Suffix Text', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Websites', 'element-forge' ),
			]
		);

		$this->add_control(
			'heading_alignment',
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
					'{{WRAPPER}} .ef-advanced-heading' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ================= Styling Section =================
		$this->start_controls_section(
			'style_section',
			[
				'label' => esc_html__( 'Typography & Colors', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'sub_heading_color',
			[
				'label'     => esc_html__( 'Sub Heading Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ef-sub-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'main_heading_color',
			[
				'label'     => esc_html__( 'Main Text Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ef-main-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'highlight_color',
			[
				'label'     => esc_html__( 'Highlight Color', 'element-forge' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ef-heading-highlight' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="ef-advanced-heading">
			<?php if ( ! empty( $settings['sub_heading_text'] ) ) : ?>
				<h4 class="ef-sub-heading"><?php echo wp_kses_post( $settings['sub_heading_text'] ); ?></h4>
			<?php endif; ?>
			
			<h2 class="ef-main-heading">
				<span class="ef-heading-prefix"><?php echo esc_html( $settings['main_heading_prefix'] ); ?></span>
				<?php if ( ! empty( $settings['main_heading_highlight'] ) ) : ?>
					<span class="ef-heading-highlight"><?php echo esc_html( $settings['main_heading_highlight'] ); ?></span>
				<?php endif; ?>
				<span class="ef-heading-suffix"><?php echo esc_html( $settings['main_heading_suffix'] ); ?></span>
			</h2>
		</div>
		<?php
	}

	protected function content_template() {
		?>
		<div class="ef-advanced-heading">
			<# if ( settings.sub_heading_text ) { #>
				<h4 class="ef-sub-heading">{{{ settings.sub_heading_text }}}</h4>
			<# } #>
			<h2 class="ef-main-heading">
				<span class="ef-heading-prefix">{{{ settings.main_heading_prefix }}}</span>
				<# if ( settings.main_heading_highlight ) { #>
					<span class="ef-heading-highlight">{{{ settings.main_heading_highlight }}}</span>
				<# } #>
				<span class="ef-heading-suffix">{{{ settings.main_heading_suffix }}}</span>
			</h2>
		</div>
		<?php
	}
}


