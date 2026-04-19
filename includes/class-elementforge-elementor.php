<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ElementForge_Elementor
 *
 * Registers the ElementForge widget category and all widget components
 * found inside the /includes/widgets/ directory.
 *
 * @package ElementForge
 * @since   1.0.0
 */
class ElementForge_Elementor {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Cached list of disabled widget slugs from the database.
	 *
	 * @var string[]
	 */
	private $disabled_widgets = [];

	/**
	 * Get or create the singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor â€” registers hooks and caches settings.
	 */
	public function __construct() {
		// Load and cache settings once to avoid multiple DB reads.
		$settings               = get_option( 'element_forge_settings', [] );
		$this->disabled_widgets = isset( $settings['disabled_widgets'] ) && is_array( $settings['disabled_widgets'] )
			? $settings['disabled_widgets']
			: [];

		// Register widget category.
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );

		// Register widgets.
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

		// Register per-widget frontend styles (lazy-loaded by Elementor).
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'register_widget_styles' ] );
	}

	/**
	 * Add the "ElementForge" category to the Elementor widget panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'ElementForge',
			[
				'title' => esc_html__( 'ElementForge', 'element-forge' ),
				'icon'  => 'eicon-plug',
			]
		);
	}

	/**
	 * Register widget CSS files.
	 * Only registered, NOT enqueued â€” Elementor enqueues them via get_style_depends().
	 */
	public function register_widget_styles() {
		foreach ( glob( ELEMENT_FORGE_PATH . 'includes/widgets/*', GLOB_ONLYDIR ) as $folder ) {
			$folder_name = basename( $folder );

			if ( in_array( $folder_name, $this->disabled_widgets, true ) ) {
				continue;
			}

			if ( file_exists( $folder . '/style.css' ) ) {
				wp_register_style(
					'elementforge-' . $folder_name . '-style',
					ELEMENT_FORGE_URL . 'includes/widgets/' . $folder_name . '/style.css',
					[],
					ELEMENT_FORGE_VERSION
				);
			}
		}
	}

	/**
	 * Register all widget classes discovered inside /includes/widgets/ subfolders.
	 *
	 * Naming convention: folder "my-widget" => class "ElementForge_My_Widget".
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		foreach ( glob( ELEMENT_FORGE_PATH . 'includes/widgets/*', GLOB_ONLYDIR ) as $folder ) {
			$folder_name = basename( $folder );

			if ( in_array( $folder_name, $this->disabled_widgets, true ) ) {
				continue;
			}

			$widget_file = $folder . '/widget.php';
			if ( ! file_exists( $widget_file ) ) {
				continue;
			}

			require_once $widget_file;

			// Convert folder slug to class name: 'my-widget' => 'ElementForge_My_Widget'.
			$class_name = 'ElementForge_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $folder_name ) ) ) . '_Widget';

			if ( class_exists( $class_name ) ) {
				$widgets_manager->register( new $class_name() );
			}
		}
	}
}



