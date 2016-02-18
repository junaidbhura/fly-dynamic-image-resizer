<?php
/*
Plugin Name: Fly Dynamic Image Resizer
Description: Dynamically create image sizes on the fly!
Version: 1.0.2
Author: Junaid Bhura
Author URI: http://www.junaidbhura.com
Text Domain: fly-images
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Main Class
 */
class Fly_Images {

	/* Variables */
	private $_image_sizes = array();
	private $_fly_dir = '';
	private $_fly_dir_writeable = false;
	private $_show_notice = false;
	private $_capability = 'manage_options';

	/**
	 * Constructor
	 */
	public function __construct() {
		/* Checks for Fly Images Directory  */
		$this->_fly_dir = $this->get_fly_dir();

		// Check if the Fly Image folder exists and is writeable
		if ( ! is_dir( $this->_fly_dir ) ) {
			try {
				if ( mkdir( $this->_fly_dir, 0755 ) ) {
					$this->_fly_dir_writeable = true;
				} } catch ( Exception $e ) {}
		} elseif ( is_writeable( $this->_fly_dir ) ) {
			$this->_fly_dir_writeable = true;
		}

		/* Initializations */
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initializes Actions
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'media_row_actions', array( $this, 'media_row_action' ), 10, 2 );
		add_action( 'delete_attachment', array( $this, 'delete_attachment' ) );
	}

	/**
	 * Admin Menu Item
	 */
	public function admin_menu() {
		/* Capability Filter */
		$this->_capability = apply_filters( 'fly_images_user_capability', $this->_capability );

		add_management_page( __( 'Fly Images', 'fly-images' ), __( 'Fly Images', 'fly-images' ), $this->_capability, 'fly-images', array( $this, 'options_page' ) );
	}

	/**
	 * Adds a new row action to media library items.
	 *
	 * @param  array $actions
	 * @param  object $post
	 * @return array
	 */
	public function media_row_action( $actions, $post ) {
		if ( 'image/' != substr( $post->post_mime_type, 0, 6 ) || ! current_user_can( $this->_capability ) ) {
			return $actions;
		}

		$url = wp_nonce_url( admin_url( 'tools.php?page=fly-images&delete-fly-image&ids=' . $post->ID ), 'delete_fly_image', 'fly_nonce' );
		$actions['fly-image-delete'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( __( 'Delete all cached image sizes for this image', 'fly-images' ) ) . '">' . __( 'Delete Cached Fly Images', 'fly-images' ) . '</a>';

		return $actions;
	}

	/**
	 * Deletes all fly images when the main image is deleted.
	 *
	 * @param  integer $post_id
	 * @return void
	 */
	public function delete_attachment( $post_id = 0 ) {
		$directory = $this->get_fly_dir( $post_id );
		if ( is_dir( $directory ) ) {
			try {
				array_map( 'unlink', ( glob( $directory . '/*' ) ) ?: array() );
				rmdir( $directory );
			} catch ( Exception $e ) {}
		}
	}

	/**
	 * The Options Page
	 */
	public function options_page() {
		/* Check for actions */
		if ( isset( $_POST ) && isset( $_POST['fly_nonce'] ) && wp_verify_nonce( $_POST['fly_nonce'], 'delete_all_fly_images' ) ) {
			// Deletes all cached images
			$subdirectories = scandir( $this->get_fly_dir() );
			if ( $subdirectories ) {
				foreach ( $subdirectories as $subdirectory ) {
					if ( '.' != $subdirectory && '..' != $subdirectory ) {
						$directory_path = $this->get_fly_dir( $subdirectory );
						array_map( 'unlink', ( glob( $directory_path . '/*' ) ) ?: array() );
						rmdir( $directory_path );
					}
				}
			}
			echo '<div class="updated"><p>' . __( 'All cached images created on the fly have been deleted.', 'fly-images' ) . '</p></div>';
		} elseif ( isset( $_GET['delete-fly-image'] ) && isset( $_GET['ids'] ) && isset( $_GET['fly_nonce'] ) && wp_verify_nonce( $_GET['fly_nonce'], 'delete_fly_image' ) ) {
			// Deletes cache for a single / few images
			$ids = array_map( 'intval', array_map( 'trim', explode( ',', $_GET['ids'] ) ) );
			if ( $ids ) {
				foreach ( $ids as $id ) {
					$directory = $this->get_fly_dir( $id );
					if ( is_dir( $directory ) ) {
						array_map( 'unlink', ( glob( $directory . '/*' ) ) ?: array() );
						rmdir( $directory );
					}
				}

				echo '<div class="updated"><p>' . __( sprintf( 'Deleted all cached sizes for this image. <a href="%s">Click here</a> to go back to the previous page.', 'javascript:history.go(-1)' ), 'fly-images' ) . '</p></div>';
			}
		}

		// Show the template
		load_template( dirname( __FILE__ ) . '/templates/options.php' );
	}

	/**
	 * Checks if the main cache folder is writeable.
	 *
	 * @return boolean
	 */
	public function fly_dir_writeable() {
		return $this->_fly_dir_writeable;
	}

	/**
	 * Add image sizes to be created on the fly.
	 *
	 * @param string   $size_name
	 * @param integer  $width
	 * @param integer  $height
	 * @param boolean  $crop
	 */
	public function add_image_size( $size_name, $width = 0, $height = 0, $crop = false ) {
		if ( '' == $size_name || ! $width || ! $height ) {
			return false;
		}

		$this->_image_sizes[ $size_name ] = array( 'size' => array( $width, $height ), 'crop' => $crop );
		return true;
	}

	/**
	 * Gets a previously declared image size.
	 *
	 * @param  string $size_name
	 * @return array
	 */
	public function get_image_size( $size_name = '' ) {
		if ( '' == $size_name || ! isset( $this->_image_sizes[ $size_name ] ) ) {
			return array();
		} else {
			return $this->_image_sizes[ $size_name ];
		}
	}

	/**
	 * Gets a dynamically generated image URL from the Fly_Images class.
	 *
	 * @param  integer  $attachment_id
	 * @param  mixed    $size
	 * @param  boolean  $crop
	 * @return array
	 */
	public function get_attachment_image_src( $attachment_id = 0, $size = '', $crop = null ) {
		if ( $attachment_id < 1 || '' == $size ) {
			return array();
		}

		// If size is 'full', we don't need a fly image
		if ( 'full' == $size ) {
			return wp_get_attachment_image_src( $attachment_id, 'full' );
		}

		// Get the attachment image
		$image = wp_get_attachment_metadata( $attachment_id );
		if ( false !== $image && $image ) {
			// Determine file name based on size
			if ( gettype( $size ) == 'string' ) {
				/* String Size */
				$image_size = $this->get_image_size( $size );
				if ( empty( $image_size ) ) {
					return array();
				}

				$width = $image_size['size'][0];
				$height = $image_size['size'][1];
				$crop = isset( $crop ) ? $crop : $image_size['crop'];
			} elseif ( gettype( $size ) == 'array' ) {
				/* Array Size */
				$width = $size[0];
				$height = $size[1];
			} else {
				return array();
			}

			$fly_dir = $this->get_fly_dir( $attachment_id );
			$fly_file_name = $fly_dir . DIRECTORY_SEPARATOR . $this->get_fly_file_name( basename( $image['file'] ), $width, $height, $crop );

			// Check if file exsists
			if ( file_exists( $fly_file_name ) ) {
				$image_editor = wp_get_image_editor( $fly_file_name );
				$image_dimensions = $image_editor->get_size();
				return array(
					'src' => $this->get_fly_path( $fly_file_name ),
					'width' => $image_dimensions['width'],
					'height' => $image_dimensions['height'],
				);
			}

			// Check if images directory is writeable
			if ( ! $this->_fly_dir_writeable ) {
				return array();
			}

			// File does not exist, lets check if directory exists
			if ( ! is_dir( $fly_dir ) ) {
				// Directory does not exist, let's create it
				try {
					if ( ! mkdir( $fly_dir, 0755 ) ) {
						return array();
					}
				} catch ( Exception $e ) {
					return array();
				}
			}

			// Get WP Image Editor Instance
			$image_path = get_attached_file( $attachment_id );
			$image_editor = wp_get_image_editor( $image_path );
			if ( ! is_wp_error( $image_editor ) ) {
				// Create new image
				$image_editor->resize( $width, $height, $crop );
				$image_editor->save( $fly_file_name );

				// Image created, return its data
				$image_dimensions = $image_editor->get_size();
				return array(
					'src' => $this->get_fly_path( $fly_file_name ),
					'width' => $image_dimensions['width'],
					'height' => $image_dimensions['height'],
				);
			}
		}

		// Something went wrong
		return array();
	}

	/**
	 * Gets a dynamically generated image HTML from the Fly_Images class.
	 *
	 * Based on /wp-includes/media.php -> wp_get_attachment_image()
	 *
	 * @param  integer  $attachment_id
	 * @param  mixed    $size
	 * @param  boolean  $crop
	 * @param  array    $attr
	 * @return string
	 */
	public function get_attachment_image( $attachment_id = 0, $size = '', $crop = null, $attr = array() ) {
		if ( $attachment_id < 1 || '' == $size ) {
			return '';
		}

		// If size is 'full', we don't need a fly image
		if ( 'full' == $size ) {
			return wp_get_attachment_image( $attachment_id, $size, $attr );
		}

		$html = '';
		$image = $this->get_attachment_image_src( $attachment_id, $size, $crop );
		if ( $image ) {
			$hwstring = image_hwstring( $image['width'], $image['height'] );
			$size_class = $size;
			if ( is_array( $size_class ) ) {
				$size_class = join( 'x', $size );
			}
			$attachment = get_post( $attachment_id );
			$default_attr = array(
				'src'	=> $image['src'],
				'class'	=> "attachment-$size_class",
				'alt'	=> trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
			);
			if ( empty( $default_attr['alt'] ) ) {
				$default_attr['alt'] = trim( strip_tags( $attachment->post_excerpt ) );
			}
			if ( empty( $default_attr['alt'] ) ) {
				$default_attr['alt'] = trim( strip_tags( $attachment->post_title ) );
			}

			$attr = wp_parse_args( $attr, $default_attr );
			$attr = apply_filters( 'fly_get_attachment_image_attributes', $attr, $attachment, $size );
			$attr = array_map( 'esc_attr', $attr );
			$html = rtrim( "<img $hwstring" );
			foreach ( $attr as $name => $value ) {
				$html .= " $name=" . '"' . $value . '"';
			}
			$html .= ' />';
		}

		return $html;
	}

	/**
	 * Gets a file name based on parameters.
	 *
	 * @param  string  $file_name
	 * @param  string  $width
	 * @param  string  $height
	 * @param  boolean $crop
	 * @return string
	 */
	public function get_fly_file_name( $file_name, $width, $height, $crop ) {
		$file_name_only = pathinfo( $file_name, PATHINFO_FILENAME );
		$file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );
		return $file_name_only . '-' . $width . 'x' . $height . ( $crop ? '-c' : '' ) . '.' . $file_extension;
	}

	/**
	 * Gets the path to the directory where all Fly images are stored.
	 *
	 * @param  string $path
	 * @return string
	 */
	public function get_fly_dir( $path = '' ) {
		if ( '' === $this->_fly_dir ) {
			$wp_upload_dir = wp_upload_dir();
			return $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'fly-images' . ( '' !== $path ? DIRECTORY_SEPARATOR . $path : '' );
		} else {
			return $this->_fly_dir . ( '' !== $path ? DIRECTORY_SEPARATOR . $path : '' );
		}
	}

	/**
	 * Gets the full path of an image based on it's absolute path.
	 *
	 * @param  string $absolute_path
	 * @return string
	 */
	public function get_fly_path( $absolute_path = '' ) {
		$wp_upload_dir = wp_upload_dir();
		$path = $wp_upload_dir['baseurl'] . str_replace( $wp_upload_dir['basedir'], '', $absolute_path );
		return str_replace( DIRECTORY_SEPARATOR, '/', $path );
	}

	/**
	 * Gets the absolute path of an image based on it's full path.
	 *
	 * @param  string $path
	 * @return string
	 */
	public function get_fly_absolute_path( $path = '' ) {
		$wp_upload_dir = wp_upload_dir();
		return $wp_upload_dir['basedir'] . str_replace( $wp_upload_dir['baseurl'], '', $path );
	}
}


if ( ! function_exists( 'fly_add_image_size' ) ) {
	/**
	 * Add image sizes to the Fly_Images class.
	 *
	 * @param string   $size_name
	 * @param integer  $width
	 * @param integer  $height
	 * @param boolean  $crop
	 */
	function fly_add_image_size( $size_name = '', $width = 0, $height = 0, $crop = false ) {
		global $fly_images;
		return $fly_images->add_image_size( $size_name, $width, $height, $crop );
	}
}

if ( ! function_exists( 'fly_get_attachment_image_src' ) ) {
	/**
	 * Gets a dynamically generated image URL from the Fly_Images class.
	 *
	 * @param  integer  $attachment_id
	 * @param  mixed    $size
	 * @param  boolean  $cropped
	 * @return string
	 */
	function fly_get_attachment_image_src( $attachment_id = 0, $size = '', $crop = null ) {
		global $fly_images;
		return $fly_images->get_attachment_image_src( $attachment_id, $size, $crop );
	}
}

if ( ! function_exists( 'fly_get_attachment_image' ) ) {
	/**
	 * Gets a dynamically generated image HTML from the Fly_Images class.
	 *
	 * @param  integer  $attachment_id
	 * @param  mixed    $size
	 * @param  boolean  $crop
	 * @param  array    $attr
	 * @return string
	 */
	function fly_get_attachment_image( $attachment_id = 0, $size = '', $crop = null, $attr = array() ) {
		global $fly_images;
		return $fly_images->get_attachment_image( $attachment_id, $size, $crop, $attr );
	}
}

// Initialize Plugin!
global $fly_images;
$fly_images = new Fly_Images();
