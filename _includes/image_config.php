<?php
$css_full_content_width = $config_site['css_full_content_width'];
$css_left_column_content_width = $config_site['css_left_column_content_width'];
$css_center_column_content_width = $config_site['css_center_column_content_width'];
$css_center_right_column_content_width = $config_site['css_center_right_column_content_width'];
$css_right_column_content_width = $config_site['css_right_column_content_width'];
$css_suggest_content_width = $config_site['css_suggest_content_width'];

$images_orientation = $config_site['images_orientation'];
$portrait_thumb_width = $config_site['portrait_thumb_width'];
$portrait_thumb_height = $config_site['portrait_thumb_height'];
$portrait_suggest_width = $config_site['portrait_suggest_width'];
$portrait_suggest_height = $config_site['portrait_suggest_height'];
$portrait_listing_width = $config_site['portrait_listing_width'];
$portrait_listing_height = $config_site['portrait_listing_height'];
$portrait_cover_width = $config_site['portrait_cover_width'];
$portrait_cover_height = $config_site['portrait_cover_height'];
$portrait_zoom_width = $config_site['portrait_zoom_width'];
$portrait_zoom_height = $config_site['portrait_zoom_height'];
$landscape_thumb_width = $config_site['landscape_thumb_width'];
$landscape_thumb_height = $config_site['landscape_thumb_height'];
$landscape_suggest_width = $config_site['landscape_suggest_width'];
$landscape_suggest_height = $config_site['landscape_suggest_height'];
$landscape_listing_width = $config_site['landscape_listing_width'];
$landscape_listing_height = $config_site['landscape_listing_height'];
$landscape_cover_width = $config_site['landscape_cover_width'];
$landscape_cover_height = $config_site['landscape_cover_height'];
$landscape_zoom_width = $config_site['landscape_zoom_width'];
$landscape_zoom_height = $config_site['landscape_zoom_height'];

$image_listing_padding_border = 14;// Border and Padding we must add for each image

switch($images_orientation){
	case 'landscape':
		$image_listing_width = $landscape_listing_width;
		$image_listing_height = $landscape_listing_height;
		
		$image_thumb_width = $landscape_thumb_width;
		$image_thumb_height = $landscape_thumb_height;
		
		$image_suggest_width = $landscape_suggest_width;
		$image_suggest_height = $landscape_suggest_height;
		$image_suggest_number_by_row = 5;
		$image_suggest_padding_right = 12;
	break;
	case 'portrait':
		$image_listing_width = $portrait_listing_width;
		$image_listing_height = $portrait_listing_height;
		
		$image_thumb_width = $portrait_thumb_width;
		$image_thumb_height = $portrait_thumb_height;
		
		$image_suggest_width = $portrait_suggest_width;
		$image_suggest_height = $portrait_suggest_height;
		$image_suggest_number_by_row = 6;
		$image_suggest_padding_right = 18;
	break;
}

// css_full_content_width
if($css_full_content_width>0){
	$image_listing_by_row = floor($css_full_content_width/($image_listing_width+$image_listing_padding_border));
	if($image_listing_by_row>1){
		$image_listing_padding_right = floor(($css_full_content_width - ($image_listing_by_row*($image_listing_width+$image_listing_padding_border)))/($image_listing_by_row-1));
	}else{
		$image_listing_padding_right = 1;
	}
	$image_listing_view = $image_listing_by_row * 4; // First view by page listing
}

// css_center_column_content_width
if($css_center_column_content_width>0){
	$image_listing_center_column_by_row = floor($css_center_column_content_width/($image_listing_width+$image_listing_padding_border));
	if($image_listing_center_column_by_row>1){
		$image_listing_center_column_padding_right = floor(($css_center_column_content_width - ($image_listing_center_column_by_row*($image_listing_width+$image_listing_padding_border)))/($image_listing_center_column_by_row-1));
	}else{
		$image_listing_center_column_padding_right = 1;
	}
	$image_listing_center_column_view = $image_listing_center_column_by_row * 4; // First view by page listing
}

// css_center_right_column_content_width
if($css_center_right_column_content_width>0){
	$image_listing_center_right_column_by_row = floor($css_center_right_column_content_width/($image_listing_width+$image_listing_padding_border));
	if($image_listing_center_right_column_by_row>1){
		$image_listing_center_right_column_padding_right = floor(($css_center_right_column_content_width - ($image_listing_center_right_column_by_row*($image_listing_width+$image_listing_padding_border)))/($image_listing_center_right_column_by_row-1));
	}else{
		$image_listing_center_right_column_padding_right = 1;
	}
	$image_listing_center_right_column_view = $image_listing_center_right_column_by_row * 4; // First view by page listing
}

// css_right_column_content_width
if($css_right_column_content_width>0){
	$image_listing_right_column_by_row = floor($css_right_column_content_width/($image_listing_width+$image_listing_padding_border));
	if($image_listing_right_column_by_row>1){
		$image_listing_right_column_padding_right = floor(($css_right_column_content_width - ($image_listing_right_column_by_row*($image_listing_width+$image_listing_padding_border)))/($image_listing_right_column_by_row-1));
	}else{
		$image_listing_right_column_padding_right = 1;
	}
	$image_listing_right_column_view = $image_listing_right_column_by_row * 4; // First view by page listing
}

// css_left_column_content_width
if($css_left_column_content_width>0){
	$image_listing_left_column_by_row = floor($css_left_column_content_width/($image_listing_width+$image_listing_padding_border));
	if($image_listing_left_column_by_row>1){
		$image_listing_left_column_padding_right = floor(($css_left_column_content_width - ($image_listing_left_column_by_row*($image_listing_width+$image_listing_padding_border)))/($image_listing_left_column_by_row-1));
	}else{
		$image_listing_left_column_padding_right = 1;
	}
	$image_listing_left_column_view = $image_listing_left_column_by_row * 4; // First view by page listing
}

// css_suggest product width
/*if($css_suggest_content_width>0){
	$image_suggest_number_by_row = floor($css_suggest_content_width/($image_suggest_width+$image_listing_padding_border));
	if($image_suggest_number_by_row>1){
		$image_suggest_padding_right = floor(($css_suggest_content_width - ($image_suggest_number_by_row*($image_suggest_width+$image_listing_padding_border)))/($image_suggest_number_by_row-1));
	}else{
		$image_suggest_padding_right = 1;	
	}
}*/

//$image_listing_name_product_height = $image_listing_height + 165;
$image_suggest_name_product_height = $image_suggest_height + ($config_site['display_price']?187:132);
?>