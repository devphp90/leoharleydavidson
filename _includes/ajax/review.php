<?php
require_once(dirname(__FILE__) . "/../../_includes/config.php");

if(isset($_POST['id']) and is_numeric($_POST['id'])){
	$id = $_POST['id'];
}else{
	$id = 0;
	echo 'false';
	exit();
}

if(isset($_POST['return'])){
	$return = $_POST['return'];
}else{
	$return = "";
}

if(isset($_POST['page'])){
	$page = $_POST['page'];
}else{
	$page = 1;
}
if(isset($_POST['language'])){
	$language = $_POST['language'];
}else{
	$language = "";
}

if(isset($_GET['action']) and !empty($_GET['action'])){
	$action = $_GET['action'];
}else{
	$action = "";
	echo 'false';
	exit();
}

switch($action){
	case 'open':
		$query = 'SELECT 
					product_description.name,
					product_description.alias,
					product_image.filename,
					IF(product_rating_count.avg_rating IS NOT NULL,product_rating_count.avg_rating,0) AS average_rated,
					IF(product_rating_count.total_rating IS NOT NULL,product_rating_count.total_rating,0) AS total_rated,
					main_product_table.id
					FROM product AS main_product_table
					INNER JOIN product_description ON main_product_table.id = product_description.id_product AND product_description.language_code = "'.$_SESSION['customer']['language'].'"
					LEFT JOIN product_image ON main_product_table.id = product_image.id_product AND product_image.cover = 1
					LEFT JOIN product_rating_count ON main_product_table.id = product_rating_count.id_product
					WHERE main_product_table.id = ' . $id . '
					GROUP BY main_product_table.id';
					//echo $query;
		if ($result = $mysqli->query($query)) {
			if($result->num_rows){
				$row = $result->fetch_assoc();
				
				$id = $row['id'];
				$name = $row['name'];
				$filename = $row['filename'];
				$average_rated = $row['average_rated'];
				$total_rated = $row['total_rated'];
				
				if(is_file(dirname(__FILE__) . '/../../images/products/thumb/'.$filename)){
					$image_src = '/images/products/thumb/'.$filename;
				}else{
					$image_src = get_blank_image('thumb');
				}
				echo '
				<div class="title_product">
					<div style="float:left"><a href="'.$url_prefix.'product/'.$row['alias'].'"><img src="'.$image_src.'" alt="'.$row['name'].'" width="'.$images_sizes['thumb_width'].'" height="'.$images_sizes['thumb_height'].'" border="0" hspace="0" vspace="0" /></a></div>
					<div style="float:left; margin-left: 5px; max-width: 650px;">'.language('_include/ajax/review','TITLE_CUSTOMER_REVIEW').'<h2>'.$name.'</h2></div>
					<div class="cb"></div>
				</div>
				
				<div class="title_product" style="padding-top:5px;">';
				if($total_rated){
					echo '<div style="float: left">
						<div><strong>'.$total_rated.' '.language('_include/ajax/review','TITLE_REVIEWS_TOTAL').'</strong></div>';
						$arr_star = array();
						
						$arr_star[5] = 0;
						$arr_star[4] = 0;
						$arr_star[3] = 0;
						$arr_star[2] = 0;
						$arr_star[1] = 0;
			
							$query = 'SELECT 
										COUNT(id) AS total_star,
										rated
										FROM 
										product_review
										WHERE (rated > 0) AND id_product = '.$id.'
										GROUP BY
										rated
										ORDER BY rated DESC';
							//echo $query;			
							//echo $query;'.(sizeof($where_display_rating) ? ' WHERE '.implode(' AND ',$where_display_rating):'').'
							if ($result = $mysqli->query($query)) {
								if($result->num_rows){
									while($row = $result->fetch_assoc()){
										$arr_star[$row['rated']] = $row['total_star'];
									}
									
									 foreach($arr_star as $key=>$value){
											echo '
											<div style="padding-bottom: 4px;">
												<div style="float: left">' . get_rated_star($key) . '</div>
												<div style="float: left; margin-left: 5px; width: 100px; height: 15px; background-color: #FDE4C4">
													<div style="background-color: #f99d1f; width: '.round(($value/$total_rated)*100).'%; height: 15px;"></div>
												</div>
												<div style="float:left; margin-left: 2px; font-size:10px">'.round(($value/$total_rated)*100).'%</div>
												<div style="float:left; margin-left:5px; font-size:10px">('.$value.')</div>
												<div class="cb"></div>
											</div>';
									}
									
								}
								$result->free();
							}
					
					
					echo '
					</div>
					<div style="float:left; margin-left: 30px">
						<div style="padding-bottom: 13px">
							<div><strong>'.language('_include/ajax/review','TITLE_AVERAGE_REVIEW').'</strong></div>
							<div>'. get_rated_star($average_rated).' <span style="font-size:10px">('.$total_rated.' '.language('_include/ajax/review','TITLE_REVIEWS_TOTAL').')</span></div>
						</div>
						<div><input value="'.language('_include/ajax/review','BTN_WRITE_REVIEW').'" class="button" type="button" onclick="'.(isset($_SESSION['customer']['id'])?'javascript:form_review()':'document.location.href=\'/account/login?return='.$return.'\'').'"></div>
						</div>
						
						
						
				';
          
				  $query_language = 'SELECT * FROM language WHERE active = 1 ORDER BY default_language DESC';
				  if ($result_language = $mysqli->query($query_language)) {
					  echo '<div class="fl" style="margin-left:20px;"><div><strong>'.language('_include/ajax/review','TITLE_WICH_SITE').'</strong></div>
					  <div><select name="language_main_site" onchange="javascript: open_review('.$id.',\'\',\'\','.$page.',this.value);">
					  <option value="0" '. ((!$language) ? 'selected="selected"':'').'>'.language('global','LABEL_ALL').'</option>';
					  while($row_language = $result_language->fetch_assoc()){
						  echo '<option value="'.$row_language['code'].'" '. (($row_language['code'] == $language) ? 'selected="selected"':'').'>'.$row_language['name'].'</option>';
					  } 
					  $result_language->close();
				  }
				  
				echo '</select></div></div>
						
						
						
						<div class="cb"></div>';
						
				}else{
					echo '
					<h3 style="margin-bottom:5px;">'.language('_include/ajax/review','TITLE_BE_FIRST').'</h3>
					<div><input value="'.language('_include/ajax/review','BTN_WRITE_REVIEW').'" class="button" type="button" onclick="'.(isset($_SESSION['customer']['id'])?'javascript:form_review()':'document.location.href=\'/account/login?return='.$return.'\'').'"></div>';
				}
						
						if(isset($_SESSION['customer']['id']) and !empty($_SESSION['customer']['id'])){
							$query = 'SELECT 
									id
									FROM product_review
									WHERE product_review.id_product = '.$id.' AND product_review.id_customer = '.$_SESSION['customer']['id'];
							if ($result = $mysqli->query($query)) {
								if($result->num_rows){
									echo '<div id="form_review_content" class="form_review">
									<strong style="color:#BF0000">'.language('_include/ajax/review','TITLE_ALREADY_ADDED').'</strong>
									</div>';
								}else{
									echo '<div id="form_review_content" class="form_review">
									<div>'.language('_include/ajax/review','TITLE_RATING').'</div>
									<div class="rating_star_big_X5">
									<div class="rating_star_big" onmouseover="rated_star(1,0);"  onmouseout="rated_star(1,0);" onclick="rated_star(1,1);" id="star_1" style="cursor:pointer"></div>
									<div class="rating_star_big" onmouseover="rated_star(2,0);"  onmouseout="rated_star(2,0);" onclick="rated_star(2,1);" id="star_2" style="cursor:pointer"></div>
									<div class="rating_star_big" onmouseover="rated_star(3,0);"  onmouseout="rated_star(3,0);" onclick="rated_star(3,1);" id="star_3" style="cursor:pointer"></div>
									<div class="rating_star_big" onmouseover="rated_star(4,0);"  onmouseout="rated_star(4,0);" onclick="rated_star(4,1);" id="star_4" style="cursor:pointer"></div>
									<div class="rating_star_big" onmouseover="rated_star(5,0);"  onmouseout="rated_star(5,0);" onclick="rated_star(5,1);" id="star_5" style="cursor:pointer"></div>
									</div>
								
								
									<div class="cb" style="margin-bottom: 5px;"></div>
									<form id="form_review_rating" name="form_review_rating">
									  <input name="rated" type="hidden" value="0" id="rated" />
									  <input name="id" type="hidden" value="'.$id.'" id="id" />
									  <div style="margin-bottom:5px">
										<label for="review_title">'.language('_include/ajax/review','LABEL_TITLE').'</label><br />
										<input type="text" name="review_title" id="review_title" class="text" style="width: 95%" />
									  </div>
									  <div style="margin-bottom:5px">
										<label for="review_review">'.language('_include/ajax/review','LABEL_COMMENTS').'</label><br />
										<textarea name="review_review" id="review_review" style="width:95%; height:100px"></textarea>
									  </div>
									  <div>
										<input type="checkbox" name="anonymous" id="anonymous" value="1" />
										<label for="anonymous">'.language('_include/ajax/review','LABEL_ANONYMOUS').'</label>
									  </div>
									  <div style="margin-top: 10px;">
											<input type="button" value="'.language('_include/ajax/review','BTN_SAVE').'" class="small_button" name="save" onclick="javascript:save_review();" />
										</div>
									</form>
									</div>';
								}
							}
							
						}
						
						echo pagination($page,$language);
				
				
				
			}
		}
	break;
	case 'save':	
		if ($_SESSION['customer']['id']) {
			$rated = $_POST['rated'];
			$title = $_POST['review_title'];
			$review = $_POST['review_review'];
			$anonymous = $_POST['anonymous'];
			$arr_save = array();
			
			if(!$rated or !is_numeric($rated)){
				$arr_save['rated'] = 1;
			}
			if(empty($title)){
				$arr_save['title'] = 1;
			}
			if(empty($review)){
				$arr_save['review'] = 1;
			}
			
			if(!sizeof($arr_save)){
				$approved = 0;
				$query = 'SELECT 
						orders.id
						FROM
						orders
						INNER JOIN
						orders_item
						ON
						orders.id = orders_item.id_orders
						INNER JOIN
						orders_item_product
						ON
						orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id_product = '.$id.'
						WHERE orders.id_customer = '.$_SESSION['customer']['id'];
						//echo $query;
				if ($result = $mysqli->query($query)) {
					if($result->num_rows){
						$approved = 1;
					}
				}
				
				$query = 'INSERT INTO 
							product_review
							SET id_product = '.$id.',
							id_customer = '.$_SESSION['customer']['id'].',
							title = "'.$mysqli->escape_string($title).'",
							review = "'.$mysqli->escape_string($review).'",
							anonymous = '.($anonymous?$anonymous:0).',
							language_code = "'.$_SESSION['customer']['language'].'",
							rated = '.$rated.',
							approved = '.$approved.',
							date_created = "'.date("Y-m-d H:i:s").'"';
				if (!$mysqli->query($query)) {
					$arr_save['error'] = language('_include/ajax/review','ALERT_ERROR');
				}else{
					$query = 'INSERT INTO 
							product_rating_count SET
							id_product = '.$id.',
							avg_rating = '.$rated.',
							total_rating = 1
							ON DUPLICATE KEY UPDATE
							total_rating = total_rating + 1,
							avg_rating = FLOOR((avg_rating + '.$rated.')/total_rating)';
					if (!$mysqli->query($query)) {
						$arr_save['error'] = language('_include/ajax/review','ALERT_ERROR');
					}else{
						$arr_save['id'] = $id;
						$arr_save['success'] = language('_include/ajax/review','ALERT_SUCCESS');
					}
				}
			}
			if(!(isset($is_product_page) && $is_product_page)) {
    			header('Content-Type: text/javascript; charset=UTF-8'); //set header
    			echo json_encode($arr_save); //display records in json format using json_encode
    			exit;
			}
		}else{
			echo 'false';
		}
	break;
}




?>