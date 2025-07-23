<?php

namespace SiteMailer\Modules\Core\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Conflicts {
	private array $conflicting_plugins;

	private const CONFLICTING_PLUGINS = [
		'fluent-smtp/fluent-smtp.php' => 'FluentSMTP',
		'post-smtp/postman-smtp.php' => 'Post SMTP',
		'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
		'mailin/sendinblue.php' => 'Newsletter, SMTP, Email marketing and Subscribe forms by Brevo',
		'smtp-mailer/main.php' => 'SMTP Mailer',
		'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
	];

	public function render_notice(): void {
		$conflicting_plugins_names = $this->conflicting_plugins;

		?>
		<div class="notice notice-warning site-mailer__notice site-mailer__notice--warning">
			<p>
				<b>
					<?php esc_html_e(
						'Site Mailer has detected multiple active SMTP plugins.',
						'site-mailer'
					); ?>
				</b>

				<span>
					<?php esc_html_e(
						'Using more than one may result in unexpected behavior.',
						'site-mailer'
					); ?>
				</span>
			</p>

			<form action="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" method="post" style="margin:0.5em 0;padding:2px">
				<span style="margin-inline-end: 8px;"><?php echo esc_html( join( ', ', $conflicting_plugins_names ) ); ?></span>

				<input type="hidden" name="action" value="deactivate-selected">

				<?php foreach ( array_keys( $this->conflicting_plugins ) as $plugin ) { ?>
					<input type="hidden" name="checked[]" value="<?php echo esc_attr( $plugin ); ?>">
				<?php } ?>

				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'bulk-plugins' ) ); ?>">

				<input type="submit"
							style="border:none;background-color:transparent;text-decoration:underline;cursor:pointer;font-size:13px;color:#135e96;"
							value="<?php esc_html_e( 'Deactivate All', 'site-mailer' ); ?>">
			</form>
		</div>
		<?php
	}

	public function get_conflicting_plugins(): array {
		$plugins = get_option( 'active_plugins' );
		$conflicting_plugins_file_names = array_keys( self::CONFLICTING_PLUGINS );
		$output = [];

		foreach ( $plugins as $plugin_file_name ) {
			if ( in_array( $plugin_file_name, $conflicting_plugins_file_names, true ) ) {
				$output[ $plugin_file_name ] = self::CONFLICTING_PLUGINS[ $plugin_file_name ];
			}
		}

		return $output;
	}

	public function __construct() {
		$this->conflicting_plugins = $this->get_conflicting_plugins();

		if ( ! empty( $this->conflicting_plugins ) ) {
			add_action( 'admin_notices', [ $this, 'render_notice' ] );
		}
	}
}
