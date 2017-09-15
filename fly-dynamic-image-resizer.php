<?php
/*
Plugin Name: Fly Dynamic Image Resizer
Description: Dynamically create image sizes on the fly!
Version: 1.0.5
Author: Junaid Bhura
Author URI: https://junaidbhura.com
Text Domain: fly-images
*/

namespace JB\FlyImages;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Plugin path.
 */
define( 'JB_FLY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Require files.
 */
require_once __DIR__ . '/inc/namespace.php';
require_once __DIR__ . '/inc/helpers.php';

/**
 * Actions.
 */
add_action( 'init', __NAMESPACE__ . '\\init' );
