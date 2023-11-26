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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ecommerceshop' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '123456' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'TnV(]T%yT:kTG8o^#GfHCXLJUwtnksH{&v}fv#F.R(Be_TAf,/(g((A`G2Ro,78=' );
define( 'SECURE_AUTH_KEY',  'xfNA*4|{nIf<4NLsF9s^^z&c({e7gRGJG(W<){6S6/<vErp2-7Tgd27AV4H?}NI!' );
define( 'LOGGED_IN_KEY',    '/>D-]=4bzO2F)(z1Fmq+6E7i.r#%!QO<<d,1J@P{-Gb+/{yZL%_ZQP`!7JzNl!~i' );
define( 'NONCE_KEY',        '%A}A:f.n}4WI;85i]]Y1W8,nTmo`T4rh.A, enB2edm/BnP,[.#^[qWEfoxYQ93=' );
define( 'AUTH_SALT',        '1Cl4k6`p0>ZCDxG^Z8d5mBhQ$;.Ae%!&9)I<bB,qo2trGZ cNnxf7`5?#ha<F<L}' );
define( 'SECURE_AUTH_SALT', '!6BeF]B/qG4((= e4%QuUhq<Hx|adTp9EK##]P.I5H[U>h*X$HCrWsC4lOd+Kq^H' );
define( 'LOGGED_IN_SALT',   '>8X)IYL1]d4@N4/EC4|LB]0ix>@g[T,qfe;)n,JXePtCd2~c=.4v=~k/e*X:bayR' );
define( 'NONCE_SALT',       '4:8K^1vU4I(XV)YZ9vaQQ|;uI :e*^%$CA,R 3UVP4z?%, <H_|0KvtHl<]=Hq=}' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
