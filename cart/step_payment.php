<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

if(count($cart->get_products()) == 0) {
  header("Location: /cart");
  exit;	
}

if(!isset($_POST['accept_term_condition']) && !isset($_GET['SCtrnApproved']) && !isset($_POST['_add_gift_certificate']) && !isset($_GET['_remove_gift_certificate']) && !isset($_POST['language_main_site'])){
	header('Location: step_validation?error=accept_term_condition');
	exit;	
}else if(isset($_GET['SCtrnApproved']) && !$_GET['SCtrnApproved']){
	if(isset($_GET['erreur_empty_field'])){
		$error = 'empty';
		$trnCardOwner = $_GET['trnCardOwner'];
		$trnCardNumber = $_GET['trnCardNumber'];
		$trnExpMonth = $_GET['trnExpMonth'];
		$trnExpYear = $_GET['trnExpYear'];
		$trnCardCvd = $_GET['trnCardCvd'];
		$trnComments = $_GET['trnComments'];
		$payment_method = $_GET['payment_method'];
	}
	
} else if (isset($_POST['_add_gift_certificate'])) {
	if ($code = trim($_POST['gift_card_code'])) {
		$cart->add_gift_certificate($code);
		
		if (sizeof($cart->messages)) $errors_fields['gift_card_code'] = implode('<br />',$cart->get_messages());
	} else {
		$errors_fields['gift_card_code'] = language('cart/step_payment', 'ERROR_GIFT_CERTIFICATE');
	}
} else if (isset($_GET['_remove_gift_certificate'])) {
	if ($id_cart_gift_certificate = (int)$_GET['id_cart_gift_certificate']) {
		$cart->del_gift_certificate($id_cart_gift_certificate);
	}	
}

// calculate grand total
$cart->calculate_total();

//Payment Gateway
require(dirname(__FILE__) . "/../_includes/transaction/current_payment_gateway.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="none" />
<meta http-equiv="Pragma" content="no-cache">
<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/../_includes/template/header.php");?>
<link rel="stylesheet" href="/includes/js/jquery/etalage/_fancybox_plugin/jquery.fancybox-1.3.4.css">
<script type="text/javascript" src="/includes/js/jquery/etalage/_fancybox_plugin/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" language="javascript">
jQuery(function(){	
	//Preload image
	jQuery.fn.preload = function() {
		this.each(function(){
			jQuery('<img/>')[0].src = this;
		});
	}
	jQuery(['<?php echo ($_SERVER['HTTPS'] == 'on' ? 'https://'.$_SERVER['HTTP_HOST']:'http://'.$_SERVER['HTTP_HOST']).'/_images/ajax-loader.gif';  ?>']).preload();
	
	// select payment method
	jQuery(".select_payment_method").on("click",function(){
		var payment_method = jQuery(this).val();		
		jQuery(".payment_methods").hide();
		jQuery("#payment_method_form_"+payment_method).show();	
		jQuery(".op_block_detail").removeClass("title_bg_text_box_error");
		jQuery(".op_block_detail .error").removeClass("error");		
	});
	
	<?php echo (isset($payment_method) && $payment_method == 0)? 'jQuery("#payment_method_form_0").show();':''; ?>
});	

function please_wait_display(form_id){
	/*jQuery.fancybox({
			'overlayShow'	:	true,
			'modal'			: 	true,
			'autoScale'		:	true,
			'hideOnOverlayClick'	:	false,
			'hideOnContentClick'	:	false,
			'showCloseButton'		:	false,
			'showNavArrows'			:	false,
			'enableEscapeButton'	:	false,
			'content'		:	'<div style="padding:3px; width:180px; text-align: center;"><img src="<?php echo ($_SERVER['HTTPS'] == 'on' ? 'https://'.$_SERVER['HTTP_HOST']:'http://'.$_SERVER['HTTP_HOST']).'/_images/ajax-loader.gif';  ?>" /><div style="margin-top:5px; font-size:18px; text-transform:uppercase;"><?php echo htmlspecialchars(language('global', 'MESSAGE_PLEASE_WAIT')); ?></div></div>'
	});*/
	// Use to give time to show image please wait
	setTimeout('document.forms["'+form_id+'"].submit();', 200);
}
</script>
<style>
form.payment_methods {
	clear:both;
	padding-top: 15px;
}
</style>
</head>
<body>
<?php 
	$returnToCart = true;//sert pour la supression depuis le mini panier
	include(dirname(__FILE__) . "/../_includes/template/top.php");
?>
<div class="main-container">
  <div class="main">
    <div class="container">
      <div class="main-content withblock" style="overflow: hidden;">
        <?php
		// Indicate wich Step to follow, to completed the transaction
		$step = 4;
		if(!$config_site['enable_shipping'])$step = 3;
		include(dirname(__FILE__) . "/../_includes/step.php");
		?>
        <?php
        $error_msg = ''; 
		switch ($error) {
			case 'empty':
				$error_msg = language('cart/step_payment', 'ERROR_EMPTY_FIELD');
				break;
		}?>
        <?php if($error_msg != '') {?>
        <div class="messages">
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <ul>
              <li><span><?php echo $error_msg;?></span></li>
            </ul>
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
          <?php if($config_site['enable_shipping']){?>
          <div id="shipping" class="col-sm-6" style="padding:0 0 0 5px;;margin-bottom: 20px;">
            <div class="op_block_title"><?php echo language('global', 'TITLE_SHIPPING_ADDRESS');?></div>
            <div class="op_block_detail">
              <?php
			   if(!$cart->local_pickup){
				   echo 
						($cart->shipping_company?'<strong>'.$cart->shipping_company.'</strong><br />':'').
						($cart->shipping_company?$cart->shipping_firstname. ' ' .$cart->shipping_lastname.'<br />':'<strong>'.$cart->shipping_firstname. ' ' .$cart->billing_lastname.'</strong><br />').
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
          <div class="cb"></div>
          <div class="col-sm-12" style="padding:0;margin-bottom: 20px;">
            <form action="step_payment" method="post">
              <div class="op_block_title" style="overflow: hidden;"><?php echo language('cart/step_payment','LABEL_GIFT_CERTIFICATES'); ?></div>
              <div class="op_block_detail" style="padding-bottom:5px; margin-bottom:0">
                <?php if(isset($errors_fields['gift_card_code'])) {?>
                <div class="messages">
                  <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <ul>
                      <li><span><?php echo $errors_fields['gift_card_code'];?></span></li>
                    </ul>
                  </div>
                </div>
                <?php }?>
                <div style="margin-bottom:5px"> <?php echo language('cart/step_payment','LABEL_CODE'); ?>
                  <input type="text" name="gift_card_code" value="" style="width:200px;" maxlength="50" />
                  <input type="submit" value="<?php echo language('global','BTN_ADD_NEW'); ?>" class="btn button-inverse" name="_add_gift_certificate" />
                </div>
              </div>
              <br>
              <?php 
					if (sizeof($gift_certificates = $cart->get_gift_certificates())) {
						foreach ($gift_certificates as $row_gift_certificate) {
							echo '<div style="margin-bottom:5px;">'.nf_currency($row_gift_certificate['amount']).($row_gift_certificate['amount_left'] > 0 ? ' ('.nf_currency($row_gift_certificate['amount_left']).' '.language('cart/step_payment','LABEL_LEFT').')':'').' - '.$row_gift_certificate['code'].' (<a href="?_remove_gift_certificate=1&id_cart_gift_certificate='.$row_gift_certificate['id'].'">'.language('cart/step_payment','LINK_DELETE').'</a>)</div>';
						}
					} 
					?>
            </form>
          </div>
          <div id="payment" class="col-sm-12" style="padding:0;margin-bottom: 20px;">
            <div class="op_block_title"><?php echo language('cart/step_payment', 'TITLE_PAYMENT');?></div>
            <div class="op_block_detail">
              <div>
                <div class="col-sm-6">
                  <?php 
                        // if payment is enabled
                        if($config_site['enable_payment'] && $cart->grand_total > 0){
                        ?>
                  <div class="op_block_title"><?php echo language('cart/step_payment', 'LABEL_PAYMENT_METHOD');?></div>
                  <?php if (sizeof($available_payment_methods)) { ?>
                  <div class="op_block_detail<?php echo($error=='empty'?' title_bg_text_box_error':'');?>">
                    <?php 
                            // cc 
                            if (isset($available_payment_methods[0])) { ?>
                      <div style="margin-bottom:5px;overflow:hidden;clear:both;">
                        <div class="fl" style="margin-top:5px; margin-right:5px;">
                          <input name="payment_method" class="select_payment_method"  type="radio" value="0" id="payment_method_0" <?php echo isset($payment_method) && $payment_method == 0 ? 'checked="checked"':''; ?> style="margin:0; padding:0;" />
                        </div>
                        <div class="fl">
                          <label for="payment_method_0" style="width:300px">
                            <?php
                              $query_cc = 'SELECT 
                                        image,
                                        name
                                        FROM 
                                        config_credit_card
                                        WHERE active = 1';
                              //echo $query;			
                              //echo $query;'.(sizeof($where_display_rating) ? ' WHERE '.implode(' AND ',$where_display_rating):'').'
                              if ($result_cc = $mysqli->query($query_cc)) {
                                  if($result_cc->num_rows){
                                      while($row_cc = $result_cc->fetch_assoc()){
                                          echo '<img src="/admin/images/'.$row_cc['image'].'" alt="'.$row_cc['name'].'" height="23" class="img_credit_card" />&nbsp;';
                                      }
                                  }
                              }
                              ?>
                          </label>
                        </div>
                        <div class="cb"></div>
                      </div>
                      <?php 
                            } 
                            
                            // paypal
                            if (isset($available_payment_methods[4])) {
                            ?>
                    <div style="margin-bottom:5px;overflow:hidden;clear:both;">
                      <div class="fl" style="margin-top:5px; margin-right:5px;">
                        <input name="payment_method" class="select_payment_method" type="radio" value="4" id="payment_method_4" />
                      </div>
                      <div class="fl">
                        <label for="payment_method_4" style="padding:0;margin:0;float:none; width:auto;">
                        <img src="/admin/images/cc/pp.png" style="margin-right:7px;" />
                        <div style="margin-top:2px;font-size:11px; font-family: Arial, Verdana;"><?php echo language('cart/step_payment', 'LABEL_PAYPAL_MESSAGE');?></div>
                        </label>
                      </div>
                      <div class="cb"></div>
                    </div>
                    <?php
                            }					
                            
        
                            // cash
                            if (isset($available_payment_methods[5])) {
                            ?>
                    <div style="margin-bottom:5px;overflow:hidden;clear:both;">
                      <div class="fl" style="margin-top:8px; margin-right:5px;">
                        <input name="payment_method" class="select_payment_method" type="radio" value="5" id="payment_method_5" />
                      </div>
                      <div class="fl">
                        <label for="payment_method_5" style="padding:0;margin:0;float:none; width:auto;">
                        <div class="fl" style="padding-top:5px;"><?php echo language('cart/step_payment', 'LABEL_CASH');?></div>
                        <div class="cb"></div>
                        </label>
                      </div>
                      <div class="cb"></div>
                    </div>
                    <?php
                            }		
                            
                            // cheque
                            if (isset($available_payment_methods[2])) {
                            ?>
                    <div style="margin-bottom:5px;overflow:hidden;clear:both;">
                      <div class="fl" style="margin-top:8px; margin-right:5px;">
                        <input name="payment_method" class="select_payment_method" type="radio" value="2" id="payment_method_2" />
                      </div>
                      <div class="fl">
                        <label for="payment_method_2" style="padding:0;margin:0;float:none; width:auto;">
                        <div class="fl" style="padding-top:5px;"><?php echo language('cart/step_payment', 'LABEL_CHEQUE');?></div>
                        <div class="cb"></div>
                        </label>
                      </div>
                      <div class="cb"></div>
                    </div>
                    <?php
                            }									
                            ?>
                  
                  <?php 
                            } 
                            
                            // cc
                            if (isset($available_payment_methods[0])) {
                        ?>
                  <form action="/_includes/transaction/<?php echo $payment_gateway_page;?>" method="post" id="payment_method_form_0" name="payment_method_form_0" class="payment_methods" style="display:none;">
                    <input name="payment_method" type="hidden" value="0" />
                    <ul>
                      <li>
                        <label for="trnCardNumber"><?php echo language('cart/step_payment', 'LABEL_CARD_NUMBER');?></label>
                        <input name="trnCardNumber" value="" maxlength="17" id="trnCardNumber" autocomplete="off" type="text"<?php echo(($error and !$trnCardNumber)?' class="error"':'');?>  style="width:220px" />
                      </li>
                      <li>
                        <label for="trnExpMonth"><?php echo language('cart/step_payment', 'LABEL_EXP_DATE');?></label>
                        <select name="trnExpMonth" id="trnExpMonth"<?php echo(($error and !$trnExpMonth)?' class="error"':'');?> style="margin-left:0;">
                          <option selected="selected" value=""><?php echo language('cart/step_payment', 'LABEL_MONTH');?></option>
                          <option value="01"<?php echo($trnExpMonth=='01'?'selected="selected"':'');?>>01</option>
                          <option value="02"<?php echo($trnExpMonth=='02'?'selected="selected"':'');?>>02</option>
                          <option value="03"<?php echo($trnExpMonth=='03'?'selected="selected"':'');?>>03</option>
                          <option value="04"<?php echo($trnExpMonth=='04'?'selected="selected"':'');?>>04</option>
                          <option value="05"<?php echo($trnExpMonth=='05'?'selected="selected"':'');?>>05</option>
                          <option value="06"<?php echo($trnExpMonth=='06'?'selected="selected"':'');?>>06</option>
                          <option value="07"<?php echo($trnExpMonth=='07'?'selected="selected"':'');?>>07</option>
                          <option value="08"<?php echo($trnExpMonth=='08'?'selected="selected"':'');?>>08</option>
                          <option value="09"<?php echo($trnExpMonth=='09'?'selected="selected"':'');?>>09</option>
                          <option value="10"<?php echo($trnExpMonth=='10'?'selected="selected"':'');?>>10</option>
                          <option value="11"<?php echo($trnExpMonth=='11'?'selected="selected"':'');?>>11</option>
                          <option value="12"<?php echo($trnExpMonth=='12'?'selected="selected"':'');?>>12</option>
                        </select>
                        <select name="trnExpYear" id="trnExpYear"<?php echo(($error and !$trnExpYear)?' class="error"':'');?>>
                          <option selected="selected" value=""><?php echo language('cart/step_payment', 'LABEL_YEAR');?></option>
                          <?php
                              for($x=0;$x<10;$x++){
                                echo '<option value="'.(date('y')+$x).'"'.($trnExpYear==(date('y')+$x)?'selected="selected"':'').'>'.(date('Y')+$x).'</option>';
                              }
                              ?>
                        </select>
                      </li>
                      <li>
                        <label><?php echo language('cart/step_payment', 'LABEL_CID');?></label>
                        <input name="trnCardCvd" maxlength="5" id="trnCardCvd" style="width:60px" type="text" <?php echo(($error and !$trnCardCvd)?' class="error"':'');?> />
                      </li>
                      <li>
                        <label><?php echo language('cart/step_payment', 'LABEL_FULL_NAME');?></label>
                        <input name="trnCardOwner" maxlength="100" id="trnCardOwner" style="width:220px" type="text"<?php echo(($error and !$trnCardOwner)?' class="error"':'');?> value="<?php echo $trnCardOwner;?>" />
                      </li>
                      <li>
                        <label><?php echo language('cart/step_payment', 'LABEL_COMMENTS');?></label><br />
                        <textarea name="trnComments" id="trnComments" style="width:100%; height:60px; margin:0;"><?php echo $trnComments;?></textarea>
                      </li>
                    </ul>
                    <?php echo language('cart/step_payment','MESSAGE_PLACE_ORDER'); ?>
                    <div style="margin-top:20px">
                      <input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="button" name="btn_previous_step" onclick="document.location.href='step_validation'" />
                      <div style="float:right">
                      <input type="button" value="<?php echo language('global', 'BTN_CHECKOUT');?>" class="button button-inverse" name="submit_order" onclick="javascript:please_wait_display(this.form.id);" /></div><div style="float:right" class="icon-padlock"></div>
                      <div class="cb"></div>
                    </div>
                  </form>
                  <?php		
                            }
                            
                            // paypal
                            if (isset($available_payment_methods[4])) {
                        ?>
                  <form action="/_includes/transaction/expresscheckout.php" method="post" id="payment_method_form_4" name="payment_method_form_4" class="payment_methods" style="display:none;">
                    <input name="payment_method" type="hidden" value="4" />
                    <div style="margin-top:20px;">
                      <input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="button" name="btn_previous_step" onclick="document.location.href='step_validation'" />
                      <input type="image"  style="float:right;" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" border="0" value="0" />
                    </div>
                  </form>
                  <?php		
                            }					
                            
                            // cash
                            if (isset($available_payment_methods[5])) {
                        ?>
                  <form action="/_includes/transaction/pay.php" method="post" id="payment_method_form_5" name="payment_method_form_5" class="payment_methods" style="display:none;">
                    <input name="payment_method" type="hidden" value="5" />
                    <?php echo language('cart/step_payment','MESSAGE_PLACE_ORDER'); ?>
                    <div style="margin-top:20px">
                      <input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="button" name="btn_previous_step" onclick="document.location.href='step_validation'" />
                      <input type="button" style="float:right;" value="<?php echo language('global', 'BTN_CHECKOUT');?>" class="button button-inverse" name="submit_order" onclick="javascript:please_wait_display(this.form.id);" />
                      <div class="cb"></div>
                    </div>
                  </form>
                  <?php							
                            }					
                            
                            // cheque
                            if (isset($available_payment_methods[2])) {
                        ?>
                  <form action="/_includes/transaction/pay.php" method="post" id="payment_method_form_2" name="payment_method_form_2" class="payment_methods" style="display:none;">
                    <input name="payment_method" type="hidden" value="2" />
                    <strong><?php echo language('cart/step_payment','MESSAGE_CHECK_PAYABLE_TO'); ?></strong>
                    <p><?php echo nl2br($available_payment_methods[2]); ?></p>
                    <?php echo language('cart/step_payment','MESSAGE_PLACE_ORDER'); ?>
                    <div style="margin-top:20px">
                      <input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="button" name="btn_previous_step" onclick="document.location.href='step_validation'" />
                      <input type="button" style="float:right;" value="<?php echo language('global', 'BTN_CHECKOUT');?>" class="button button-inverse" name="submit_order" onclick="javascript:please_wait_display(this.form.id);" />
                      <div class="cb"></div>
                    </div>
                  </form>
                  <?php							
                            }?>
                            
                            </div>					
                            
                      <?php	  } else {
                        ?>
                  <form action="/_includes/transaction/pay.php" method="post" id="payment_method_form_3" name="payment_method_form_3" class="payment_methods">
                    <input name="payment_method" type="hidden" value="3" />
                    <div style="margin-bottom:20px"><?php echo language('cart/step_payment','MESSAGE_PLACE_ORDER'); ?></div>
                    <div class="fl">
                      <input type="button" value="<?php echo language('global', 'BTN_PREVIOUS_STEP');?>" class="button" name="btn_previous_step" onclick="document.location.href='step_validation'" />
                    </div>
                    <div class="fr">
                    <input type="button" style="float:right;" value="<?php echo language('global', 'BTN_CHECKOUT');?>" class="button button-inverse" name="submit_order" onclick="javascript:please_wait_display(this.form.id);"/>
                    </div>
                    <div class="cb"></div>
                  </form>
                  <?php	
                        }
                        ?>
                </div>
                <div class="col-sm-6">
                  <div class="op_block_title"><?php echo language('cart/step_payment', 'LABEL_CONTACT_US');?></div>
                  <div class="op_block_detail"><?php echo language('cart/step_payment', 'LABEL_CONTACT_US_DESCRIPTION');?> <strong><?php echo $config_site['company_company'];?></strong><br />
                    <?php echo $config_site['company_address']?' ' . $config_site['company_address']:'';?> <?php echo $config_site['company_city']?'<br />' . $config_site['company_city']:'';?>
                    <?php
                            $country_name = '';
                            $state_name = ''; 
                            if($config_site['company_country_code']){
                                $query = 'SELECT 
                                    country_description.name
                                    FROM country_description
                                    WHERE country_description.country_code = "'.$config_site['company_country_code'].'" AND country_description.language_code = "'.$_SESSION['customer']['language'].'"';
                            
                                    if ($result = $mysqli->query($query)) {
                                        $row = $result->fetch_assoc();
                                        $country_name = $row['name'];
                                    }
                            }
                            if($config_site['company_state_code']){
                                $query = 'SELECT 
                                    state_description.name
                                    FROM state_description
                                    WHERE state_description.state_code = "'.$config_site['company_state_code'].'" AND state_description.language_code = "'.$_SESSION['customer']['language'].'"';
                            
                                    if ($result = $mysqli->query($query)) {
                                        $row = $result->fetch_assoc();
                                        $state_name = $row['name'];
                                    }
                            }
                            echo $state_name?' ' . $state_name:'';?>
                    <?php echo $country_name?' ' . '<br />'.$country_name:'';?> <?php echo $config_site['company_zip']?' ' . $config_site['company_zip']:'';?>
                    <div>&nbsp;</div>
                    <?php echo $config_site['company_telephone']?'<strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_T').'</strong> ' . $config_site['company_telephone']:'';?> <?php echo $config_site['company_fax']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_F').'</strong> ' . $config_site['company_fax']:'';?> <?php echo $config_site['company_email']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_E').'</strong> <a href="mailto:' . $config_site['company_email'].'">' . $config_site['company_email'] . '</a>':'';?> </div>
                </div>
              </div>
            </div>
          </div>
          <div>
            <?php 
	$step = 'payment';
	include(dirname(__FILE__) . "/../_includes/checkout_cart.php");?>
            <p>&nbsp;</p>
          </div>
        </div>
      </div>
    </div>
    <div class="cb"></div>
  </div>
</div>
<?php include(dirname(__FILE__) . "/../_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>