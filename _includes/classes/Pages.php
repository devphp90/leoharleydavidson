<?php
class Pages {
	// constructor
	public function __construct() {}	

	// get list of pages
	public static function get_pages($id_parent=0, $bottom=0)
	{
		return self::list_pages($id_parent, $bottom);
	}		
	
	private function list_pages($id_parent, $bottom=0)
	{
		global $mysqli;		
		
		$id_parent = (int)$id_parent;
		$bottom = (int)$bottom;
		
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
		COUNT(c.id) AS total_child,
		cmspage.id_subscription_contest
		FROM cmspage
		INNER JOIN 
		cmspage_description 
		ON 
		(cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'")
		LEFT JOIN
		cmspage AS c
		ON
		(cmspage.id = c.id_parent AND c.active = 1 AND c.display = 1 AND (c.display_menu = 0 OR c.display_menu = 1))
		WHERE 
		'.(!$bottom ? 'cmspage.active = 1 AND cmspage.display = 1 AND (cmspage.display_menu = 0 OR cmspage.display_menu = 1) AND ':'cmspage.active = 1 AND cmspage.display = 1 AND (cmspage.display_menu = 0 OR cmspage.display_menu = 2) AND cmspage.header_only = 0 AND ').'		
		cmspage.id_parent = "'.$id_parent.'"
		GROUP BY 
		cmspage.id
		ORDER BY 
		cmspage.sort_order ASC')) throw new Exception('An error occured while trying to get list of pages.'."\r\n\r\n".$mysqli->error);

		$pages = array();
		if ($result->num_rows) {					
			while ($row = $result->fetch_assoc()) {
				$pages[] = array(
					'id' => $row['id'],
					'name' => htmlspecialchars($row['name']),
					'url' => $row['external_link'] ? $row['external_link_link']:($row['id_subscription_contest'] ? '/'.$_SESSION['customer']['language'].'/registration?id='.$row['id_subscription_contest']:'/'.$_SESSION['customer']['language'].'/page/'.($row['indexing'] ? $row['alias']:makesafetitle($row['name']).'-'.$row['id'])),
					'header_only' => $row['header_only'],
					'open_new_window' => $row['external_link_target_blank'] ? 1:0,
					'childs' => $row['total_child'] ? self::list_pages($row['id']):array(),
				);
			}
		}
		$result->free();
		
		return $pages;
	}		
}
