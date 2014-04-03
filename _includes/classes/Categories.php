<?php
class Categories {
	// constructor
	public function __construct() {}	
	
	// get list of categories
	public static function get_categories($id_parent=0)
	{
		return self::list_categories($id_parent);
	}
	
	private function list_categories($id_parent)
	{
		global $mysqli;		
		
		$current_datetime = date('Y-m-d H:i:s');
		
		if (!$result = $mysqli->query('SELECT 
		category.id,
		category_description.name,
		category.featured,
		(SELECT COUNT(c.id) FROM category AS c WHERE c.id_parent = category.id AND c.active = 1 AND c.start_date <= "'.$current_datetime.'" AND (c.end_date = "0000-00-00 00:00:00" OR c.end_date > "'.$current_datetime.'")) AS total_child
		FROM 
		category
		
		INNER JOIN 
		category_description ON (category.id = category_description.id_category AND category_description.language_code = "'.$_SESSION['customer']['language'].'")
				
		WHERE 
		category.active = 1 
		AND 
		category.start_date <= "'.$current_datetime.'"
		AND 
		(category.end_date = "0000-00-00 00:00:00" OR category.end_date > "'.$current_datetime.'") 
		AND 
		category.id_parent = "'.$id_parent.'"
		ORDER BY 
		category.sort_order ASC')) throw new Exception('An error occured while trying to get list of categories.'."\r\n\r\n".$mysqli->error);

		$categories = array();
		$current_alias='';
		if ($result->num_rows) {		
			if (!$stmt_total_products = $mysqli->prepare('SELECT 
			COUNT(t.id) AS total 
			FROM 
			((SELECT 
			product.id,
			product_image_variant.id AS id_product_image_variant
			FROM 
			product_category 
			INNER JOIN 
			product 
			ON 
			(product_category.id_product = product.id) 
			INNER JOIN 
			product_image_variant
			ON 
			(product.id = product_image_variant.id_product AND product_image_variant.displayed_in_listing = 1)
			WHERE 
			product_category.id_category = ? 
			AND
			product.active = 1 
			AND 
			product.display_in_catalog = 1 
			AND 
			product.date_displayed <= "'.$current_datetime.'"
			AND 
			product.has_variants = 1) 
			
			UNION 
			
			(SELECT 
			product.id,
			0 AS id_product_image_variant
			FROM 
			product_category 
			INNER JOIN 
			product 
			ON 
			(product_category.id_product = product.id) 
			WHERE 
			product_category.id_category = ? 
			AND
			product.active = 1 
			AND 
			product.display_in_catalog = 1 
			AND 
			product.date_displayed <= "'.$current_datetime.'")) AS t')) throw new Exception('An error occured while trying to prepare get total products statement.'."\r\n\r\n".$mysqli->error);			   		
		
			while ($row = $result->fetch_assoc()) {
				if (!$stmt_total_products->bind_param("ii", $row['id'], $row['id'])) throw new Exception('An error occured while trying to bind params to get total products statement.'."\r\n\r\n".$mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_total_products->execute()) throw new Exception('An error occured while trying to get total products.'."\r\n\r\n".$mysqli->error);								
				
				/* store result */
				$stmt_total_products->store_result();	
				
				/* bind result variables */
				$stmt_total_products->bind_result($total_products);									
				
				$stmt_total_products->fetch();				
				
				$categories[] = array(
					'id' => $row['id'],
					'name' => htmlspecialchars($row['name']),
					'url' => self::get_url($row['id']),
					'featured' => $row['featured'],
					'childs' => $row['total_child'] ? self::list_categories($row['id']):array(),
					'total_products' => $total_products,
				);
			}
			
			$stmt_total_products->close();			
		}
		$result->free();
		
		return $categories;
	}	
	
	public static function get_url($id,$language_code='')
	{
		global $mysqli, $products, $url_prefix;
		
		$language_code = !empty($language_code) ? $language_code:$_SESSION['customer']['language'];
		
		$array = array();
		
		if (!$stmt_get_alias = $mysqli->prepare('SELECT 
		category.id_parent,
		category_description.alias
		FROM
		category
		INNER JOIN
		category_description
		ON
		(category.id = category_description.id_category AND category_description.language_code = "'.$mysqli->escape_string($language_code).'")
		
		WHERE
		category.id = ? 
		LIMIT 1')) throw new Exception('An error occured while trying to prepare get category alias statement.'."\r\n\r\n".$mysqli->error);	   				
		do {
			if (!$stmt_get_alias->bind_param("i", $id)) throw new Exception('An error occured while trying to bind params to get category alias statement.'."\r\n\r\n".$mysqli->error);
			
			/* Execute the statement */
			if (!$stmt_get_alias->execute()) throw new Exception('An error occured while trying to get category alias statement.'."\r\n\r\n".$mysqli->error);								
			
			/* store result */
			$stmt_get_alias->store_result();	
			
			/* bind result variables */
			$stmt_get_alias->bind_result($id, $alias);									
			
			$stmt_get_alias->fetch();	
			
			if ($alias) array_unshift($array,$alias);
		} while ($id);		
		
		$stmt_get_alias->close();
		
		/*$querystr = $_SERVER['QUERY_STRING'];
		$querystr = preg_replace('/_lang=([a-z]{2})&?/i','',$querystr);		
		$querystr = preg_replace('/alias=([a-z0-9-_]+)&?/i','',$querystr);*/
		
		if (is_object($products) && sizeof($filters = $products->get_filters_array())) {
			$querystr = http_build_query($filters);
		}
				
		return '/'.$language_code.'/catalog'.(sizeof($array) ? '/'.implode('/',$array):'').($querystr ? '?'.$querystr:'');
	}	
}