<?php

/**
 * Plugin-related tests.
 */
class JB_Test_Fly_Plugin extends WP_UnitTestCase {

	private static $_core;
	private static $_image_id = 0;

	/**
	 * Setup.
	 */
	static function setUpBeforeClass() {
		self::$_core = \JB\FlyImages\Core::get_instance();
		self::$_image_id = self::upload_image();
	}

	/**
	 * Tear down.
	 */
	static function tearDownAfterClass() {
		wp_delete_attachment( self::$_image_id, true );
	}

	/**
	 * Upload an image.
	 *
	 * @return int|WP_Error
	 */
	static function upload_image() {
		$wp_upload_dir = wp_upload_dir();

		$file_name = $wp_upload_dir['path'] . DIRECTORY_SEPARATOR . 'image-' . rand_str( 6 ) . '.jpg';
		$file_type = wp_check_filetype( basename( $file_name ), null );
		copy( JB_FLY_PLUGIN_PATH . '/tests/data/image.jpg', $file_name );

		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $file_name ),
			'post_mime_type' => $file_type['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$image_id = wp_insert_attachment( $attachment, $file_name );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $image_id, $file_name );
		wp_update_attachment_metadata( $image_id, $attach_data );

		return $image_id;
	}

	/**
	 * @covers JB\FlyImages\Core::get_fly_dir
	 */
	function test_fly_dir() {
		$this->assertTrue( ! empty( self::$_core->get_fly_dir() ), 'Fly directory empty.' );
	}

	/**
	 * @covers JB\FlyImages\Core::fly_dir_writable
	 */
	function test_dir_writable() {
		$this->assertTrue( self::$_core->fly_dir_writable(), 'Fly directory is not writeable.' );
	}

	/**
	 * @covers JB\FlyImages\Core::delete_attachment_fly_images
	 */
	function test_delete_fly_image() {
		$test_image_id = $this->upload_image();
		self::$_core->get_attachment_image_src( $test_image_id, array( 100, 100 ), true );
		$this->assertTrue( self::$_core->delete_attachment_fly_images( $test_image_id ), 'Cannot delete fly images.' );
		wp_delete_attachment( $test_image_id );
	}

	/**
	 * @covers JB\FlyImages\Core::user_can_optimize_images
	 */
	function test_user_can_optimize_image() {
		$this->assertFalse( has_action( 'fly_image_created' ), 'Fly Image Created action already exists.' );
		$this->assertFalse( has_filter( 'fly_optimize_new_images' ), 'Fly Optimize New Images filter already exists.' );
		$this->assertFalse( has_filter( 'fly_optimize_new_images_logged_in' ), 'Fly Optimize New Images for Logged In Users filter already exists.' );

		add_filter( 'fly_optimize_new_images', '__return_true', 10 );
		$this->assertTrue( self::$_core->user_can_optimize_images(), 'User unable to optimize images.' );

		add_filter( 'fly_optimize_new_images_logged_in', '__return_true', 10 );
		$this->assertFalse( self::$_core->user_can_optimize_images(), 'Logged out user able to optimize images.' );

		wp_set_current_user( 1 );
		$this->assertTrue( self::$_core->user_can_optimize_images(), 'Logged in user unable to optimize images.' );
	}

	/**
	 * @covers JB\FlyImages\Core::add_image_size
	 */
	function test_add_image_size_string() {
		$this->assertTrue( self::$_core->add_image_size( '400x200', 400, 200, true ), 'Could not create image size.' );
	}

	/**
	 * @covers JB\FlyImages\Core::get_image_size
	 */
	function test_get_image_size_string() {
		$image_size = self::$_core->get_image_size( '400x200' );
		$this->assertTrue( 400 === $image_size['size'][0], 'Wrong image width.' );
		$this->assertTrue( 200 === $image_size['size'][1], 'Wrong image height.' );
	}

	/**
	 * @covers JB\FlyImages\Core::get_attachment_image_src
	 */
	function test_image_src() {
		$src = self::$_core->get_attachment_image_src( self::$_image_id, '400x200' );
		$this->assertTrue( 400 === $src['width'], 'Wrong width for SRC image with string name.' );
		$this->assertTrue( 200 === $src['height'], 'Wrong height for SRC image with string name.' );

		$src = self::$_core->get_attachment_image_src( self::$_image_id, array( 300, 100 ), true );
		$this->assertTrue( 300 === $src['width'], 'Wrong width for SRC image with array values (hard).' );
		$this->assertTrue( 100 === $src['height'], 'Wrong height for SRC image with array values (hard).' );

		$src = self::$_core->get_attachment_image_src( self::$_image_id, array( 300, 100 ), false );
		$this->assertTrue( 178 === $src['width'], 'Wrong width for SRC image with array values.' );
		$this->assertTrue( 100 === $src['height'], 'Wrong height for SRC image with array values.' );
	}

	/**
	 * @covers JB\FlyImages\Core::get_attachment_image
	 */
	function test_image_html() {
		$img = self::$_core->get_attachment_image( self::$_image_id, '400x200' );
		$this->assertTrue( 0 === strpos( $img, '<img' ), 'IMG tag blank for image with string name.' );

		$img = self::$_core->get_attachment_image( self::$_image_id, array( 300, 100 ), true );
		$this->assertTrue( 0 === strpos( $img, '<img' ), 'IMG tag blank for image with array values (hard).' );

		$img = self::$_core->get_attachment_image( self::$_image_id, array( 300, 100 ), false );
		$this->assertTrue( 0 === strpos( $img, '<img' ), 'IMG tag blank for image with array values.' );
	}

	/**
	 * @covers JB\FlyImages\Core::get_file_name
	 */
	function test_file_name() {
		$this->assertEquals( 'test-200x100.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, false ) );
		$this->assertEquals( 'test-200x100-c.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, true ) );
		$this->assertEquals( 'test-200x100-lt.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, array( 'left', 'top' ) ) );
		$this->assertEquals( 'test-200x100-lb.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, array( 'left', 'bottom' ) ) );
		$this->assertEquals( 'test-200x100-rt.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, array( 'right', 'top' ) ) );
		$this->assertEquals( 'test-200x100-rb.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, array( 'right', 'bottom' ) ) );
	}

	/**
	 * @covers JB\FlyImages\Core::delete_all_fly_images
	 */
	function test_delete_all_fly_images() {
		$this->assertTrue( self::$_core->delete_all_fly_images(), 'Cannot delete all fly images.' );
	}

}
