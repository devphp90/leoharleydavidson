<?php
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;

// get config information from admin config file
$admin_config = require(dirname(__FILE__).'/../admin/protected/config/main.php');

// set timezone
date_default_timezone_set($admin_config['timeZone']);

$db_dbname = explode('dbname=',$admin_config['components']['db']['connectionString']);
$db_dbname = $db_dbname[1];
preg_match('/host=(.*);/',$admin_config['components']['db']['connectionString'],$db_host);
$db_host = $db_host[1];
$db_user = $admin_config['components']['db']['username'];
$db_pass = $admin_config['components']['db']['password'];

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

$current_datetime = date('Y-m-d H:i:s');

// recalculate sell_price if changed for simple products
// check for price change
if (!$result = $mysqli->query('SELECT 
product.id,
IF(product.special_price_to_date != "0000-00-00 00:00:00" AND "'.$current_datetime.'" BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price) AS sell_price
FROM
product 
WHERE
product.active = 1
AND
product.product_type = 0
AND
product.sell_price != IF(product.special_price_to_date != "0000-00-00 00:00:00" AND "'.$current_datetime.'" BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)')) throw new Exception('An error occured while trying to get product prices that changed.'."\r\n\r\n".$mysqli->error);

if (!$stmt_update_price = $mysqli->prepare('UPDATE 
product
SET
product.sell_price = ?
WHERE
product.id = ?
LIMIT 1')) throw new Exception('An error occured while trying to prepare update product price statement.'."\r\n\r\n".$mysqli->error);

if (!$stmt_update_product_combo = $mysqli->prepare('UPDATE					
product_combo
INNER JOIN
product 			
ON
(product_combo.id_product = product.id)
SET
product.cost_price = get_product_cost_price(product.id,0),
product.price = get_combo_base_price(product.id),
product.sell_price = get_product_current_price(product.id,0,0)			
WHERE
product_combo.id_combo_product = ?
AND
product.active = 1')) throw new Exception('An error occured while trying to prepare update product combo price statement.'."\r\n\r\n".$mysqli->error);

if (!$stmt_update_product_bundle = $mysqli->prepare('UPDATE			
product_bundled_product_group_product
INNER JOIN 
(product_bundled_product_group CROSS JOIN product)
ON
(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id AND product_bundled_product_group.id_product = product.id)
SET
product.cost_price = get_product_cost_price(product.id,0),
product.price = get_product_current_price(product.id,0,0),
product.sell_price = get_product_current_price(product.id,0,0)		
WHERE
product_bundled_product_group_product.id_product = ?
AND
product.active = 1')) throw new Exception('An error occured while trying to prepare update product bundle price statement.'."\r\n\r\n".$mysqli->error);

while ($row = $result->fetch_assoc()) {
	// update price
	if (!$stmt_update_price->bind_param("di", $row['sell_price'],$row['id'])) throw new Exception('An error occured while trying to bind params to update product price statement.'."\r\n\r\n".$this->mysqli->error);
	
	// Execute the statement 
	if (!$stmt_update_price->execute()) throw new Exception('An error occured while trying to update product price.'."\r\n\r\n".$this->mysqli->error);	
	
	// update combos
	if (!$stmt_update_product_combo->bind_param("i", $row['id'])) throw new Exception('An error occured while trying to bind params to update product combo price statement.'."\r\n\r\n".$this->mysqli->error);
	
	// Execute the statement 
	if (!$stmt_update_product_combo->execute()) throw new Exception('An error occured while trying to update product combo price.'."\r\n\r\n".$this->mysqli->error);	
		
	// update bundles	
	if (!$stmt_update_product_bundle->bind_param("i", $row['id'])) throw new Exception('An error occured while trying to bind params to update product bundle price statement.'."\r\n\r\n".$this->mysqli->error);
	
	// Execute the statement 
	if (!$stmt_update_product_bundle->execute()) throw new Exception('An error occured while trying to update product bundle price.'."\r\n\r\n".$this->mysqli->error);				
}

$result->free();
$stmt_update_price->close();
$stmt_update_product_combo->close();
$stmt_update_product_bundle->close();

// products to update
$update_products = array();

// get products not associated to a rebate
if (!$result_product = $mysqli->query('SELECT id, get_applicable_rebate_id(id, sell_price) AS id_rebate_coupon FROM product WHERE active = 1 AND product_type = 0 AND id_rebate_coupon = 0 AND sell_price > 0')) throw new Exception('An error occured while trying to get products.');

while ($row_product = $result_product->fetch_assoc()) $update_products[$row_product['id']] = $row_product;

// get products associated to a rebate that expired
if (!$result_product = $mysqli->query('SELECT product.id, get_applicable_rebate_id(product.id, product.sell_price) AS id_rebate_coupon FROM product LEFT JOIN rebate_coupon ON (product.id_rebate_coupon = rebate_coupon.id) WHERE product.active = 1 AND product.product_type = 0 AND product.id_rebate_coupon != 0 AND (rebate_coupon.id IS NULL OR rebate_coupon.active = 0 OR (rebate_coupon.end_date != "0000-00-00 00:00:00" AND rebate_coupon.end_date < "'.$current_datetime.'") OR (product.on_sale_end_date = "0000-00-00 00:00:00" AND rebate_coupon.end_date != product.on_sale_end_date) OR ((SELECT COUNT(id) FROM rebate_coupon_product WHERE id_rebate_coupon = product.id_rebate_coupon AND id_product = product.id) = 0) OR ((SELECT COUNT(rebate_coupon_category.id_category) FROM product_category INNER JOIN rebate_coupon_category ON (product_category.id_category = rebate_coupon_category.id_category) WHERE product_category.id_product = product.id AND rebate_coupon_category.id_rebate_coupon = product.id_rebate_coupon) = 0))')) throw new Exception('An error occured while trying to get products.');

while ($row_product = $result_product->fetch_assoc()) $update_products[$row_product['id']] = $row_product;

if (sizeof($update_products)) {
	foreach ($update_products as $row_product) {
		if (!$result_product = $mysqli->query('SELECT IF(rebate_coupon.id IS NOT NULL AND rebate_coupon.end_date != "0000-00-00 00:00:00" AND rebate_coupon.end_date > "'.$current_datetime.'" AND (product.special_price_to_date = "0000-00-00 00:00:00" OR (product.special_price_to_date != "0000-00-00 00:00:00" AND rebate_coupon.end_date < product.special_price_to_date)),rebate_coupon.end_date,IF(product.special_price_to_date != "0000-00-00 00:00:00" AND "'.$current_datetime.'" BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price_to_date,"")) AS on_sale_end_date FROM product LEFT JOIN rebate_coupon ON (rebate_coupon.id = "'.($row_product['id_rebate_coupon'] ? $row_product['id_rebate_coupon']:0).'") WHERE product.id = "'.$row_product['id'].'" LIMIT 1')) throw new Exception('An error occured while trying to get product info.');
		
		$row_product_info = $result_product->fetch_assoc();
	
		$mysqli->query('UPDATE product SET id_rebate_coupon = "'.$row_product['id_rebate_coupon'].'", on_sale_end_date = "'.$row_product_info['on_sale_end_date'].'" WHERE id = "'.$row_product['id'].'"');	
	}
}

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = ($endtime - $starttime); 

echo "This page was created in ".$totaltime." seconds"; 
?>