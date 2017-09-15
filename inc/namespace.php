<?php
namespace JB\FlyImages;

/**
 * Autoloader.
 *
 * @param string $class_name
 */
function autoload( $class_name = '' ) {
	if ( 0 === strpos( $class_name, 'JB\\FlyImages' ) ) {
		$file = str_replace( '\\', DIRECTORY_SEPARATOR, strtolower( $class_name ) );
		require_once JB_FLY_PLUGIN_PATH . '/inc/class-' . basename( $file ) . '.php';
	}
}
spl_autoload_register( __NAMESPACE__ . '\\autoload' );

/**
 * Initialize plugin.
 */
function init() {
	$fly_images = Core::get_instance();
	$fly_images->init();
}
