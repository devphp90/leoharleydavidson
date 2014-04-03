<?php
class Page {
	// constructor
	public function __construct() {}	
	
	// load by id
	public function load_id($id_page)
	{
		global $mysqli, $is_admin;
		
		$id_page = (int)$id_page;
		
		if (!$result = $mysqli->query('SELECT 
		cmspage.id,
		cmspage_description.name,
		cmspage_description.description,
		cmspage_description.meta_description,
		cmspage_description.meta_keywords
		FROM cmspage
		INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
		WHERE '.(!$is_admin ? 'cmspage.active = 1 AND ':'').'cmspage.id = "'.$id_page.'"')) throw new Exception('An error occured while trying to get page id.'."\r\n\r\n".$mysqli->error);
		
		$row = $result->fetch_assoc();
		
		if (!$row['id']) return;
		
		foreach ($row as $k => $v) $this->$k = $v;
		
		return true;
	}
	
	// id
	public function get_id(){
		return $this->id;	
	}	
	
	public function get_name(){
		return $this->name;	
	}
	
	public function get_description(){
		return $this->description;	
	}
	
	public function get_meta_description(){
		return $this->meta_description;	
	}
	
	public function get_meta_keywords(){
		return $this->meta_keywords;	
	}

	// get list of pages
	public static function get_url($id, $language_code='')
	{
		global $mysqli, $is_admin;		
		
		$id = (int)$id;
		$language_code = !empty($language_code) ? $language_code:$_SESSION['customer']['language'];
		$output = array();
					
		if (!$result = $mysqli->query('SELECT 
		cmspage.id,
		cmspage.header_only,
		cmspage.indexing,
		cmspage.external_link,
		cmspage_description.external_link_link,
		cmspage_description.external_link_target_blank,
		cmspage.sort_order,
		cmspage_description.name,
		cmspage_description.alias,
		cmspage.id_subscription_contest
		FROM cmspage
		INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"
		WHERE '.(!$is_admin ? 'cmspage.active = 1 AND ':'').'
		cmspage.id = '.$id.' 
		ORDER BY sort_order ASC')) throw new Exception('An error occured while trying to get page info.'."\r\n\r\n".$mysqli->error);
		$row = $result->fetch_assoc();
		
		$output = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'url' => $row['external_link'] ? $row['external_link_link']:($row['id_subscription_contest'] ? '/'.$_SESSION['customer']['language'].'/registration?id='.$row['id_subscription_contest']:'/'.$_SESSION['customer']['language'].'/page/'.($row['indexing'] ? $row['alias']:makesafetitle($row['name']).'-'.$row['id'])),			
			'open_new_window' => $row['external_link_target_blank'] ? 1:0,
		);

		if($row['id_parent']){
			$output['pages'] = $this->get_url($row['id_parent'], $language_code);
		}
		
		return $output;
	}
	
	// breadcrumb
	public function get_breadcrumb()
	{	
		$crumbs = array();

		$id_cmspage = $this->id;
		
		do {
			$result = $this->get_parent_id($id_cmspage);
			
			array_unshift($crumbs, array(
				'url' => Categories::get_url($id_cmspage),
				'name' => $result['name'],
			));		
			
			$id_cmspage = (int)$result['id'];						
		} while ($id_cmspage);
		
		return $crumbs;
	}
	
	private function get_parent_id($id)
	{
		if (!$stmt_get_id = $this->mysqli->prepare('SELECT 
		cmspage.id_parent,
		cmspage_description.name 
		FROM
		cmspage
		INNER JOIN
		cmspage_description
		ON
		(cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'")
		
		WHERE
		cmspage.id = ? 
		LIMIT 1')) throw new Exception('An error occured while trying to prepare get cmspage parent id statement.'."\r\n\r\n".$this->mysqli->error);	   				
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