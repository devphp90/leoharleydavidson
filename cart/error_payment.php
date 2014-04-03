<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");
include_once(dirname(__FILE__).'/../_includes/classes/SC_Order.php');

if ($id_orders = (int)$_GET['id_orders']) {
	$order = new SC_Order($mysqli);
	if (!$order->load($id_orders)) {
		exit('error invalid order id');
	}
	
	$transaction_details = $order->transaction_details ? unserialize(base64_decode($order->transaction_details)):array();
	//echo '<pre>'.print_r($transaction_details,1).'</pre>';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="none" />
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
    <div class="main-content withblock" style="overflow: hidden;">

     <div id="content" style="background-color:#FFF">
     	<div class="messages">
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <ul><li><span><?php echo language('cart/error_payment','MESSAGE_TRANSACTION_ERROR');?></span></li></ul>
          </div>
        </div>    	
        
        <div class="button_previous_step"><input type="button" value="<?php echo language('cart/error_payment','BTN_RETRY');?>" class="previous_step button" name="btn_previous_step" onclick="document.location.href='/cart/step_validation'" /></div>
        <div class="fr"><a href="print_version?id_orders=<?php echo $id_orders; ?>" target="_blank"><div class="ico_printer" style="margin-bottom:3px;"><span class="icon"><?php echo language('global','BTN_PRINT_VERSION');?></span></div></a></div><div class="cb"></div>
        
        <?php 
		$error_page = 1;
		include("../_includes/template/print_include.php");?>
    </div>
    </div>
</div>
</div>
</div>
<?php include(dirname(__FILE__) . "/../_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>