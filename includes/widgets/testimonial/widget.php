<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Testimonial_Widget extends ElementForge_Widget_Base {

	public function get_name() {
		return 'elementforge_testimonial';
	}

	public function get_title() {
		return esc_html__( 'Testimonial', 'element-forge' );
	}

	public function get_icon() {
		return parent::get_icon();
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-testimonial-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Testimonial', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'testimonial_content',
			[
				'label'   => esc_html__( 'Review', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'rows'    => 10,
				'default' => esc_html__( '"ElementForge is incredibly lightweight and exactly what my workflow needed. Highly recommended!"', 'element-forge' ),
			]
		);

		$this->add_control(
			'testimonial_image',
			[
				'label'   => esc_html__( 'Reviewer Image', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'testimonial_name',
			[
				'label'   => esc_html__( 'Name', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Jane Doe', 'element-forge' ),
			]
		);

		$this->add_control(
			'testimonial_job',
			[
				'label'   => esc_html__( 'Job / Role', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'CEO at TechCorp', 'element-forge' ),
			]
		);

		$this->add_control(
			'testimonial_rating',
			[
				'label'   => esc_html__( 'Rating (0-5)', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 5,
				'step'    => 1,
				'default' => 5,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="ef-testimonial-wrapper">
			<div class="ef-testimonial-stars">
				<?php
				$rating = intval( $settings['testimonial_rating'] );
				for ( $i = 0; $i < 5; $i++ ) {
					if ( $i < $rating ) {
						echo '<span class="dashicons dashicons-star-filled"></span>';
					} else {
						echo '<span class="dashicons dashicons-star-empty"></span>';
					}
				}
				?>
			</div>
			
			<div class="ef-testimonial-content">
				<?php echo wp_kses_post( $settings['testimonial_content'] ); ?>
			</div>
			
			<div class="ef-testimonial-meta">
				<?php if ( ! empty( $settings['testimonial_image']['url'] ) ) : ?>
					<div class="ef-testimonial-image">
						<img src="<?php echo esc_url( $settings['testimonial_image']['url'] ); ?>" alt="<?php echo esc_attr( $settings['testimonial_name'] ); ?>">
					</div>
				<?php endif; ?>
				<div class="ef-testimonial-details">
					<h4 class="ef-testimonial-name"><?php echo esc_html( $settings['testimonial_name'] ); ?></h4>
					<span class="ef-testimonial-job"><?php echo esc_html( $settings['testimonial_job'] ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}
}
