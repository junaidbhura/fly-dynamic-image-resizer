<?php
global $fly_images;
?>
<div class="wrap">

	<h2>Fly Images</h2>

	<div class="card">
		<h3><?php _e( 'Fly Images Directory', 'fly-images' ); ?></h3>
		<p><code>/wp-content/uploads/fly-images</code></p>
		<?php if ( $fly_images->fly_dir_writeable() ) : ?>
			<p style="color: #7AD03A;"><?php _e( 'Writeable!', 'fly-images' ) ?></p>
		<?php else : ?>
			<p style="color: #A00;"><?php _e( 'Not Writeable. Please make sure this folder exists and is writeable!', 'fly-images' ) ?></p>
		<?php endif; ?>
	</div> <!-- .card -->

	<div class="card">
		<h3><?php _e( 'Delete Cached Images', 'fly-images' ); ?></h3>
		<p><?php _e( 'Do you want to delete the cached images created on the fly for all images?', 'fly-images' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'delete_all_fly_images', 'fly_nonce' ); ?>
			<p class="submit"><input class="button-primary" value="<?php _e( 'Delete All Cached Images', 'fly-images' ); ?>" type="submit"></p>
		</form>
	</div> <!-- .card -->

</div> <!-- .wrap -->
