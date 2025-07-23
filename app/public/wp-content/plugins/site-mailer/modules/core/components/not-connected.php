<?php

namespace SiteMailer\Modules\Core\Components;

use SiteMailer\Classes\Utils;
use SiteMailer\Modules\Connect\Module as Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Not_Connected {
	const NOT_CONNECTED_NOTICE_SLUG = 'site-mailer-not-connected';

	public function render_not_connected_notice() {
		if ( Pointers::is_dismissed( self::NOT_CONNECTED_NOTICE_SLUG ) ) {
			return;
		}

		?>
		<div class="notice notice-info notice is-dismissible site-mailer__notice site-mailer__notice--pink"
			 data-notice-slug="<?php echo esc_attr( self::NOT_CONNECTED_NOTICE_SLUG ); ?>">
			<div class="site-mailer__icon-block">
				<svg width="32" height="32" fill="none" role="presentation">
				<g clip-path="url(#clip0_5917_59556)">
				<circle cx="16" cy="16" r="16" fill="#FF7BE5" />
				<path fill-rule="evenodd" clip-rule="evenodd" d="M15.9452 7.70649C15.9805 7.69159 16.0203 7.69159 16.0556 7.70649L26.0368 11.9176C26.337 12.0442 26.532 12.3383 26.532 12.664V23.899C26.532 24.1221 26.3507 24.3029 26.127 24.3029H5.87381C5.6501 24.3029 5.46875 24.1221 5.46875 23.899V12.1673L16.0004 15.7966L19.4434 15.2903L16.0004 17.0118V24.1952C16.0004 24.2326 16.0468 24.25 16.0714 24.2218L26.1924 12.6549C26.3643 12.4585 26.2248 12.1511 25.9638 12.1511L5.46875 12.1511V12.1266L15.9452 7.70649Z" fill="white" />
			</g>
			<defs>
				<clipPath id="clip0_5917_59556">
					<rect width="32" height="32" fill="white" />
				</clipPath>
			</defs>
				</svg>
			</div>

			<p>
				<b>
					<?php esc_html_e(
						'Site Mailer is not connected right now. Connect your account to ensure your site reliably sends emails.',
						'site-mailer'
					); ?>
				</b>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . \SiteMailer\Modules\Settings\Module::SETTING_BASE_SLUG ) ); ?>">
						<?php esc_html_e(
							'Connect now',
							'site-mailer'
						); ?>
					</a>
			</p>
		</div>

		<script>
			const onNotConnectedNoticeClose = () => {
				const pointer = '<?php echo esc_js( self::NOT_CONNECTED_NOTICE_SLUG ); ?>';

				return wp.ajax.post( 'site_mailer_pointer_dismissed', {
					data: {
						pointer,
					},
					nonce: '<?php echo esc_js( wp_create_nonce( 'site-mailer-pointer-dismissed' ) ); ?>',
				} );
			}

			jQuery( document ).ready( function( $ ) {
				setTimeout(() => {
					const $closeButton = $( '[data-notice-slug="<?php echo esc_js( self::NOT_CONNECTED_NOTICE_SLUG ); ?>"] .notice-dismiss' )

					$closeButton
						.first()
						.on( 'click', onNotConnectedNoticeClose )

					$( '[data-notice-slug="<?php echo esc_js( self::NOT_CONNECTED_NOTICE_SLUG ); ?>"] a' )
						.first()
						.on( 'click', function ( e ) {
							e.preventDefault();

							onNotConnectedNoticeClose().promise().done(() => {
								window.open( $( this ).attr( 'href' ), '_blank' ).focus();
								$closeButton.click();
							});
						})
				}, 0);
			} );
		</script>
		<?php
	}

	public function enqueue_styles () {
		if ( Connect::is_connected() ) {
			return;
		}

		if ( Pointers::is_dismissed( self::NOT_CONNECTED_NOTICE_SLUG ) ) {
			return;
		}

		wp_enqueue_style(
			'site-mailer-notice',
			\SITE_MAILER_ASSETS_URL . 'css/notice.css',
			[],
			\SITE_MAILER_VERSION
		);
	}

	public function __construct() {
		add_action('current_screen', function () {
			if ( ! Utils::user_is_admin() ) {
				return;
			}

			if ( Connect::is_connected() ) {
				return;
			}

			if ( Utils::is_wp_dashboard_page() || Utils::is_wp_settings_page() ) {
				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
				add_action( 'admin_notices', [ $this, 'render_not_connected_notice' ] );
			}
		});
	}
}
