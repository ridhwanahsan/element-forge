<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ElementForge_Template_Source
 *
 * Extends Elementor's Source_Base to implement a remote template library.
 * Templates are fetched from the configured API endpoint and cached via transients.
 *
 * @package ElementForge
 * @since   1.0.0
 */
class ElementForge_Template_Source extends \Elementor\TemplateLibrary\Source_Base {

	/**
	 * Transient cache key for the templates list.
	 */
	const CACHE_KEY = 'elementforge_templates_list';

	/**
	 * Cache duration: 12 hours.
	 */
	const CACHE_EXPIRY = 43200;

	/**
	 * Get the remote API base URL.
	 * Filterable so developers can point it to their own endpoint.
	 *
	 * @return string
	 */
	private function get_api_url() {
		return apply_filters( 'elementforge_template_api_url', 'https://api.elementforge.com/v1/templates/' );
	}

	/**
	 * Unique source ID used by Elementor internally.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'elementforge';
	}

	/**
	 * Source label shown in the Elementor template library UI.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'ElementForge Library', 'element-forge' );
	}

	/**
	 * Required by Source_Base â€” not used for remote sources.
	 */
	public function register_data() {}

	public function get_items( $args = [] ) {
		// Mock implementation
		return [
			[
				'template_id' => 'ef-hero-1',
				'title'       => __( 'Modern Hero Section', 'element-forge' ),
				'type'        => 'section',
				'thumbnail'   => 'https://picsum.photos/300/200',
				'date'        => gmdate('Y-m-d'),
				'tags'        => [ 'hero', 'modern' ],
				'is_pro'      => false,
			],
			[
				'template_id' => 'ef-pricing-1',
				'title'       => __( 'SaaS Pricing Table', 'element-forge' ),
				'type'        => 'section',
				'thumbnail'   => 'https://picsum.photos/300/200?random=2',
				'date'        => gmdate('Y-m-d'),
				'tags'        => [ 'pricing', 'saas' ],
				'is_pro'      => true,
			],
		];
	}

	public function get_data( array $args, $context = 'display' ) {
		if ( empty( $args['template_id'] ) ) {
			return new \WP_Error( 'missing_id', esc_html__( 'Template ID is required.', 'element-forge' ) );
		}

		$template_id = sanitize_text_field( $args['template_id'] );

        // Very basic mock of Elementor JSON
		return [
			'page_settings' => [],
			'type'          => 'section',
			'content'       => [
				[
					'id'         => 'ef_mock_' . wp_generate_password( 7, false ),
					'elType'     => 'section',
					'isInner'    => false,
					'settings'   => [],
					'elements'   => [
						[
							'id'       => 'ef_mock_col_' . wp_generate_password( 7, false ),
							'elType'   => 'column',
							'isInner'  => false,
							'settings' => [ '_column_size' => 100 ],
							'elements' => [
								[
									'id'       => 'ef_mock_wid_' . wp_generate_password( 7, false ),
									'elType'   => 'widget',
									'isInner'  => false,
									'settings' => [
										'title' => 'Imported ElementForge Template: ' . esc_html( $template_id ),
									],
									'widgetType' => 'heading',
								],
							],
						],
					],
				],
			],
		];
	}

	// Required abstract methods â€” remote source does not support write operations.

	/**
	 * @param array $template_data Template data.
	 * @return \WP_Error
	 */
	public function save_item( $template_data ) {
		return new \WP_Error( 'not_supported', esc_html__( 'Saving is not supported for this source.', 'element-forge' ) );
	}

	/**
	 * @param array $new_data New template data.
	 * @return \WP_Error
	 */
	public function update_item( $new_data ) {
		return new \WP_Error( 'not_supported', esc_html__( 'Updating is not supported for this source.', 'element-forge' ) );
	}

	/**
	 * @param int $template_id Template ID.
	 * @return \WP_Error
	 */
	public function delete_template( $template_id ) {
		return new \WP_Error( 'not_supported', esc_html__( 'Deleting is not supported for this source.', 'element-forge' ) );
	}

	/**
	 * @param int $template_id Template ID.
	 * @return \WP_Error
	 */
	public function export_template( $template_id ) {
		return new \WP_Error( 'not_supported', esc_html__( 'Exporting is not supported for this source.', 'element-forge' ) );
	}

	/**
	 * @param int $template_id Template ID.
	 * @return array
	 */
	public function get_item( $template_id ) {
		return [];
	}
}

