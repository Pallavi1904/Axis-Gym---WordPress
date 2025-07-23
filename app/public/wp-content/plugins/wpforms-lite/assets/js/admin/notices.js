/* global wpforms_admin_notices */

/**
 * WPForms Dismissible Notices.
 *
 * @since 1.6.7.1
 */

var WPFormsAdminNotices = window.WPFormsAdminNotices || ( function( document, window, $ ) {
	/**
	 * Public functions and properties.
	 *
	 * @since 1.6.7.1
	 *
	 * @type {Object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.6.7.1
		 */
		init() {
			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.6.7.1
		 */
		ready() {
			app.events();
		},

		/**
		 * Dismissible notices events.
		 *
		 * @since 1.6.7.1
		 */
		events() {
			$( document ).on(
				'click',
				'.wpforms-notice .notice-dismiss, .wpforms-notice .wpforms-notice-dismiss',
				app.dismissNotice
			);
		},

		/**
		 * Dismiss notice event handler.
		 *
		 * @since 1.6.7.1
		 *
		 * @param {Object} e Event object.
		 */
		dismissNotice( e ) {
			const $element = $( e.target );

			if ( ! $element.hasClass( 'wpforms-review-out' ) ) {
				e.preventDefault();
			}

			$element.closest( '.wpforms-notice' ).remove();

			$.post(
				wpforms_admin_notices.ajax_url,
				{
					action: 'wpforms_notice_dismiss',
					nonce:   wpforms_admin_notices.nonce,
					id: 	 ( $element.closest( '.wpforms-notice' ).attr( 'id' ) || '' ).replace( 'wpforms-notice-', '' ),
				}
			);
		},
	};

	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsAdminNotices.init();
