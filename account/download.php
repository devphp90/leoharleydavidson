<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

if ($id = (int)$_GET['id']) {
	if ($result_downloadable_files = $mysqli->query('SELECT
	orders_item_product_downloadable_files.id,
	orders_item_product_downloadable_files.current_no_downloads,
	orders_item_product_downloadable_files.id_product_downloadable_files,
	DATE_ADD(IF(orders.id IS NOT NULL,orders.date_order,o.date_order),INTERVAL orders_item_product_downloadable_files.no_days_expire DAY) AS date_expire,
	product_downloadable_files.no_days_expire,
	product_downloadable_files.no_downloads,
	product_downloadable_files.filename,
	product_downloadable_files.source,
	product_downloadable_files.type
	FROM 
	orders_item_product_downloadable_files 
	LEFT JOIN
	(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
	ON
	(orders_item_product_downloadable_files.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
	
	LEFT JOIN
	(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
	ON
	(orders_item_product_downloadable_files.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
	
	INNER JOIN 
	product_downloadable_files
	ON
	(orders_item_product_downloadable_files.id_product_downloadable_files = product_downloadable_files.id)
	
	WHERE
	orders_item_product_downloadable_files.id = "'.$id.'"
	AND
	(
		(orders.id IS NOT NULL AND orders.status IN (1,7) AND orders.id_customer = "'.(int)$_SESSION['customer']['id'].'")
		OR
		(o.id IS NOT NULL AND o.status IN (1,7) AND o.id_customer = "'.(int)$_SESSION['customer']['id'].'")
	)			
	LIMIT 1')) {
		if (!$result_downloadable_files->num_rows) {
			//echo json_encode(array('errors' => language('account/order', 'ERROR_DOWNLOAD_EXPIRED')));
			echo language('account/order', 'ERROR_DOWNLOAD_EXPIRED');
			exit;	
		}
		
		$row_downloadable_file = $result_downloadable_files->fetch_assoc(); 	
		
		if ($row_downloadable_file['no_downloads'] > 0 && $row_downloadable_file['current_no_downloads'] >= $row_downloadable_file['no_downloads']) {
			echo language('account/order', 'ERROR_NO_DOWNLOADS_REACHED');
			exit;	
		}
				
		if ($row['no_days_expire'] > 0 && strtotime($row['date_expire']) <= time()) {
			//echo json_encode(array('errors' => language('account/order', 'ERROR_DOWNLOAD_EXPIRED')));
			echo language('account/order', 'ERROR_DOWNLOAD_EXPIRED');
			exit;				
		}		
		
		$targetPath = realpath(dirname(__FILE__).'/../admin/protected/downloadable_files').'/';		
		$filename = $row_downloadable_file['filename'];
		$source = $row_downloadable_file['source'];	
		
		// check extension
		$extension = pathinfo($source,PATHINFO_EXTENSION);
		
		// if zip
		if ($extension == 'zip') {
			// check if scorm course
			$za = new ZipArchive();
			
			$za->open($targetPath.$source);			
			
			// look for manifest file
			if ($fp = $za->getStream('imsmanifest.xml')) {								
				// read content of manifest into variable
				$contents = '';
				while (!feof($fp)) $contents .= fread($fp, 2);
			
				fclose($fp);	
				
				// load xml
				if ($manifest = new SimpleXMLElement($contents)) {
					if ($manifest->metadata[0]->schema[0] == 'ADL SCORM' && $manifest->metadata[0]->schemaversion[0] == '1.2' || $manifest->organizations[0]->organization[0]->metadata[0]->schema[0] == 'ADL SCORM' && $manifest->organizations[0]->organization[0]->metadata[0]->schemaversion[0] == '1.2') {
						header('Location: scorm/load.php?id='.$row_downloadable_file['id']);
						
						$skip_download = 1;
					}
				}
			}
		}
		
		if (!$skip_download) {		
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
			header("Pragma: public");  
			header("Content-Type: application/octet-stream; charset=utf-8");  
			
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			if ($filesize = @filesize($targetPath.$source)) header("Content-Length: ".$filesize);
					
			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'wb');
			
			if ($handle = @fopen($targetPath.$source, "rb")) {					
				while (($buffer = fgets($handle)) !== false) {
					fwrite($output,$buffer);
				}
				fclose($handle);
			}
			
			fclose($output);
		}
		
		$mysqli->query('UPDATE orders_item_product_downloadable_files SET current_no_downloads = current_no_downloads+1 WHERE id = "'.$mysqli->escape_string($id).'" LIMIT 1');				
		exit;		
	}
}

header("HTTP/1.0 404 Not Found");
header('Location: /404?error=invalid_download');
exit;	
?>