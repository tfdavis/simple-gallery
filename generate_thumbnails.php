#!/usr/bin/php
<?php
/**
 * My script for generating thumbnails whenever I upload a new gallery.
 *
 * Usage: ./generate_thumbnails.php /full/path/to/directory/containing/images.jpg
 *
 * Also writes out a json file for the metadata.
 *
 */
/******************************************************************************/
/* Config                                                                     */

$thumbnail_img_max_width  = 150;
$thumbnail_img_max_height = 150;
$main_img_max_width       = 1024;
$main_img_max_height      = 768;

$metadata_json = 'metadata.json';


/******************************************************************************/
/* Main                                                                       */

// TODO - probably need to make this more abstract, to support using this in 
// subdirectories.

$WEB_DOCROOT = getcwd();

if (php_sapi_name() != "cli") {
	print "Script only to be run at command line (for now).";
	exit;
}

$image_dir = $argv[1];

if (!is_dir($image_dir)) {
	print "ERROR: $image_dir - no such directory.\n";
	exit(1);
}
if (!preg_match("/^\//", $image_dir )) {
	print "ERROR: $image_dir - Directory must be fully qualified, beginning with /\n";
}
if (!preg_match("/\/$/", $image_dir )) {
	$image_dir = $image_dir . '/';
}

$images = glob($image_dir.'*');

print "Found " . count($images) . " images.....\n";

$files = Array();

foreach ($images as $file) {
	if (! is_file($file)) {
		continue;
	}
	if (preg_match("/thumb/", $file )) {
		continue;
	}
	if (preg_match("/^(.*)\.(jpg|png|gif)$/i", $file, $matches)) {
		$thumb_filename = $matches[1] . '-thumb' . '.' . $matches[2];
		$main_filename = $matches[1] . '-main' . '.' . $matches[2];
		print "Processing $file\n";
		resize_image($file, $thumb_filename, $thumbnail_img_max_width, $thumbnail_img_max_height);
		resize_image($file, $main_filename, $main_img_max_width, $main_img_max_height);
		print "\t-$thumb_filename generated\n";
		print "\t-$main_filename generated\n";

		$web_path = get_web_path($file);
		$thumb_web_path = get_web_path($thumb_filename);

		$item = Array(
			'image' => get_web_path($main_filename),
			'thumb' => get_web_path($thumb_filename),
			'big'   => $web_path
		);
		array_push($files, $item);
	}
}

print "Saving metadata to json file...\n";
if (! file_put_contents($image_dir . $metadata_json, json_encode($files, JSON_UNESCAPED_SLASHES) ) ) {
	print "ERROR could not write json file\n";
}



/******************************************************************************/
/* Functions                                                                  */

function get_web_path($file) {
	global $WEB_DOCROOT;
	if (preg_match("@^$WEB_DOCROOT(.*)$@", $file, $matches)) {
		return($matches[1]);
	}
	return false;
}

/*
 * PHP function to resize an image maintaining aspect ratio
 * Modified from http://salman-w.blogspot.com/2008/10/resize-images-using-phpgd-library.html
 *
 * Creates a resized (e.g. thumbnail, small, medium, large)
 * version of an image file and saves it as another file
 */

function resize_image($source_image_path, $thumbnail_image_path, $max_width, $max_height) {
    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_image_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_image_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_image_path);
            break;
    }
    if ($source_gd_image === false) {
        return false;
    }
    $source_aspect_ratio = $source_image_width / $source_image_height;
    $thumbnail_aspect_ratio = $max_width / $max_height;
    if ($source_image_width <= $max_width && $source_image_height <= $max_height) {
        $thumbnail_image_width = $source_image_width;
        $thumbnail_image_height = $source_image_height;
    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
        $thumbnail_image_width = (int) ($max_height * $source_aspect_ratio);
        $thumbnail_image_height = $max_height;
    } else {
        $thumbnail_image_width = $max_width;
        $thumbnail_image_height = (int) ($max_width / $source_aspect_ratio);
    }
    $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
    imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
    imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
    imagedestroy($source_gd_image);
    imagedestroy($thumbnail_gd_image);
    return true;
}

?>
