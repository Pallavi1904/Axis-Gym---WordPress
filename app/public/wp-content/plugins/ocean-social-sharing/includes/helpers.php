<?php
/**
 * Helper functions.
 *
 * @package Ocean WordPress theme
 */

/**
 * Returns social sharing template part
 */
if ( ! function_exists( 'oss_social_share_sites' ) ) {

	function oss_social_share_sites() {

		// Default socials
		$socials = array(
			'twitter',
			'facebook',
			'google_plus',
			'pinterest',
			'linkedin',
			'viber',
			'vk',
			'reddit',
			'tumblr',
			'viadeo',
			'whatsapp',
		);

		// Get socials from Customizer
		$socials = get_theme_mod( 'oss_social_share_sites', $socials );

		if (is_string($socials)) {
			$socials = json_decode($socials, true);
		}

		// Turn into array if string
		if ( $socials && ! is_array( $socials ) ) {
			$socials = explode( ',', $socials );
		}

		// Apply filters for easy modification
		$socials = apply_filters( 'oss_social_share_sites_filter', $socials );

		// Return socials
		return $socials;

	}

}