<?php
date_default_timezone_set('America/Montreal');

// get config information from admin config file
$admin_config = require(dirname(__FILE__).'/../config/main.php');
$db_dbname = explode('dbname=',$admin_config['components']['db']['connectionString']);
$db_dbname = $db_dbname[1];
preg_match('/host=(.*);/',$admin_config['components']['db']['connectionString'],$db_host);
$db_host = $db_host[1];
$db_user = $admin_config['components']['db']['username'];
$db_pass = $admin_config['components']['db']['password'];

require(dirname(__FILE__).'/../../../_includes/function.php');

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

$id = (int)$_ENV['id'];
$columns_separated_with = trim($_ENV['columns_separated_with']);
$columns_enclosed_with = trim($_ENV['columns_enclosed_with']);
$columns_escaped_with = trim($_ENV['columns_escaped_with']);
$skip_first_row = (int)$_ENV['skip_first_row'];
$set_active = (int)$_ENV['set_active'];

setlocale(LC_ALL, 'en_US.UTF8');

$current_datetime = date('Y-m-d H:i:s');
$current_id_user = (int)$_ENV['id_user'];							
$current_row = array();

$errors = array();
$columns = array();
$columns_keys = array();

if ($result = $mysqli->query('SELECT 
import_tpl_files.source,
import_tpl_files.status,
import_tpl_files.progress,
import_tpl.type,
import_tpl.subtract_qty
FROM
import_tpl_files
INNER JOIN
import_tpl
ON
(import_tpl_files.id_import_tpl = import_tpl.id)
WHERE
import_tpl_files.id = "'.$mysqli->escape_string($id).'"
AND
import_tpl_files.errors = ""
AND
import_tpl_files.date_imported = "0000-00-00 00:00:00"
LIMIT 1')) {
	if ($row = $result->fetch_assoc()) {	
		$type = $row['type'];
		$subtract_qty = $row['subtract_qty'];

		// get template columns				
		if ($result_columns = $mysqli->query('SELECT
		import_tpl_columns.id_import_columns,
		import_tpl_columns.extra
		FROM 
		import_tpl_files
		
		INNER JOIN
		import_tpl_columns
		ON
		(import_tpl_files.id_import_tpl = import_tpl_columns.id_import_tpl)
		
		WHERE
		import_tpl_files.id = "'.$mysqli->escape_string($id).'"
		ORDER BY
		import_tpl_columns.sort_order ASC')) {
			$i=0;
			while ($row_column = $result_columns->fetch_assoc()) {
				$columns[$i] = array(
					'id_import_columns' => $row_column['id_import_columns'],
					'extra' => $row_column['extra'],
				);
				
				$columns_keys[$row_column['id_import_columns']][] = $i;
				
				++$i;
			}
			
			$row_column = NULL;
		}	
		$result_columns->free();			
		
		// update progress
						
		/* Prepare the statement */
		if (!$stmt_upd_progress = $mysqli->prepare('UPDATE
		import_tpl_files
		SET
		import_tpl_files.progress = ?,
		import_tpl_files.errors = ?,
		import_tpl_files.status = ?,
		import_tpl_files.date_imported = ?
		WHERE
		import_tpl_files.id = ?
		LIMIT 1')) throw new Exception('An error occured while trying to update progress statement.'."\r\n\r\n".$mysqli->error);		

		switch ($type) {
			// add products
			case 0:
			// add / update
			case 1:
			// update
			case 2:
				// require simple image
				require(dirname(__FILE__).'/SimpleImage.php');
				
				$file_path = dirname(__FILE__).'/../import_files/';
				$targetPath = dirname(__FILE__).'/../../../images/products/';	
				$allowed_ext = array(
					'gif',
					'jpeg',
					'jpg',
					'png',
				);
				
				$image = new SimpleImage();		
				
				$sku_length = 50;
				$alias_length = 150;
				
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
		
				$source = $row['source'];		
				$status = $row['status'];
				$progress = !empty($row['progress']) ? unserialize(base64_decode($row['progress'])):array();
				$progress_data = !empty($progress['data']) ? md5(serialize($progress['data'])):'';	
				$row = NULL;
				
				// update format options
				$mysqli->query('UPDATE 
				import_tpl_files
				SET
				import_tpl_files.columns_separated_with = "'.$mysqli->escape_string($columns_separated_with).'",
				import_tpl_files.columns_enclosed_with = "'.$mysqli->escape_string($columns_enclosed_with).'",
				import_tpl_files.columns_escaped_with = "'.$mysqli->escape_string($columns_escaped_with).'",
				import_tpl_files.skip_first_row = "'.$mysqli->escape_string($skip_first_row).'"
				import_tpl_files.set_active = "'.$mysqli->escape_string($set_active).'"
				WHERE
				import_tpl_files.id = "'.$mysqli->escape_string($id).'"
				LIMIT 1');		
				
				// check if sku exists
								
				/* Prepare the statement */
				if (!$stmt_sku_exists = $mysqli->prepare('SELECT
				COUNT(product.id) 
				FROM
				product
				WHERE
				product.sku = ?
				LIMIT 1')) throw new Exception('An error occured while trying to check sku statement.'."\r\n\r\n".$mysqli->error);		
				
				// check if alias exists
								
				/* Prepare the statement */
				if (!$stmt_alias_exists = $mysqli->prepare('SELECT
				COUNT(product_description.id_product) 
				FROM
				product_description
				WHERE
				product_description.alias = ?
				AND
				product_description.language_code = ?
				LIMIT 1')) throw new Exception('An error occured while trying to check alias statement.'."\r\n\r\n".$mysqli->error);	
				
				// add product
								
				/* Prepare the statement */
				$table_columns = array();
				
				if (isset($columns_keys[7])) $table_columns[] = 'sku = ?';
				if (isset($columns_keys[25])) $table_columns[] = 'taxable = ?';
				if (isset($columns_keys[8])) $table_columns[] = 'brand = ?';
				if (isset($columns_keys[9])) $table_columns[] = 'model = ?';
				if (isset($columns_keys[10])) $table_columns[] = 'cost_price = ?';
				if (isset($columns_keys[11])) $table_columns[] = 'price = ?';
				if (isset($columns_keys[12])) $table_columns[] = 'special_price = ?';
				if (isset($columns_keys[13])) $table_columns[] = 'special_price_from_date = ?';
				if (isset($columns_keys[14])) $table_columns[] = 'special_price_to_date = ?';
				if (isset($columns_keys[17])) $table_columns[] = 'qty = ?';
				if (isset($columns_keys[18])) $table_columns[] = 'notify_qty = ?';
				if (isset($columns_keys[19])) $table_columns[] = 'out_of_stock = ?';
				if (isset($columns_keys[20])) $table_columns[] = 'out_of_stock_enabled = ?';
				if (isset($columns_keys[21])) $table_columns[] = 'weight = ?';
				if (isset($columns_keys[22])) $table_columns[] = 'enable_local_pickup = ?';
				if (isset($columns_keys[23])) $table_columns[] = 'used = ?';
				if (isset($columns_keys[24])) $table_columns[] = 'featured = ?';
				if (isset($columns_keys[26]) or $set_active) $table_columns[] = 'active = ?';
				if (isset($columns_keys[29])) $table_columns[] = 'length = ?';
				if (isset($columns_keys[30])) $table_columns[] = 'width = ?';
				if (isset($columns_keys[31])) $table_columns[] = 'height = ?';
				$table_columns[] = 'date_created = ?';
				
				if (!$stmt_add_product = $mysqli->prepare('INSERT INTO
				product
				SET
				'.implode(',',$table_columns))) throw new Exception('An error occured while trying to add product statement.'."\r\n\r\n".$mysqli->error);		
				
				$stmt_types = '';
				$stmt_add_product_params = array();
						
				if (isset($columns_keys[7])){
					$stmt_types .= 's';
					$stmt_add_product_params[] = &$sku;
				}
				if (isset($columns_keys[25])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$taxable;
				}
				if (isset($columns_keys[8])){
					$stmt_types .= 's';
					$stmt_add_product_params[] = &$brand;
				}
				if (isset($columns_keys[9])){
					$stmt_types .= 's';
					$stmt_add_product_params[] = &$model;
				}
				if (isset($columns_keys[10])){
					$stmt_types .= 'd';
					$stmt_add_product_params[] = &$cost_price;
				}
				if (isset($columns_keys[11])){
					$stmt_types .= 'd';
					$stmt_add_product_params[] = &$price;
				}
				if (isset($columns_keys[12])){
					$stmt_types .= 'd';
					$stmt_add_product_params[] = &$special_price;
				}
				if (isset($columns_keys[13])){
					$stmt_types .= 's';
					$stmt_add_product_params[] = &$special_price_from_date;
				}
				if (isset($columns_keys[14])){
					$stmt_types .= 's';
					$stmt_add_product_params[] = &$special_price_to_date;
				}				
				if (isset($columns_keys[17])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$qty;
				}
				if (isset($columns_keys[18])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$notify_qty;
				}
				if (isset($columns_keys[19])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$out_of_stock;
				}
				if (isset($columns_keys[20])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$out_of_stock_enabled;
				} 
				if (isset($columns_keys[21])){
					$stmt_types .= 'd';
					$stmt_add_product_params[] = &$weight;
				}
				if (isset($columns_keys[22])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$enable_local_pickup;
				}
				if (isset($columns_keys[23])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$used;
				}
				if (isset($columns_keys[24])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$featured;
				}
				if (isset($columns_keys[26]) or $set_active){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$active;
				}
				if (isset($columns_keys[29])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$length;
				}				
				if (isset($columns_keys[30])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$width;
				}								
				if (isset($columns_keys[31])){
					$stmt_types .= 'i';
					$stmt_add_product_params[] = &$height;
				}								
				
				$stmt_types .= 's';
 				$stmt_add_product_params[] = &$current_datetime;
				
				array_unshift($stmt_add_product_params, $stmt_types);
				
				// update product
				
				/* Prepare the statement */
				$table_columns = array();
				
				if (isset($columns_keys[25])) $table_columns[] = 'taxable = ?';
				if (isset($columns_keys[8])) $table_columns[] = 'brand = ?';
				if (isset($columns_keys[9])) $table_columns[] = 'model = ?';
				if (isset($columns_keys[10])) $table_columns[] = 'cost_price = ?';
				if (isset($columns_keys[11])) $table_columns[] = 'price = ?';
				if (isset($columns_keys[12])) $table_columns[] = 'special_price = ?';
				if (isset($columns_keys[13])) $table_columns[] = 'special_price_from_date = ?';
				if (isset($columns_keys[14])) $table_columns[] = 'special_price_to_date = ?';
				if (isset($columns_keys[17])) $table_columns[] = 'qty = ?';
				if (isset($columns_keys[18])) $table_columns[] = 'notify_qty = ?';
				if (isset($columns_keys[19])) $table_columns[] = 'out_of_stock = ?';
				if (isset($columns_keys[20])) $table_columns[] = 'out_of_stock_enabled = ?';
				if (isset($columns_keys[21])) $table_columns[] = 'weight = ?';
				if (isset($columns_keys[22])) $table_columns[] = 'enable_local_pickup = ?';
				if (isset($columns_keys[23])) $table_columns[] = 'used = ?';
				if (isset($columns_keys[24])) $table_columns[] = 'featured = ?';
				if (isset($columns_keys[26]) or $set_active) $table_columns[] = 'active = ?';				
				if (isset($columns_keys[29])) $table_columns[] = 'length = ?';
				if (isset($columns_keys[30])) $table_columns[] = 'width = ?';
				if (isset($columns_keys[31])) $table_columns[] = 'height = ?';				
				
				if (sizeof($table_columns)) {
					if (!$stmt_upd_product = $mysqli->prepare('UPDATE
					product
					SET
					'.implode(',',$table_columns).'
					WHERE
					product.sku = ?
					LIMIT 1')) throw new Exception('An error occured while trying to update product statement.'."\r\n\r\n".$mysqli->error);						
	
					$stmt_types = '';
					$stmt_upd_product_params = array();
							
					if (isset($columns_keys[25])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$taxable;
					}
					if (isset($columns_keys[8])){
						$stmt_types .= 's';
						$stmt_upd_product_params[] = &$brand;
					}
					if (isset($columns_keys[9])){
						$stmt_types .= 's';
						$stmt_upd_product_params[] = &$model;
					}
					if (isset($columns_keys[10])){
						$stmt_types .= 'd';
						$stmt_upd_product_params[] = &$cost_price;
					}
					if (isset($columns_keys[11])){
						$stmt_types .= 'd';
						$stmt_upd_product_params[] = &$price;
					}
					if (isset($columns_keys[12])){
						$stmt_types .= 'd';
						$stmt_upd_product_params[] = &$special_price;
					}
					if (isset($columns_keys[13])){
						$stmt_types .= 's';
						$stmt_upd_product_params[] = &$special_price_from_date;
					}
					if (isset($columns_keys[14])){
						$stmt_types .= 's';
						$stmt_upd_product_params[] = &$special_price_to_date;
					}				
					if (isset($columns_keys[17])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$qty;
					}
					if (isset($columns_keys[18])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$notify_qty;
					}
					if (isset($columns_keys[19])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$out_of_stock;
					}
					if (isset($columns_keys[20])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$out_of_stock_enabled;
					} 
					if (isset($columns_keys[21])){
						$stmt_types .= 'd';
						$stmt_upd_product_params[] = &$weight;
					}
					if (isset($columns_keys[22])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$enable_local_pickup;
					}
					if (isset($columns_keys[23])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$used;
					}
					if (isset($columns_keys[24])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$featured;
					}
					if (isset($columns_keys[26]) or $set_active){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$active;
					}
					if (isset($columns_keys[29])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$length;
					}				
					if (isset($columns_keys[30])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$width;
					}								
					if (isset($columns_keys[31])){
						$stmt_types .= 'i';
						$stmt_upd_product_params[] = &$height;
					}							
					
					// where
					$stmt_types .= 's';
					$stmt_upd_product_params[] = &$sku;
					
					array_unshift($stmt_upd_product_params, $stmt_types);	
				}
				
				// update product variant
				
				/* Prepare the statement */
				$table_columns = array();
				
				if (isset($columns_keys[10])) $table_columns[] = 'cost_price = ?';
				if (isset($columns_keys[11])) $table_columns[] = 'price = ?';
				if (isset($columns_keys[17])) $table_columns[] = 'qty = ?';
				if (isset($columns_keys[18])) $table_columns[] = 'notify_qty = ?';
				if (isset($columns_keys[21])) $table_columns[] = 'weight = ?';
				if (isset($columns_keys[26]) or $set_active) $table_columns[] = 'active = ?';
				if (isset($columns_keys[29])) $table_columns[] = 'length = ?';
				if (isset($columns_keys[30])) $table_columns[] = 'width = ?';
				if (isset($columns_keys[31])) $table_columns[] = 'height = ?';					
				
				if (sizeof($table_columns)) {
					if (!$stmt_upd_product_variant = $mysqli->prepare('UPDATE
					product_variant
					SET
					'.implode(',',$table_columns).'
					WHERE
					product_variant.id = ?
					LIMIT 1')) throw new Exception('An error occured while trying to update product variant statement.'."\r\n\r\n".$mysqli->error);						
	
					$stmt_types = '';
					$stmt_upd_product_variant_params = array();
							
					if (isset($columns_keys[10])){
						$stmt_types .= 'd';
						$stmt_upd_product_variant_params[] = &$cost_price;
					}
					if (isset($columns_keys[11])){
						$stmt_types .= 'd';
						$stmt_upd_product_variant_params[] = &$price;
					}
					if (isset($columns_keys[17])){
						$stmt_types .= 'i';
						$stmt_upd_product_variant_params[] = &$qty;
					}
					if (isset($columns_keys[18])){
						$stmt_types .= 'i';
						$stmt_upd_product_variant_params[] = &$notify_qty;
					}
					if (isset($columns_keys[21])){
						$stmt_types .= 'd';
						$stmt_upd_product_variant_params[] = &$weight;
					}
					if (isset($columns_keys[26]) or $set_active){
						$stmt_types .= 'i';
						$stmt_upd_product_variant_params[] = &$active;
					}
					if (isset($columns_keys[29])){
						$stmt_types .= 'i';
						$stmt_upd_product_variant_params[] = &$length;
					}				
					if (isset($columns_keys[30])){
						$stmt_types .= 'i';
						$stmt_upd_product_variant_params[] = &$width;
					}								
					if (isset($columns_keys[31])){
						$stmt_types .= 'i';
						$stmt_upd_product_variant_params[] = &$height;
					}					
					
					// where
					$stmt_types .= 'i';
					$stmt_upd_product_variant_params[] = &$id_product_variant;
					
					array_unshift($stmt_upd_product_variant_params, $stmt_types);								
				}
				
				// add product description
				$table_columns = array();
				
				if (isset($columns_keys[1])) $table_columns[] = 'name = ?';
				if (isset($columns_keys[2])) $table_columns[] = 'short_desc = ?';
				if (isset($columns_keys[3])) $table_columns[] = 'description = ?';
				if (isset($columns_keys[4])) $table_columns[] = 'meta_description = ?';
				if (isset($columns_keys[5])) $table_columns[] = 'meta_keywords = ?';
				if (isset($columns_keys[6]) or $type != 2) $table_columns[] = 'alias = ?';	
				
				if (sizeof($table_columns)) {				
					$table_columns[] = 'id_product = ?';
					$table_columns[] = 'language_code = ?';	
					
					if (!$stmt_add_product_description = $mysqli->prepare('INSERT INTO
					product_description
					SET
					'.implode(',',$table_columns))) throw new Exception('An error occured while trying to add product description statement.'."\r\n\r\n".$mysqli->error);		
					
					$stmt_types = '';
					$stmt_add_product_description_params = array();
					
					if (isset($columns_keys[1])){
						$stmt_types .= 's';
						$stmt_add_product_description_params[] = &$name;
					}
					if (isset($columns_keys[2])){
						$stmt_types .= 's';
						$stmt_add_product_description_params[] = &$short_desc;
					}
					if (isset($columns_keys[3])){
						$stmt_types .= 's';
						$stmt_add_product_description_params[] = &$description;
					}
					if (isset($columns_keys[4])){
						$stmt_types .= 's';
						$stmt_add_product_description_params[] = &$meta_description;
					}
					if (isset($columns_keys[5])){
						$stmt_types .= 's';
						$stmt_add_product_description_params[] = &$meta_keywords;
					}
					if (isset($columns_keys[6]) or $type != 2){
						$stmt_types .= 's';
						$stmt_add_product_description_params[] = &$alias;
					}
					
					// id_product and language_code
					$stmt_types .= 'is';
					$stmt_add_product_description_params[] = &$id_product;
					$stmt_add_product_description_params[] = &$language_code;
					
					array_unshift($stmt_add_product_description_params, $stmt_types);		
				}
				
				// update product description
								
				/* Prepare the statement */
				$table_columns = array();
				
				if (isset($columns_keys[1])) $table_columns[] = 'name = ?';
				if (isset($columns_keys[2])) $table_columns[] = 'short_desc = ?';
				if (isset($columns_keys[3])) $table_columns[] = 'description = ?';
				if (isset($columns_keys[4])) $table_columns[] = 'meta_description = ?';
				if (isset($columns_keys[5])) $table_columns[] = 'meta_keywords = ?';
				if (isset($columns_keys[6])) $table_columns[] = 'alias = ?';
				
				if (sizeof($table_columns)) {						
					if (!$stmt_upd_product_description = $mysqli->prepare('UPDATE
					product_description
					SET
					'.implode(',',$table_columns).'
					WHERE 
					id_product = ?
					AND
					language_code = ?')) throw new Exception('An error occured while trying to update product description statement.'."\r\n\r\n".$mysqli->error);		
					
					$stmt_types = '';
					$stmt_upd_product_description_params = array();
					
					if (isset($columns_keys[1])){
						$stmt_types .= 's';
						$stmt_upd_product_description_params[] = &$name;
					}
					if (isset($columns_keys[2])){
						$stmt_types .= 's';
						$stmt_upd_product_description_params[] = &$short_desc;
					}
					if (isset($columns_keys[3])){
						$stmt_types .= 's';
						$stmt_upd_product_description_params[] = &$description;
					}
					if (isset($columns_keys[4])){
						$stmt_types .= 's';
						$stmt_upd_product_description_params[] = &$meta_description;
					}
					if (isset($columns_keys[5])){
						$stmt_types .= 's';
						$stmt_upd_product_description_params[] = &$meta_keywords;
					}
					if (isset($columns_keys[6])){
						$stmt_types .= 's';
						$stmt_upd_product_description_params[] = &$alias;
					}
					
					// id_product and language_code
					$stmt_types .= 'is';
					$stmt_upd_product_description_params[] = &$id_product;
					$stmt_upd_product_description_params[] = &$language_code;
					
					array_unshift($stmt_upd_product_description_params, $stmt_types);						
				}
				
				// check if product description exists
								
				/* Prepare the statement */
				if (!$stmt_check_product_description = $mysqli->prepare('SELECT
				COUNT(product_description.id_product)
				FROM
				product_description
				WHERE 
				id_product = ?
				AND
				language_code = ?')) throw new Exception('An error occured while trying to check if product description exists statement.'."\r\n\r\n".$mysqli->error);	
				
				// add category
								
				/* Prepare the statement */
				$table_columns = array();
				
				if (isset($columns_keys[28])) $table_columns[] = 'id_parent = ?';
				$table_columns[] = 'active = 1';
				
				if (!$stmt_add_category = $mysqli->prepare('INSERT INTO
				category
				SET
				'.implode(',',$table_columns))) throw new Exception('An error occured while trying to add product statement.'."\r\n\r\n".$mysqli->error);		
				
				$stmt_types = '';
				$stmt_add_category_params = array();
						
				if (isset($columns_keys[28])){
					$stmt_types .= 'i';
					$stmt_add_category_params[] = &$id_category_parent;
				}

				array_unshift($stmt_add_category_params, $stmt_types);
				
				
				// add category description
				$table_columns = array();
				
				if (isset($columns_keys[27]) or isset($columns_keys[28])){
					$table_columns[] = 'name = ?';
					$table_columns[] = 'meta_description = ?';
					$table_columns[] = 'meta_keywords = ?';
					$table_columns[] = 'alias = ?';	
				}
				
				if (sizeof($table_columns)) {				
					$table_columns[] = 'id_category = ?';
					$table_columns[] = 'language_code = ?';	
					
					if (!$stmt_add_category_description = $mysqli->prepare('INSERT INTO
					category_description
					SET
					'.implode(',',$table_columns))) throw new Exception('An error occured while trying to add product description statement.'."\r\n\r\n".$mysqli->error);		
					
					$stmt_types = '';
					$stmt_add_category_description_params = array();
					
					$stmt_types .= 'ssss';
					$stmt_add_category_description_params[] = &$name;
					$stmt_add_category_description_params[] = &$meta_description;
					$stmt_add_category_description_params[] = &$meta_keywords;
					$stmt_add_category_description_params[] = &$alias;
					
					// id_category and language_code
					$stmt_types .= 'is';
					$stmt_add_category_description_params[] = &$id_category;
					$stmt_add_category_description_params[] = &$language_code;
					
					array_unshift($stmt_add_category_description_params, $stmt_types);		
				}
				
				// check if category description exists
								
				/* Prepare the statement */
				if (!$stmt_check_category_description = $mysqli->prepare('SELECT
				category_description.id_category
				FROM
				category_description
				INNER JOIN category
				ON
				category_description.id_category = category.id
				WHERE
				category_description.name = ?
				AND
				category_description.language_code = ?
				AND
				category.id_parent = ?
				LIMIT 1')) throw new Exception('An error occured while trying to check if product description exists statement.'."\r\n\r\n".$mysqli->error);
				
				// check if product category exists
								
				/* Prepare the statement */
				if (!$stmt_check_product_category = $mysqli->prepare('SELECT
				product_category.id_product
				FROM
				product_category
				WHERE
				product_category.id_product = ?
				AND
				product_category.id_category = ?
				LIMIT 1')) throw new Exception('An error occured while trying to check if product description exists statement.'."\r\n\r\n".$mysqli->error);
				
				// add product category	
														
				/* Prepare the statement */
				if (!$stmt_add_product_category = $mysqli->prepare('INSERT INTO
				product_category
				SET
				id_product = ?,
				id_category = ?')) throw new Exception('An error occured while trying to add product image statement.'."\r\n\r\n".$mysqli->error);	
				
				
				// check if category alias exists
								
				/* Prepare the statement */
				if (!$stmt_check_alias_category = $mysqli->prepare('SELECT 
				COUNT(category_description.id_category)
				FROM
				category_description
				INNER JOIN category
				ON
				category_description.id_category = category.id
				WHERE
				category_description.alias = ?
				AND
				category_description.language_code = ?
				LIMIT 1')) throw new Exception('An error occured while trying to check if category alias exists statement.'."\r\n\r\n".$mysqli->error);
								
				// add product image
				if (isset($columns_keys[15]) || isset($columns_keys[16])) { 											
					/* Prepare the statement */
					if (!$stmt_add_product_image = $mysqli->prepare('INSERT INTO
					product_image
					SET
					id_product = ?,
					cover = ?,
					id_user_created = ?,
					id_user_modified = ?,
					date_created = ?')) throw new Exception('An error occured while trying to add product image statement.'."\r\n\r\n".$mysqli->error);												
					
					// update product image
									
					/* Prepare the statement */
					if (!$stmt_upd_product_image = $mysqli->prepare('UPDATE
					product_image
					SET
					original = ?,
					filename = ?,
					force_crop = ?,
					sort_order = ?
					WHERE
					id = ?
					LIMIT 1')) throw new Exception('An error occured while trying to add product image statement.'."\r\n\r\n".$mysqli->error);		
									
					// remove cover image
									
					/* Prepare the statement */
					if (!$stmt_remove_product_image_cover = $mysqli->prepare('UPDATE
					product_image
					SET
					product_image.cover = 0
					WHERE
					product_image.id_product = ?')) throw new Exception('An error occured while trying to remove product image cover statement.'."\r\n\r\n".$mysqli->error);				
					
					// get product image sort order
									
					/* Prepare the statement */
					if (!$stmt_get_product_image_sort_order = $mysqli->prepare('SELECT
					product_image.sort_order+1
					FROM 
					product_image
					WHERE
					product_image.id_product = ?
					ORDER BY 
					product_image.sort_order DESC
					LIMIT 1')) throw new Exception('An error occured while trying to get product image sort order statement.'."\r\n\r\n".$mysqli->error);		
				}
				
				// get existing product id by sku
								
				/* Prepare the statement */
				if (!$stmt_get_product_id_by_sku = $mysqli->prepare('SELECT
				product.id,
				product.qty
				FROM 
				product
				WHERE
				product.sku = ?
				LIMIT 1')) throw new Exception('An error occured while trying to get product id by sku statement.'."\r\n\r\n".$mysqli->error);		
				
				// get existing product variant id by sku
								
				/* Prepare the statement */
				if (!$stmt_get_product_variant_id_by_sku = $mysqli->prepare('SELECT
				product_variant.id,
				product_variant.id_product,
				product_variant.qty,
				product.sell_price
				FROM 
				product_variant
				INNER JOIN
				product
				ON
				(product_variant.id_product = product.id)
				WHERE
				product_variant.sku = ?
				LIMIT 1')) throw new Exception('An error occured while trying to get product id by sku statement.'."\r\n\r\n".$mysqli->error);						
				
				// get existing product id by alias
								
				/* Prepare the statement */
				if (!$stmt_get_product_id_by_alias = $mysqli->prepare('SELECT
				product_description.id_product
				FROM 
				product_description
				WHERE
				product_description.alias = ?
				AND
				product_description.language_code = ?
				LIMIT 1')) throw new Exception('An error occured while trying to get product id by alias statement.'."\r\n\r\n".$mysqli->error);
								
				// get existing product price by id
				if (isset($columns_keys[12])) {								
					/* Prepare the statement */
					if (!$stmt_get_product_price_by_id = $mysqli->prepare('SELECT
					product.price
					FROM 
					product
					WHERE
					product.id = ?
					LIMIT 1')) throw new Exception('An error occured while trying to get product price by id statement.'."\r\n\r\n".$mysqli->error);															
				}
				
				// status is pending
				// status is validating
				if (!$status || $status == 1) {				
					if (($handle = fopen($file_path.$source, "r")) !== FALSE) {
						$date_imported = '0000-00-00 00:00:00';
						
						$x=0;
						$current_row = '';			
						$skus_in_file = array();	
						while (($data = fgetcsv($handle,0,$columns_separated_with,$columns_enclosed_with,$columns_escaped_with))) {		
							$current_row = md5(serialize($data));
						
							if (!$skip_first_row || $skip_first_row && $x > 0) {	
								// progress_data is empty
								// progress_data is not empty and matches current_row
								if (!$progress_data || $progress_data && $progress_data == $current_row) {	
									$progress_data = '';
																	
									foreach ($data as $col => $value) {						
										$id_import_columns = $columns[$col]['id_import_columns'];							
										$extra = $columns[$col]['extra'];		
								
										switch ($id_import_columns) {
											// alias
											case 6:		
												$value = makesafetitle(substr($value,0,$alias_length));	
											
												if (!$type) {							
													if (!$stmt_alias_exists->bind_param("ss", $value, $columns[$col]['extra'])) throw new Exception('An error occured while trying to bind params to check sku statement.'."\r\n\r\n".$mysqli->error);			
													
													/* Execute the statement */
													if (!$stmt_alias_exists->execute()) throw new Exception('An error occured while trying to check alias statement.'."\r\n\r\n".$mysqli->error);	
													
													/* store result */
													$stmt_alias_exists->store_result();													
																		
													/* bind result variables */
													$stmt_alias_exists->bind_result($alias_exists);																											
														
													$stmt_alias_exists->fetch();										
					
													// check if alias exists
													if (!empty($value) && $alias_exists) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Alias exists.';
												} else {
													$sku = isset($columns_keys[7]) ? $data[$columns_keys[7][0]]:'';
													
													if (!$stmt_get_product_id_by_sku->bind_param("s", $sku)) throw new Exception('An error occured while trying to bind params to get product id by sku statement.'."\r\n\r\n".$mysqli->error);			
													
													/* Execute the statement */
													if (!$stmt_get_product_id_by_sku->execute()) throw new Exception('An error occured while trying to get product id by sku statement.'."\r\n\r\n".$mysqli->error);	
													
													/* store result */
													$stmt_get_product_id_by_sku->store_result();													
																		
													/* bind result variables */
													$stmt_get_product_id_by_sku->bind_result($id_product);																											
														
													$stmt_get_product_id_by_sku->fetch();		
													
													if ($id_product) {												
														// get product id by alias				
																									
														if (!$stmt_get_product_id_by_alias->bind_param("ss", $value, $extra)) throw new Exception('An error occured while trying to bind params to get product id by alias statement.'."\r\n\r\n".$mysqli->error);			
														
														/* Execute the statement */
														if (!$stmt_get_product_id_by_alias->execute()) throw new Exception('An error occured while trying to get product id by alias statement.'."\r\n\r\n".$mysqli->error);	
														
														/* store result */
														$stmt_get_product_id_by_alias->store_result();													
																			
														/* bind result variables */
														$stmt_get_product_id_by_alias->bind_result($id_product2);																											
															
														$stmt_get_product_id_by_alias->fetch();											
														
														if (!empty($id_product2) && $id_product != $id_product2) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Alias already in use by another product.';
													}													
												}
												break;								
											// sku
											case 7:		
												if (!$type) {							
													$value = substr($value,0,$sku_length);
													
													if (isset($skus_in_file[$value])) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: SKU exists in file. Duplicated?';
													
													if (!$stmt_sku_exists->bind_param("s", $value)) throw new Exception('An error occured while trying to bind params to check sku statement.'."\r\n\r\n".$mysqli->error);			
													
													/* Execute the statement */
													if (!$stmt_sku_exists->execute()) throw new Exception('An error occured while trying to check sku statement.'."\r\n\r\n".$mysqli->error);	
													
													/* store result */
													$stmt_sku_exists->store_result();													
																		
													/* bind result variables */
													$stmt_sku_exists->bind_result($sku_exists);																											
														
													$stmt_sku_exists->fetch();								
																					
													// check if sku exists
													if (!empty($value) && $sku_exists) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: SKU exists.'; 
													else if (!isset($skus_in_file[$value]))	$skus_in_file[$value] = 1;
																							
												}
												break;
											// special_price
											case 12:
												// if we have a special price
												if (!empty($value)) {
													if (isset($columns_keys[11]) && $data[$columns_keys[11][0]]) {
														$price = $data[$columns_keys[11][0]];
														
														if ($value > $price) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Special Price must be lower than '.$price.'.';
													} else if ($sku = (isset($columns_keys[7]) ? $data[$columns_keys[7][0]]:'')) {														
														if (!$stmt_get_product_id_by_sku->bind_param("s", $sku)) throw new Exception('An error occured while trying to bind params to get product id by sku statement.'."\r\n\r\n".$mysqli->error);			
														
														/* Execute the statement */
														if (!$stmt_get_product_id_by_sku->execute()) throw new Exception('An error occured while trying to get product id by sku statement.'."\r\n\r\n".$mysqli->error);	
														
														/* store result */
														$stmt_get_product_id_by_sku->store_result();													
																			
														/* bind result variables */
														$stmt_get_product_id_by_sku->bind_result($id_product);																											
															
														$stmt_get_product_id_by_sku->fetch();		
														
														if ($id_product) {	
															// get current price													
															if (!$stmt_get_product_price_by_id->bind_param("i", $id_product)) throw new Exception('An error occured while trying to bind params to get product price by id statement.'."\r\n\r\n".$mysqli->error);			
															
															/* Execute the statement */
															if (!$stmt_get_product_price_by_id->execute()) throw new Exception('An error occured while trying to get product price by id statement.'."\r\n\r\n".$mysqli->error);	
															
															/* store result */
															$stmt_get_product_price_by_id->store_result();													
																				
															/* bind result variables */
															$stmt_get_product_price_by_id->bind_result($price);																											
																
															$stmt_get_product_price_by_id->fetch();
															
															if ($value > $price) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Special Price must be lower than '.$price.'.';
														}													
													}																														
												}												
												break;
												
											// special price from date
											case 13:
												if (!empty($value)) {
													$date = date_parse($value);
													
													if ($date['error_count']) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Invalid Special Price From Date.';													
												} else if (isset($columns_keys[14]) && isset($data[$columns_keys[14][0]]) && !empty($data[$columns_keys[14][0]]) && empty($value)) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Invalid Special Price From Date.';				
												break;
											// special price to date
											case 14:
												if (!empty($value)) {
													$date = date_parse($value);
													
													if ($date['error_count']) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Invalid Special Price To Date.';
													else if (isset($columns_keys[13]) && isset($data[$columns_keys[13][0]]) && !empty($data[$columns_keys[13][0]])) {
														$date_from = date_parse($data[$columns_keys[13][0]]);
																	
														if (!$date_from['error_count'] && strtotime($value) < strtotime($data[$columns_keys[13][0]])) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Special Price To Date must be bigger than Special Price From Date.';
													}
												} else if (isset($columns_keys[13]) && isset($data[$columns_keys[13][0]]) && !empty($data[$columns_keys[13][0]]) && empty($value)) $errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Invalid Special Price To Date.';				
												break;
											// cover image
											case 15:
											// additional images
											case 16:
												if (!empty($value)) {	
													if ($image->load($value)) {
														$ext = strtolower(trim(pathinfo($value, PATHINFO_EXTENSION)));
														$force_crop = 0;
						
														if (empty($ext) || !in_array($ext, $allowed_ext)) {
															//$errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Image type not allowed.';
														} else {
															// original file renamed
															$original = md5($value.time()).'.'.$ext;	
															$filename = md5($original).'.jpg';				
														
															$width = $image->getWidth();
															$height = $image->getHeight();
															
															if (!$width || !$height) {
																$errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Failed to load image.';
															// if our image size is smaller than our min 800x600					
															/*} else if ($width < $default_cover_width || $height < $default_cover_height) { 
																$errors[] = 'Line: '.$x.', Column: '.$col.'<br />Error: Wrong image resolution.';							*/
															}
															
															// free up memory
															$image->destroy();	
														}
													} else {
														//$errors[] = 'Line: '.$x.', Column: '.$col.'<br />Content: '.$value.'<br />Error: Failed to load image.';
													}
												}
												break;
										} 		
									}
									
									// save validation progress and errors
									save_import_progress(base64_encode(serialize(array('data'=>$data))),$errors,1);
								}
							}
																									
							++$x;
							if(sizeof($errors)>100)break;
						}
						fclose($handle);
						$handle = NULL;
					} else {
						$errors[] = 'Error, unable to read from source file: '.$file_path.$source;	
					}
					
					// save validation progress and errors
					save_import_progress('',$errors,2);	
				}
				
				// if no errors occured / start import
				if (!sizeof($errors)) {
					if (($handle = fopen($file_path.$source, "r")) !== FALSE) {															
						$x=0;
						$current_row = '';				
						while (($data = fgetcsv($handle,0,$columns_separated_with,$columns_enclosed_with,$columns_escaped_with))) {		
							$current_row = md5(serialize($data));
							$id_product='';
							$id_product_variant='';
							
							// crash the script
							//if ($x > 5) exit;							
						
							if (!$skip_first_row || $skip_first_row && $x > 0) {	
								// progress_data is empty
								// progress_data is not empty and matches current_row
								if (!$progress_data || $progress_data && $progress_data == $current_row) {	
									$progress_data = '';
																	
									$sku = isset($columns_keys[7]) ? trim($data[$columns_keys[7][0]]):'';
									$brand = isset($columns_keys[8]) ? trim($data[$columns_keys[8][0]]):'';
									$model = isset($columns_keys[9]) ? trim($data[$columns_keys[9][0]]):'';
									$cost_price = isset($columns_keys[10]) ? $data[$columns_keys[10][0]]:'';							
									$price = isset($columns_keys[11]) ? $data[$columns_keys[11][0]]:'';
									$special_price = isset($columns_keys[12]) ? $data[$columns_keys[12][0]]:'';
									$special_price_from_date = isset($columns_keys[13]) && !empty($data[$columns_keys[13][0]]) ? date('Y-m-d H:i:s',strtotime($data[$columns_keys[13][0]])):'';
									$special_price_to_date = isset($columns_keys[14]) && !empty($data[$columns_keys[14][0]]) ? date('Y-m-d H:i:s',strtotime($data[$columns_keys[14][0]])):'';
									$qty = isset($columns_keys[17]) ? $data[$columns_keys[17][0]]:'';
									$notify_qty = isset($columns_keys[18]) ? $data[$columns_keys[18][0]]:'';
									$out_of_stock = isset($columns_keys[19]) ? $data[$columns_keys[19][0]]:'';
									$out_of_stock_enabled = isset($columns_keys[20]) ? $data[$columns_keys[20][0]]:'';
									$weight = isset($columns_keys[21]) ? $data[$columns_keys[21][0]]:'';
									$enable_local_pickup = isset($columns_keys[22]) ? $data[$columns_keys[22][0]]:'';
									$used = isset($columns_keys[23]) ? $data[$columns_keys[23][0]]:'';
									$featured = isset($columns_keys[24]) ? $data[$columns_keys[24][0]]:'';
									$taxable = isset($columns_keys[25]) ? $data[$columns_keys[25][0]]:'';
									if($set_active){
										$active = 1;
									}else{
										$active = isset($columns_keys[26]) ? $data[$columns_keys[26][0]]:'';
									}
									
									$length = isset($columns_keys[29]) ? $data[$columns_keys[29][0]]:0;
									$width = isset($columns_keys[30]) ? $data[$columns_keys[30][0]]:0;
									$height = isset($columns_keys[31]) ? $data[$columns_keys[31][0]]:0;
									
									$product_description = array();
									
									if (isset($columns_keys[1]) || isset($columns_keys[2]) || isset($columns_keys[3]) || isset($columns_keys[4]) || isset($columns_keys[5]) || isset($columns_keys[6])) {																
										// name
										if (isset($columns_keys[1])) {
											foreach ($columns_keys[1] as $col) {
												$language_code = $columns[$col]['extra'];
												$value = $data[$col];
												$product_description[$language_code]['name'] = trim($value);	
											}
										}
										
										// short_desc
										if (isset($columns_keys[2])) {
											foreach ($columns_keys[2] as $col) {
												$language_code = $columns[$col]['extra'];
												$value = $data[$col];
												$product_description[$language_code]['short_desc'] = trim($value);	
											}
										}	
										
										// description
										if (isset($columns_keys[3])) {
											foreach ($columns_keys[3] as $col) {
												$language_code = $columns[$col]['extra'];
												$value = $data[$col];
												$product_description[$language_code]['description'] = trim($value);	
											}
										}			
										
										// meta_description
										if (isset($columns_keys[4])) {
											foreach ($columns_keys[4] as $col) {
												$language_code = $columns[$col]['extra'];
												$value = $data[$col];
												$product_description[$language_code]['meta_description'] = trim($value);	
											}
										}		
		
										// meta_keywords
										if (isset($columns_keys[5])) {
											foreach ($columns_keys[5] as $col) {
												$language_code = $columns[$col]['extra'];
												$value = $data[$col];
												$product_description[$language_code]['meta_keywords'] = trim($value);	
											}
										}		
										
										// alias
										if (isset($columns_keys[6])) {
											foreach ($columns_keys[6] as $col) {
												$language_code = $columns[$col]['extra'];
												$value = $data[$col];
												$product_description[$language_code]['alias'] = makesafetitle(trim($value));	
											}
										}																																						
									}
									
									// Category
									$category_description = array();
									
									if (isset($columns_keys[27])) {																
										// Infos
										foreach ($columns_keys[27] as $col) {
											$language_code = $columns[$col]['extra'];
											$value = trim($data[$col]);
											$category_description[$language_code]['name'] = $value;
											$category_description[$language_code]['meta_description'] = $value;
											$category_description[$language_code]['meta_keywords'] = $value;
											$category_description[$language_code]['alias'] = makesafetitle($value);
										}																																	
									}
									
									// Sub Category
									$sub_category_description = array();
									
									if (isset($columns_keys[28])) {																
										// Infos
										foreach ($columns_keys[28] as $col) {
											$language_code = $columns[$col]['extra'];
											$value = trim($data[$col]);
											$sub_category_description[$language_code]['name'] = $value;
											$sub_category_description[$language_code]['meta_description'] = $value;
											$sub_category_description[$language_code]['meta_keywords'] = $value;
											$sub_category_description[$language_code]['alias'] = makesafetitle($value);
										}																																	
									}
									
									
									// images
									$product_images = array();
									
									// cover image
									if (isset($columns_keys[15])) {
										foreach ($columns_keys[15] as $col) {
											$value = $data[$col];
											$image = new SimpleImage();	
											if (!$image->load($value)) {
												$value = '';
											}
											if (!empty($value)) {
												$product_images[] = array(
													'file' => $value,
													'cover' => 1,
												);	
											}
										}
									}
									
									// additional images
									if (isset($columns_keys[16])) {
										foreach ($columns_keys[16] as $col) {
											$value = $data[$col];
											$image = new SimpleImage();	
											if (!$image->load($value)) {
												$value = '';
											}
											if (!empty($value)) {
												$product_images[] = array(
													'file' => $value,
													'cover' => 0,
												);	
											}
										}
									}				
									
									if (!$id_product = $progress['id_product']) {		
										// add / update or update only
										if ($type) {
											if (!$stmt_get_product_id_by_sku->bind_param("s", $sku)) throw new Exception('An error occured while trying to bind params to get product id by sku statement.'."\r\n\r\n".$mysqli->error);			
											
											/* Execute the statement */
											if (!$stmt_get_product_id_by_sku->execute()) throw new Exception('An error occured while trying to get product id by sku statement.'."\r\n\r\n".$mysqli->error);	
											
											/* store result */
											$stmt_get_product_id_by_sku->store_result();													
																
											/* bind result variables */
											$stmt_get_product_id_by_sku->bind_result($id_product, $current_qty);																											
												
											$stmt_get_product_id_by_sku->fetch();	
											
											// if no existing product, check variants
											if (!$id_product) {
												if (!$stmt_get_product_variant_id_by_sku->bind_param("s", $sku)) throw new Exception('An error occured while trying to bind params to get product id by sku statement.'."\r\n\r\n".$mysqli->error);			
												
												/* Execute the statement */
												if (!$stmt_get_product_variant_id_by_sku->execute()) throw new Exception('An error occured while trying to get product id by sku statement.'."\r\n\r\n".$mysqli->error);	
												
												/* store result */
												$stmt_get_product_variant_id_by_sku->store_result();													
																	
												/* bind result variables */
												$stmt_get_product_variant_id_by_sku->bind_result($id_product_variant, $id_product, $current_qty, $sell_price);																											
													
												$stmt_get_product_variant_id_by_sku->fetch();	
											}
										}
										
										// add or add / update
										if ($type != 2 && !$id_product && !$id_product_variant) {																					
											// add product
											$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
											$method->invokeArgs($stmt_add_product, $stmt_add_product_params);  
																						
											/* Execute the statement */
											if (!$stmt_add_product->execute()) throw new Exception('An error occured while trying to add product statement.'."\r\n\r\n".$mysqli->error);								
											
											// get insert id
											$id_product = $mysqli->insert_id;
										// update
										} else if ($type && $id_product) {
											// update product
											if (!$id_product_variant && $stmt_upd_product) {
												// if we subtract qty
												if ($subtract_qty) {
													if ($qty > 0) {
														$lowest_qty = $current_qty < $qty ? $current_qty:$qty;
														$diff = abs($current_qty - $qty);	
														$lowest_qty -= $diff;
														$qty = $lowest_qty;
														
														if ($qty < 0) $qty = 0;
													} else {
														$qty = 0;
													}	
												}
												
												$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
												$method->invokeArgs($stmt_upd_product, $stmt_upd_product_params);  
																							
												/* Execute the statement */
												if (!$stmt_upd_product->execute()) throw new Exception('An error occured while trying to update product statement.'."\r\n\r\n".$mysqli->error);			
											// update product variant
											} else if ($id_product_variant && $stmt_upd_product_variant) {
												// if we subtract qty
												if ($subtract_qty) {
													if ($qty > 0) {
														$lowest_qty = $current_qty < $qty ? $current_qty:$qty;
														$diff = abs($current_qty - $qty);	
														$lowest_qty -= $diff;
														$qty = $lowest_qty;
														
														if ($qty < 0) $qty = 0;
													} else {
														$qty = 0;
													}	
												}				
																															
												// cost price and variant price is added to product base price
												// so when updating these price, they must not include product base price
												$price = $price > 0 ? $price-$sell_price:0;
																							
												$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
												$method->invokeArgs($stmt_upd_product_variant, $stmt_upd_product_variant_params);  
																							
												/* Execute the statement */
												if (!$stmt_upd_product_variant->execute()) throw new Exception('An error occured while trying to update product variant statement.'."\r\n\r\n".$mysqli->error);
											}
										}
										
										$progress = array('data'=>$data,'id_product'=>$id_product,'id_product_variant'=>$id_product_variant);
										
										save_import_progress(base64_encode(serialize($progress)),$errors,2);								
									}									
									
									if ($id_product && !$id_product_variant) {														
										// add description
										if (sizeof($product_description)) {
											foreach ($product_description as $language_code => $row_description) {
												// proceed if doesn't exist
												//if (!isset($progress['product_description'][$language_code])) {		
													$name = !empty($row_description['name']) ? $row_description['name']:'';
													$short_desc = !empty($row_description['short_desc']) ? $row_description['short_desc']:'';
													$description = !empty($row_description['description']) ? $row_description['description']:'';
													$meta_description = !empty($row_description['meta_description']) ? $row_description['meta_description']:'';
													$meta_keywords = !empty($row_description['meta_keywords']) ? $row_description['meta_keywords']:'';

													$alias = !empty($row_description['alias']) ? makesafetitle($row_description['alias']):'';
													if(!$alias and $name and $type != 2){
														$new_alias = makesafetitle($name);
														
														
														if (!$stmt_alias_exists->bind_param("ss", $new_alias, $language_code)) throw new Exception('An error occured while trying to bind params to check alias statement.'."\r\n\r\n".$mysqli->error);			
													
														/* Execute the statement */
														if (!$stmt_alias_exists->execute()) throw new Exception('An error occured while trying to check alias statement.'."\r\n\r\n".$mysqli->error);	
														
														/* store result */
														$stmt_alias_exists->store_result();		
														
														/* bind result variables */
														$stmt_alias_exists->bind_result($alias_exists);																											
															
														$stmt_alias_exists->fetch();		
														
														if (!$alias_exists) {
															$alias = $new_alias;
														}else{
															$alias = $new_alias."_".($alias_exists+1);
														}
														
														
														/*if ($result_alias = $mysqli->query('SELECT 
															id_product
															FROM
															product_description
															WHERE
															alias = "'.$mysqli->escape_string($new_alias).'"
															AND
															language_code = "'.$mysqli->escape_string($language_code).'"
															LIMIT 1')) {
															if (!$result_alias->num_rows) {
																$alias = $new_alias;
															}else{
																$alias = $new_alias."_".($result_alias->num_rows+1);
															}
														}*/
													}

													if (!$stmt_check_product_description->bind_param("is", $id_product, $language_code)) throw new Exception('An error occured while trying to to check if product description exists statement.'."\r\n\r\n".$mysqli->error);	
																										
													/* Execute the statement */
													if (!$stmt_check_product_description->execute()) throw new Exception('An error occured while trying to check if product description exists.'."\r\n\r\n".$mysqli->error);	
													
													/* store result */
													$stmt_check_product_description->store_result();													
																		
													/* bind result variables */
													$stmt_check_product_description->bind_result($pd_exists);																											
														
													$stmt_check_product_description->fetch();	
													
													if (!$pd_exists) {																															
														$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
														$method->invokeArgs($stmt_add_product_description, $stmt_add_product_description_params);  
														
														/* Execute the statement */
														if (!$stmt_add_product_description->execute()) throw new Exception('An error occured while trying to add product description statement.'."\r\n\r\n".$mysqli->error);	
													} else {
														$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
														$method->invokeArgs($stmt_upd_product_description, $stmt_upd_product_description_params);  
														
														/* Execute the statement */
														if (!$stmt_upd_product_description->execute()) throw new Exception('An error occured while trying to update product description statement.'."\r\n\r\n".$mysqli->error);	
													}
								
											//		$id_product_description = $mysqli->insert_id;
													
											//		$progress['product_description'][$language_code] = $id_product_description;
													
													save_import_progress(base64_encode(serialize($progress)),$errors,2);	
													
													//if ($x > 5 && $language_code == 'en') exit;																													
												//} 
											}
										}
										
										
										// add category
										if (sizeof($category_description)) {
											$current_categorie = 0;
											$id_category = 0;
											$id_category_parent = 0;
											foreach ($category_description as $language_code => $row_description) {
												if($row_description['name']){
													$cd_exists = 0;
													$name = $row_description['name'];	
													$meta_description = $row_description['meta_description'];
													$meta_keywords = $row_description['meta_keywords'];
													$alias = makesafetitle($row_description['alias']);
	
													if (!$stmt_check_alias_category->bind_param("ss", $alias, $language_code)) throw new Exception('An error occured while trying to bind params to check alias category statement.'."\r\n\r\n".$mysqli->error);			
														
													/* Execute the statement */
													if (!$stmt_check_alias_category->execute()) throw new Exception('An error occured while trying to check alias category statement.'."\r\n\r\n".$mysqli->error);	
													
													/* store result */
													$stmt_check_alias_category->store_result();			
													
													/* bind result variables */
													$stmt_check_alias_category->bind_result($alias_exists);																											
														
													$stmt_check_alias_category->fetch();		
													
													if ($alias_exists) {
														$alias = $alias."_".($alias_exists+1);
													}
													
													
												
													if (!$stmt_check_category_description->bind_param("ssi", $name, $language_code, $id_category_parent)) throw new Exception('An error occured while trying to to check if product description exists statement.'."\r\n\r\n".$mysqli->error);	
																										
													/* Execute the statement */
													if (!$stmt_check_category_description->execute()) throw new Exception('An error occured while trying to check if category description exists.'."\r\n\r\n".$mysqli->error);	
													
													/* store result */
													$stmt_check_category_description->store_result();													
																		
													/* bind result variables */
													$stmt_check_category_description->bind_result($cd_exists);																											
														
													$stmt_check_category_description->fetch();	
													
													if (!$cd_exists) {																															
	
														if(!$current_categorie){
															$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
															$method->invokeArgs($stmt_add_category, $stmt_add_category_params);
															/* Execute the statement */
															if (!$stmt_add_category->execute()) throw new Exception('An error occured while trying to add product description statement.'."\r\n\r\n".$mysqli->error);  
															
														
															// get insert id_category
															$id_category = $mysqli->insert_id;
															$current_categorie = 1;
															
															
															// Insert into product category
															if (!$stmt_add_product_category->bind_param("ii", $id_product, $id_category)) throw new Exception('An error occured while trying to bind params to add product category statement.'."\r\n\r\n".$mysqli->error);
															
															/* Execute the statement */
															if (!$stmt_add_product_category->execute()) throw new Exception('An error occured while trying to add product category statement.'."\r\n\r\n".$mysqli->error);
															
														}
	
														$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
														$method->invokeArgs($stmt_add_category_description, $stmt_add_category_description_params);  
															
														/* Execute the statement */
														if (!$stmt_add_category_description->execute()) throw new Exception('An error occured while trying to add product description statement.'."\r\n\r\n".$mysqli->error);
															
													}else{
														
														if(!$current_categorie){
															// get insert id_category
															$id_category = $cd_exists;
															$current_categorie = 1;
															
															// Verify if product category exist
															if (!$stmt_check_product_category->bind_param("ii", $id_product, $id_category)) throw new Exception('An error occured while trying to bind params to check alias category statement.'."\r\n\r\n".$mysqli->error);			
														
															/* Execute the statement */
															if (!$stmt_check_product_category->execute()) throw new Exception('An error occured while trying to check alias category statement.'."\r\n\r\n".$mysqli->error);	
															
															/* store result */
															$stmt_check_product_category->store_result();			
															
															if (!$stmt_check_product_category->num_rows) {
																
																// Insert into product category
																if (!$stmt_add_product_category->bind_param("ii", $id_product, $id_category)) throw new Exception('An error occured while trying to bind params to add product category statement.'."\r\n\r\n".$mysqli->error);
																
																/* Execute the statement */
																if (!$stmt_add_product_category->execute()) throw new Exception('An error occured while trying to add product category statement.'."\r\n\r\n".$mysqli->error);
																
																
															}	
														}
													}
												}

												save_import_progress(base64_encode(serialize($progress)),$errors,2);	
	
											}
											
											$id_category_parent = $id_category;
											$id_category = 0;
											

											// add sub category
											if (sizeof($sub_category_description)) {
												$current_categorie = 0;
												

												foreach ($sub_category_description as $language_code => $row_description) {
													if($row_description['name'] and $id_category_parent){
														$scd_exists = 0;
														$name = $row_description['name'];	
														$meta_description = $row_description['meta_description'];
														$meta_keywords = $row_description['meta_keywords'];
														$alias = makesafetitle($row_description['alias']);
														
														if (!$stmt_check_alias_category->bind_param("ss", $alias, $language_code)) throw new Exception('An error occured while trying to bind params to check alias category statement.'."\r\n\r\n".$mysqli->error);			
															
														/* Execute the statement */
														if (!$stmt_check_alias_category->execute()) throw new Exception('An error occured while trying to check alias category statement.'."\r\n\r\n".$mysqli->error);	
														
														/* store result */
														$stmt_check_alias_category->store_result();			
														
														/* bind result variables */
														$stmt_check_alias_category->bind_result($alias_exists);																											
															
														$stmt_check_alias_category->fetch();		
														
														if ($alias_exists) {
															$alias = $alias."_".($alias_exists+1);
														}											
		
														if (!$stmt_check_category_description->bind_param("ssi", $name, $language_code, $id_category_parent)) throw new Exception('An error occured while trying to to check if product description exists statement.'."\r\n\r\n".$mysqli->error);	
																											
														/* Execute the statement */
														if (!$stmt_check_category_description->execute()) throw new Exception('An error occured while trying to check if category description exists.'."\r\n\r\n".$mysqli->error);	
														
														/* store result */
														$stmt_check_category_description->store_result();													
																			
														/* bind result variables */
														$stmt_check_category_description->bind_result($scd_exists);																											
															
														$stmt_check_category_description->fetch();	
														
														if (!$scd_exists) {																															
														
															if(!$current_categorie){
																$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
																$method->invokeArgs($stmt_add_category, $stmt_add_category_params);
																/* Execute the statement */
																if (!$stmt_add_category->execute()) throw new Exception('An error occured while trying to add product description statement.'."\r\n\r\n".$mysqli->error);  
																
																// get insert id_category
																$id_category = $mysqli->insert_id;
																$current_categorie = 1;
																
																
																// Insert into product category
																if (!$stmt_add_product_category->bind_param("ii", $id_product, $id_category)) throw new Exception('An error occured while trying to bind params to add product category statement.'."\r\n\r\n".$mysqli->error);
																
																/* Execute the statement */
																if (!$stmt_add_product_category->execute()) throw new Exception('An error occured while trying to add product category statement.'."\r\n\r\n".$mysqli->error);
															}
															
															
															
															
															$method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
															$method->invokeArgs($stmt_add_category_description, $stmt_add_category_description_params);  
																
															/* Execute the statement */
															if (!$stmt_add_category_description->execute()) throw new Exception('An error occured while trying to add product description statement.'."\r\n\r\n".$mysqli->error);
														}else{
															if(!$current_categorie){
																
																// get insert id_category
																$id_category = $scd_exists;
																$current_categorie = 1;
																
																// Verify if product category exist
																if (!$stmt_check_product_category->bind_param("ii", $id_product, $id_category)) throw new Exception('An error occured while trying to bind params to check alias category statement.'."\r\n\r\n".$mysqli->error);			
															
																/* Execute the statement */
																if (!$stmt_check_product_category->execute()) throw new Exception('An error occured while trying to check alias category statement.'."\r\n\r\n".$mysqli->error);	
																
																/* store result */
																$stmt_check_product_category->store_result();			
																
																if (!$stmt_check_product_category->num_rows) {
																	
																	// Insert into product category
																	if (!$stmt_add_product_category->bind_param("ii", $id_product, $id_category)) throw new Exception('An error occured while trying to bind params to add product category statement.'."\r\n\r\n".$mysqli->error);
																	
																	/* Execute the statement */
																	if (!$stmt_add_product_category->execute()) throw new Exception('An error occured while trying to add product category statement.'."\r\n\r\n".$mysqli->error);

																}	
															}	
														}
													}

													save_import_progress(base64_encode(serialize($progress)),$errors,2);	
		
												}
												
											}
	
										}
																		
										
										// add images
										if (sizeof($product_images)) {
											$i=0;
											foreach ($product_images as $key_row_image => $row_image) {
												// proceed if doesn't exist
												if (!isset($progress['product_images'][$key_row_image])) {												
													$cover = $row_image['cover'];
													
													// set cover image
													if ($cover) {
														if (!$stmt_remove_product_image_cover->bind_param("i", $id_product)) throw new Exception('An error occured while trying to bind params to remove product image cover statement.'."\r\n\r\n".$mysqli->error);
														
														/* Execute the statement */
														if (!$stmt_remove_product_image_cover->execute()) throw new Exception('An error occured while trying to remove product image cover statement.'."\r\n\r\n".$mysqli->error);	
													}
													
													if (!$stmt_add_product_image->bind_param("iiiis", $id_product, $cover, $current_id_user, 
													$current_id_user, $current_datetime)) throw new Exception('An error occured while trying to bind params to add product image statement.'."\r\n\r\n".$mysqli->error);
													
													/* Execute the statement */
													if (!$stmt_add_product_image->execute()) throw new Exception('An error occured while trying to add product image statement.'."\r\n\r\n".$mysqli->error);	
													
													// get insert id
													if ($id_product_image = $mysqli->insert_id) {
														if (!$stmt_get_product_image_sort_order->bind_param("i", $id_product)) throw new Exception('An error occured while trying to bind params to get product images sort order statement.'."\r\n\r\n".$mysqli->error);
														
														/* Execute the statement */
														if (!$stmt_get_product_image_sort_order->execute()) throw new Exception('An error occured while trying to get product images sort order.'."\r\n\r\n".$mysqli->error);	
														
														/* store result */
														$stmt_get_product_image_sort_order->store_result();																														
															
														/* bind result variables */
														$stmt_get_product_image_sort_order->bind_result($sort_order);
														
														$stmt_get_product_image_sort_order->fetch();
														
														$sort_order = $sort_order ? $sort_order:1;
														
														// save image
														
														// original file renamed
														$original = md5($row_image['file'].time()).'.'.$ext;	
														$filename = md5($original).'.jpg';				
													
														if ($image->load($row_image['file'])) {										
															$width = $image->getWidth();
															$height = $image->getHeight();
																											
															// vs
															switch ($config_site['images_orientation']) {
																case 'portrait':
																	// ratio is not correct, force crop
																	if (($width/$height) != 0.75) {
																		$force_crop = 1;
																	}	
																	break;
																case 'landscape':
																	// ratio is not correct, force crop
																	if (($height/$width) != 0.75) {
																		$force_crop = 1;
																	}					
																	break;
															}											
														
															// save original
															if (!$image->save($targetPath.'original/'.$original)) {
															}												
																													
															// save image ZOOM
															if ($width > $default_zoom_width && !$image->resizeToWidth($default_zoom_width)) {
															} else if (!$image->save($targetPath.'zoom/'.$filename)) {
															}
															
															// save image COVER
															if ($width > $default_cover_width && !$image->resizeToWidth($default_cover_width)) {
															} else if (!$image->save($targetPath.'cover/'.$filename)) {
															}
															
															// save image LISTING
															if (($width > $height || $width == $height) && !$image->resizeToWidth($default_listing_width)) {
															} else if ($height > $width && !$image->resizeToHeight($default_listing_height)) {															

															} else if (!$image->save($targetPath.'listing/'.$filename)) {
															}
											
															// save image SUGGEST
															if (($width > $height || $width == $height) && !$image->resizeToWidth($default_suggest_width)) {
															} else if ($height > $width && !$image->resizeToHeight($default_suggest_height)) {																					
															
															} else if (!$image->save($targetPath.'suggest/'.$filename)) {
															}
															
															// save image THUMB
															if (($width > $height || $width == $height) && !$image->resizeToWidth($default_thumb_width)) {
															} else if ($height > $width && !$image->resizeToHeight($default_thumb_height)) {																										
															
															} else if (!$image->save($targetPath.'thumb/'.$filename)) {
															}
														}
														
														// free up memory
														$image->destroy();											
																									
														if (!$stmt_upd_product_image->bind_param("ssiii", $original, $filename, 
														$force_crop, $sort_order, $id_product_image)) throw new Exception('An error occured while trying to bind params to add product image statement.'."\r\n\r\n".$mysqli->error);
														
														/* Execute the statement */
														if (!$stmt_upd_product_image->execute()) throw new Exception('An error occured while trying to add product image statement.'."\r\n\r\n".$mysqli->error);		
														
														$progress['product_images'][$key_row_image] = $id_product_image;
														
														save_import_progress(base64_encode(serialize($progress)),$errors,2);	
														
														++$i;
														
														//if ($x > 0 && $i > 2) exit;															
													}
												}
											}
										}							
									}	
									
									// empty
									$progress = array();																
								}
							}
																									
							++$x;
						}
						
						fclose($handle);
						$handle = NULL;
						
						// save validation progress and errors				
						$date_imported = $current_datetime;		
						
						save_import_progress('',$errors,3);
					} else {
						$errors[] = 'Error, unable to read from source file: '.$file_path.$source;	
					}
				}
				
				$errors = NULL;
				$image = NULL;
					
				/* Close statements */
				if (is_resource($stmt_sku_exists)) $stmt_sku_exists->close();
				if (is_resource($stmt_alias_exists)) $stmt_alias_exists->close();	
				if (is_resource($stmt_upd_progress)) $stmt_upd_progress->close();
				if (is_resource($stmt_add_product)) $stmt_add_product->close();
				if (is_resource($stmt_add_product_description)) $stmt_add_product_description->close();
				
				if (is_resource($stmt_get_product_price_by_id)) $stmt_get_product_price_by_id->close();

				if (is_resource($stmt_add_product_image)) $stmt_add_product_image->close();
				if (is_resource($stmt_upd_product_image)) $stmt_upd_product_image->close();
				if (is_resource($stmt_remove_product_image_cover)) $stmt_remove_product_image_cover->close();
				if (is_resource($stmt_get_product_image_sort_order)) $stmt_get_product_image_sort_order->close();

				if (is_resource($stmt_get_product_id_by_sku)) $stmt_get_product_id_by_sku->close();
				if (is_resource($stmt_get_product_id_by_alias)) $stmt_get_product_id_by_alias->close();
				
				if (is_resource($stmt_check_alias_category)) $stmt_check_alias_category->close();
				if (is_resource($stmt_check_category_description)) $stmt_check_category_description->close();
				if (is_resource($stmt_add_category)) $stmt_add_category->close();
				if (is_resource($stmt_add_product_category)) $stmt_add_product_category->close();
				if (is_resource($stmt_add_category_description)) $stmt_add_category_description->close();
				
				
				
				break;
			case 2:
				break;
		}
	}
}
$result->free();		

function save_import_progress($progress_str='',$errors=array(),$status=0)
{
	global $id_product, $errors, $date_imported, $stmt_upd_progress, $stmt_upd_progress, $id;
	
	// save validation progress and errors
	$error_str = sizeof($errors) ? base64_encode(serialize($errors)):'';
	
	if (!$stmt_upd_progress->bind_param("ssisi", $progress_str, $error_str, $status, $date_imported, $id)) throw new Exception('An error occured while trying to bind params to update progress statement.'."\r\n\r\n".$mysqli->error);
	
	/* Execute the statement */
	if (!$stmt_upd_progress->execute()) throw new Exception('An error occured while trying to update progress statement.'."\r\n\r\n".$mysqli->error);		
}
// test