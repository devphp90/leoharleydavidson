<?php
class Category {
	// database resource
	private $mysqli;	
	
	private $id=0;
	private $id_parent=0;
	private $name='';
	private $description='';
	private $meta_description='';
	private $meta_keywords='';
	private $product_sort_by=0;
	
	// constructor
	public function __construct() {
		global $mysqli;
		
		if (!$mysqli instanceof MySQLi) throw new Exception('Invalid mysqli object');
		$this->mysqli=$mysqli;		
	}	
	
	// load by id
	public function load_id($id_category)
	{
		$current_datetime = date('Y-m-d H:i:s');
		
		if (!$result = $this->mysqli->query('SELECT 
		category.id,
		category.id_parent,
		category.product_sort_by,
		category_description.name,
		category_description.description,
		category_description.meta_description,
		category_description.meta_keywords
		FROM 
		category 
		INNER JOIN
		category_description
		ON
		(category.id = category_description.id_category AND category_description.language_code = "'.$_SESSION['customer']['language'].'")
		WHERE
		category.id = "'.$this->mysqli->escape_string($id_category).'"
		AND 
		category.active = 1 
		AND 
		category.start_date <= "'.$current_datetime.'"
		AND
		(category.end_date = "0000-00-00 00:00:00" OR category.end_date > "'.$current_datetime.'")
		LIMIT 1')) throw new Exception('An error occured while trying to get category by id.'."\r\n\r\n".$this->mysqli->error);
		
		$row = $result->fetch_assoc();		
		$result->free();
		
		if (!$row['id']) return false;
		
		$this->id = $row['id'];
		$this->id_parent = $row['id_parent'];
		$this->name = $row['name'];
		$this->description = $row['description'];
		$this->meta_description = $row['meta_description'];
		$this->meta_keywords = $row['meta_keywords'];
		$this->product_sort_by = $row['product_sort_by'];
		
		return true;
	}
	
	// load by alias
	public function load_alias($alias) 
	{
		$array=explode('/',$alias);
		
		if(sizeof($array)) {
			$current_datetime = date('Y-m-d H:i:s');
			
			if (!$stmt_category = $this->mysqli->prepare('SELECT 
			category.id,
			category_description.name,
			category_description.description,
			category.product_sort_by,
			category_description.meta_description,
			category_description.meta_keywords
			FROM 
			category 
			INNER JOIN
			category_description
			ON
			(category.id = category_description.id_category AND category_description.language_code = "'.$_SESSION['customer']['language'].'")
			WHERE
			category.id_parent = ?
			AND
			category_description.alias = ?
			AND 
			category.active = 1 
			AND 
			category.start_date <= "'.$current_datetime.'"
			AND
			(category.end_date = "0000-00-00 00:00:00" OR category.end_date > "'.$current_datetime.'")
			LIMIT 1')) throw new Exception('An error occured while trying to prepare get category by alias statement.'."\r\n\r\n".$this->mysqli->error);
			
			$id_parent = 0;
			$id_categories = array();
			foreach ($array as $value) {
				if (!$stmt_category->bind_param("is", $id_parent, $value)) throw new Exception('An error occured while trying to bind params to get category id by alias statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_category->execute()) throw new Exception('An error occured while trying to get category by alias.'."\r\n\r\n".$this->mysqli->error);								
				
				/* store result */
				$stmt_category->store_result();	
				
				/* bind result variables */
				$stmt_category->bind_result($id_category, $name, $description, $product_sort_by, $meta_description, $meta_keywords);									
				
				$stmt_category->fetch();						
				
				// if no id category
				if (!$id_category) return false;
				
				$id_parent = $id_category;
				
				$id_categories[] = $id_category;
			}	
			
			$this->id = $id_category;
			$this->name = $name;
			$this->description = $description;
			$this->meta_description = $meta_description;
			$this->meta_keywords = $meta_keywords;
			$this->id_categories = $id_categories;
			$this->product_sort_by = $product_sort_by;
			
			return true;
		}
		
		return false;
	}	
	
	// id
	public function get_id()
	{
		return $this->id;	
	}
	
	// name
	public function get_name()
	{
		return htmlspecialchars($this->name);
	}
	
	// description
	public function get_description()
	{
		return $this->description;	
	}
	
	// product_sort_by
	public function get_product_sort_by()
	{
		return $this->product_sort_by;	
	}
	
	// meta description
	public function get_meta_description()
	{
		return htmlspecialchars($this->meta_description);
	}
	
	// meta keywords
	public function get_meta_keywords()
	{
		return htmlspecialchars($this->meta_keywords);
	}
	
	// url
	public function get_url($language_code)
	{		
		return Categories::get_url($this->id,$language_code);
	}
	
	// breadcrumb
	public function get_breadcrumb()
	{	
		$crumbs = array();

		$id_category = $this->id;
		
		do {
			$result = $this->get_parent_id($id_category);
			
			array_unshift($crumbs, array(
				'url' => Categories::get_url($id_category),
				'name' => $result['name'],
			));		
			
			$id_category = (int)$result['id'];						
		} while ($id_category);
		
		return $crumbs;
	}
	
	private function get_parent_id($id)
	{
		if (!$stmt_get_id = $this->mysqli->prepare('SELECT 
		category.id_parent,
		category_description.name 
		FROM
		category
		INNER JOIN
		category_description
		ON
		(category.id = category_description.id_category AND category_description.language_code = "'.$_SESSION['customer']['language'].'")
		
		WHERE
		category.id = ? 
		LIMIT 1')) throw new Exception('An error occured while trying to prepare get category parent id statement.'."\r\n\r\n".$this->mysqli->error);	   				
		if (!$stmt_get_id->bind_param("i", $id)) throw new Exception('An error occured while trying to bind params to get parent id statement.'."\r\n\r\n".$this->mysqli->error);
		
		/* Execute the statement */
		if (!$stmt_get_id->execute()) throw new Exception('An error occured while trying to get parent id.'."\r\n\r\n".$mysqli->error);								
		
		/* store result */
		$stmt_get_id->store_result();	
		
		/* bind result variables */
		$stmt_get_id->bind_result($id_parent, $name);									
		
		$stmt_get_id->fetch();	
		
		$stmt_get_id->close();
		
		return array(
			'id' => $id_parent,
			'name' => $name,
		);
	}
}
?>