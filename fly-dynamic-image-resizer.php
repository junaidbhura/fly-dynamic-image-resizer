<?php
/*
Plugin Name: Fly Dynamic Image Resizer
Description: Dynamically create image sizes on the fly!
Version: 2.0.8
Author: Junaid Bhura
Author URI: https://junaid.dev
Text Domain: fly-images
*/

namespace JB\FlyImages;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Plugin path.
 */
define( 'JB_FLY_PLUGIN_PATH', __DIR__ );

/**
 * Require files.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once JB_FLY_PLUGIN_PATH . '/inc/class-fly-cli.php';
}
require_once JB_FLY_PLUGIN_PATH . '/inc/namespace.php';
require_once JB_FLY_PLUGIN_PATH . '/inc/helpers.php';

/**
 * Actions.
 */
add_action( 'init', __NAMESPACE__ . '\\init' );
