<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

if(count($cart->get_products()) == 0) {
  header("Location: /cart");
  exit;	
}

// Verify if shipping is choose
if(!$cart->shipping_validated && $config_site['enable_shipping']){
	header('Location: step_shipping?error=shipping_service');
	exit;	
}elseif(!$config_site['enable_shipping']){
	// Update Table Cart
	if (!$mysqli->query('UPDATE 
			cart
			SET
			shipping_gateway_company = "",
			shipping_service = "",
			shipping = 0,
			shipping_estimated = "",
			free_shipping = 0,
			shipping_validated = 0
			WHERE
			id = "'.$mysqli->escape_string($cart->id).'"
			LIMIT 1')){
		throw new Exception('An error occured while trying to update cart.'."\r\n\r\n".$mysqli->mysqli->error);	
		//$cart->init();	
	}
	$cart->calculate_subtotal();
	$cart->calculate_taxes();
	$cart->calculate_total();
	$cart->init();
}elseif($cart->shipping_validated && $config_site['enable_shipping']){
	if(!$cart->free_shipping && !$cart->local_pickup){
		$shipping_ok = 0;
		foreach($_SESSION['customer']['verify_shipping_price_check'] as $value){
			if(number_format($value,2) == number_format($cart->shipping,2)){
				$shipping_ok = 1;
				break;
			}
		}
		if(!$shipping_ok){
			header('Location: step_shipping?error=shipping_service');
			exit;
		}
	}
}


if($_GET['error']){
	$error = $_GET['error'];
}else{
	$error = '';	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/../_includes/template/header.php");?>
</head>
<body>
<?php
	$returnToCart = true;//sert pour la supression depuis le mini panier 
	include(dirname(__FILE__) . "/../_includes/template/top.php");
?>
<div class="main-container">
<div class="main">	
    <div class="container">
    <div class="main-content withblock">
    	<?php
      // Indicate wich Step to follow, to completed the transaction
      $step = 3;
      if(!$config_site['enable_shipping'])$step = 2;
      include(dirname(__FILE__) . "/../_includes/step.php");
      ?>
		<?php 
		$error_msg='';
        switch ($error) {
        case 'accept_term_condition':
        $error_msg = language('cart/step_validation', 'ERROR_ACCEPT_TERM');
        break;        
        }?>
        <?php if($error_msg != '') {?>
        <div class="messages">
        	<div class="alert alert-danger">
        		<button type="button" class="close" data-dismiss="alert">×</button>
        		<ul><li><span><?php echo $error_msg;?></span></li></ul>
        	</div>
        </div>    
        <?php }?>
        <div id="address">
            <div id="billing" class="col-sm-6" style="padding:0 5px 0 0;margin-bottom: 20px;">
                <div class="op_block_title"><?php echo language('global', 'TITLE_BILLING_ADDRESS');?></div>
                <div class="op_block_detail">
                    <?php
                    echo 
                    ($cart->billing_company?'<strong>'.$cart->billing_company.'</strong><br />':'').
                    ($cart->billing_company?$cart->billing_firstname. ' ' .$cart->billing_lastname.'<br />':'<strong>'.$cart->billing_firstname. ' ' .$cart->billing_lastname.'</strong><br />').
                    $cart->billing_address.'<br />'.
                    $cart->billing_city.
                    ($cart->billing_state?' '.$cart->billing_state:'').'<br />'.$cart->billing_country.' '.strtoupper($cart->billing_zip);
                    ?> 
                </div>  
            </div>
           	<?php
		   	if($config_site['enable_shipping']){?>
            <div id="shipping" class="col-sm-6" style="padding:0 0 0 5px;;margin-bottom: 20px;">
                <div class="op_block_title"><?php echo language('global', 'TITLE_SHIPPING_ADDRESS');?></div>
                <div class="op_block_detail">
                    <?php
                    if(!$cart->local_pickup){
                    echo 
                    ($cart->shipping_company?'<strong>'.$cart->shipping_company.'</strong><br />':'').
                    ($cart->shipping_company?$cart->shipping_firstname. ' ' .$cart->shipping_lastname.'<br />':'<strong>'.$cart->shipping_firstname. ' ' .$cart->shipping_lastname.'</strong><br />').
                    $cart->shipping_address.'<br />'.
                    $cart->shipping_city.
                    ($cart->shipping_state?' '.$cart->shipping_state:'').'<br />'.$cart->shipping_country.' '.strtoupper($cart->shipping_zip);
                    }else{
                    echo '<div style="margin-bottom:10px; font-weight: bold; font-size: 14px;">'.language('global', 'TITLE_LOCAL_PICKUP').'</div>'; 
					echo $cart->local_pickup_address?
                    $cart->local_pickup_address.'<br />'.
                    $cart->local_pickup_city.' '.$cart->local_pickup_state.'<br />'.$cart->local_pickup_country.' '.strtoupper($cart->local_pickup_zip):'';  
                    }
                    ?> 
                </div>
            </div>
            <?php }?>
            
            <?php include(dirname(__FILE__) . "/../_includes/checkout_cart.php");?>
            
            <div id="step_validation_terms" style="display:none;clear:both;border:1px solid #dcdcdc; padding:0 15px;">
				<?php
                $query = 'SELECT 
                            cmspage_description.name,
                            cmspage_description.description
                            FROM cmspage
                            INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'"
                            WHERE cmspage.active = 1 AND cmspage.id = 24';
                
                if ($result = $mysqli->query($query)) {
                    if($result->num_rows){
                        $row = $result->fetch_assoc();
                        $description = $row['description'];
                        $name = $row['name'];
                    }
                }
                
                echo '<h1>'.$name.'</h1>';
                echo '<div style="font-size:10px">'.$description.'</div>';
                ?>
                <br>
            </div>
            <p>&nbsp;</p>
            <div id="step_validation_policy" style="display:none;clear:both;border:1px solid #dcdcdc; padding:0 15px;">
				<?php
                $query = 'SELECT 
                            cmspage_description.name,
                            cmspage_description.description
                            FROM cmspage
                            INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'"
                            WHERE cmspage.active = 1 AND cmspage.id = 25';
                
                if ($result = $mysqli->query($query)) {
                    if($result->num_rows){
                        $row = $result->fetch_assoc();
                        $description = $row['description'];
                        $name = $row['name'];
                    }
                }
                
                echo '<h1>'.$name.'</h1>';
                echo '<div style="font-size:10px">'.$description.'</div>';
                ?>
            </div>            
            <br />
            <form method="post" action="step_payment">
                <div>
                    <div style="float:left;margin-top: 6px;"><input name="accept_term_condition" type="checkbox" value="1" id="accept_term_condition" /></div>
                    <div style="margin-left: 10px; float:left;"><label for="accept_term_condition"><strong  class="<?php echo($error=='accept_term_condition'?' title_bg_text_box_error':'');?>"><?php echo language('cart/step_validation', 'LABEL_ACCEPT_TERM');?></strong></label></div>
                    <div class="cb"></div>
                </div>
                <div style="clear:both; padding-top:15px;">
                	<input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="button" name="btn_previous_step" onclick="document.location.href='<?php echo $config_site['enable_shipping']?'step_shipping':'/cart';?>'" />
                	<input type="submit" value="<?php echo language('global', 'BTN_NEXT_STEP');?>" class="button button-inverse" name="btn_next_step" style="float:right;"/>
                </div>
            </form>
            <div class="cb"></div>
        </div>        
    </div>
    </div>
    <div class="cb"></div>
</div>
</div>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>