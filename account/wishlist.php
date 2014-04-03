<?php
include(dirname(__FILE__) . "/../_includes/config.php");


switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$success = trim($_GET['success']);
		if ($id_wishlist = (int)$_GET['id_wishlist']) {
			if (!$result_wishlist = $mysqli->query('SELECT 
			customer_wishlist.id,
			customer_wishlist.id_customer,
			customer.firstname,
			customer.lastname
			FROM 
			customer_wishlist
			INNER JOIN customer
			ON
			customer_wishlist.id_customer = customer.id
			WHERE
			customer_wishlist.id = "'.$mysqli->escape_string($id_wishlist).'" 
			'.(!$_SESSION['customer']['id']?'AND customer_wishlist.public = 1':''))) throw new Exception('An error occured while trying to get wishlist.'."\r\n\r\n".$mysqli->error);
			$row_wishlist = $result_wishlist->fetch_assoc();
			$result_wishlist->free();
			
			if (!$id_wishlist = $row_wishlist['id']) {
				header('Location: /404?error=invalid_wishlist');
				exit;
			}
			
			$wishlist_id_customer = $row_wishlist['id_customer'];
			$customer_name = $row_wishlist['firstname'] . ' ' . $row_wishlist['lastname'];
		} else if (!isset($_SESSION['customer']['id'])) {
			header('Location: /account/login');
			exit;
		}
		
		if (isset($_GET['task'])) {
			switch ($_GET['task']) {
				case 'update_sort_order':
					if (($wishlist_product = $_GET['wishlist_product']) && sizeof($wishlist_product)) {
						if (!$stmt_update = $mysqli->prepare('UPDATE
						customer_wishlist_product
						INNER JOIN
						customer_wishlist
						ON
						(customer_wishlist_product.id_customer_wishlist = customer_wishlist.id)
						SET
						customer_wishlist_product.sort_order = ?
						WHERE
						customer_wishlist_product.id = ?
						AND
						customer_wishlist.id_customer = ?')) throw new Exception('An error occured while trying to prepare update wishlist product statement.'."\r\n\r\n".$mysqli->error);						
						
						$i=0;
						foreach ($wishlist_product as $id_wishlist_product) {
							if (!$stmt_update->bind_param("iii", $i, $id_wishlist_product, $_SESSION['customer']['id'])) throw new Exception('An error occured while trying to bind params to update wishlist product statement.'."\r\n\r\n".$this->mysqli->error);			
							
							/* Execute the statement */
							if (!$stmt_update->execute()) throw new Exception('An error occured while trying to update wishlist product.'."\r\n\r\n".$this->mysqli->error);														
							
							++$i;
						}
						
						$stmt_update->close();
					}
				
					exit;
					break;	
			}
		}
		break;
	case 'POST':
		if (isset($_POST['_delete']) || isset($_POST['_delete2'])) {
			if (($wishlist_product = $_POST['wishlist_product']) && sizeof($wishlist_product)) {
				if (!$stmt_delete = $mysqli->prepare('DELETE FROM
				customer_wishlist_product				
				USING
				customer_wishlist_product
				INNER JOIN
				customer_wishlist
				ON
				(customer_wishlist_product.id_customer_wishlist = customer_wishlist.id)
				WHERE
				customer_wishlist_product.id = ?
				AND
				customer_wishlist.id_customer = ?')) throw new Exception('An error occured while trying to prepare delete wishlist product statement.'."\r\n\r\n".$mysqli->error);
								
				foreach ($wishlist_product as $id_wishlist_product) {
					if (!$stmt_delete->bind_param("ii", $id_wishlist_product, $_SESSION['customer']['id'])) throw new Exception('An error occured while trying to bind params to delete wishlist product statement.'."\r\n\r\n".$this->mysqli->error);			
					
					/* Execute the statement */
					if (!$stmt_delete->execute()) throw new Exception('An error occured while trying to delete wishlist product.'."\r\n\r\n".$this->mysqli->error);							
				}
				
				$stmt_delete->close();
				
				header('Location: ?success=delete_product');
				exit;
			} else {
				$error = 'no_product';	
			}
		}elseif (isset($_POST['_public'])) {
				
			if (!$mysqli->query('UPDATE
						customer_wishlist
						SET
						public = IF(public=1,0,1)
						WHERE
						customer_wishlist.id_customer = '.$_SESSION['customer']['id'])) {
							throw new Exception('An error occured while trying to activate account.'."\r\n\r\n".$mysqli->mysqli->error);	
						}
			header('Location: ?success=public');
			exit;
		
		}elseif (isset($_POST['_send_email'])) {
			$send_email_mail = $_POST['send_email_mail'];
			// validation rules
			$validation = array(
				'send_email_mail' => array(
					'required' => 1,
					'email' => 1,
				),
			);
			if (!sizeof($errors = validate_fields($_POST, $validation))) {
				
				include_mailer();
				
				// send email to customer with activation link
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->CharSet = 'UTF-8';
				
				// text only
				//$mail->IsHTML(false);
			
				$mail->SetFrom($_POST['send_email_customer_mail'], $_POST['send_email_customer_name']);
		
				$mail->AddAddress($send_email_mail);
				
				$mail->Subject = language('account/wishlist', 'TEXT_EMAIL_TITLE');
				
				$mail->AltBody = language('account/wishlist', 'TEXT_EMAIL_PLAIN',array(1=>$config_site['site_name'],2=>"http://".$_SERVER['HTTP_HOST']."/account/wishlist?id_wishlist=".$_POST['id_wishlist'])).get_company_signature(0);
				
				$mail->MsgHTML(language('account/wishlist', 'TEXT_EMAIL_HTML',array(0=>$customer_name,1=>$config_site['site_name'],2=>"http://".$_SERVER['HTTP_HOST']."/account/wishlist?id_wishlist=".$_POST['id_wishlist'])).get_company_signature(1));
	
				$sendmail_failed = $mail->Send() ? 0:1;
				if(!$sendmail_failed){
					header('Location: ?success=send_mail');
					exit;
				}else{
					$error = 'send_mail';
				}
			}
		
		}
		break;	
}

if (!$id_wishlist && isset($_SESSION['customer']['id'])) {
	if (!$result_wishlist = $mysqli->query('SELECT 
	customer_wishlist.id,
	customer_wishlist.id_customer,
	customer_wishlist.public,
	customer.firstname,
	customer.lastname,
	customer.email
	FROM 
	customer_wishlist
	INNER JOIN customer
	ON
	customer_wishlist.id_customer = customer.id
	WHERE
	customer_wishlist.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'"
	ORDER BY 
	customer_wishlist.id ASC
	LIMIT 1')) throw new Exception('An error occured while trying to get wishlist.'."\r\n\r\n".$mysqli->error);
	$row_wishlist = $result_wishlist->fetch_assoc();
	$result_wishlist->free();
	
	$id_wishlist = $row_wishlist['id'];
	$wishlist_id_customer = $row_wishlist['id_customer'];
	$public = $row_wishlist['public'];
	$customer_name = $row_wishlist['firstname'] . ' ' . $row_wishlist['lastname']; 
	$customer_email = $row_wishlist['email']; 
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>
<script type="text/javascript" language="javascript">
jQuery(function() {
	<?php if ($wishlist_id_customer == $_SESSION['customer']['id']) { ?>
	jQuery( "#sortable" ).sortable({		
		update: function(event, ui) { 
			var products=[];
			jQuery("#form_wishlist :checkbox").each(function(){
				products.push("wishlist_product[]="+$(this).val());
			});
		
			// update position			
			jQuery.ajax({				
				url: "<?php echo $_SERVER['PHP_SELF']; ?>",
				data: products.join("&")+"&task=update_sort_order"
			});				
		}
	});
	jQuery( "#sortable" ).disableSelection();
	
	jQuery(".delete_product").on("click",function(){
		if (!confirm("<?php echo language('account/wishlist','LABEL_CONFIRM_DELETE'); ?>")) return false;
	});
	<?php } ?>
	
	<?php if (sizeof($errors)) { ?>
	jQuery( "#form_email_top" ).toggle();
	<?php } ?>
});
</script>
</head>
<body class="bv3">
<?php include("../_includes/template/top.php");?>
<div class="main-container">	
    <div class="breadcrumbs">   	
		<div class="container">
          <ul>
          	<li>
            	<a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<a href="/account"><?php echo language('global', 'BREADCRUMBS_YOUR_ACCOUNT');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<strong><?php echo language('global', 'BREADCRUMBS_MY_WISHLIST');?></strong>
            </li>
          </ul>
      	</div>            
    </div>
	<div class="main">
    <div class="container">
    <div class="main-content"> 

            <h2 class="subtitle"><?php echo ($_SESSION['customer']['id']?language('account/wishlist', 'TITLE_PAGE'):language('account/wishlist','TITLE_PAGE_PUBLIC',array(0=>$customer_name)));?></h2>
       
       	<?php
          $msg_success = '';	
          switch ($success) {
              case 'delete_product':
                  $msg_success = language('account/wishlist', 'SUCCESS_DELETE_PRODUCT');
                  break;
			case 'send_mail':
                  $msg_success = language('account/wishlist', 'SUCCESS_SEND_MAIL');
                  break;			
          }
          $msg_error = '';
          switch ($error) {
			case 'no_product':
				$msg_error = language('account/wishlist', 'ERRRO_NO_PRODUCT');
				break;
			case 'send_mail':
				$msg_error = language('account/wishlist', 'ERRRO_SEND_MAIL');
				break;	
		  }
        ?>
        <?php if(!empty($msg_success)) {?>
        <div class="messages">
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <ul><li><span><?php echo $msg_success?></span></li></ul>
          </div>
        </div>
        <?php }?>
        <?php if(!empty($msg_error)) {?>
        <div class="messages">
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <ul><li><span><?php echo $msg_error;?></span></li></ul>
          </div>
        </div>
        <?php }?>
        
        <p class="desc">
        	<?php language('account/wishlist', 'TITLE_PAGE_DESCRIPTION');?>
        	<?php echo ($public?'<br>'.language('account/wishlist', 'TITLE_PUBLIC', array(0=>"<strong>http://".$_SERVER['HTTP_HOST']."/account/wishlist?id_wishlist=".$id_wishlist."</strong>")):'');?>
        </p>  
        
 		<?php
                			
			if ($id_wishlist) {
				if (!$result_wishlist_product = $mysqli->query('SELECT 
				customer_wishlist_product.id,
				customer_wishlist_product.id_product,
				customer_wishlist_product.id_product_variant,
				product_description.name,
				product_description.short_desc,
				product_description.alias,
				IF(product_variant.id IS NOT NULL,GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", "),"") AS variant_name,
				product_image.filename,
				calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
				get_product_current_price(customer_wishlist_product.id_product,customer_wishlist_product.id_product_variant,"'.$mysqli->escape_string($_SESSION['customer']['id_customer_type']).'") AS sell_price,
				IF(product_rating_count.avg_rating IS NOT NULL,product_rating_count.avg_rating,0) AS average_rated,
				IF(product_rating_count.total_rating IS NOT NULL,product_rating_count.total_rating,0) AS total_rated
				FROM
				customer_wishlist_product
				INNER JOIN 
				(product CROSS JOIN product_description)
				ON
				(customer_wishlist_product.id_product = product.id AND product.id = product_description.id_product AND product_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'") 
				LEFT JOIN product_rating_count ON product.id = product_rating_count.id_product
				LEFT JOIN
				product_image
				ON
				(product.id = product_image.id_product AND product_image.cover = 1 AND product_image.force_crop = 0)
				LEFT JOIN
				(product_variant
				CROSS JOIN product_variant_option 
				CROSS JOIN product_variant_group 
				CROSS JOIN product_variant_group_option 
				CROSS JOIN product_variant_group_option_description
				CROSS JOIN product_variant_group_description)						
				ON
				(customer_wishlist_product.id_product_variant = product_variant.id 
				AND product_variant.id = product_variant_option.id_product_variant 
				AND product_variant_option.id_product_variant_group = product_variant_group.id 
				AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
				AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option
				AND product_variant_group_option_description.language_code = product_description.language_code
				AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
				AND product_variant_group_description.language_code = product_description.language_code)
				
				WHERE
				customer_wishlist_product.id_customer_wishlist = "'.$mysqli->escape_string($id_wishlist).'"
				GROUP BY 
				customer_wishlist_product.id
				ORDER BY 
				customer_wishlist_product.sort_order ASC,
				customer_wishlist_product.id ASC')) throw new Exception('An error occured while trying to get wishlist products.'."\r\n\r\n".$mysqli->error);
				
				if ($result_wishlist_product->num_rows) {
					echo '<form id="form_wishlist" method="post" enctype="multipart/form-data">
					
					<div style="margin-bottom:5px;" class="noprint">';
					
					if ($wishlist_id_customer == $_SESSION['customer']['id']) {
						echo '
						
						<div style="margin:0px; position:relative" class="button_regular">
							<input type="submit" name="_delete" value="'.language('account/wishlist', 'BTN_REMOVE').'" class="fr button" style="margin-left:10px;" />
							<input type="submit" name="_public" value="'.language('account/wishlist', ($public?'BTN_PRIVATE':'BTN_PUBLIC')).'" class="button button-inverse fr" />
							<input type="button" value="'.language('account/wishlist', 'BTN_EMAIL').'" class="regular button" onclick="javascript:jQuery(\'#form_email_top\').toggle(\'fast\');" '.(!$public ? 'style="visibility:hidden;"':'').' />
							<div style="display:none; position:absolute; top: 40px; left: 0px; padding: 8px; background-color: #D3D3D3; border: solid 1px #707070;" id="form_email_top">
								<div>'.language('account/wishlist', 'LABEL_EMAIL').' </div>
								<div style="margin-bottom: 10px;">
								<input type="text" name="send_email_mail" size="50" '.($errors['send_email_mail'] ? 'class="error"':'').' value="'.$send_email_mail.'" />
								<br /><span class="error">'.$errors['send_email_mail'].'</span>
								<input type="hidden" name="send_email_customer_mail" value="'.$customer_email.'" />
								<input type="hidden" name="send_email_customer_name" value="'.$customer_name.'" />
								<input type="hidden" name="id_wishlist" value="'.$id_wishlist.'" />
								</div>
								<div class="button_regular" style="float:left;">
									<input type="submit" name="_send_email" value="'.language('account/wishlist', 'BTN_SEND').'" class="regular button button-inverse" />
								</div>
								<div class="fl" style="margin-right:5px;"></div>
								<div class="button_regular" style="float:right;">
									<input type="button" value="'.language('account/wishlist', 'BTN_CLOSE').'" class="regular button" onclick="javascript:jQuery(\'#form_email_top\').toggle(\'fast\');" />
								</div>
								<div class="cb"></div>
							</div>
						</div>';
					}

					echo '
						
						<!--<div style="margin:0px; margin-right:5px;" class="button_regular">
							<input type="button" value="'.language('account/wishlist', 'BTN_PRINT').'" class="regular" onclick="javascript:window.print();" />
						</div>-->
						<div class="cb"></div>
					</div>
					
					<ul id="sortable" style="width:100%;padding-left: 0;">
					';
					
					while ($row_wishlist_product = $result_wishlist_product->fetch_assoc()) {
						$cover_image = $row_wishlist_product['filename'] ? '/images/products/thumb/'.$row_wishlist_product['filename']:get_blank_image('thumb');
						
						echo '<li style="padding:10px; margin-bottom:5px; border:1px solid #cccccc; background-color:#FFFFFF; overflow:hidden">
						<div class="fl" style="margin-right:5px;"><img src="'.$cover_image.'" width="'.$images_sizes['thumb_width'].'" height="'.$images_sizes['thumb_height'].'" style="display:block;border:1px solid #ddd;" /></div>
						
						<div class="fl" style="width: 60%;">
							<a href="'.$url_prefix.'product/'.$row_wishlist_product['alias'].($row_wishlist_product['id_product_variant'] ? '?id_product_variant='.$row_wishlist_product['id_product_variant']:'').'">'.$row_wishlist_product['name'].'</a>'
							.($row_wishlist_product['variant_name'] ? '<div style="margin-top:5px;">'.$row_wishlist_product['variant_name'].'</div>':'')
							.($row_wishlist_product['short_desc'] ? '<div style="margin-top:5px;">'.$row_wishlist_product['short_desc'].'</div>':'')
							.'
						</div>';
						
						if ($wishlist_id_customer == $_SESSION['customer']['id']) {										
						echo '
						<div class="fr noprint" style="background-color:#F2F2F2; width:25px;">
							<div style="padding: 5px;">
								<input type="checkbox" value="'.$row_wishlist_product['id'].'" name="wishlist_product[]" />
							</div>
							<div class="cb"></div>
						</div>';
						}
						
						echo '
						<div class="fr" style="margin-right:12px; width:20%">								
							<div align="right" style="font-size:16px;" '.(($row_wishlist_product['price'] != $row_wishlist_product['sell_price']) ? 'class="special_price"':'').'>'.nf_currency($row_wishlist_product['sell_price']).'</div>
							
							<div class="fr" style="margin-top:10px;">
							<div class="rating-box">
				                <div class="rating" style="width:'.((int)$row_wishlist_product['average_rated']*100/5).'%"></div>
				                
				            </div>
				            
							</div>
						
					
						<div class="cb"></div>																		
						</li>
						';
					}
					
					echo '</ul>
					
					<div style="margin-bottom:5px;" class="noprint">';
					
					if ($wishlist_id_customer == $_SESSION['customer']['id']) {
						echo '
						<div style="margin:0px; float:right;" class="button_regular button_delete">
							<input type="submit" name="_delete" value="'.language('account/wishlist', 'BTN_REMOVE').'" class="button regular delete_product" />
						</div>';
					}

					echo '
						<!--<div style="margin:0px; margin-right:5px;" class="button_regular">
							<input type="button" value="'.language('account/wishlist', 'BTN_PRINT').'" class="regular" onclick="javascript:window.print();" />
						</div>-->
						<div class="cb"></div>
					</div></form>';
				} else {
					echo '<div>'.language('account/wishlist', 'NO_PRODUCTS').'</div>';
				}
			} else {
				echo '<div>'.language('account/wishlist', 'NO_PRODUCTS').'</div>';	
			}
		?>
        </div>
		<div class="cb"></div>
	</div>  
	</div>
	</div>	      
</div>
<?php include(dirname(__FILE__) . "/../_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>