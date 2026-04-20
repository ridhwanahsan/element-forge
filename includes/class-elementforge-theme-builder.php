<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ElementForge_Theme_Builder
 * Theme Builder with UAE-style Display Conditions (search-based chip UI).
 *
 * @package ElementForge
 * @since   1.0.0
 */
class ElementForge_Theme_Builder {

	private static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'register_cpt' ] );

		// Admin
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_meta_box_scripts' ] );

		// AJAX: search for pages/posts/terms
		add_action( 'wp_ajax_ef_search_items', [ $this, 'ajax_search_items' ] );

		// Admin menu highlight
		add_filter( 'parent_file', [ $this, 'fix_parent_menu' ] );
		add_filter( 'submenu_file', [ $this, 'fix_submenu_menu' ] );

		// Frontend
		add_action( 'wp_body_open', [ $this, 'render_header' ], 5 );
		add_action( 'wp_footer', [ $this, 'render_footer' ], 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_override_styles' ] );
	}

	// =========================================================================
	// CPT
	// =========================================================================

	public function register_cpt() {
		register_post_type( 'ef-theme-builder', [
			'labels'              => [
				'name'               => _x( 'Theme Builder Templates', 'Post type general name', 'element-forge' ),
				'singular_name'      => _x( 'Theme Builder Template', 'Post type singular name', 'element-forge' ),
				'add_new'            => __( 'Add New', 'element-forge' ),
				'add_new_item'       => __( 'Add New Template', 'element-forge' ),
				'edit_item'          => __( 'Edit Template', 'element-forge' ),
				'new_item'           => __( 'New Template', 'element-forge' ),
				'view_item'          => __( 'View Template', 'element-forge' ),
				'search_items'       => __( 'Search Templates', 'element-forge' ),
				'not_found'          => __( 'No Templates found.', 'element-forge' ),
				'not_found_in_trash' => __( 'No Templates found in Trash.', 'element-forge' ),
			],
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title', 'editor' ],
			'rewrite'             => false,
		] );

		// Enable Elementor for this CPT
		$cpt_support = get_option( 'elementor_cpt_support', [ 'page', 'post' ] );
		if ( ! in_array( 'ef-theme-builder', $cpt_support, true ) ) {
			$cpt_support[] = 'ef-theme-builder';
			update_option( 'elementor_cpt_support', $cpt_support );
		}
	}

	// =========================================================================
	// AJAX SEARCH
	// =========================================================================

	/**
	 * AJAX handler: search posts + terms and return as JSON.
	 */
	public function ajax_search_items() {
		check_ajax_referer( 'ef_tb_search', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		if ( strlen( $search ) < 2 ) {
			wp_send_json_success( [] );
		}

		$results = [];

		// Search Posts & Pages
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		unset( $post_types['attachment'] );

		$posts = get_posts( [
			's'              => $search,
			'post_type'      => array_keys( $post_types ),
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'no_found_rows'  => true,
		] );

		foreach ( $posts as $post ) {
			$type_label = isset( $post_types[ $post->post_type ] ) ? $post_types[ $post->post_type ]->labels->singular_name : ucfirst( $post->post_type );
			$results[]  = [
				'id'    => $post->ID,
				'title' => get_the_title( $post ),
				'type'  => $post->post_type,
				'label' => $type_label,
				'kind'  => 'post',
			];
		}

		// Search Terms (categories + tags)
		$taxonomies = [ 'category', 'post_tag' ];
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms( [
				'taxonomy'   => $taxonomy,
				'search'     => $search,
				'hide_empty' => false,
				'number'     => 5,
			] );
			if ( ! is_wp_error( $terms ) ) {
				$tax_obj = get_taxonomy( $taxonomy );
				foreach ( $terms as $term ) {
					$results[] = [
						'id'    => $term->term_id,
						'title' => $term->name,
						'type'  => $taxonomy,
						'label' => $tax_obj->labels->singular_name,
						'kind'  => 'term',
					];
				}
			}
		}

		wp_send_json_success( $results );
	}

	// =========================================================================
	// META BOXES
	// =========================================================================

	public function add_meta_boxes() {
		// Single combined meta box (UAE-style)
		add_meta_box(
			'ef-tb-options',
			__( 'ElementForge Header & Footer Builder Options', 'element-forge' ),
			[ $this, 'render_combined_meta_box' ],
			'ef-theme-builder',
			'normal',
			'high'
		);
	}

	public function enqueue_meta_box_scripts( $hook ) {
		global $post;
		if (
			( 'post.php' === $hook || 'post-new.php' === $hook ) &&
			isset( $post ) &&
			'ef-theme-builder' === $post->post_type
		) {
			wp_enqueue_script(
				'ef-tb-meta-box',
				ELEMENT_FORGE_URL . 'assets/js/theme-builder-meta.js',
				[ 'jquery' ],
				ELEMENT_FORGE_VERSION,
				true
			);
			wp_localize_script( 'ef-tb-meta-box', 'efTbData', [
				'ajaxUrl'        => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
				'nonce'          => wp_create_nonce( 'ef_tb_search' ),
				'conditionOptions' => $this->get_condition_options(),
				'conditionsWithSearch' => [ 'specific_pages' ],
				'i18n'           => [
					'searchPlaceholder' => __( 'Search pages / posts / categories...', 'element-forge' ),
					'minChars'          => __( 'Please enter 2 or more characters', 'element-forge' ),
					'noResults'         => __( 'No results found', 'element-forge' ),
					'searching'         => __( 'Searching...', 'element-forge' ),
				],
			] );
			wp_enqueue_style(
				'ef-tb-meta-box-style',
				ELEMENT_FORGE_URL . 'assets/css/theme-builder-meta.css',
				[],
				ELEMENT_FORGE_VERSION
			);
		}
	}

	/**
	 * Combined meta box (UAE-style): Type of Template + Display Conditions.
	 */
	public function render_combined_meta_box( $post ) {
		wp_nonce_field( 'ef_tb_save_' . $post->ID, 'ef_tb_nonce' );

		$template_type   = get_post_meta( $post->ID, '_ef_template_type', true );
		$display_rules   = get_post_meta( $post->ID, '_ef_display_rules', true );
		$exclusion_rules = get_post_meta( $post->ID, '_ef_exclusion_rules', true );

		if ( ! is_array( $display_rules ) || empty( $display_rules ) ) {
			$display_rules = [ [ 'condition' => 'specific_pages', 'items' => [] ] ];
		}
		if ( ! is_array( $exclusion_rules ) ) {
			$exclusion_rules = [];
		}
		?>
		<div id="ef-tb-options-wrap">
			<table class="ef-options-table">

				<!-- Row 1: Type of Template -->
				<tr>
					<th><label for="ef_template_type"><?php esc_html_e( 'Type of Template', 'element-forge' ); ?></label></th>
					<td>
						<select name="ef_template_type" id="ef_template_type" class="ef-select-full">
							<option value=""><?php esc_html_e( '- Select Type -', 'element-forge' ); ?></option>
							<option value="header" <?php selected( $template_type, 'header' ); ?>><?php esc_html_e( 'Header', 'element-forge' ); ?></option>
							<option value="footer" <?php selected( $template_type, 'footer' ); ?>><?php esc_html_e( 'Footer', 'element-forge' ); ?></option>
						</select>
					</td>
				</tr>

				<!-- Row 2: Display On -->
				<tr class="ef-display-row">
					<th>
						<?php esc_html_e( 'Display On', 'element-forge' ); ?>
						<span class="ef-help-icon" title="<?php esc_attr_e( 'Choose where this template appears on your website.', 'element-forge' ); ?>">?</span>
					</th>
					<td>
						<!-- Display Rules -->
						<div id="ef-display-rules-list">
							<?php foreach ( $display_rules as $i => $rule ) :
								$this->render_rule_row( 'ef_display_rules', $i, $rule, false );
							endforeach; ?>
						</div>
						<!-- Exclusion Rules (hidden if empty) -->
						<?php if ( ! empty( $exclusion_rules ) ) : ?>
						<div id="ef-exclusion-rules-list" class="ef-exclusion-list">
							<?php foreach ( $exclusion_rules as $i => $rule ) :
								$this->render_rule_row( 'ef_exclusion_rules', $i, $rule, true );
							endforeach; ?>
						</div>
						<?php else : ?>
						<div id="ef-exclusion-rules-list" class="ef-exclusion-list" style="display:none;"></div>
						<?php endif; ?>
						<!-- Buttons -->
						<div class="ef-action-btns">
							<button type="button" class="ef-add-rule-btn" data-target="ef-display-rules-list" data-prefix="ef_display_rules" data-type="display">
								<?php esc_html_e( 'Add Display Rule', 'element-forge' ); ?>
							</button>
							<button type="button" class="ef-add-rule-btn ef-add-exclusion-btn" data-target="ef-exclusion-rules-list" data-prefix="ef_exclusion_rules" data-type="exclusion">
								<?php esc_html_e( 'Add Exclusion Rule', 'element-forge' ); ?>
							</button>
						</div>
					</td>
				</tr>

			</table>
		</div>
		<?php
	}

	/**
	 * Render a single rule row (PHP side).
	 */
	private function render_rule_row( $prefix, $index, $rule, $is_exclusion ) {
		$condition    = isset( $rule['condition'] ) ? $rule['condition'] : ( $is_exclusion ? 'specific_pages' : 'entire_site' );
		$items        = isset( $rule['items'] ) && is_array( $rule['items'] ) ? $rule['items'] : [];
		$items_json   = wp_json_encode( $items );
		$options      = $this->get_condition_options();
		$show_search  = ( 'specific_pages' === $condition );
		?>
		<div class="ef-rule-row" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="ef-rule-inner">
				<select
					name="<?php echo esc_attr( $prefix ); ?>[<?php echo esc_attr( $index ); ?>][condition]"
					class="ef-condition-select"
				>
					<?php foreach ( $options as $val => $label ) :
						if ( $is_exclusion && 'entire_site' === $val ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $condition, $val ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<!-- Search area (only if condition = specific_pages) -->
				<div class="ef-search-area" style="<?php echo $show_search ? '' : 'display:none;'; ?>">
					<!-- Chips container -->
					<div class="ef-chips-wrap">
						<?php foreach ( $items as $item ) : ?>
							<span class="ef-chip" data-id="<?php echo esc_attr( $item['id'] ); ?>" data-type="<?php echo esc_attr( $item['type'] ); ?>" data-kind="<?php echo esc_attr( $item['kind'] ); ?>">
								<span class="ef-chip-type"><?php echo esc_html( $item['label'] ); ?></span>
								<span class="ef-chip-title"><?php echo esc_html( $item['title'] ); ?></span>
								<button type="button" class="ef-chip-remove" title="<?php esc_attr_e( 'Remove', 'element-forge' ); ?>">&#x2715;</button>
							</span>
						<?php endforeach; ?>
					</div>
					<!-- Search input -->
					<div class="ef-search-box-wrap">
						<input type="text" class="ef-search-input" placeholder="<?php esc_attr_e( 'Search pages / posts / categories...', 'element-forge' ); ?>" autocomplete="off" />
						<div class="ef-search-dropdown" style="display:none;">
							<p class="ef-search-hint"><?php esc_html_e( 'Please enter 2 or more characters', 'element-forge' ); ?></p>
						</div>
					</div>
					<!-- Hidden JSON field -->
					<input
						type="hidden"
						class="ef-items-json"
						name="<?php echo esc_attr( $prefix ); ?>[<?php echo esc_attr( $index ); ?>][items]"
						value="<?php echo esc_attr( $items_json ); ?>"
					/>
				</div>
			</div>
			<button type="button" class="ef-remove-rule" title="<?php esc_attr_e( 'Remove Rule', 'element-forge' ); ?>">&#x2715;</button>
		</div>
		<?php
	}

	/**
	 * Available display condition options.
	 */
	private function get_condition_options() {
		return [
			'entire_site'       => __( 'Entire Website', 'element-forge' ),
			'front_page'        => __( 'Front Page', 'element-forge' ),
			'blog_page'         => __( 'Blog / Posts Page', 'element-forge' ),
			'singular'          => __( 'Singular', 'element-forge' ),
			'404_page'          => __( '404 Page', 'element-forge' ),
			'search_page'       => __( 'Search Results', 'element-forge' ),
			'post_type_archive' => __( 'Post Type Archive', 'element-forge' ),
			'category_archive'  => __( 'Category Archive', 'element-forge' ),
			'tag_archive'       => __( 'Tag Archive', 'element-forge' ),
			'specific_pages'    => __( 'Specific Pages / Posts / Taxonomies, etc.', 'element-forge' ),
		];
	}

	// =========================================================================
	// SAVE META
	// =========================================================================

	public function save_meta_boxes( $post_id, $post ) {
		if ( 'ef-theme-builder' !== $post->post_type ) {
			return;
		}
		if (
			! isset( $_POST['ef_tb_nonce'] ) ||
			! wp_verify_nonce( wp_unslash( $_POST['ef_tb_nonce'] ), 'ef_tb_save_' . $post_id ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Template type
		if ( isset( $_POST['ef_template_type'] ) ) {
			$type = sanitize_text_field( wp_unslash( $_POST['ef_template_type'] ) );
			if ( in_array( $type, [ 'header', 'footer' ], true ) ) {
				update_post_meta( $post_id, '_ef_template_type', $type );
			} else {
				delete_post_meta( $post_id, '_ef_template_type' );
			}
		}

		// Rules
		update_post_meta( $post_id, '_ef_display_rules',   $this->sanitize_rules( 'ef_display_rules' ) );
		update_post_meta( $post_id, '_ef_exclusion_rules', $this->sanitize_rules( 'ef_exclusion_rules' ) );
	}

	/**
	 * Sanitize rules from POST.
	 */
	private function sanitize_rules( $key ) {
		$rules = [];
		if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $rules;
		}
		$valid_conditions = array_keys( $this->get_condition_options() );
		foreach ( $_POST[ $key ] as $rule ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$condition = isset( $rule['condition'] ) ? sanitize_text_field( wp_unslash( $rule['condition'] ) ) : '';
			if ( ! $condition || ! in_array( $condition, $valid_conditions, true ) ) {
				continue;
			}
			$items = [];
			if ( 'specific_pages' === $condition && isset( $rule['items'] ) ) {
				$decoded = json_decode( wp_unslash( $rule['items'] ), true );
				if ( is_array( $decoded ) ) {
					foreach ( $decoded as $item ) {
						$items[] = [
							'id'    => absint( $item['id'] ),
							'type'  => sanitize_text_field( $item['type'] ),
							'kind'  => sanitize_text_field( $item['kind'] ),
							'title' => sanitize_text_field( $item['title'] ),
							'label' => sanitize_text_field( $item['label'] ),
						];
					}
				}
			}
			$rules[] = [
				'condition' => $condition,
				'items'     => $items,
			];
		}
		return $rules;
	}

	// =========================================================================
	// ADMIN MENU
	// =========================================================================

	public function fix_parent_menu( $parent_file ) {
		global $current_screen;
		if ( isset( $current_screen->post_type ) && 'ef-theme-builder' === $current_screen->post_type ) {
			$parent_file = 'ElementForge';
		}
		return $parent_file;
	}

	public function fix_submenu_menu( $submenu_file ) {
		global $current_screen;
		if ( isset( $current_screen->post_type ) && 'ef-theme-builder' === $current_screen->post_type ) {
			$submenu_file = 'ef-theme-builder-list';
		}
		return $submenu_file;
	}

	// =========================================================================
	// CONDITION CHECKER
	// =========================================================================

	private function rules_match( $rules ) {
		if ( empty( $rules ) ) {
			return false;
		}
		foreach ( $rules as $rule ) {
			$condition = isset( $rule['condition'] ) ? $rule['condition'] : '';
			$items     = isset( $rule['items'] ) ? $rule['items'] : [];

			switch ( $condition ) {
				case 'entire_site':
					return true;
				case 'front_page':
					if ( is_front_page() ) return true;
					break;
				case 'blog_page':
					if ( is_home() ) return true;
					break;
				case '404_page':
					if ( is_404() ) return true;
					break;
				case 'search_page':
					if ( is_search() ) return true;
					break;
				case 'singular':
					if ( is_singular() ) return true;
					break;
				case 'post_type_archive':
					if ( is_post_type_archive() ) return true;
					break;
				case 'category_archive':
					if ( is_category() ) return true;
					break;
				case 'tag_archive':
					if ( is_tag() ) return true;
					break;
				case 'specific_pages':
					if ( ! empty( $items ) ) {
						$post_ids = [];
						$term_ids = [];
						foreach ( $items as $item ) {
							if ( 'post' === $item['kind'] ) {
								$post_ids[] = (int) $item['id'];
							} elseif ( 'term' === $item['kind'] ) {
								$term_ids[] = (int) $item['id'];
							}
						}
						if ( is_singular() && in_array( (int) get_the_ID(), $post_ids, true ) ) {
							return true;
						}
						if ( is_tax() || is_category() || is_tag() ) {
							$queried_term = get_queried_object();
							if ( $queried_term && in_array( (int) $queried_term->term_id, $term_ids, true ) ) {
								return true;
							}
						}
					}
					break;
			}
		}
		return false;
	}

	private function should_render( $post_id ) {
		$display_rules   = get_post_meta( $post_id, '_ef_display_rules', true );
		$exclusion_rules = get_post_meta( $post_id, '_ef_exclusion_rules', true );
		if ( ! is_array( $display_rules ) || empty( $display_rules ) ) {
			return false;
		}
		if ( ! $this->rules_match( $display_rules ) ) {
			return false;
		}
		if ( is_array( $exclusion_rules ) && ! empty( $exclusion_rules ) && $this->rules_match( $exclusion_rules ) ) {
			return false;
		}
		return true;
	}

	private function get_active_template_id( $type ) {
		$posts = get_posts( [
			'post_type'              => 'ef-theme-builder',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_query'             => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'   => '_ef_template_type',
					'value' => $type,
				],
			],
		] );
		foreach ( $posts as $post ) {
			if ( $this->should_render( $post->ID ) ) {
				return $post->ID;
			}
		}
		return null;
	}

	// =========================================================================
	// FRONTEND RENDERING
	// =========================================================================

	public function enqueue_override_styles() {
		if ( $this->is_elementor_editor() ) return;
		$header_id = $this->get_active_template_id( 'header' );
		$footer_id = $this->get_active_template_id( 'footer' );
		$css = '';
		if ( $header_id ) {
			$css .= '#masthead,.site-header,header#header,.ast-site-header-cartbar-scroll,#site-header,.header-main{display:none!important;}';
		}
		if ( $footer_id ) {
			$css .= '#colophon,.site-footer,footer#footer,.ast-site-footer,#site-footer,.footer-main{display:none!important;}';
		}
		if ( $css ) {
			wp_register_style( 'ef-theme-builder-override', false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( 'ef-theme-builder-override' );
			wp_add_inline_style( 'ef-theme-builder-override', $css );
		}
	}

	public function render_header() {
		if ( $this->is_elementor_editor() ) return;
		$id = $this->get_active_template_id( 'header' );
		if ( ! $id || ! class_exists( '\Elementor\Plugin' ) ) return;
		echo '<div class="ef-theme-builder-header elementor">';
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	public function render_footer() {
		if ( $this->is_elementor_editor() ) return;
		$id = $this->get_active_template_id( 'footer' );
		if ( ! $id || ! class_exists( '\Elementor\Plugin' ) ) return;
		echo '<div class="ef-theme-builder-footer elementor">';
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	private function is_elementor_editor() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) return false;
		return \Elementor\Plugin::$instance->preview->is_preview_mode() ||
			   \Elementor\Plugin::$instance->editor->is_edit_mode();
	}
}
