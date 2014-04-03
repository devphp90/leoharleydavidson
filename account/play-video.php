<?php
apc_clear_cache();
require(dirname(__FILE__) . "/../_includes/config.php");
require(dirname(__FILE__) . "/../_includes/validate_session.php");

$id = (int)$_GET['id'];

if (!$result = $mysqli->query('SELECT
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
LIMIT 1')) throw new Exception('An error occured while trying to get video info.'."\r\n\r\n".$mysqli->mysqli->error);

if (!$result->num_rows) {
	throw new Exception(language('account/order', 'ERROR_DOWNLOAD_EXPIRED'));
	exit;   
}       

$row = $result->fetch_assoc();  
$result->free();

if ($row['no_downloads'] > 0 && $row['current_no_downloads'] >= $row['no_downloads']) {
	throw new Exception(language('account/order', 'ERROR_NO_DOWNLOADS_REACHED'));
	exit;   
}       

if ($row['no_days_expire'] > 0 && strtotime($row['date_expire']) <= time()) {
	throw new Exception(language('account/order', 'ERROR_DOWNLOAD_EXPIRED'));
	exit;               
}

$file = dirname(__FILE__).'/../admin/protected/streaming_videos/'.$row['source'];
//$file = dirname(__FILE__).'/../fab23601420ba1e10a9eab72fc89c15b2.mp4';

require(dirname(__FILE__).'/../_includes/classes/stream.php');

$st = new VSTREAM();
//$st->stream(dirname(__FILE__).'/../397d729225599bcda978ac4fd4aaeba2.mp4'); 
$st->stream($file); 
//readfile($file);