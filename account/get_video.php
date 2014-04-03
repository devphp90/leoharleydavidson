<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

if ($id = (int)$_GET['id']) {
	if ($result = $mysqli->query('SELECT
	orders_item_product_downloadable_videos.id,
	orders_item_product_downloadable_videos.current_no_downloads,
	DATE_ADD(IF(orders.id IS NOT NULL,orders.date_order,o.date_order),INTERVAL orders_item_product_downloadable_videos.no_days_expire DAY) AS date_expire,
	product_downloadable_videos.embed_code,
	orders_item_product_downloadable_videos.no_days_expire,
	orders_item_product_downloadable_videos.no_downloads,
	product_downloadable_videos.filename,
	product_downloadable_videos.stream,
	product_downloadable_videos.source
	
	FROM 
	orders_item_product_downloadable_videos 
	LEFT JOIN
	(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
	ON
	(orders_item_product_downloadable_videos.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
	
	LEFT JOIN
	(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
	ON
	(orders_item_product_downloadable_videos.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
	
	INNER JOIN 
	product_downloadable_videos
	ON
	(orders_item_product_downloadable_videos.id_product_downloadable_videos = product_downloadable_videos.id)
	WHERE
	orders_item_product_downloadable_videos.id = "'.$id.'"
	AND
	(
		(orders.id IS NOT NULL AND orders.status IN (1,7) AND orders.id_customer = "'.(int)$_SESSION['customer']['id'].'")
		OR
		(o.id IS NOT NULL AND o.status IN (1,7) AND o.id_customer = "'.(int)$_SESSION['customer']['id'].'")
	)		
	LIMIT 1')) {
		if (!$result->num_rows) {
			echo json_encode(array('errors' => language('account/order', 'ERROR_DOWNLOAD_EXPIRED')));
			exit;	
		}		
		
		$row = $result->fetch_assoc(); 	
		
		if ($row['no_downloads'] > 0 && $row['current_no_downloads'] >= $row['no_downloads']) {
			echo json_encode(array('errors' => language('account/order', 'ERROR_NO_DOWNLOADS_REACHED')));
			exit;	
		}		
		
		if ($row['no_days_expire'] > 0 && strtotime($row['date_expire']) <= time()) {
			echo json_encode(array('errors' => language('account/order', 'ERROR_DOWNLOAD_EXPIRED')));
			exit;				
		}
		
		$output = array();
		$video = $row['embed_code'];
		$filename = $row['source'];
		$width = 640;
		$height = 360;
		
		if (!$row['stream']) {
			// dailymotion
			if (strstr($video,'dailymotion')) $stream = 0;
			else {
				$stream = 1;
				
				$video = "<div id='mediaspace' style='width:".$width."px; height:".$height."px;'></div>
				<script type='text/javascript'>  
				// setimeout is used because dialog doesn't center properly when using html5
				setTimeout(function(){
					jwplayer('mediaspace').setup({
						playlist: [{
							sources: [{
								file: '".$video."'
							}]
						}],
						height: ".$height.",
						primary: 'flash',
						width: ".$width."
					});										
				},1);
				</script>";
			}
		} else {			
			$video = $filename;
			
			$video = "<div id='mediaspace' style='width:".$width."px; height:".$height."px;'></div>
			<script type='text/javascript'>  
			// setimeout is used because dialog doesn't center properly when using html5
			setTimeout(function(){
				jwplayer('mediaspace').setup({
					playlist: [{
						sources: [{ 
							//file: 'rtmp://198.1.127.89/oflaDemo/mp4:".get_current_user()."/".$filename."'
							file: 'play-video.mp4?id=".$id."&type=mp4'
						},{
							file: 'play-video.mp4?id=".$id."&type=mp4'
						}]
					}],
					height: ".$height.",
					primary: 'flash',
					width: ".$width."
				});
			},1);
			</script>";
		}
		
		$output = array('video' => $video, 'video_width' => $width, 'video_height' => $height, 'stream' => $stream);
		
		
		echo json_encode($output);
		
		$mysqli->query('UPDATE orders_item_product_downloadable_videos SET current_no_downloads = current_no_downloads+1 WHERE id = "'.$mysqli->escape_string($id).'" LIMIT 1');
		exit;
	}
}
?>