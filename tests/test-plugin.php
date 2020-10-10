<?php

/**
 * Plugin-related tests.
 */
class JB_Test_Fly_Plugin extends WP_UnitTestCase {

	private static $_core;
	private static $_image_id = 0;
	private static $blog_2_id = 0;

	/**
	 * Setup.
	 */
	static function setUpBeforeClass() {
		self::$_core     = \JB\FlyImages\Core::get_instance();
		self::$_image_id = self::upload_image();
		self::$blog_2_id = wpmu_create_blog( 'example.org', 'blog-2', 'Blog 2', 1 );
	}

	/**
	 * Tear down.
	 */
	static function tearDownAfterClass() {
		wp_delete_attachment( self::$_image_id, true );
		wpmu_delete_blog( self::$blog_2_id, true );
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
	 * @covers fly_get_image_size()
	 */
	function test_fly_get_image_size() {
		$image_size = fly_get_image_size( '400x200' );
		$this->assertTrue( 400 === $image_size['size'][0], 'Wrong image width.' );
		$this->assertTrue( 200 === $image_size['size'][1], 'Wrong image height.' );
		$this->assertTrue( true === $image_size['crop'], 'Wrong crop.' );
	}

	/**
	 * @covers fly_get_all_image_sizes()
	 */
	function test_get_all_image_sizes() {
		self::$_core->add_image_size( 'another_test_size', 200, 300, array( 'left', 'top' ) );

		$all_image_sizes = fly_get_all_image_sizes();
		$this->assertTrue( ! empty( $all_image_sizes ), 'Image sizes empty.' );

		$this->assertTrue( 200 === $all_image_sizes['another_test_size']['size'][0], 'Wrong image width.' );
		$this->assertTrue( 300 === $all_image_sizes['another_test_size']['size'][1], 'Wrong image height.' );
		$this->assertTrue( 'left' === $all_image_sizes['another_test_size']['crop'][0], 'Wrong crop X.' );
		$this->assertTrue( 'top' === $all_image_sizes['another_test_size']['crop'][1], 'Wrong crop Y.' );
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
	 * @covers JB\FlyImages\Core::get_fly_file_name
	 */
	function test_file_name() {
		$this->assertEquals( 'test-200x100.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, false ) );
		$this->assertEquals( 'test-200x100.jpg', self::$_core->get_fly_file_name( 'test.JPG', 200, 100, false ) );
		$this->assertEquals( 'test-200x100-c.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200, 100, true ) );
		$this->assertEquals( 'test-200x100-c.jpg', self::$_core->get_fly_file_name( 'test.jpg', 200.333, 100.5, true ) );
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

	/**
	 * @covers JB\FlyImages\Core::blog_switched
	 */
	function test_multisite() {
		$wp_upload_dir = wp_upload_dir();
		$path_1        = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'fly-images';
		$this->assertEquals( self::$_core->get_fly_dir(), $path_1 );

		switch_to_blog( self::$blog_2_id );

		$wp_upload_dir = wp_upload_dir();
		$path_2        = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'fly-images';
		$this->assertEquals( self::$_core->get_fly_dir(), $path_2 );

		restore_current_blog();
		$this->assertEquals( self::$_core->get_fly_dir(), $path_1 );
	}

}
