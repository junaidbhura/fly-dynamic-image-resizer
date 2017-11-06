<?php

if ( ! function_exists( 'fly_add_image_size' ) ) {
	/**
	 * Add image sizes to the JB\FlyImages\Core class.
	 *
	 * @param string   $size_name
	 * @param integer  $width
	 * @param integer  $height
	 * @param boolean  $crop
	 * @return boolean
	 */
	function fly_add_image_size( $size_name = '', $width = 0, $height = 0, $crop = false ) {
		$fly_images = JB\FlyImages\Core::get_instance();
		return $fly_images->add_image_size( $size_name, $width, $height, $crop );
	}
}

if ( ! function_exists( 'fly_get_attachment_image_src' ) ) {
	/**
	 * Get a dynamically generated image URL from the JB\FlyImages\Core class.
	 *
	 * @param  integer  $attachment_id
	 * @param  mixed    $size
	 * @param  boolean  $crop
	 * @return array
	 */
	function fly_get_attachment_image_src( $attachment_id = 0, $size = '', $crop = null ) {
		$fly_images = JB\FlyImages\Core::get_instance();
		return $fly_images->get_attachment_image_src( $attachment_id, $size, $crop );
	}
}

if ( ! function_exists( 'fly_get_attachment_image' ) ) {
	/**
	 * Get a dynamically generated image HTML from the JB\FlyImages\Core class.
	 *
	 * @param  integer  $attachment_id
	 * @param  mixed    $size
	 * @param  boolean  $crop
	 * @param  array    $attr
	 * @return string
	 */
	function fly_get_attachment_image( $attachment_id = 0, $size = '', $crop = null, $attr = array() ) {
		$fly_images = JB\FlyImages\Core::get_instance();
		return $fly_images->get_attachment_image( $attachment_id, $size, $crop, $attr );
	}
}

if ( ! function_exists( 'fly_get_image_size' ) ) {
	/**
	 * Get a previously declared image size from the JB\FlyImages\Core class.
	 *
	 * @param  string $size_name
	 * @return array
	 */
	function fly_get_image_size( $size_name = '' ) {
		$fly_images = JB\FlyImages\Core::get_instance();
		return $fly_images->get_image_size( $size_name );
	}
}

if ( ! function_exists( 'fly_get_all_image_sizes' ) ) {
	/**
	 * Get all declared images sizes from the JB\FlyImages\Core class.
	 *
	 * @return array
	 */
	function fly_get_all_image_sizes() {
		$fly_images = JB\FlyImages\Core::get_instance();
		return $fly_images->get_all_image_sizes();
	}
}
