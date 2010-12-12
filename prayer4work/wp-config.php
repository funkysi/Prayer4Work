<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'wordpress');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'x-:%%~JU*|b|X4QMAmE!^L^lAfK6-T+AfW]N%p|rP*g?FI,QuS18/-c%84wL=6Pq');
define('SECURE_AUTH_KEY',  ',miN3oLdFP*ti>0|rZXI%[bT{lrN8W=N6T;_zqA=eQDM:=[haS)#76hi|2~qyR,+');
define('LOGGED_IN_KEY',    'tZ=N2{)AIa|+12^hMm^NEvaD.U!~RFeC;llIZf?s/wG_2.6{yZm?Q#jOHkHS@.al');
define('NONCE_KEY',        'c|mryXWGLPW4=4qC<,:ST-f}v9wx*<]7N3 ],LbW7+vb_X6kTJ*&f-2/tH-r;xz,');
define('AUTH_SALT',        '2RdhsWVO qR5XjjK,/jW_lj3=,.*bi(|i|:81SLt^INp-f;Q-_aXq[6_+3l<~k5-');
define('SECURE_AUTH_SALT', '_NWK]A!KO?!8d~S-ch-RM=@MO4L.GfwDF|&51z4I}CGz^n94{G|IZ#-)MQ>-4_r.');
define('LOGGED_IN_SALT',   '-g,_}Fhv;?dUyj+6}Et4BHlg(.= |?W`T4=BwiwsMN|p6&S,LA_V2s@qz ,Igs(2');
define('NONCE_SALT',       '6&2b.=b*cLd#L/WT>7M/ah36@O+6_zNd!&&kWqE{oRSY>39&NOTS?Bm|*f|*9z&%');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'pp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
