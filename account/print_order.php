<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include_once(dirname(__FILE__).'/../_includes/classes/SC_Order.php');

if ($id_orders = (int)$_GET['id_orders']) {
	$order = new SC_Order($mysqli);
	if (!$order->load($id_orders)) {
		header("HTTP/1.0 404 Not Found");
		header('Location: /404?error=invalid_order');
		exit;	
	}
	
	$transaction_details = $order->transaction_details ? unserialize(base64_decode($order->transaction_details)):array();
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
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<link rel="stylesheet" type="text/css" href="/_css/print_version_order.css" />
</head>

<body>
<div id="content">
<?php 
$show_company_header = 1;
include("../_includes/template/print_include.php");?>
</div>
</body>
</html>
