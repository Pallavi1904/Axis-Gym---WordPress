<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'KIE.6Q2Xb|zgW!0pfi72Y0PMK+5Xvtcz/cW)Ad+%!)D|6iN<RsD*#OBVc[u[vvMD' );
define( 'SECURE_AUTH_KEY',   '<p5vuz9W$n:[?7Or2Dx#AQ,39VfMRJanqdu*buV=5+1*v{YN+!^0/)5t-4,bFjK_' );
define( 'LOGGED_IN_KEY',     'Kx)/v4oNq6ReqE$NP28w#IruLof@^ gssVnF?2z8+cmGJ^z3b9}KvGN s#rGA/e`' );
define( 'NONCE_KEY',         'Yi@L#}j4xk_NKwQHPD#vOrO#H<m]8a?SqyomV9L 6)2Gd(ye`c><Ff>%b9t//det' );
define( 'AUTH_SALT',         'HuoT(HX?O0_xzN{O6CZ*4/:c^%R3?FlNU$F}(T~C8~>`)/i?y3Dpv@Ap[^ub0P>(' );
define( 'SECURE_AUTH_SALT',  'CnwX)xXciDmpD^tM=&+,bEL?~H;2FGfZ%;#[iP~zhYv/]-:F.%;s LA*;vj&r3|s' );
define( 'LOGGED_IN_SALT',    'IhlIaIQO:^M3(FYX/5$1/Sf^ovXU9,+Q?-&9f({mni5&(Vupjiy%%v; }:z}&HQn' );
define( 'NONCE_SALT',        'Gt.s#:.fL[yof;RK`^4]]#)@(83{_U@v1u?`}Kk]jza7gTqx]PVkLvw|24mB`-?7' );
define( 'WP_CACHE_KEY_SALT', '}{7XgU2Lz$.@.l_ROn0q@/0&}#&Yf&q,F~y,M]I<RXO6=n-.auh}Zo(Xb|b~%#,4' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
