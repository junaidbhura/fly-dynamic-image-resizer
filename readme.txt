=== Fly Dynamic Image Resizer ===
Contributors: junaidbhura
Tags: media library, images, resize, dynamic, on the fly
Requires at least: 3.0
Tested up to: 4.8
Stable tag: 1.0.5

Dynamically create image sizes on the fly!

== Description ==

[Check out the Github Repository ♥](https://github.com/junaidbhura/fly-dynamic-image-resizer)

One of the biggest problems theme developers face is the problem of multiple image sizes. When you upload an image in the media library, WordPress automatically creates thumbnails based on **all the image sizes** you have defined using **`add_image_size()`** whether you want to use them or not. So the vast majority of the images in wp-content/uploads directory **are a waste, and are never used.** This is not the optimum way of creating image sizes.

With this plugin, you can create **as many image sizes as you want** without the guilt of unnecessary image sizes taking up your disk space!

This is because the images created using this plugin are dynamically created when the image is called for the **first time**, rather than when it is uploaded. You can also delete the cached images for each image individually, or all the cached images.

= How this plugin works =

1. You either define an image size in your code using the **`fly_add_image_size()`** function, or directly call the image size in the code
2. The admin uploads the image in the media library, but the fly dynamic images are not created
3. The user visits the page for the first time, and the image is dynamically created and is stored
4. The user visits the page again for the second time, and the stored version of the image is served

= Documentation =

Here are some functions and example code to get you started!

**fly_get_attachment_image_src( $attachment_id, $size, $crop )**

* **attachment_id** (integer)(required) : The ID of the image attachment
* **size** (string/array)(required) : Either the name of the pre-defined size defined using `fly_add_image_size`, or an array with the width and height. Ex: array( 500, 500 )
* **crop** (boolean/array)(optional) : Whether the image should be cropped, or the crop position

Returns an array:

`array(
	'src' => string,
	'width' => integer,
	'height' => integer
)`

&nbsp;

**fly_get_attachment_image( $attachment_id, $size, $crop, $attr )**

* **attachment_id** (integer)(required) : The ID of the image attachment
* **size** (string/array)(required) : Either the name of the pre-defined size defined using `fly_add_image_size`, or an array with the width and height. Ex: array( 500, 500 )
* **crop** (boolean/array)(optional) : Whether the image should be cropped, or the crop position
* **attr** (array)(optional) : An array of attributes. Ex: `array( 'alt' => 'Alt text', 'title' => 'Title text', 'class' => 'my-class', 'id' => 'my-id' )`

Returns a HTML IMG element string:

`<img src="http://yoursite.com/wp-content/uploads/fly-images/10/your-image-500x500-c.jpg" width="500" height="500" alt="Alt text" />`

&nbsp;

= Example 1: Pre-defined Image Sizes =

In this method, you define as many image sizes as you want in your **functions.php** file.

`if ( function_exists( 'fly_add_image_size' ) ) {
	fly_add_image_size( 'home_page_square', 500, 500, true );
	fly_add_image_size( 'home_page_square_2x', 1000, 1000, true );
	fly_add_image_size( 'cropped_top_left', 1000, 1000, array( 'left', 'top' ) );
}`

Now, lets get the post thumbnail using the image sizes we just defined:

`<?php echo fly_get_attachment_image( get_post_thumbnail_id(), 'home_page_square' ); ?>`

Here's another way you can do this:

`<?php $image = fly_get_attachment_image_src( get_post_thumbnail_id(), 'home_page_square' ); echo '<img src="' . $image['src'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '" />'; ?>`

Let's get the image from our example above which has a crop position defined:

`<?php echo fly_get_attachment_image( get_post_thumbnail_id(), 'cropped_top_left' ); ?>`

&nbsp;

= Example 2: Dynamic Image Sizes =

Lets get the post thumbnail using some dynamic image sizes:

`<?php echo fly_get_attachment_image( get_post_thumbnail_id(), array( 500, 500 ), true ); ?>`

Here's another way you can do this:

`<?php $image = fly_get_attachment_image_src( get_post_thumbnail_id(), 'home_page_square', array( 500, 500 ), true ); echo '<img src="' . $image['src'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '" />'; ?>`

Lets get the post thumbnail cropped from the bottom right:

`<?php echo fly_get_attachment_image( get_post_thumbnail_id(), array( 500, 500 ), array( 'right', 'bottom' ) ); ?>`

&nbsp;

= A note on Crop Positions =

Crop positions are set using an array. The first parameter of the array needs to be the x-axis crop and the second parameter needs to be the y-axis crop. This feature **will not** work the other way around.

For example:

✓ `fly_get_attachment_image( get_post_thumbnail_id(), array( 500, 500 ), array( 'right', 'bottom' ) )` Will work! :)

✘ `fly_get_attachment_image( get_post_thumbnail_id(), array( 500, 500 ), array( 'bottom', 'right' ) )` Will not work! :(

== Installation ==

Upload 'fly-dynamic-image-resizer' to the '/wp-content/plugins/' directory

Activate the plugin through the 'Plugins' menu in WordPress

Create dynamic image sizes in your PHP code!

== Screenshots ==

1. The Tools page
2. Delete individual images' cached fly images

== Changelog ==

= 1.0.5 =
* Added support for crop positions. Images can now be cropped from the top, left, right, bottom or the default: center.
* Added new hook 'fly_image_created'.

= 1.0.4 =
* Check for an error in get_attachment_image_src() . Props [christopherrolfe198](https://github.com/christopherrolfe198).

= 1.0.3 =
* Added new filter 'fly_dir_path'.

= 1.0.2 =
* Fixed IIS URLs.

= 1.0.1 =
* Fixed user capabilities.
* "Full" size for fly_get_attachment_image_src now returns wp_get_attachment_image_src

= 1.0.0 =
* First stable release.
