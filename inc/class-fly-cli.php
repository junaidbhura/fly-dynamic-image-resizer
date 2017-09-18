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

	/**
	 * Optimizes all images.
	 *
	 * @subcommand optimize [--all]
	 */
	public function optimize( $args, $args_assoc ) {
		if ( ! isset( $args_assoc['all'] ) ) {
			WP_CLI::line( esc_html__( 'Looking for new images...', 'fly-images' ) );
			$last_run = get_option( 'fly_images_optimized_at' );
			$current_time = current_time( 'mysql' );
			if ( empty( $last_run ) ) {
				$last_run = $current_time;
			}
			update_option( 'fly_images_optimized_at', $current_time );
			$last_run = strtotime( $last_run );
		} else {
			WP_CLI::line( esc_html__( 'Looking for all images...', 'fly-images' ) );
			$last_run = strtotime( '1970-01-01 00:00:00' );
		}

		$fly_images = Core::get_instance();
		$optimizer = Optimizer::get_instance();
		$rdi = new \RecursiveDirectoryIterator( $fly_images->get_fly_dir() );
		$rii = new \RecursiveIteratorIterator( $rdi );
		$accepted_extensions = $optimizer->get_allowed_extensions();
		$files_to_optimize = [];

		foreach ( $rii as $file_path => $file ) {
			if ( filemtime( $file_path ) >= $last_run && in_array( strtolower( $file->getExtension() ), $accepted_extensions, true ) ) {
				$files_to_optimize[] = $file_path;
			}
		}

		if ( empty( $files_to_optimize ) ) {
			WP_CLI::success( esc_html__( 'No images to optimize.', 'fly-images' ) );
			return;
		}

		$total_files = count( $files_to_optimize );
		$progress = WP_CLI\Utils\make_progress_bar( 'Optimizing Images', $total_files );
		foreach ( $files_to_optimize as $file ) {
			$optimizer->optimize( $file );
			$progress->tick();
		}
		$progress->finish();

		WP_CLI::success( esc_html__( 'Total images optimized:', 'fly-images' ) . ' ' . $total_files );
	}

}

WP_CLI::add_command( 'fly-images', __NAMESPACE__ . '\\Fly_CLI' );
