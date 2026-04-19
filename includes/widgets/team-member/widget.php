<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Team_Member_Widget extends ElementForge_Widget_Base {

	public function get_name() {
		return 'elementforge_team_member';
	}

	public function get_title() {
		return esc_html__( 'Team Member', 'element-forge' );
	}

	public function get_icon() {
		return parent::get_icon();
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-team-member-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Member Details', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'member_image',
			[
				'label'   => esc_html__( 'Profile Image', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'member_name',
			[
				'label'   => esc_html__( 'Name', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'John Doe', 'element-forge' ),
			]
		);

		$this->add_control(
			'member_role',
			[
				'label'   => esc_html__( 'Role / Position', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Project Manager', 'element-forge' ),
			]
		);

		$this->add_control(
			'member_bio',
			[
				'label'   => esc_html__( 'Short Bio', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Passionate about building scalable digital solutions.', 'element-forge' ),
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'social_section',
			[
				'label' => esc_html__( 'Social Links', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'social_icon',
			[
				'label'   => esc_html__( 'Icon', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fab fa-twitter',
					'library' => 'fa-brands',
				],
			]
		);
		$repeater->add_control(
			'social_link',
			[
				'label' => esc_html__( 'Link', 'element-forge' ),
				'type'  => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://twitter.com/',
			]
		);

		$this->add_control(
			'social_list',
			[
				'label'   => esc_html__( 'Profiles', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => [
					[
						'social_icon' => [ 'value' => 'fab fa-twitter', 'library' => 'fa-brands' ],
						'social_link' => [ 'url' => '#' ],
					],
					[
						'social_icon' => [ 'value' => 'fab fa-linkedin', 'library' => 'fa-brands' ],
						'social_link' => [ 'url' => '#' ],
					],
				],
				'title_field' => 'Social Link',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="ef-team-member-card">
			<div class="ef-team-image">
				<?php if ( ! empty( $settings['member_image']['url'] ) ) : ?>
					<img src="<?php echo esc_url( $settings['member_image']['url'] ); ?>" alt="<?php echo esc_attr( $settings['member_name'] ); ?>">
				<?php endif; ?>
				
				<div class="ef-team-overlay">
					<div class="ef-team-social">
						<?php foreach ( $settings['social_list'] as $social ) : 
							$target = $social['social_link']['is_external'] ? ' target="_blank"' : '';
							$url = ! empty( $social['social_link']['url'] ) ? $social['social_link']['url'] : '#';
						?>
							<a href="<?php echo esc_url( $url ); ?>" class="ef-social-icon"<?php echo wp_kses_post( $target ); ?>>
								<?php \Elementor\Icons_Manager::render_icon( $social['social_icon'], [ 'aria-hidden' => 'true' ] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			
			<div class="ef-team-content">
				<h3 class="ef-team-name"><?php echo esc_html( $settings['member_name'] ); ?></h3>
				<span class="ef-team-role"><?php echo esc_html( $settings['member_role'] ); ?></span>
				<p class="ef-team-bio"><?php echo esc_html( $settings['member_bio'] ); ?></p>
			</div>
		</div>
		<?php
	}
}
