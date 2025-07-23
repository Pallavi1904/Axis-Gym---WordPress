<?php

namespace SiteMailer\Modules\Core\Components;

use SiteMailer\Classes\Utils;
use SiteMailer\Modules\Connect\Module as Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Quota_Exhausted_Banner {
	const QUOTA_EXHAUSTED_SLUG = 'site-mailer-quota-exhausted';

	public function render_not_connected_notice() {
		// Return if the notice is already dismissed
		if ( Pointers::is_dismissed( self::QUOTA_EXHAUSTED_SLUG ) ) {
			return;
		}

		// Get plan data
		$plan_data = get_option( 'site_mailer_plan_data' );

		// Return if plan data is not available
		if ( false === $plan_data ) {
			return;
		}

		// Return if quota is not available in plan data
		if ( ! isset( $plan_data->plan->quota ) ) {
			return;
		}

		// Calculate quota used in % to show the notice
		$quota_used = (int) ( $plan_data->plan->quota->used / $plan_data->plan->quota->allowed ) * 100;

		// Return if quota used is not 100%
		if ( 100 !== $quota_used ) {
			return;
		}

		$message = esc_html__(
			'No additional emails will be sent until the monthly quota renews, unless you upgrade your plan.',
			'site-mailer'
		);

		// Switch message if the plan is not free trial
		if ( $plan_data->plan->name !== 'Free Trial' ) {
			$message = esc_html__(
				'No additional emails will be sent, unless you upgrade your plan.',
				'site-mailer'
			);
		}

		?>
		<div class="notice notice-error notice is-dismissible site-mailer__notice site-mailer__notice--error-custom"
			 data-notice-slug="<?php echo esc_attr( self::QUOTA_EXHAUSTED_SLUG ); ?>"
			style="padding-bottom: 15px; padding-left: 32px;">
			<p class="primary-heading">
				<?php esc_html_e(
					'Oh no! Site Mailer has reached the maximum email limit.',
					'site-mailer'
				); ?>
			</p>
			<p class="sub-heading">
				<?php echo esc_html( $message ); ?>
			</p>
			<a style="display: inline-block;background:#93003f;padding: 5px 16px;border-radius: 3px;color: #fff; text-decoration:none;"
			href="https://go.elementor.com/sm-trialend-notice/">
				<?php esc_html_e(
					'Upgrade Now',
					'site-mailer'
				); ?>
			</a>
		</div>

		<script>
			const onNotConnectedNoticeClose = () => {
				const pointer = '<?php echo esc_js( self::QUOTA_EXHAUSTED_SLUG ); ?>';

				return jQuery.ajax(
					{
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'site_mailer_pointer_dismissed',
							data: {
								pointer,
							},
							nonce: '<?php echo esc_js( wp_create_nonce( 'site-mailer-pointer-dismissed' ) ); ?>'
						}
					}
				)
			}

			jQuery( document ).ready( function( $ ) {
				setTimeout(() => {
					const $closeButton = $( '[data-notice-slug="<?php echo esc_js( self::QUOTA_EXHAUSTED_SLUG ); ?>"] .notice-dismiss' )

					$closeButton
						.first()
						.on( 'click', onNotConnectedNoticeClose )

					$( '[data-notice-slug="<?php echo esc_js( self::QUOTA_EXHAUSTED_SLUG ); ?>"] a' )
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

	public function enqueue_styles() {
		// Don't load styles if the notice is already dismissed
		if ( Pointers::is_dismissed( self::QUOTA_EXHAUSTED_SLUG ) ) {
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
			// Don't load if the user is not an admin
			if ( ! Utils::user_is_admin() ) {
				return;
			}

			// Don't load if not connected
			if ( ! Connect::is_connected() ) {
				return;
			}

			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
				add_action( 'admin_notices', [ $this, 'render_not_connected_notice' ] );
			}
		});
	}
}
