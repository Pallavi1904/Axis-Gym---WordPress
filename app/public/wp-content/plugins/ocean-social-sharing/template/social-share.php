<?php
/**
 * Social Share Buttons Output
 *
 * @package Ocean WordPress theme
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Disabled if post is password protected or if disabled
if ( post_password_required() ) {
	return;
}

// Get sharing sites
$sites = oss_social_share_sites();

// Return if there aren't any sites enabled
if ( empty( $sites ) ) {
	return;
}

// Declare main vars
$style 				= get_theme_mod( 'oss_social_share_style', 'minimal' );
$style 				= $style ? $style : 'minimal';
$heading 			= oceanwp_tm_translation( 'oss_social_share_heading', get_theme_mod( 'oss_social_share_heading', 'Please Share This' ) );
$headingPosition 	= get_theme_mod( 'oss_social_share_heading_position', 'side' );
$headingPosition 	= $headingPosition ? $headingPosition : 'side';
$name 				= get_theme_mod( 'oss_social_share_name', false );
$name 				= $name ? $name : false;
$post_id  			= get_the_ID();
$url      			= apply_filters( 'oss_social_share_url', get_permalink( $post_id ) );
$title    			= get_the_title();

// Classes
$classes = array( 'entry-share', 'clr' );

// Add the style class
$classes[] = $style;

// Add the heading position class
$classes[] = $headingPosition;

// Add class if name
if ( true == $name ) {
	$classes[] = 'has-name';
}

// Add class if no heading
if ( empty( $heading ) ) {
	$classes[] = 'no-heading';
}

// Turn classes into space seperated string
$classes = implode( ' ', $classes ); ?>

<div class="<?php echo esc_attr( $classes ); ?>">

	<?php
	// If heading
	if ( ! empty( $heading )
		|| is_customize_preview() ) { ?>

		<h3 class="theme-heading social-share-title">
			<span class="text" aria-hidden="true"><?php echo esc_attr( $heading ); ?></span>
			<span class="screen-reader-text"><?php echo esc_attr__( 'Share this content', 'ocean-social-sharing' ); ?></span>
		</h3>

	<?php
	} ?>

	<ul class="oss-social-share clr" aria-label="<?php echo esc_attr__( 'Available sharing options', 'ocean-social-sharing' ); ?>">

		<?php
		// Loop through sites
		foreach ( $sites as $site ) :

			// Twitter
			if ( 'twitter' == $site ) {

				// Get SEO meta and use instead if they exist
				if ( defined( 'WPSEO_VERSION' ) ) {
					if ( $meta = get_post_meta( $post_id, '_yoast_wpseo_twitter-title', true ) ) {
						$title = $meta;
					}
					if ( $meta = get_post_meta( $post_id, '_yoast_wpseo_twitter-description', true ) ) {
						$title = $title .': '. $meta;
						$title = $title;
					}
				}

				// Get twitter handle
				$handle = get_theme_mod( 'oss_social_share_twitter_handle' );
				$handle = str_replace( '@' , '' , trim( $handle ) ); ?>

				<li class="twitter">
					<a href="https://twitter.com/share?text=<?php echo html_entity_decode( wp_strip_all_tags( $title ) ); ?>&amp;url=<?php echo rawurlencode( esc_url( $url ) ); ?><?php if ( $handle ) echo '&amp;via='. esc_attr( $handle ); ?>" aria-label="<?php esc_attr_e( 'Share on X', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
						<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'X', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// Facebook
			if ( 'facebook' == $site ) { ?>

				<li class="facebook">
					<a href="https://www.facebook.com/sharer.php?u=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Facebook', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
						<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15
								37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11
								71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'Facebook', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// Pinterest
			if ( 'pinterest' == $site ) { ?>

				<li class="pinterest">
					<a href="https://www.pinterest.com/pin/create/button/?url=<?php echo rawurlencode( esc_url( $url ) ); ?>&amp;media=<?php echo wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ); ?>&amp;description=<?php echo urlencode( wp_trim_words( strip_shortcodes( get_the_content( $post_id ) ), 40 ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Pinterest', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
						<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 496 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59
								8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9
								150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3.8-3.4 5-20.3 6.9-28.1.6-2.5.3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6
								0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8
								7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29
								123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'Pinterest', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// LinkedIn
			if ( 'linkedin' == $site ) { ?>

				<li class="linkedin">
					<a href="https://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo rawurlencode( esc_url( $url ) ); ?>&amp;title=<?php echo html_entity_decode(wp_strip_all_tags( $title )); ?>&amp;summary=<?php echo urlencode( wp_trim_words( strip_shortcodes( get_the_content( $post_id ) ), 40 ) ); ?>&amp;source=<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
					<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4
								416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1
								243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'LinkedIn', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// Viber
			if ( 'viber' == $site ) { ?>

				<li class="viber">
					<a href="viber://forward?text=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Viber', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
						<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M444 49.9C431.3 38.2 379.9.9 265.3.4c0 0-135.1-8.1-200.9 52.3C27.8 89.3 14.9 143 13.5 209.5c-1.4 66.5-3.1 191.1 117
								224.9h.1l-.1 51.6s-.8 20.9 13 25.1c16.6 5.2 26.4-10.7 42.3-27.8 8.7-9.4 20.7-23.2 29.8-33.7 82.2 6.9 145.3-8.9 152.5-11.2 16.6-5.4 110.5-17.4
								125.7-142 15.8-128.6-7.6-209.8-49.8-246.5zM457.9 287c-12.9 104-89 110.6-103 115.1-6 1.9-61.5 15.7-131.2 11.2 0 0-52 62.7-68.2 79-5.3 5.3-11.1
								4.8-11-5.7 0-6.9.4-85.7.4-85.7-.1 0-.1 0 0 0-101.8-28.2-95.8-134.3-94.7-189.8 1.1-55.5 11.6-101 42.6-131.6 55.7-50.5 170.4-43 170.4-43 96.9.4
								143.3 29.6 154.1 39.4 35.7 30.6 53.9 103.8 40.6 211.1zm-139-80.8c.4 8.6-12.5 9.2-12.9.6-1.1-22-11.4-32.7-32.6-33.9-8.6-.5-7.8-13.4.7-12.9 27.9
								1.5 43.4 17.5 44.8 46.2zm20.3 11.3c1-42.4-25.5-75.6-75.8-79.3-8.5-.6-7.6-13.5.9-12.9 58 4.2 88.9 44.1 87.8 92.5-.1 8.6-13.1 8.2-12.9-.3zm47 13.4c.1
								8.6-12.9 8.7-12.9.1-.6-81.5-54.9-125.9-120.8-126.4-8.5-.1-8.5-12.9 0-12.9 73.7.5 133 51.4 133.7 139.2zM374.9 329v.2c-10.8 19-31 40-51.8
								33.3l-.2-.3c-21.1-5.9-70.8-31.5-102.2-56.5-16.2-12.8-31-27.9-42.4-42.4-10.3-12.9-20.7-28.2-30.8-46.6-21.3-38.5-26-55.7-26-55.7-6.7-20.8 14.2-41
								33.3-51.8h.2c9.2-4.8 18-3.2 23.9 3.9 0 0 12.4 14.8 17.7 22.1 5 6.8 11.7 17.7 15.2 23.8 6.1 10.9 2.3 22-3.7 26.6l-12 9.6c-6.1 4.9-5.3 14-5.3 14s17.8
								67.3 84.3 84.3c0 0 9.1.8 14-5.3l9.6-12c4.6-6 15.7-9.8 26.6-3.7 14.7 8.3 33.4 21.2 45.8 32.9 7 5.7 8.6 14.4 3.8 23.6z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'Viber', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// VK
			if ( 'vk' == $site ) { ?>

				<li class="vk">
					<a href="https://vk.com/share.php?url=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on VK', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
					<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M31.4907 63.4907C0 94.9813 0 145.671 0 247.04V264.96C0 366.329 0 417.019 31.4907 448.509C62.9813 480 113.671 480
								215.04 480H232.96C334.329 480 385.019 480 416.509 448.509C448 417.019 448 366.329 448 264.96V247.04C448 145.671 448 94.9813
								416.509 63.4907C385.019 32 334.329 32 232.96 32H215.04C113.671 32 62.9813 32 31.4907 63.4907ZM75.6 168.267H126.747C128.427
								253.76 166.133 289.973 196 297.44V168.267H244.16V242C273.653 238.827 304.64 205.227 315.093 168.267H363.253C359.313 187.435
								351.46 205.583 340.186 221.579C328.913 237.574 314.461 251.071 297.733 261.227C316.41 270.499 332.907 283.63 346.132 299.751C359.357
								315.873 369.01 334.618 374.453 354.747H321.44C316.555 337.262 306.614 321.61 292.865 309.754C279.117 297.899 262.173 290.368
								244.16 288.107V354.747H238.373C136.267 354.747 78.0267 284.747 75.6 168.267Z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'VK', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// Reddit
			if ( 'reddit' == $site ) { ?>

				<li class="reddit">
					<a href="https://www.reddit.com/submit?url=<?php echo rawurlencode( esc_url( $url ) ); ?>&amp;title=<?php echo html_entity_decode(wp_strip_all_tags( $title )); ?>" aria-label="<?php esc_attr_e( 'Share on Reddit', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
						<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M201.5 305.5c-13.8 0-24.9-11.1-24.9-24.6 0-13.8 11.1-24.9 24.9-24.9 13.6 0 24.6 11.1 24.6 24.9 0 13.6-11.1 24.6-24.6
								24.6zM504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zm-132.3-41.2c-9.4 0-17.7 3.9-23.8 10-22.4-15.5-52.6-25.5-86.1-26.6l17.4-78.3
								55.4 12.5c0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.3 24.9-24.9s-11.1-24.9-24.9-24.9c-9.7 0-18 5.8-22.1 13.8l-61.2-13.6c-3-.8-6.1 1.4-6.9
								4.4l-19.1 86.4c-33.2 1.4-63.1 11.3-85.5 26.8-6.1-6.4-14.7-10.2-24.1-10.2-34.9 0-46.3 46.9-14.4 62.8-1.1 5-1.7 10.2-1.7 15.5 0 52.6 59.2
								95.2 132 95.2 73.1 0 132.3-42.6 132.3-95.2 0-5.3-.6-10.8-1.9-15.8 31.3-16 19.8-62.5-14.9-62.5zM302.8 331c-18.2 18.2-76.1 17.9-93.6
								0-2.2-2.2-6.1-2.2-8.3 0-2.5 2.5-2.5 6.4 0 8.6 22.8 22.8 87.3 22.8 110.2 0 2.5-2.2 2.5-6.1 0-8.6-2.2-2.2-6.1-2.2-8.3 0zm7.7-75c-13.6
								0-24.6 11.1-24.6 24.9 0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.1 24.9-24.6 0-13.8-11-24.9-24.9-24.9z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'Reddit', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// Tumblr
			if ( 'tumblr' == $site ) { ?>

				<li class="tumblr">
					<a href="https://www.tumblr.com/widgets/share/tool?canonicalUrl=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Tumblr', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
					<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
								<path d="M309.8 480.3c-13.6 14.5-50 31.7-97.4 31.7-120.8 0-147-88.8-147-140.6v-144H17.9c-5.5 0-10-4.5-10-10v-68c0-7.2 4.5-13.6
								11.3-16 62-21.8 81.5-76 84.3-117.1.8-11 6.5-16.3 16.1-16.3h70.9c5.5 0 10 4.5 10 10v115.2h83c5.5 0 10 4.4 10 9.9v81.7c0 5.5-4.5
								10-10 10h-83.4V360c0 34.2 23.7 53.6 68 35.8 4.8-1.9 9-3.2 12.7-2.2 3.5.9 5.8 3.4 7.4 7.9l22 64.3c1.8 5 3.3 10.6-.4 14.5z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'Tumblr', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// Viadeo
			if ( 'viadeo' == $site ) { ?>

				<li class="viadeo">
					<a href="https://partners.viadeo.com/share?url=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Viadeo', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;">
						<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
							<path d="M276.2 150.5v.7C258.3 98.6 233.6 47.8 205.4 0c43.3 29.2 67 100 70.8 150.5zm32.7 121.7c7.6 18.2 11 37.5 11 57 0 77.7-57.8
								141-137.8 139.4l3.8-.3c74.2-46.7 109.3-118.6 109.3-205.1 0-38.1-6.5-75.9-18.9-112 1 11.7 1 23.7 1 35.4 0 91.8-18.1 241.6-116.6 280C95 455.2
								49.4 398 49.4 329.2c0-75.6 57.4-142.3 135.4-142.3 16.8 0 33.7 3.1 49.1 9.6 1.7-15.1 6.5-29.9 13.4-43.3-19.9-7.2-41.2-10.7-62.5-10.7-161.5 0-238.7
								195.9-129.9 313.7 67.9 74.6 192 73.9 259.8 0 56.6-61.3 60.9-142.4 36.4-201-12.7 8-27.1 13.9-42.2 17zM418.1 11.7c-31 66.5-81.3 47.2-115.8 80.1-12.4
								12-20.6 34-20.6 50.5 0 14.1 4.5 27.1 12 38.8 47.4-11 98.3-46 118.2-90.7-.7 5.5-4.8 14.4-7.2 19.2-20.3 35.7-64.6 65.6-99.7 84.9 14.8 14.4
								33.7 25.8 55 25.8 79 0 110.1-134.6 58.1-208.6z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'Viadeo', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php }

			// WhatsApp
			if ( 'whatsapp' == $site ) { ?>

				<li class="whatsapp">
					<a href="https://wa.me/?text=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_html_e( 'Share on WhatsApp', 'ocean-social-sharing' ); ?>" onclick="oss_onClick( this.href );return false;" data-action="share/whatsapp/share">
						<span class="screen-reader-text"><?php echo esc_attr__( 'Opens in a new window', 'ocean-social-sharing' ); ?></span>
						<span class="oss-icon-wrap">
							<svg class="oss-icon" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false">
								<path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7
								68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72
								359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1
								130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2
								3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7
								0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6
								32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
							</svg>
						</span>
						<?php
						// If name
						if ( true == $name
							|| is_customize_preview() ) { ?>
							<span class="oss-name" aria-hidden="true"><?php esc_html_e( 'WhatsApp', 'ocean-social-sharing' ); ?></span>
						<?php
						} ?>
					</a>
				</li>

			<?php } ?>

		<?php endforeach; ?>

	</ul>

</div><!-- .entry-share -->