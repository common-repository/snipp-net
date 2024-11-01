<?php
/**
 * Plugin Name: Snipp.net
 * Description: WordPress plugin to authorize snipp.net for news publisher
 * Version: 1.0
 * Author: Snipp
 * Author URI: https://snipp.net
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: snipp-net
 */

/**
 * Shortcut constant to the path of this file.
 */
define( 'SNIPP_NET_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Version of the plugin.
 */
define( 'SNIPP_NET_VERSION', '1.0.0' );

/**
 * Load composer dependencies
 */
require SNIPP_NET_DIR . 'vendor/autoload.php';

/**
 * Include the core that handles the common bits.
 */
require_once SNIPP_NET_DIR . 'class-snipp-net-core.php';

SnippNetCore::add_hooks();
