<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Countdown_Timer_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'elementforge_countdown_timer';
	}

	public function get_title() {
		return esc_html__( 'Countdown Timer', 'element-forge' );
	}

	public function get_icon() {
		return 'eicon-countdown';
	}

	public function get_categories() {
		return [ 'ElementForge' ];
	}

	public function get_style_depends() {
		return [ 'elementforge-countdown-timer-style' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Timer Options', 'element-forge' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'due_date',
			[
				'label'       => esc_html__( 'Due Date', 'element-forge' ),
				'type'        => \Elementor\Controls_Manager::DATE_TIME,
				'default'     => gmdate( 'Y-m-d H:i', strtotime( '+1 month' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
				'description' => esc_html__( 'Set the target date and time for the countdown.', 'element-forge' ),
			]
		);

		$this->add_control(
			'label_days',
			[
				'label'   => esc_html__( 'Days Label', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Days', 'element-forge' ),
			]
		);
		$this->add_control(
			'label_hours',
			[
				'label'   => esc_html__( 'Hours Label', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Hours', 'element-forge' ),
			]
		);
		$this->add_control(
			'label_minutes',
			[
				'label'   => esc_html__( 'Minutes Label', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Minutes', 'element-forge' ),
			]
		);
		$this->add_control(
			'label_seconds',
			[
				'label'   => esc_html__( 'Seconds Label', 'element-forge' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Seconds', 'element-forge' ),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id = 'ef-countdown-' . $this->get_id();
		$due_date = ! empty( $settings['due_date'] ) ? $settings['due_date'] : '';
		
		// Convert to timestamp
		$timestamp = strtotime( $due_date );
		if ( ! $timestamp ) {
			$timestamp = time() + ( 30 * DAY_IN_SECONDS ); // fallback 30 days
		}
		
		// For JS (convert back to UTC string so JS `new Date()` parses reliably)
		$js_date = gmdate( 'Y-m-d\TH:i:s\Z', $timestamp );

		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="ef-countdown-wrapper" data-date="<?php echo esc_attr( $js_date ); ?>">
			<div class="ef-countdown-item">
				<span class="ef-countdown-digits ef-days">00</span>
				<span class="ef-countdown-label"><?php echo esc_html( $settings['label_days'] ); ?></span>
			</div>
			<div class="ef-countdown-item">
				<span class="ef-countdown-digits ef-hours">00</span>
				<span class="ef-countdown-label"><?php echo esc_html( $settings['label_hours'] ); ?></span>
			</div>
			<div class="ef-countdown-item">
				<span class="ef-countdown-digits ef-minutes">00</span>
				<span class="ef-countdown-label"><?php echo esc_html( $settings['label_minutes'] ); ?></span>
			</div>
			<div class="ef-countdown-item">
				<span class="ef-countdown-digits ef-seconds">00</span>
				<span class="ef-countdown-label"><?php echo esc_html( $settings['label_seconds'] ); ?></span>
			</div>
		</div>

		<script>
			(function() {
				var wrapper = document.getElementById("<?php echo esc_js( $id ); ?>");
				if(!wrapper) return;
				var targetDate = new Date(wrapper.getAttribute("data-date")).getTime();
				
				var daysEl = wrapper.querySelector(".ef-days");
				var hoursEl = wrapper.querySelector(".ef-hours");
				var minutesEl = wrapper.querySelector(".ef-minutes");
				var secondsEl = wrapper.querySelector(".ef-seconds");

				function pad(n) { return (n < 10 ? '0' : '') + n; }

				function updateTimer() {
					var now = new Date().getTime();
					var distance = targetDate - now;

					if (distance < 0) {
						daysEl.innerText = "00";
						hoursEl.innerText = "00";
						minutesEl.innerText = "00";
						secondsEl.innerText = "00";
						return; // Stop timer
					}

					var days = Math.floor(distance / (1000 * 60 * 60 * 24));
					var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
					var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
					var seconds = Math.floor((distance % (1000 * 60)) / 1000);

					daysEl.innerText = pad(days);
					hoursEl.innerText = pad(hours);
					minutesEl.innerText = pad(minutes);
					secondsEl.innerText = pad(seconds);
				}

				updateTimer();
				setInterval(updateTimer, 1000);
			})();
		</script>
		<?php
	}
}
