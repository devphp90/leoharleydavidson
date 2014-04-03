<?php
// Adds (Publicity)
if (!$result_pub = $mysqli->query('SELECT 
	pub.id,
	pub.width,
	pub.display_in_column,
	pub.display_in_page,
	pub_description.url_type,
	pub_description.url,
	pub_description.target_blank,
	pub_description.filename,
	pub_description.id_cmspage,
	cmspage.external_link,
	cmspage_description.external_link_link,
	cmspage_description.external_link_target_blank,
	cmspage_description.alias,
	pub_description.id_subscription_contest
	FROM pub
	INNER JOIN
	pub_description
	ON
	(pub.id = pub_description.id_pub AND pub_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
	LEFT JOIN
	(cmspage_description CROSS JOIN cmspage)
	ON
	(pub_description.id_cmspage = cmspage_description.id_cmspage AND cmspage_description.language_code = pub_description.language_code AND cmspage_description.id_cmspage = cmspage.id)
	WHERE 
	pub.active = 1
	AND
	pub_description.filename <> "" 
	AND 
	pub.display_start_date <= "'.$mysqli->escape_string($current_datetime).'"
	AND
	(pub.display_end_date = "0000-00-00 00:00:00" OR pub.display_end_date > "'.$mysqli->escape_string($current_datetime).'")
	'.(!$page_home?'AND (pub.display_in_page = 1)':'').'
	ORDER BY 
	pub.display_in_column ASC, pub.sort_order ASC')) throw new Exception('An error occured while trying to get pub.'."\r\n\r\n".$mysqli->error);
	
	while($row_pub = $result_pub->fetch_assoc()){

			if(is_file($_SERVER["DOCUMENT_ROOT"].'/_images/pub/'.$row_pub['filename'])){
				
				$image_path = $_SERVER["DOCUMENT_ROOT"].'/_images/pub/'.$row_pub['filename'];
				list($width, $height, $type, $attr)= getimagesize($image_path); 

				switch ($row_pub['url_type']) {
						// none
						case 0:
							$display_in_column_left[$row_pub['id']]['url'] = 0;
							break;
						// url
						case 1:
							$display_in_column_left[$row_pub['id']]['url'] = $row_pub['url'];
							$display_in_column_left[$row_pub['id']]['target_blank'] = $row_pub['target_blank'];
							break;
						// cmspage
						case 2:
							$display_in_column_left[$row_pub['id']]['url'] = $row_pub['external_link'] ? $row_pub['external_link_link']:$url_prefix.'page/'.$row_pub['alias'];
							$display_in_column_left[$row_pub['id']]['target_blank'] = $row_pub['external_link_target_blank'];
							break;
						// subscription contest
						case 3:
							$display_in_column_left[$row_pub['id']]['url'] = $url_prefix.'registration?id='.$row_pub['id_subscription_contest'];
							$display_in_column_left[$row_pub['id']]['target_blank'] = 0;
							break;	
				}

				echo '<div style="margin-bottom: 20px;">'.($pub_description['url']?'<a href="'.$pub_description['url'].'"'.($row_pub['target_blank']?' target="_blank"':'').'>':'').'<img src="/_images/pub/'.$row_pub['filename'].'" style="width:100%;" />'.($pub_description['url']?'</a>':'').'</div>';

				
			}
	}
	
?>
