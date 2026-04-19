/**
 * ElementForge Editor Library Script
 *
 * Injects the ElementForge template library button into the Elementor editor
 * and handles AJAX template fetching and import.
 *
 * @package ElementForge
 * @since   1.0.0
 */

( function ( $ ) {
	'use strict';

	/**
	 * ElementForge Library namespace object.
	 * All methods are kept within this closure to avoid polluting global scope.
	 */
	var efLibrary = {

		/**
		 * Initialize the library by hooking into Elementor's preview:loaded event.
		 */
		init: function () {
			elementor.on( 'preview:loaded', function () {
				efLibrary.initModal();
				efLibrary.addLibraryButton();
			} );
		},

		/**
		 * Inject the modal HTML into the Elementor editor DOM (once only).
		 */
		initModal: function () {
			if ( $( '#ef-library-modal' ).length > 0 ) {
				return;
			}

			var $modal = $(
				'<div id="ef-library-modal" class="ef-library-modal" aria-hidden="true" role="dialog">' +
					'<div class="ef-modal-overlay"></div>' +
					'<div class="ef-modal-content">' +
						'<div class="ef-modal-header">' +
							'<h2 class="ef-modal-title">ElementForge Library</h2>' +
							'<button class="ef-modal-close" aria-label="Close">' +
								'<i class="eicon-close"></i>' +
							'</button>' +
						'</div>' +
						'<div class="ef-modal-body">' +
							'<p class="ef-loading">' + window.elementForgeEditorI18n.loading + '</p>' +
							'<div class="ef-templates-grid" hidden></div>' +
						'</div>' +
					'</div>' +
				'</div>'
			);

			$( 'body' ).append( $modal );

			// Close on overlay or close button click.
			$modal.find( '.ef-modal-close, .ef-modal-overlay' ).on( 'click', function () {
				efLibrary.closeModal();
			} );

			// Close on ESC key.
			$( document ).on( 'keyup.efLibrary', function ( e ) {
				if ( 27 === e.which ) {
					efLibrary.closeModal();
				}
			} );
		},

		/**
		 * Open the library modal.
		 */
		openModal: function () {
			var $modal = $( '#ef-library-modal' );
			$modal.removeAttr( 'aria-hidden' );
			efLibrary.fetchTemplates();
		},

		/**
		 * Close the library modal.
		 */
		closeModal: function () {
			$( '#ef-library-modal' ).attr( 'aria-hidden', 'true' );
		},

		/**
		 * Poll the Elementor canvas to add the ElementForge button to each Add Section area.
		 * Uses MutationObserver for efficiency, falling back to a brief interval.
		 */
		addLibraryButton: function () {
			var $previewContent = elementor.$previewContents;
			var observer;

			var attach = function () {
				$previewContent.find( '.elementor-add-section-inner' ).each( function () {
					if ( $( this ).find( '.ef-add-button' ).length === 0 ) {
						var $btn = $(
							'<div class="elementor-add-section-area-button ef-add-button" ' +
								'role="button" tabindex="0" ' +
								'title="ElementForge Library">' +
								'<i class="eicon-code"></i>' +
							'</div>'
						);
						$btn.on( 'click', function ( e ) {
							e.preventDefault();
							efLibrary.openModal();
						} );
						$( this ).append( $btn );
					}
				} );
			};

			// Use MutationObserver if available.
			if ( window.MutationObserver ) {
				observer = new MutationObserver( attach );
				$previewContent.each( function () {
					observer.observe( this, { childList: true, subtree: true } );
				} );
			}

			// Initial run.
			attach();
		},

		/**
		 * Fetch templates from the server via AJAX.
		 * Only fetches once; caches results in the grid.
		 */
		fetchTemplates: function () {
			var $grid    = $( '#ef-library-modal .ef-templates-grid' );
			var $loading = $( '#ef-library-modal .ef-loading' );

			// Show modal.
			$( '#ef-library-modal' ).removeAttr( 'aria-hidden' );

			// If already loaded, just show.
			if ( $grid.children().length > 0 ) {
				return;
			}

			$loading.show();
			$grid.attr( 'hidden', true );

			$.ajax( {
				url:    window.ElementForgeLibrary.ajaxUrl,
				type:   'POST',
				data:   {
					action: 'elementforge_get_templates',
					nonce:  window.ElementForgeLibrary.nonce
				},
				success: function ( response ) {
					$loading.hide();
					$grid.removeAttr( 'hidden' );

					if ( response.success && response.data.length > 0 ) {
						$.each( response.data, function ( i, template ) {
							var $item = $(
								'<div class="ef-template-item">' +
									'<div class="ef-template-thumbnail">' +
										'<img src="' + $( '<div>' ).text( template.thumbnail ).html() + '" ' +
											'alt="' + $( '<div>' ).text( template.title ).html() + '" />' +
									'</div>' +
									'<div class="ef-template-title">' + $( '<div>' ).text( template.title ).html() + '</div>' +
									'<button class="ef-template-insert" data-id="' + $( '<div>' ).text( template.template_id ).html() + '">' +
										window.elementForgeEditorI18n.insert +
									'</button>' +
								'</div>'
							);
							$grid.append( $item );
						} );

						$grid.find( '.ef-template-insert' ).on( 'click', function () {
							var templateId = $( this ).data( 'id' );
							$( this ).text( window.elementForgeEditorI18n.inserting ).prop( 'disabled', true );
							efLibrary.importTemplate( templateId, $( this ) );
						} );
					} else {
						$grid.append( '<p>' + window.elementForgeEditorI18n.noTemplates + '</p>' );
					}
				},
				error: function () {
					$loading.text( window.elementForgeEditorI18n.error );
				}
			} );
		},

		/**
		 * Import a single template into the Elementor canvas.
		 *
		 * @param {string} templateId The template ID to import.
		 * @param {jQuery} $btn       The insert button jQuery element.
		 */
		importTemplate: function ( templateId, $btn ) {
			$.ajax( {
				url:   window.ElementForgeLibrary.ajaxUrl,
				type:  'POST',
				data:  {
					action:      'elementforge_import_template',
					template_id: templateId,
					nonce:       window.ElementForgeLibrary.nonce
				},
				success: function ( response ) {
					if ( response.success ) {
						elementor.channels.data.trigger( 'template:before:insert', {} );

						if ( response.data.content && response.data.content.length > 0 ) {
							elementor.getPreviewView().addChildModel( response.data.content[0] );
						}

						elementor.channels.data.trigger( 'template:after:insert', {} );
						efLibrary.closeModal();
					} else {
						window.console.error( 'ElementForge: Import failed - ' + response.data.message );
					}
					$btn.text( window.elementForgeEditorI18n.insert ).prop( 'disabled', false );
				},
				error: function () {
					window.console.error( 'ElementForge: Connection error during template import.' );
					$btn.text( window.elementForgeEditorI18n.insert ).prop( 'disabled', false );
				}
			} );
		}
	};

	// Boot the library when Elementor is ready.
	$( window ).on( 'elementor:init', efLibrary.init );

}( jQuery ) );
