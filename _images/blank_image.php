<?php apc_clear_cache(); ?>
<?php

include(dirname(__FILE__) . "/../_includes/config.php");
$size = $_GET['size'];

switch($config_site['images_orientation']){
	case 'landscape':
		switch($size){
			case 'thumb':
				$width = $config_site['landscape_thumb_width'];
				$height = $config_site['landscape_thumb_height'];
			break;
			case 'suggest':
				$width = $config_site['landscape_suggest_width'];
				$height = $config_site['landscape_suggest_height'];
			break;
			case 'listing':
				$width = $config_site['landscape_listing_width'];
				$height = $config_site['landscape_listing_height'];
			break;
			case 'cover':
				$width = $config_site['landscape_cover_width'];
				$height = $config_site['landscape_cover_height'];
			break;
			case 'zoom':
				$width = $config_site['landscape_zoom_width'];
				$height = $config_site['landscape_zoom_height'];
			break;
			default:
				$width = $config_site['landscape_listing_width'];
				$height = $config_site['landscape_listing_height'];
			break;
		}
	break;
	case 'portrait':
		switch($size){
			case 'thumb':
				$width = $config_site['portrait_thumb_width'];
				$height = $config_site['portrait_thumb_height'];
			break;
			case 'suggest':
				$width = $config_site['portrait_suggest_width'];
				$height = $config_site['portrait_suggest_height'];
			break;
			case 'listing':
				$width = $config_site['portrait_listing_width'];
				$height = $config_site['portrait_listing_height'];
			break;
			case 'cover':
				$width = $config_site['portrait_cover_width'];
				$height = $config_site['portrait_cover_height'];
			break;
			case 'zoom':
				$width = $config_site['portrait_zoom_width'];
				$height = $config_site['portrait_zoom_height'];
			break;
			default:
				$width = $config_site['portrait_listing_width'];
				$height = $config_site['portrait_listing_height'];
			break;
		}
	break;
}

// LOGO
$filename = 'logo.jpg';

// Get new dimensions
list($width_logo, $height_logo) = getimagesize($filename);
// Calculate new dimensions
$percent = 0.7;

$new_width = $width * $percent;
$new_height = ($height_logo * $new_width)/$width_logo;

// Resample
$image_p = imagecreatetruecolor($new_width, $new_height);
$image = imagecreatefromjpeg($filename);
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width_logo, $height_logo);


$im = @imagecreate($width, $height) or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 255, 255, 255);


// Set the margins for the stamp and get the height/width of the stamp image
$marge_right = ($width - $new_width)/2;
$marge_bottom = ($height - $new_height)/2;
$sx = imagesx($image_p);
$sy = imagesy($image_p);

// Copy the stamp image onto our photo using the margin offsets and the photo 
// width to calculate positioning of the stamp. 
imagecopymerge($im, $image_p, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($image_p), imagesy($image_p),20);

// Output and free memory
header('Content-type: image/png');
imagepng($im);
imagedestroy($im);

?>

