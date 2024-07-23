<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 * You can get Mysql setttings from your web host.
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
define('DB_NAME', 'parforcetrail_d');

/** MySQL database username */
define('DB_USER', 'parforcetrail_d');

/** MySQL database password */
define('DB_PASSWORD', 'rLrNynp2');

/** MySQL hostname */
define('DB_HOST', 'parforcetrail.dk.mysql.service.one.com');

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
define('AUTH_KEY',         'efe-LWh3zxotmcgEsHkeihpdEkHOeW7vLpUcO8UVVvs=');
define('SECURE_AUTH_KEY',  '8OALWjpYKxyKPyc2m_O3W517exHtFfYJWIAKkiYVtWo=');
define('LOGGED_IN_KEY',    '_zItDvV6FW6WrM47uYOq5_fwAyf3b4KGC2D8v9w21Ag=');
define('NONCE_KEY',        'W7GO16-ZxAA_UFYqCdLpHEKr-jdoxclMZRbbGh3fmgA=');
define('AUTH_SALT',        'zodXmTleTtQQdRwyzntIM-1se6nbDz_d0TN_aiRZqFQ=');
define('SECURE_AUTH_SALT', '55pkDM5zRQJlo9WVi2xGc4qo2W9_LyozK_KrKC0pGkA=');
define('LOGGED_IN_SALT',   'XfTZcsmUmXes0G0ACdGFQ2WZ0Y77GKVSJt2ilswepzo=');
define('NONCE_SALT',       'OftYc9TN5_-3OOR52ZIje9IF6wr65Sj7qbPePm3DUSo=');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wordpress_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'da_DK');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/**
 * Prevent file editing from WP admin.
 * Just set to false if you want to edit templates and plugins from WP admin.
 */
define('DISALLOW_FILE_EDIT', true);

/**
 * API for One.com wordpress themes and plugins
 */
define('ONECOM_WP_ADDONS_API', 'https://wpapi.one.com');


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
