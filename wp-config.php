<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
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
define( 'DB_NAME', 'if0_36761425_wp592' );

/** Database username */
define( 'DB_USER', '36761425_3' );

/** Database password */
define( 'DB_PASSWORD', '-p4Y-44eJS' );

/** Database hostname */
define( 'DB_HOST', 'sql111.byetcluster.com' );

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
define( 'AUTH_KEY',         'y1evv2pyqkgtqchnxmidif3pddnxvv5yuihp8nw2mdwtznvm3cebb2ez5udxhvbr' );
define( 'SECURE_AUTH_KEY',  'kdzdlmfpi8vy1v7ylsisd8vtn7pvetwp9kmhn2vwyu7zi03tto21swr6e5gt7ubj' );
define( 'LOGGED_IN_KEY',    'mmw7pzi4qacbpnhomjklf1jgdhbg9bh6h0duwi21wdmx6gcziebsfull1cmlyyfi' );
define( 'NONCE_KEY',        '2v22hgcgpbbjhumcj5rlwfirpmvvmhnxvwa6cnj5bmlpr3p6wathol5swzylitww' );
define( 'AUTH_SALT',        'xzhw8ev0remg3aduhfxfbeajondg4xausst7woqyia6ty8hcf4cbuzuleekyp1jr' );
define( 'SECURE_AUTH_SALT', 'r4am7dodeatqmzkrrsfvact09pmuqe2hm0kftsephuifnladtlngbcs0z0fgk6s2' );
define( 'LOGGED_IN_SALT',   '2kdwtssimdomttp3qjle4suni6sxtr5v1xi18ft866ao4w1lv0npu9jlia9sizym' );
define( 'NONCE_SALT',       'sp4ecazmxdtek26zkf8ouwy44lj7esveu0sadaw1sdygpnwedpbrlxs0uh6pdvtm' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpct_';

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
