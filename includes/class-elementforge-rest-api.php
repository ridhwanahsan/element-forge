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
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		$settings = wp_parse_args(
			$settings,
			[
				'disabled_widgets'         => [],
				'disabled_extensions'      => [],
				'disable_on_non_elementor' => 'yes',
				'load_fa_icons'            => 'yes',
				'remove_data_on_uninstall' => 'no',
			]
		);
		
		return rest_ensure_response( [
			'success' => true,
			'data'    => $settings,
		] );
	}

	public function update_settings( $request ) {
		$params = $request->get_json_params();
		
		if ( isset( $params['settings'] ) && is_array( $params['settings'] ) ) {
			$existing_settings = get_option( 'element_forge_settings', [] );
			$new_settings      = $params['settings'];

			if ( ! is_array( $existing_settings ) ) {
				$existing_settings = [];
			}

			$array_setting_keys = [
				'disabled_widgets',
				'disabled_extensions',
			];

			foreach ( $array_setting_keys as $setting_key ) {
				if ( ! array_key_exists( $setting_key, $new_settings ) ) {
					continue;
				}

				$setting_value = $new_settings[ $setting_key ];
				if ( ! is_array( $setting_value ) ) {
					$existing_settings[ $setting_key ] = [];
					continue;
				}

				$existing_settings[ $setting_key ] = array_values(
					array_unique(
						array_filter(
							array_map( 'sanitize_key', $setting_value )
						)
					)
				);
			}

			$string_setting_keys = [
				'disable_on_non_elementor' => [ 'yes', 'no' ],
				'load_fa_icons'            => [ 'yes', 'no' ],
				'remove_data_on_uninstall' => [ 'yes', 'no' ],
			];

			foreach ( $string_setting_keys as $setting_key => $allowed_values ) {
				if ( ! array_key_exists( $setting_key, $new_settings ) ) {
					continue;
				}

				$setting_value = sanitize_key( (string) $new_settings[ $setting_key ] );
				if ( in_array( $setting_value, $allowed_values, true ) ) {
					$existing_settings[ $setting_key ] = $setting_value;
				}
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
