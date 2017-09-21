<?php
namespace JB\FlyImages;

use \WP_CLI;
use \WP_CLI_Command;

class Fly_CLI extends WP_CLI_Command {

	/**
	 * Delete all fly images.
	 *
	 * @subcommand delete-all
	 */
	public function delete_all( $args, $args_assoc ) {
		$fly_images = Core::get_instance();
		WP_CLI::line( esc_html__( 'Deleting all fly images...', 'fly-images' ) );
		if ( $fly_images->delete_all_fly_images() ) {
			WP_CLI::success( esc_html__( 'All fly images deleted.', 'fly-images' ) );
		} else {
			WP_CLI::error( esc_html__( 'There was a problem deleting the fly images.', 'fly-images' ) );
		}
	}

	/**
	 * Delete fly images based on attachment IDs.
	 *
	 * @subcommand delete
	 * @synopsis <attachment-ids>
	 */
	public function delete_ids( $args, $args_assoc ) {
		$ids = array_map( 'trim', explode( ',', $args[0] ) );
		if ( empty( $ids ) ) {
			WP_CLI::error( esc_html__( 'Please enter valid IDs.', 'fly-images' ) );
		}

		$fly_images = Core::get_instance();
		foreach ( $ids as $id ) {
			WP_CLI::line( esc_html__( 'Deleting: ', 'fly-images' ) . $id );
			$fly_images->delete_attachment_fly_images( $id );
		}

		WP_CLI::success( esc_html__( 'The selected fly images have been deleted.', 'fly-images' ) );
	}

}

WP_CLI::add_command( 'fly-images', __NAMESPACE__ . '\\Fly_CLI' );
