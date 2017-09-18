<?php $fly_images = JB\FlyImages\Core::get_instance(); ?>
<div class="wrap">

	<h2>Fly Images</h2>

	<div class="card">
		<h3><?php esc_html_e( 'Fly Images Directory', 'fly-images' ); ?></h3>
		<p><code><?php echo esc_html( $fly_images->get_fly_dir() ); ?></code></p>
		<?php if ( $fly_images->fly_dir_writable() ) : ?>
			<p style="color: #7AD03A;"><?php esc_html_e( 'Writeable!', 'fly-images' ); ?></p>
		<?php else : ?>
			<p style="color: #A00;"><?php esc_html_e( 'Not Writeable. Please make sure this folder exists and is writeable!', 'fly-images' ); ?></p>
		<?php endif; ?>
	</div> <!-- .card -->

	<div class="card">
		<h3><?php esc_html_e( 'Delete Cached Images', 'fly-images' ); ?></h3>
		<p><?php esc_html_e( 'Do you want to delete the cached images created on the fly for all images?', 'fly-images' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'delete_all_fly_images', 'fly_nonce' ); ?>
			<p class="submit"><input class="button-primary" value="<?php esc_html_e( 'Delete All Cached Images', 'fly-images' ); ?>" type="submit"></p>
		</form>
	</div> <!-- .card -->

</div> <!-- .wrap -->
