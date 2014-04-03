<?php
apc_clear_cache();
session_start();

date_default_timezone_set('America/Montreal');

// get config information from admin config file
$admin_config = require(dirname(__FILE__).'/protected/config/main.php');
$db_dbname = explode('dbname=',$admin_config['components']['db']['connectionString']);
$db_dbname = $db_dbname[1];
preg_match('/host=(.*);/',$admin_config['components']['db']['connectionString'],$db_host);
$db_host = $db_host[1];
$db_user = $admin_config['components']['db']['username'];
$db_pass = $admin_config['components']['db']['password'];

$is_admin = 0;
foreach ($_SESSION as $key => $value) if (strstr($key,'__id') && ($match = str_replace('__id','',$key)) && isset($_SESSION[$match.'__name'])) $is_admin = 1;

if (!$is_admin) exit;

require(dirname(__FILE__).'/../_includes/function.php');
// require simple image
require(dirname(__FILE__).'/protected/components/SimpleImage.php');

/* Connect */
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_dbname);

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

/* change character set to utf8 */
if (!$mysqli->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
}

$config_site = array();
if ($result = $mysqli->query('SELECT * FROM config')) {	
	while($row = $result->fetch_assoc()){
		$config_site[$row['name']] = $row['value'];
    } 
}
$result->free();


// vs
switch ($config_site['images_orientation']) {
	case 'portrait':
		$default_zoom_width = $config_site['portrait_zoom_width'];
		$default_zoom_height = $config_site['portrait_zoom_height'];

		$default_cover_width = $config_site['portrait_cover_width'];
		$default_cover_height = $config_site['portrait_cover_height'];
		
		$default_listing_width = $config_site['portrait_listing_width'];
		$default_listing_height = $config_site['portrait_listing_height'];

		$default_suggest_width = $config_site['portrait_suggest_width'];
		$default_suggest_height = $config_site['portrait_suggest_height'];
		
		$default_thumb_width = $config_site['portrait_thumb_width'];
		$default_thumb_height = $config_site['portrait_thumb_height'];				
		break;
	case 'landscape':
		$default_zoom_width = $config_site['landscape_zoom_width'];
		$default_zoom_height = $config_site['landscape_zoom_height'];

		$default_cover_width = $config_site['landscape_cover_width'];
		$default_cover_height = $config_site['landscape_cover_height'];
		
		$default_listing_width = $config_site['landscape_listing_width'];
		$default_listing_height = $config_site['landscape_listing_height'];

		$default_suggest_width = $config_site['landscape_suggest_width'];
		$default_suggest_height = $config_site['landscape_suggest_height'];
		
		$default_thumb_width = $config_site['landscape_thumb_width'];
		$default_thumb_height = $config_site['landscape_thumb_height'];
		break;
}	

$images_path = dirname(__FILE__).'/../images/products/';
$zoom = $images_path.'zoom/';

// get images
if (!$result = $mysqli->query('SELECT * FROM product_image')) throw new Exception('An error occured while trying to get images.');

while ($row = $result->fetch_assoc()) {
	if (is_file($zoom.$row['filename'])) {
		$image = new SimpleImage();
		if ($image->load($zoom.$row['filename'])) {			
			$width = $image->getWidth();
			$height = $image->getHeight();
				
			// save image ZOOM
			if (($width > $height) && !$image->resizeToWidth($default_zoom_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($height > $width && !$image->resizeToHeight($default_zoom_height)) {															
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_zoom_width < $default_zoom_height && !$image->resizeToWidth($default_zoom_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_zoom_width > $default_zoom_height && !$image->resizeToHeight($default_zoom_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'zoom/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
			
			// save image COVER
			if (($width > $height) && !$image->resizeToWidth($default_cover_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($height > $width && !$image->resizeToHeight($default_cover_height)) {															
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_cover_width < $default_cover_height && !$image->resizeToWidth($default_cover_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_cover_width > $default_cover_height && !$image->resizeToHeight($default_cover_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'cover/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
			
			// save image LISTING
			if (($width > $height) && !$image->resizeToWidth($default_listing_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($height > $width && !$image->resizeToHeight($default_listing_height)) {															
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_listing_width < $default_listing_height && !$image->resizeToWidth($default_listing_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_listing_width > $default_listing_height && !$image->resizeToHeight($default_listing_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'listing/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
			
			
			
			// save image LISTING
			if (($width > $height) && !$image->resizeToWidth($default_listing_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($height > $width && !$image->resizeToHeight($default_listing_height)) {															
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_listing_width < $default_listing_height && !$image->resizeToWidth($default_listing_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_listing_width > $default_listing_height && !$image->resizeToHeight($default_listing_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'listing/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
		
			// save image SUGGEST
			if ($width > $height && !$image->resizeToWidth($default_suggest_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			} else if ($height > $width && !$image->resizeToHeight($default_suggest_height)) {																					
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_suggest_width < $default_suggest_height && !$image->resizeToWidth($default_suggest_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_suggest_width > $default_suggest_height && !$image->resizeToHeight($default_suggest_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'suggest/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
			
			// save image THUMB
			if ($width > $height && !$image->resizeToWidth($default_thumb_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			} else if ($height > $width && !$image->resizeToHeight($default_thumb_height)) {																										
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_thumb_width < $default_thumb_height && !$image->resizeToWidth($default_thumb_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_thumb_width > $default_thumb_height && !$image->resizeToHeight($default_thumb_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'thumb/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}	
			
				
		}	
		$image = NULL;
	}
}

// get images variant
if (!$result = $mysqli->query('SELECT * FROM product_image_variant_image')) throw new Exception('An error occured while trying to get images variants.');

while ($row = $result->fetch_assoc()) {
	if (is_file($zoom.$row['filename'])) {
		$image = new SimpleImage();
		if ($image->load($zoom.$row['filename'])) {			
			$width = $image->getWidth();
			$height = $image->getHeight();
				
			// save image ZOOM
			if (($width > $height) && !$image->resizeToWidth($default_zoom_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($height > $width && !$image->resizeToHeight($default_zoom_height)) {															
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_zoom_width < $default_zoom_height && !$image->resizeToWidth($default_zoom_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_zoom_width > $default_zoom_height && !$image->resizeToHeight($default_zoom_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'zoom/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
			
			// save image COVER
			if (($width > $height) && !$image->resizeToWidth($default_cover_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($height > $width && !$image->resizeToHeight($default_cover_height)) {															
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_cover_width < $default_cover_height && !$image->resizeToWidth($default_cover_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_cover_width > $default_cover_height && !$image->resizeToHeight($default_cover_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'cover/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
			
			
			// save image LISTING
			if ($width > $height && !$image->resizeToWidth($default_listing_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			} else if ($height > $width && !$image->resizeToHeight($default_listing_height)) {															
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_listing_width < $default_listing_height && !$image->resizeToWidth($default_listing_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_listing_width > $default_listing_height && !$image->resizeToHeight($default_listing_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'listing/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
		
			// save image SUGGEST
			if ($width > $height && !$image->resizeToWidth($default_suggest_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			} else if ($height > $width && !$image->resizeToHeight($default_suggest_height)) {																					
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_suggest_width < $default_suggest_height && !$image->resizeToWidth($default_suggest_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_suggest_width > $default_suggest_height && !$image->resizeToHeight($default_suggest_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'suggest/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}
			
			// save image THUMB
			if ($width > $height && !$image->resizeToWidth($default_thumb_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			} else if ($height > $width && !$image->resizeToHeight($default_thumb_height)) {																										
				throw new Exception('An error occur while resizing: '.$row['filename']);			
			} else if ($width == $height && $default_thumb_width < $default_thumb_height && !$image->resizeToWidth($default_thumb_width)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if ($width == $height && $default_thumb_width > $default_thumb_height && !$image->resizeToHeight($default_thumb_height)) {
				throw new Exception('An error occur while resizing: '.$row['filename']);
			} else if (!$image->save($images_path.'thumb/'.$row['filename'])) {
				throw new Exception('An error occur while resizing: '.$row['filename']);				
			}		
		}	
		$image = NULL;
	}
}

echo 'done resizing';