<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Admin_Page {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		// Add type="module" for Vite bundles which export ES Modules.
		add_filter( 'script_loader_tag', [ $this, 'add_type_module_to_react_script' ], 10, 3 );
	}

	public function add_plugin_page() {
		// This page will be under "ElementForge"
		add_menu_page(
			__( 'ElementForge', 'element-forge' ), // page_title
			__( 'ElementForge', 'element-forge' ), // menu_title
			'manage_options', // capability
			'ElementForge', // menu_slug
			[ $this, 'create_admin_page' ], // function
			'dashicons-admin-generic', // icon_url
			58 // position
		);

		// Add Theme Builder submenu pointing to the ef-theme-builder CPT list table.
		add_submenu_page(
			'ElementForge',                              // parent_slug
			__( 'Theme Builder', 'element-forge' ),      // page_title
			__( 'Theme Builder', 'element-forge' ),      // menu_title
			'manage_options',                            // capability
			'ef-theme-builder-list',                     // menu_slug (unique slug)
			[ $this, 'redirect_to_theme_builder' ]       // callback (redirect)
		);
	}

	/**
	 * Redirect the Theme Builder submenu to the CPT list table.
	 * We use a real slug + callback to ensure WP registers it properly,
	 * then immediately redirect to the actual CPT list page.
	 */
	public function redirect_to_theme_builder() {
		wp_safe_redirect( admin_url( 'edit.php?post_type=ef-theme-builder' ) );
		exit;
	}

	public function create_admin_page() {
		?>
		<div class="wrap">
			<div id="root">
				<!-- React App will render here -->
				<p><?php esc_html_e( 'Loading ElementForge Dashboard...', 'element-forge' ); ?></p>
			</div>
		</div>
		<?php
	}

	public function enqueue_admin_scripts( $hook ) {
		// Only load scripts on the specific admin page
		if ( 'toplevel_page_ElementForge' !== $hook ) {
			return;
		}

		// Let's define the path and URL of the Dist folder
		$dist_url  = ELEMENT_FORGE_URL . 'dist/';
		$dist_path = ELEMENT_FORGE_PATH . 'dist/';

		// The JS file is predictable due to vite.config.ts setup
		$js_file = 'assets/main.js';
		
		if ( file_exists( $dist_path . $js_file ) ) {
			wp_enqueue_script(
				'element-forge-react-app',
				$dist_url . $js_file,
				[ 'wp-element' ], // We might depend on wp-element or just load it standalone.
				ELEMENT_FORGE_VERSION,
				true
			);

			// Pass settings to React
			wp_localize_script( 'element-forge-react-app', 'elementForge', [
				'apiUrl'    => esc_url_raw( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl' => ELEMENT_FORGE_URL,
				'adminUrl'  => esc_url_raw( admin_url() ),
				'version'   => ELEMENT_FORGE_VERSION,
			]);

			// Pass current user info separately for sidebar profile
			$current_user = wp_get_current_user();
			wp_localize_script( 'element-forge-react-app', 'elementForgeUser', [
				'displayName' => esc_html( $current_user->display_name ),
				'email'       => esc_html( $current_user->user_email ),
			]);
		}

		$text_fix_script = ELEMENT_FORGE_PATH . 'assets/js/elementforge-admin-text-fix.js';
		if ( file_exists( $text_fix_script ) ) {
			wp_enqueue_script(
				'element-forge-admin-text-fix',
				ELEMENT_FORGE_URL . 'assets/js/elementforge-admin-text-fix.js',
				[],
				(string) filemtime( $text_fix_script ),
				true
			);
		}

		// Note: The CSS filename might vary (e.g. index.css). We can scan the assets folder to enqueue the CSS.
		$assets_dir = $dist_path . 'assets/';
		if ( is_dir( $assets_dir ) ) {
			$files = scandir( $assets_dir );
			$css_counter = 1;
			foreach ( $files as $file ) {
				$ext = pathinfo( $file, PATHINFO_EXTENSION );
				if ( 'css' === $ext ) {
					wp_enqueue_style(
						'element-forge-react-styles-' . $css_counter,
						$dist_url . 'assets/' . $file,
						[],
						ELEMENT_FORGE_VERSION
					);
					$css_counter++;
				}
			}
		}
	}

	public function add_type_module_to_react_script( $tag, $handle, $src ) {
		if ( 'element-forge-react-app' !== $handle ) {
			return $tag;
		}
		// Change the script tag by adding type="module"
		return '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '-js"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}
}
