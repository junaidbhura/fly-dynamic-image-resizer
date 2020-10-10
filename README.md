# Fly Dynamic Image Resizer

![GitHub Actions](https://github.com/junaidbhura/fly-dynamic-image-resizer/workflows/Coding%20Standards%20and%20Tests/badge.svg)

**[Download the WP Plugin! ♥](https://wordpress.org/plugins/fly-dynamic-image-resizer/)**

## What does this plugin do?


One of the biggest problems theme developers face is the problem of multiple image sizes. When you upload an image in the media library, WordPress automatically creates thumbnails based on **all the image sizes** you have defined using **`add_image_size()`** whether you want to use them or not. So the vast majority of the images in wp-content/uploads directory **are a waste, and are never used.** This is not the optimum way of creating image sizes.

With this plugin, you can create **as many image sizes as you want** without the guilt of unnecessary image sizes taking up your disk space!

This is because the images created using this plugin are dynamically created when the image is called for the **first time**, rather than when it is uploaded. You can also delete the cached images for each image individually, or all the cached images.

## How does this plugin work?

1. You either define an image size in your code using the **`fly_add_image_size()`** function, or directly call the image size in the code
2. The admin uploads the image in the media library, but the fly dynamic images are not created
3. The user visits the page for the first time, and the image is dynamically created and is stored
4. The user visits the page again for the second time, and the stored version of the image is served


## Documentation

The wiki contains all the documentation for this plugin: [Documentation](https://github.com/junaidbhura/fly-dynamic-image-resizer/wiki)
