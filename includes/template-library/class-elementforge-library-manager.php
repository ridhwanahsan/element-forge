<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_Library_Manager {

	private static $instance = null;

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'elementor/init', [ $this, 'register_source' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_editor_styles' ] );
		
		// Setup AJAX endpoints for UI actions
		add_action( 'wp_ajax_elementforge_get_templates', [ $this, 'ajax_get_templates' ] );
		add_action( 'wp_ajax_elementforge_import_template', [ $this, 'ajax_import_template' ] );
	}

	public function register_source() {
		require_once ELEMENT_FORGE_PATH . 'includes/template-library/class-elementforge-template-source.php';
		\Elementor\Plugin::$instance->templates_manager->register_source( 'ElementForge_Template_Source' );
	}

	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			'elementforge-editor-library',
			ELEMENT_FORGE_URL . 'assets/js/elementforge-editor-library.js',
			[ 'jquery', 'wp-util', 'underscore' ],
			ELEMENT_FORGE_VERSION,
			true
		);

		wp_localize_script( 'elementforge-editor-library', 'ElementForgeLibrary', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'elementforge_library_nonce' ),
		] );

		// Localizable strings for the editor JS - no hardcoded English in JS files.
		wp_localize_script( 'elementforge-editor-library', 'elementForgeEditorI18n', [
			'loading'     => esc_html__( 'Loading Templates...', 'element-forge' ),
			'insert'      => esc_html__( 'Insert', 'element-forge' ),
			'inserting'   => esc_html__( 'Inserting...', 'element-forge' ),
			'noTemplates' => esc_html__( 'No templates found.', 'element-forge' ),
			'error'       => esc_html__( 'Error fetching templates.', 'element-forge' ),
		] );
	}

	public function enqueue_editor_styles() {
		wp_enqueue_style(
			'elementforge-editor-library',
			ELEMENT_FORGE_URL . 'assets/css/elementforge-editor-library.css',
			[],
			ELEMENT_FORGE_VERSION
		);
	}

	public function ajax_get_templates() {
		check_ajax_referer( 'elementforge_library_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Insufficient permissions.', 'element-forge' ) ] );
			return;
		}

		// Instantiate Source
		$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'element-forge' );
		
		if ( ! $source ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Template source not found.', 'element-forge' ) ] );
			return;
		}

		$items = $source->get_items();
		wp_send_json_success( $items );
	}

	public function ajax_import_template() {
		check_ajax_referer( 'elementforge_library_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Insufficient permissions.', 'element-forge' ) ] );
			return;
		}
		
		$template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
		
		if ( empty( $template_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Missing Template ID.', 'element-forge' ) ] );
			return;
		}

		$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'element-forge' );
		
		if ( ! $source ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Template source not found.', 'element-forge' ) ] );
			return;
		}

		$template_data = $source->get_data( [ 'template_id' => $template_id ] );
		
		if ( is_wp_error( $template_data ) ) {
			wp_send_json_error( [ 'message' => esc_html( $template_data->get_error_message() ) ] );
			return;
		}

		wp_send_json_success( $template_data );
	}
}

