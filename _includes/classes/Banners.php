<?php
class Banners {
	// constructor
	public function __construct() {}	

	// get list of banners
	public static function get_banners()
	{
		global $mysqli;
		
		$current_datetime = date('Y-m-d H:i:s');
		
		if (!$result = $mysqli->query('SELECT 
		banner.id,
		banner_description.url_type,
		banner_description.url,
		banner_description.target_blank,
		banner_description.filename,
		banner_description.id_cmspage,
		cmspage.external_link,
		cmspage_description.external_link_link,
		cmspage_description.external_link_target_blank,
		cmspage_description.alias,
		banner_description.id_subscription_contest
		FROM banner
		INNER JOIN
		banner_description
		ON
		(banner.id = banner_description.id_banner AND banner_description.language_code = "'.$_SESSION['customer']['language'].'")
		LEFT JOIN
		(cmspage_description CROSS JOIN cmspage)
		ON
		(banner_description.id_cmspage = cmspage_description.id_cmspage AND cmspage_description.language_code = banner_description.language_code AND cmspage_description.id_cmspage = cmspage.id)
		WHERE 
		banner.active = 1
		AND
		banner_description.filename <> "" 
		AND 
		banner.display_start_date <= "'.$current_datetime.'"
		AND
		(banner.display_end_date = "0000-00-00 00:00:00" OR	banner.display_end_date > "'.$current_datetime.'")')) throw new Exception('An error occured while trying to list banners.'."\r\n\r\n".$mysqli->error);
		
		$banners = array();
		while($row = $result->fetch_assoc()){			
			switch ($row['url_type']) {
				// none
				case 0:
					$url = '';
					break;
				// url
				case 1:
					$url = $row['url'];
					break;
				// cmspage
				case 2:
					if ($row['id_cmspage'] == 1) $url = '/'.$_SESSION['customer']['language'].'/';
					else $url = $row['external_link'] ? $row['external_link_link']:'/'.$_SESSION['customer']['language'].'/page/'.$row['alias'];
					break;
				// subscription contest
				case 3:
					$url = '/'.$_SESSION['customer']['language'].'/registration?id='.$row['id_subscription_contest'];
					break;	
			}			
			$image = is_file(realpath('_images/banner/'.$row['filename']))?'/_images/banner/'.$row['filename']:'';
			$banners[] = array(
				'id' => $row['id'],
				'url' => $url,
				'open_new_window' => $row['target_blank'] ? 1:0,
				'image' => $image,
			);
		}
		
		return $banners;
	}		
			
}