<?php
class ExportFileForm extends CFormModel
{
	// database fields
	public $id_export_tpl=0;
	public $type=0;
	public $columns_separated_with=',';
	public $columns_enclosed_with='"';
	public $filters=array(
		'active' => -1,
		'display_in_catalog' => -1,
		'featured' => -1,
		'used' => -1,
		'downloadable' => -1,				
	);

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	 
	
	public function rules()
	{
		return array(	
		);
	}	  

	public function validate()
	{		
		
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$current_datetime = date('Y-m-d H:i:s');
		$app = Yii::app();
		$current_id_user = (int)$app->user->getId();
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/export_files/';
		$image_path = 'http://'.$_SERVER['HTTP_HOST'].$app->params['product_images_base_url'].'original/';
		
		if (!$p = Tbl_ExportTpl::model()->findByPk($this->id_export_tpl)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));					

		// get template columns				
		$sql='SELECT
		export_tpl_columns.id_export_columns,
		export_tpl_columns.extra
		FROM 
		export_tpl_columns
		
		WHERE
		export_tpl_columns.id_export_tpl = :id_export_tpl
		ORDER BY
		export_tpl_columns.sort_order ASC';
		$command_columns = $connection->createCommand($sql);				
		$i=0;
		foreach ($command_columns->queryAll(true,array(':id_export_tpl'=>$this->id_export_tpl)) as $row_column) {
			$columns[$i] = array(
				'id_export_columns' => $row_column['id_export_columns'],
				'extra' => $row_column['extra'],
			);
			
			++$i;
		}					
		
		
		switch ($this->type) {
			// products
			case 0:
				$active = $this->filters['active'];
				$display_in_catalog = $this->filters['display_in_catalog'];
				$featured = $this->filters['featured'];
				$used = $this->filters['used'];				
				$downloadable = $this->filters['downloadable'];
				$sku = $this->filters['sku'];
				$name = $this->filters['name'];
				$brand = $this->filters['brand'];
				$model = $this->filters['model'];
				
				$params = array(':language_code'=>Yii::app()->language);				
				if ($active == 1 || $active == 0) $params[':active'] = $active;
				if ($display_in_catalog == 1 || $display_in_catalog == 0) $params['display_in_catalog'] = $display_in_catalog;
				if ($featured == 1 || $active == 0) $params[':featured'] = $featured;
				if ($used == 1 || $used == 0) $params[':used'] = $used;
				if ($downloadable == 1 || $downloadable == 0) $params[':downloadable'] = $downloadable;
				if (!empty($sku)) $params[':sku'] = $sku;
				if (!empty($name)) $params[':name'] = $name;
				if (!empty($brand)) $params[':brand'] = $brand;
				if (!empty($model)) $params[':model'] = $model;
					
				// products list			
				$sql = 'SELECT
				*
				FROM
				((SELECT 
				product.id, 
				0 AS id_product_variant,
				"" AS variant_code,
				product.sku,
				product.featured,
				product.used,
				product.cost_price,
				product.price,
				product.special_price,
				product.special_price_from_date,
				product.special_price_to_date,
				product.qty,
				0 AS sort_order,
				product.notify_qty,
				product.out_of_stock,
				product.out_of_stock_enabled,
				product.weight,
				product.enable_local_pickup,
				product.taxable,
                product.brand,
                product.model,
				product.active,
				product.length,
				product.width,
				product.height
				FROM
				product
				
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code) 
			
				WHERE
				product.has_variants = 0 '
				.($active == 1 || $active == 0 ? ' AND product.active = :active ':'')
				.($display_in_catalog == 1 || $display_in_catalog == 0 ? ' AND product.display_in_catalog = :display_in_catalog ':'')
				.($featured == 1 || $featured == 0 ? ' AND product.featured = :featured ':'')
				.($used == 1 || $used == 0 ? ' AND product.used = :used ':'')
				.($downloadable == 1 || $downloadable == 0 ? ' AND product.downloadable = :downloadable ':'')
				.(!empty($sku) ? ' AND product.sku LIKE CONCAT("%",:sku,"%") ':'')
				.(!empty($name) ? ' AND product_description.name LIKE CONCAT("%",:name,"%") ':'')				
				.(!empty($brand) ? ' AND product.brand LIKE CONCAT("%",:brand,"%") ':'')
				.(!empty($model) ? ' AND product.model LIKE CONCAT("%",:model,"%") ':'').')
				
				UNION
				
				(SELECT 
				product.id, 
				product_variant.id AS id_product_variant,
				product_variant.variant_code,
				product_variant.sku,
				product.featured,
				product.used,
				(product.cost_price+product_variant.cost_price) AS cost_price,
				calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
				calc_sell_price(product.special_price,product_variant.price_type,product_variant.price,0,0,0,0) AS special_price,
				product.special_price_from_date,
				product.special_price_to_date,
				product_variant.qty,
				product_variant.sort_order,
				product.notify_qty,
				product.out_of_stock,
				product.out_of_stock_enabled,
				product_variant.weight,
				product.enable_local_pickup,
				product.taxable,
                product.brand,
                product.model,
				product.active,
				product_variant.length,
				product_variant.width,
				product_variant.height				
				FROM
				product
				
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code) 
				
				INNER JOIN
				(product_variant
				CROSS JOIN 
				product_variant_description)						
				ON
				(product.id = product_variant.id_product
				AND product_variant.id = product_variant_description.id_product_variant
				AND product_variant_description.language_code = product_description.language_code)
								
				
				WHERE
				product.has_variants = 1 '
				.($active == 1 || $active == 0 ? ' AND product.active = :active AND product_variant.active = :active ':'')
				.($display_in_catalog == 1 || $display_in_catalog == 0 ? ' AND product.display_in_catalog = :display_in_catalog ':'')
				.($featured == 1 || $featured == 0 ? ' AND product.featured = :featured ':'')
				.($used == 1 || $used == 0 ? ' AND product.used = :used ':'')
				.($downloadable == 1 || $downloadable == 0 ? ' AND product.downloadable = :downloadable ':'')
				.(!empty($sku) ? ' AND product_variant.sku LIKE CONCAT("%",:sku,"%") ':'')
				.(!empty($name) ? ' AND (product_description.name LIKE CONCAT("%",:name,"%") OR product_variant_description.name LIKE CONCAT("%",:name,"%")) ':'')				
				.(!empty($brand) ? ' AND product.brand LIKE CONCAT("%",:brand,"%") ':'')
				.(!empty($model) ? ' AND product.model LIKE CONCAT("%",:model,"%") ':'').')) AS product
				
				ORDER BY 
				id,
				sort_order';
				$command=$connection->createCommand($sql);			
				
				// product description
				$sql = 'SELECT
				product_description.*
				FROM 
				product_description
				WHERE
				product_description.id_product = :id_product';	
				$command_description=$connection->createCommand($sql);	
				
				// categories
				$sql = 'SELECT
				category_description.name
				FROM 
				product_category
				INNER JOIN category_description ON product_category.id_category = category_description.id_category
				WHERE
				product_category.id_product = :id_product
				AND
				category_description.language_code = :language_code';	
				$command_category_description=$connection->createCommand($sql);
				
				// variant
				$sql = 'SELECT
				product_variant.*,
				product_variant_description.language_code,
				product_variant_description.name AS variant				
				FROM 
				product_variant
				INNER JOIN
				product_variant_description
				ON
				(product_variant.id = product_variant_description.id_product_variant)
				WHERE
				product_variant.id = :id
				GROUP BY 
				language_code';	
				$command_variant=$connection->createCommand($sql);							
				
				// variant cover image
				$sql = 'SELECT 
				product_image_variant_image.filename
				FROM 
				product_image_variant
				INNER JOIN
				product_image_variant_image
				ON
				(product_image_variant.id = product_image_variant_image.id_product_image_variant)
				WHERE
				product_image_variant.id_product = :id_product
				AND
				product_image_variant.variant_code = :variant_code
				AND
				product_image_variant_image.cover = 1
				ORDER BY 
				product_image_variant_image.cover DESC,
				product_image_variant_image.sort_order ASC
				LIMIT 1';
				$command_variant_cover_image=$connection->createCommand($sql);							
				
				// variant images
				$sql = 'SELECT
				product_image_variant_image.filename				
				FROM 
				product_image_variant
				INNER JOIN
				product_image_variant_image
				ON
				(product_image_variant.id = product_image_variant_image.id_product_image_variant)
				WHERE
				product_image_variant.id_product = :id_product
				AND
				product_image_variant.variant_code = :variant_code
				AND
				product_image_variant_image.cover = 0
				ORDER BY 
				product_image_variant_image.cover DESC,
				product_image_variant_image.sort_order ASC';
				$command_variant_images=$connection->createCommand($sql);							
							
				break;	
		}
		
		$rows = $command->queryAll(true,$params);
		
		if (sizeof($rows)) {	
			$filename = 'export-'.$this->id_export_tpl.'-'.time().'.csv';
			// output headers so that the file is downloaded rather than displayed
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
			header("Pragma: public");  
			header ("Content-Type: application/octet-stream; charset=utf-8");  
			
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			
			echo "\xEF\xBB\xBF"; // UTF-8 BOM
			
			// create a file pointer connected to the output stream
			$fp = fopen('php://output', 'w');
			
			
			
			//Insert column name in file
			$fields = array();
			foreach ($columns as $row_column) {
				$fields[] = Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_'.$row_column['id_export_columns']).($row_column['extra']?' ('.$row_column['extra'].')':'');
			}
			fputcsv($fp, $fields);
			
			
			
			
			
			
			foreach ($rows as $row) {				
				$fields = array();
				
				$i=0;
				$additional_images = array();				
				foreach ($columns as $row_column) {
					$filename = '';
					
					switch ($this->type) {
						// products
						case 0:
							// product description
							if ($row_column['id_export_columns'] == 1 || $row_column['id_export_columns'] == 2 || $row_column['id_export_columns'] == 3 || $row_column['id_export_columns'] == 4 || $row_column['id_export_columns'] == 5 || $row_column['id_export_columns'] == 6 || $row_column['id_export_columns'] == 27) {	
								$description = array();
							
								foreach ($command_description->queryAll(true,array(':id_product'=>$row['id'])) as $row_description) {
									$description[$row_description['language_code']] = $row_description;
								}
								
								
								// if variant
								if ($row['id_product_variant']) {
									foreach ($command_variant->queryAll(true,array(':id'=>$row['id_product_variant'])) as $row_description) {
										$description[$row_description['language_code']]['variant'] = $row_description['variant'];									
									}
								}
								
								// name
								if ($row_column['id_export_columns'] == 1) {
									$fields[$i] = $description[$row_column['extra']]['name'];
								}
										
								// short_desc
								if ($row_column['id_export_columns'] == 2) {
									$fields[$i] = $description[$row_column['extra']]['short_desc'];
								}	
								
								// description
								if ($row_column['id_export_columns'] == 3) {
									$fields[$i] = $description[$row_column['extra']]['description'];
								}			
								
								// meta_description
								if ($row_column['id_export_columns'] == 4) {
									$fields[$i] = $description[$row_column['extra']]['meta_description'];
								}		
	
								// meta_keywords
								if ($row_column['id_export_columns'] == 5) {
									$fields[$i] = $description[$row_column['extra']]['meta_keywords'];
								}		
								
								// alias
								if ($row_column['id_export_columns'] == 6) {
									$fields[$i] = $description[$row_column['extra']]['alias'];
								}								
							}
							
							// product cover image
							if ($row_column['id_export_columns'] == 15) {
								// if variant, check for cover image
								if (!empty($row['variant_code'])) {
									$x = sizeof(explode(',',$row['variant_code']));				
									$variant_codes = array();
									$tmp_array = explode(',',$row['variant_code']);
									for ($y=0; $y<$x; ++$y) {
										$tmpstr = implode(',',$tmp_array);	
										if (!in_array($tmpstr,$variant_codes)) $variant_codes[] = $tmpstr;
										
										$z=1;
										foreach (array_reverse($tmp_array,1) as $k => $v) {
											// skip the last array (the first one we do not split)
											if ($z == $x) break;
											
											if (strstr($v,':')) {
												$v = array_shift(explode(':',$v));
												$tmp_array[$k] = $v;
												break;
											}
															
											++$z;		
										}
									}		
									
									foreach ($variant_codes as $row_variant_code) {
										$filename = $command_variant_cover_image->queryScalar(array(':id_product'=>$row['id'],':variant_code'=>$row_variant_code));
										
										// if an image was found
										if (!empty($filename)) break;
									}									
								}								
								
								if (!$filename) $filename = Tbl_ProductImage::model()->find('id_product=:id_product AND cover=1',array(':id_product'=>$row['id']))->filename;
								
								$fields[$i] = $filename ? $image_path.$filename:'';								
							}
							
							// product images
							if ($row_column['id_export_columns'] == 16) {
// if variant, check for cover image
								if (!empty($row['variant_code'])) {
									$x = sizeof(explode(',',$row['variant_code']));				
									$variant_codes = array();
									$tmp_array = explode(',',$row['variant_code']);
									for ($y=0; $y<$x; ++$y) {
										$tmpstr = implode(',',$tmp_array);	
										if (!in_array($tmpstr,$variant_codes)) $variant_codes[] = $tmpstr;
										
										$z=1;
										foreach (array_reverse($tmp_array,1) as $k => $v) {
											// skip the last array (the first one we do not split)
											if ($z == $x) break;
											
											if (strstr($v,':')) {
												$v = array_shift(explode(':',$v));
												$tmp_array[$k] = $v;
												break;
											}
															
											++$z;		
										}
									}		
									
									foreach ($variant_codes as $row_variant_code) {
										$rows_images = $command_variant_images->queryAll(true,array(':id_product'=>$row['id'],':variant_code'=>$row_variant_code));
										
										foreach ($rows_images as $row_image) {											
											// if not already exported
											if (!isset($additional_images[$row_image['filename']])) {
												$additional_images[$row_image['filename']] = $row_image['filename'];
												
												$filename = $row_image['filename'];
												
												break;
											}
										}
										
										if ($filename || sizeof($rows_images)) break;
									}		
								}
								
								if (!$filename && !sizeof($rows_images)) {
									
									foreach (Tbl_ProductImage::model()->findAll(array(
										'select'=>'filename',
										'condition'=>'id_product=:id_product AND cover=0',
										'params'=>array(':id_product'=>$row['id']),
										'order'=>'sort_order ASC',
									)) as $row_image) {
										// if not already exported
										if (!isset($additional_images[$row_image['filename']])) {
											$additional_images[$row_image['filename']] = $row_image['filename'];
											
											$filename = $row_image['filename'];
											
											break;
										}
									}
								}
								
								$fields[$i] = $filename ? $image_path.$filename:'';	
							}	
							
							// sku
							if ($row_column['id_export_columns'] == 7) {
								$fields[$i] = $row['sku'];
							}
							
							// brand
							if ($row_column['id_export_columns'] == 8) {
								$fields[$i] = $row['brand'];							
							}
							
							// model
							if ($row_column['id_export_columns'] == 9) {
								$fields[$i] = $row['model'];							
							}
								
							// cost price
							if ($row_column['id_export_columns'] == 10) {
								$fields[$i] = $row['cost_price'];						
							}
							
							// price
							if ($row_column['id_export_columns'] == 11) {
								$fields[$i] = $row['price'];
							}
								
							// special price
							if ($row_column['id_export_columns'] == 12) {
								$fields[$i] = $row['special_price'];
							}
								
							// special price from date
							if ($row_column['id_export_columns'] == 13) {
								$fields[$i] = $row['special_price_from_date'] != '0000-00-00 00:00:00' ? $row['special_price_from_date']:'';							
							}
	
							// special price to date
							if ($row_column['id_export_columns'] == 14) {
								$fields[$i] = $row['special_price_to_date'] != '0000-00-00 00:00:00' ? $row['special_price_to_date']:'';							
							}
							
							// qty
							if ($row_column['id_export_columns'] == 17) {
								$fields[$i] = $row['qty'];
							}		
							
							// notify qty
							if ($row_column['id_export_columns'] == 18) {
								$fields[$i] = $row['notify_qty'];
							}	
							
							// out_of_stock
							if ($row_column['id_export_columns'] == 19) {
								$fields[$i] = $row['out_of_stock'];
							}																			
	
							// out_of_stock_enabled
							if ($row_column['id_export_columns'] == 20) {
								$fields[$i] = $row['out_of_stock_enabled'];
							}																			
	
							// weight
							if ($row_column['id_export_columns'] == 21) {
								$fields[$i] = $row['weight'];
							}																			
	
							// enable_local_pickup
							if ($row_column['id_export_columns'] == 22) {
								$fields[$i] = $row['enable_local_pickup'];
							}	
							
							// used
							if ($row_column['id_export_columns'] == 23) {
								$fields[$i] = $row['used'];
							}		
							
							// featured
							if ($row_column['id_export_columns'] == 24) {
								$fields[$i] = $row['featured'];
							}
							
							// taxable
							if ($row_column['id_export_columns'] == 25) {
								$fields[$i] = $row['taxable'];
							}
							
							// status
							if ($row_column['id_export_columns'] == 26) {
								$fields[$i] = $row['active'];
							}							
							
							// if category
							if ($row_column['id_export_columns'] == 27) {
								$description_category = '';
								foreach ($command_category_description->queryAll(true,array(':id_product'=>$row['id'], ':language_code'=>$row_column['extra'])) as $row_description) {
									$description_category = $description_category . $row_description['name'] . ', ';
								}
								
								$fields[$i] = rtrim($description_category,', ');
							}
								
							// length
							if ($row_column['id_export_columns'] == 29) {
								$fields[$i] = $row['length'];
							}
							
							// width
							if ($row_column['id_export_columns'] == 30) {
								$fields[$i] = $row['width'];
							}							
							
							// height
							if ($row_column['id_export_columns'] == 31) {
								$fields[$i] = $row['height'];
							}																									
							break;	
					}
					
					++$i;
				}
				
				fputcsv($fp, $fields);
			}
			
			fclose($fp);	
		} else echo Yii::t('global','LABEL_GRID_PAGING_NO_RECORDS_FOUND');

		return true;
	}
}
?>