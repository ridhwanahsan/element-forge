<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ElementForge_REST_API {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route( 'element-forge/v1', '/settings', [
			[
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_settings' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'update_settings' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	public function get_settings( $request ) {
		$settings = get_option( 'element_forge_settings', [] );
		
		return rest_ensure_response( [
			'success' => true,
			'data'    => $settings,
		] );
	}

	public function update_settings( $request ) {
		$params = $request->get_json_params();
		
		if ( isset( $params['settings'] ) && is_array( $params['settings'] ) ) {
			$existing_settings = get_option( 'element_forge_settings', [] );
			$new_settings = $params['settings'];

			// Specifically handle our disabled_widgets mapping properly
			if ( isset( $new_settings['disabled_widgets'] ) && is_array( $new_settings['disabled_widgets'] ) ) {
				$existing_settings['disabled_widgets'] = array_map( 'sanitize_text_field', $new_settings['disabled_widgets'] );
			} else if ( isset( $new_settings['disabled_widgets'] ) && empty( $new_settings['disabled_widgets'] ) ) {
				$existing_settings['disabled_widgets'] = [];
			}
			
			update_option( 'element_forge_settings', $existing_settings );
			
			return rest_ensure_response( [
				'success' => true,
				'message' => esc_html__( 'Settings updated successfully.', 'element-forge' ),
			] );
		}

		return new \WP_Error( 'missing_settings', __( 'Settings parameter is missing.', 'element-forge' ), [ 'status' => 400 ] );
	}
}

