<?php
/*
//Tags
$query_tag = "SELECT 
	tag.id,
	tag_description.name,
	tag_description.alias,
	tag_description.description 
	FROM 
	tag 
	INNER JOIN 
	tag_description 
	ON 
	(tag.id = tag_description.id_tag AND tag_description.language_code = '".$mysqli->escape_string($_SESSION['customer']['language'])."')
	ORDER BY visited_qty DESC
	LIMIT 20";	
if ($result_tag = $mysqli->query($query_tag)) {
	if ($result_tag->num_rows) {
		echo '<div id="tag_home_page">
		<h3>'.language('_include/template/tag','TITLE_POPULAR_TAG').'</h3>
		<ul>';
		while($row_tag = $result_tag->fetch_assoc()){
			// Products Listing
			echo ' <li><a href="'.$url_prefix.'tag/'.$row_tag['alias'].'">'.$row_tag['name'].'</a></li>';
		} 
		echo '</ul>
		<div class="cb"></div>
		<div style="text-align:right;margin-top:5px;"><a href="'.$url_prefix.'tag">'.language('_include/template/tag','LINK_ALL_TAG').'</a></div>
		<div class="cb"></div>
		</div>';
	}
	$result_tag->close();
}
*/
?>