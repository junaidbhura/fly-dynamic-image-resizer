<?php
namespace JB\FlyImages;

class Optimizer {

	/**
	 * Properties.
	 */
	private static $_instance    = null;
	private $_allowed_extensions = [ 'png', 'jpg', 'jpeg' ];
	private $_commands           = [
		'png' => 'pngquant --speed 1 --ext=.png --force',
		'jpg' => 'jpegoptim --strip-all --all-progressive --max=80',
		'gif' => 'gifsicle -b -O2',
	];

	/**
	 * Get current instance.
	 *
	 * @return Optimizer
	 */
	public static function get_instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new Optimizer();
		}
		return self::$_instance;
	}

	/**
	 * Optimizer constructor.
	 */
	public function __construct() {
		$this->_commands = apply_filters( 'fly_images_optimize_commands', $this->_commands );
	}

	/**
	 * Get allowed file extensions.
	 *
	 * @return array
	 */
	public function get_allowed_extensions() {
		return $this->_allowed_extensions;
	}

	/**
	 * Optimize an image based on it's path.
	 *
	 * @param string $file_path
	 * @return bool
	 */
	public function optimize( $file_path = '' ) {
		$info = pathinfo( $file_path );
		$extention = strtolower( $info['extension'] );
		if ( 'jpeg' === $extention ) {
			$extention = 'jpg';
		}

		if ( ! empty( $this->_commands[ $extention ] ) ) {
			exec( trim( $this->_commands[ $extention ] ) . ' ' . escapeshellarg( $file_path ), $output, $result ); // @codingStandardsIgnoreLine
			if ( 0 === $result ) {
				return true;
			}
		}

		return false;
	}

}
