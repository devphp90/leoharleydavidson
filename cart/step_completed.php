<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");
include_once(dirname(__FILE__).'/../_includes/classes/SC_Order.php');
require_once (dirname(__FILE__).'/../_includes/transaction/paypalfunctions.php');

if ($id_orders = (int)$_GET['id_orders']) {
	$order = new SC_Order($mysqli);
	if (!$order->load($id_orders)) {
		exit('error invalid order id');
	}
		
	$transaction_details = $order->transaction_details ? unserialize(base64_decode($order->transaction_details)):array();
	
	//echo '<pre>'.print_r($transaction_details,1).'</pre>';
} else {
	header("HTTP/1.0 404 Not Found");
	header('Location: /404?error=invalid_order');
	exit;		
}

list($width_logo, $height_logo, $type_logo, $attr_logo) = getimagesize(dirname(__FILE__) . "/../_images/logo_print.jpg");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="none" />
<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/../_includes/template/header.php");?>
<?php if (preg_match('/UA-([0-9]+)-([0-9]+)/',$config_site['google_analytics_tracking_id'])) { ?>

<?php include("../_includes/template/google_analytics.php");?>
<script type="text/javascript">  
_gaq.push(['_addTrans',    
	'<?php echo $order->id; ?>',           // order ID - required    
	"<?php echo $_SESSION['customer']['language']; ?>",  // affiliation or store name    
	'<?php echo $order->subtotal; ?>',          // total - required    
	'<?php echo $order->taxes; ?>',           // tax    
	'<?php echo $order->shipping; ?>',              // shipping    
	'<?php echo $order->shipping_city; ?>',       // city    
	'<?php echo $order->shipping_state; ?>',     // state or province    
	'<?php echo $order->shipping_country; ?>'             // country  
]);   

// add item might be called for every item in the shopping cart   
// where your ecommerce engine loops through each item in the cart and   
// prints out _addItem for each  
<?php 
if ($get_products = $order->get_products()) { 
	$products=array();
	foreach ($get_products as $row_product) {
		$sku = $row_product['variant_sku'] ? $row_product['variant_sku']:$row_product['sku'];
		
		$products[$sku]['name'] = $row_product['name'];
		$products[$sku]['variant_name'] = $row_product['variant_name'];
		
		$sell_price = $row_product['subtotal'];
		
		if ($product_discounts = $order->get_product_discounts($row_product['id_orders_item_product'])) {			
			foreach ($product_discounts as $row_product_discount) {
				$sell_price -= $row_product_discount['amount'];
			}
		}
		
		$products[$sku]['sell_price'] += $sell_price;
		$products[$sku]['qty'] += $row_product['qty'];
		
	}
	
	foreach ($products as $sku => $row_product) {
		$sell_price = round($row_product['sell_price']/$row_product['qty'],2);
?>
_gaq.push(['_addItem',    
	'<?php echo $order->id; ?>',            // order ID - required    
	'<?php echo $sku; ?>',           // SKU/code - required    
	'<?php echo $row_product['name']; ?>',        // product name    
	'<?php echo $row_product['variant_name']; ?>',   // category or variation    
	'<?php echo $sell_price; ?>',          // unit price - required    
	'<?php echo $row_product['qty']; ?>'               // quantity - required  
]);  
<?php
	}
}
?>
_gaq.push(['_trackTrans']); //submits transaction to the Analytics servers  
</script>
<?php } ?>
<style>
.cb {
clear: both;
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
	$step = 5;
	if(!$config_site['enable_shipping'])$step = 4;
	include(dirname(__FILE__) . "/../_includes/step.php");
	?>
    <div class="messages">
      <div class="alert alert-success success-msg">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <ul><li><span><?php echo language('cart/step_completed','MESSAGE_TRANSACTION_SUCCESS');?></span></li></ul>
      </div>
    </div>
    
    <div class="fr"><a href="print_version?id_orders=<?php echo $id_orders; ?>" target="_blank"><div class="ico_printer" style="margin-bottom:3px;"><span class="icon"><?php echo language('global','BTN_PRINT_VERSION');?></span></div></a></div><div class="cb"></div>
        
        <?php include("../_includes/template/print_include.php");?>
    </div>
    </div>
</div>
</div>
<?php include(dirname(__FILE__) . "/../_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>