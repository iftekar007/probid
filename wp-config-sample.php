<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */
define('DB_NAME', 'probidauto');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', '192.168.0.140');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

define('AUTH_KEY',         'C M(tp}Nr?O7^RoDwo&+}Ji]yXi7@P&W+8o%Jbm$K5L~)}Co,06#/Q+Leu3$p#4l');
define('SECURE_AUTH_KEY',  'WmP*3)na:e[X?rvUY)m<-xuQ+=Fef.f#~A/RosN:2+0?E0VT>_n4cW VF*nl~z]a');
define('LOGGED_IN_KEY',    'Io_I@0Rne.UWOjWFj<R6CSfL,(ADxv@QFU%bDRJO?K9uqFrqQF{1cvQ!1mIfUc<R');
define('NONCE_KEY',        '.rIw)`b`n<d&.10W1D,wp<tPZzC4glp/J&QN$NQ|l6Fy{xTM3Y383z;Nw:m,d/8]');
define('AUTH_SALT',        '}w92O<6.e*wqE=FDe%O)vJlIaY)o{|Su;V!N?.@6L^rs&{_F9{$s{Urjs(T4LJb|');
define('SECURE_AUTH_SALT', 'Y@mxA;LK7%Cg!-b@Kng=nY@V(M?c{P9~*WOPv3olK52H-@ToEXAe#<a4nnWV=bQ]');
define('LOGGED_IN_SALT',   'W]vD~DIJK3 :n}YV#1Qj_j6Wu#~0MyVW&UFvcu+JsfSN&]NIH(tYOqSqzH{[c_o|');
define('NONCE_SALT',       'wy1[*SjN&yv~+F_4]i0x>$Y1xCB3Z#j{b;N $S-rI*an*mu:(D-AD?M8iO<*x8gB');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
define('WP_SITEURL','http://192.168.0.140');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
