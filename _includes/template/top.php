<?php 
	//List of CMS Pages
    $pages = new Pages;
	
	function get_page($id_parent=0){
		global $pages;
		$arr_pages = $pages->get_pages($id_parent);
			
		if (sizeof($arr_pages)) {
			$counter_parent = 0;
			foreach($arr_pages as $page){
				$counter_parent++;
				echo '<li>
				<a href="'.($page['header_only']?'javascript:void(0);':$page['url']).'"'.($page['open_new_window']?' target="_blank"':'').'>'.$page['name'].'</a>';
  
				if(sizeof($page['childs'])){
					echo '<ul>';
					get_page($page['id']);
					echo '</ul>';
				}
				echo '</li>';				
			}
		}
	}
	$pages_cms = $pages->get_pages(0);
	$pagesCms = array();
	foreach($pages_cms as $pcms) {
	  $pagesCms[$pcms["id"]] = $pcms;
	}	
	
	$categories = new Categories;
	$mainCats = $categories->get_categories(0);	
?>
<div class="warrper">
<header>				
				<div class="top">
					<div class="top-head-content">
					<?php if($config_site['display_telephone']) {?>
					<span><?php echo $config_site['company_telephone'];?></span>					
					
					<?php }?>
					
					<?php
						$arr_language = get_languages();
	            	    if(sizeof($arr_language)>1){
							echo '&nbsp;&nbsp;
					<img src="/_images/sep-nav.png">
                    &nbsp;&nbsp;';
	                  		foreach($arr_language as $language){
	                  		  if($_SESSION['customer']['language'] != $language['code']) {
	                ?>
	                	<a href="javascript:jQuery('#lang-choice').val('<?php echo $language['code'];?>');jQuery('#form-lang').submit()" style="margin-right: 17px;text-transform:uppercase;">
	                		<?php echo $language['name']?>
	                	</a>
	                <?php 		  	
	                  		  }
	                  		}
	            	    }
                  	?>					
					<form method="post" action="" id="form-lang">
                      	<input type="hidden" name="language_main_site" value="<?php echo $_SESSION['customer']['language'];?>" id="lang-choice">
                    </form>
					</div>
				</div>
				<div class="main-nav-wrap">
					<div class="top-nav-content">					
					<nav id="menu-nav-mobile" style="z-index:10000;">
						<ul>
							<?php if(!empty($pagesCms[33])) {?>
							<li class="link" style="margin-top:3px;"><a href="<?php echo $pagesCms[33]["url"];?>" style="color:#d3782e; font-size:20px;"><?php echo $pagesCms[33]["name"];?></a></li>
							<li class="img"><img src="/_images/sep-nav.png" /></li>
							<?php }?>
							<?php if(!empty($mainCats[0])) {?>
							<li class="link"><a href="<?php echo $mainCats[0]["url"]?>"><?php echo str_replace(" "," ",$mainCats[0]["name"]);?></a></li>
							<li class="img"><img src="/_images/sep-nav.png" /></li>
							<?php }?>
							<?php if(!empty($pagesCms[28])) {?>
							<li class="link"><a href="<?php echo $pagesCms[28]["url"];?>"><?php echo $pagesCms[28]["name"];?></a></li>
							<li class="img"><img src="/_images/sep-nav.png" /></li>
							<?php }?>
							<?php if(!empty($pagesCms[29])) {?>
							<li class="link"><a href="<?php echo $pagesCms[29]["url"];?>"><?php echo $pagesCms[29]["name"];?></a></li>
							<li class="img"><img src="/_images/sep-nav.png" /></li>
							<?php }?>
							<?php if(!empty($pagesCms[18])) {?>
							<li class="link"><a href="<?php echo $pagesCms[18]["url"];?>"><?php echo $pagesCms[18]["name"];?></a></li>
							<?php }?>			
						</ul>
					</nav>
					<a href="javascript:void(0);" onclick="jQuery('#menu-nav-mobile').toggle()" id="btn-menu">menu</a>
					<a href="/" class="logo"><img style="height:105px;" src="/_images/logo.png" alt="HARLEY-DAVIDSON" /> </a>
					<a href="/" class="leo-top"><img src="/_images/leo.jpg" class="leo" /></a>
					<!--div>
						<h1>léo harley-davidson rien d'autre</h1>
					</div>	-->	
					</div>			
				</div>
				<div>
					
				</div>

			</header>

<?php
function get_product_cover_image_top($id_product, $id_product_variant=0)
{
	global $mysqli;
	
	// get product image
	$cover_image = '';

	if (!$stmt_variant_image = $mysqli->prepare('SELECT 
	product_image_variant_image.filename
	FROM 
	product_image_variant
	INNER JOIN
	product_image_variant_image
	ON
	(product_image_variant.id = product_image_variant_image.id_product_image_variant)
	WHERE
	product_image_variant.id_product = ?
	AND
	product_image_variant.variant_code = ?
	AND
	product_image_variant_image.cover = 1
	ORDER BY 
	product_image_variant_image.cover DESC,
	product_image_variant_image.sort_order ASC
	LIMIT 1')) throw new Exception('An error occured while trying to prepare get variant cover image statement.'."\r\n\r\n".$mysqli->error);		
	
	if (!$stmt_image = $mysqli->prepare('SELECT
	product_image.filename
	FROM
	product_image
	WHERE
	product_image.id_product = ?
	AND
	product_image.cover = 1
	LIMIT 1')) throw new Exception('An error occured while trying to prepare get variant image statement.'."\r\n\r\n".$mysqli->error);	
	
	// if variant get variant code
	if ($id_product_variant) {
		if (!$result = $mysqli->query('SELECT variant_code FROM product_variant WHERE id = "'.$id_product_variant.'" LIMIT 1')) throw new Exception('An error occured while trying to get variant code.');
		$row = $result->fetch_assoc();
		
		$variant_code = $row['variant_code'];
	}

	// check if variant
	if (!empty($variant_code)) {
		/*
			This code below outputs this result (example)
			12:25,13:27,14:32
			12:25,13:27,14
			12:25,13,14					
			
			In that order, it allows us to get the variant codes of product image variants and loop through each to find an image 
		*/
		$i = sizeof(explode(',',$variant_code));				
		$variant_codes = array();
		$tmp_array = explode(',',$variant_code);
		for ($x=0; $x<$i; ++$x) {
			$tmpstr = implode(',',$tmp_array);	
			if (!in_array($tmpstr,$variant_codes)) $variant_codes[] = $tmpstr;
			
			$z=1;
			foreach (array_reverse($tmp_array,1) as $k => $v) {
				// skip the last array (the first one we do not split)
				if ($z == $i) break;
				
				if (strstr($v,':')) {
					$v = array_shift(explode(':',$v));
					$tmp_array[$k] = $v;
					break;
				}
								
				++$z;		
			}
		}		
		
		foreach ($variant_codes as $row_variant_code) {
			// check if we have a cover image for this variant code
			if (!$stmt_variant_image->bind_param("is", $id_product, $row_variant_code)) throw new Exception('An error occured while trying to bind params to get variant cover image statement.'."\r\n\r\n".$mysqli->error);
			
			/* Execute the statement */
			if (!$stmt_variant_image->execute()) throw new Exception('An error occured while trying to get variant cover image.'."\r\n\r\n".$mysqli->error);	
			
			/* store result */
			$stmt_variant_image->store_result();		

			/* bind result variables */
			$stmt_variant_image->bind_result($cover_image);																											
			
			$stmt_variant_image->fetch();
			
			// if an image was found
			if (!empty($cover_image)) break;
		}
	}
	
	if (!$cover_image) {
		if (!$stmt_image->bind_param("i", $id_product)) throw new Exception('An error occured while trying to bind params to get image statement.'."\r\n\r\n".$this->mysqli->error);
		
		/* Execute the statement */
		if (!$stmt_image->execute()) throw new Exception('An error occured while trying to execute get image statement.'."\r\n\r\n".$mysqli->error);				
		
		/* store result */
		$stmt_image->store_result();																											
		
		/* bind result variables */
		$stmt_image->bind_result($cover_image);	
			
		$stmt_image->fetch();
	}
	
	$stmt_variant_image->close();
	$stmt_image->close();
		
	return $cover_image;
}
 ?>