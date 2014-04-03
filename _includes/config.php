<?php
session_start();
// get config information from admin config file
$admin_config = require(dirname(__FILE__).'/../admin/protected/config/main.php');

// set timezone
date_default_timezone_set($admin_config['timeZone']);

$db_dbname = explode('dbname=',$admin_config['components']['db']['connectionString']);
$db_dbname = $db_dbname[1];
preg_match('/host=(.*);/',$admin_config['components']['db']['connectionString'],$db_host);
$db_host = $db_host[1];
$db_user = $admin_config['components']['db']['username'];
$db_pass = $admin_config['components']['db']['password'];

/* Connect */
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_dbname);

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

/* change character set to utf8 */
if (!$mysqli->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
}

include_once(dirname(__FILE__).'/function.php');
include_once(dirname(__FILE__).'/classes/SC_Cart.php');

require(dirname(__FILE__).'/classes/Banners.php');
require(dirname(__FILE__).'/classes/Pages.php');
require(dirname(__FILE__).'/classes/Page.php');
require(dirname(__FILE__).'/classes/Categories.php');
require(dirname(__FILE__).'/classes/Category.php');
require(dirname(__FILE__).'/classes/Products.php');
require(dirname(__FILE__).'/classes/Product.php');
require(dirname(__FILE__).'/classes/zebra_pagination/Zebra_Pagination.php');


/* Cart expiration time in seconds */
$cart_expiration_time = ini_get('session.gc_maxlifetime');// Get the session duration

/* Config */
$query = 'SELECT * FROM config';
if ($result = $mysqli->query($query)) {
	$config_site = array();
	while($obj = $result->fetch_object()){
		$config_site[$obj->name] = $obj->value;
    } 
    $result->close();
	
	$is_admin = 0;
	foreach ($_SESSION as $key => $value) if (strstr($key,'__id') && ($match = str_replace('__id','',$key)) && isset($_SESSION[$match.'__name'])) $is_admin = 1;
	
	// Verify if we are in maintenance mode and not in the maintenance page to avoid make a loop...we also have to declare a variable($page_styles) in the style.php because it also include config.php.
	if ($config_site['maintenance_mode'] && !$page_maintenance && !$page_styles && !$is_admin) {
		header("Location: /302",TRUE,302);
		exit();			
	}
	
	if ($config_site['maintenance_mode']) apc_clear_cache();
		
	// if we are passing the language in the url
	if (isset($_GET['_lang']) && $_GET['_lang']) {
		/*
		// we need to make sure language exists and is active*/
		if (!$result = $mysqli->query('SELECT 
		COUNT(*) AS total
		FROM
		language
		WHERE
		code = "'.$mysqli->escape_string($_GET['_lang']).'" 
		AND
		active = 1
		LIMIT 1')) throw new Exception('An error occured while trying to check if language is active.'."\r\n\r\n".$mysqli->error);
		$row = $result->fetch_assoc();
		
		if ($row['total'] || $is_admin) $_SESSION['customer']['language'] = $_GET['_lang'];
		else {
			
			
//			$_SESSION['customer']['language'] = $config_site['language'];
			$url_redirect = str_replace('/'.$_GET['_lang'].'/','/'.$config_site['language'].'/',$_SERVER['REDIRECT_URL']);		
			$_SESSION['customer']['language'] = $config_site['language'];
			
			header('Location: '.$url_redirect);
			exit;
		}
		
		//$_SESSION['customer']['language'] = $_GET['_lang'];
	
	}
	// if we have a cookie set for the language
	else if (!isset($_SESSION['customer']['language'])) {				
		// if we have a cookie
		if ($language_code = get_cookie_value('language')) {
			$_SESSION['customer']['language'] = $language_code;			
		// set default language
		} else {
			$_SESSION['customer']['language'] = $config_site['language'];	
		}		
	}
	
	// if we are switching languages in any other pages other than: catalog, product, page, tag
	if (isset($_POST['language_main_site']) && $_POST['language_main_site'] && !stristr($_SERVER['HTTP_REFERER'],'/catalog') &&
	!stristr($_SERVER['HTTP_REFERER'],'/product') && !stristr($_SERVER['HTTP_REFERER'],'/page') && 
	!stristr($_SERVER['HTTP_REFERER'],'/tag'.$_SESSION['customer']['language'])) {
		//if (stristr($_SERVER['HTTP_REFERER'],'/'.$_SESSION['customer']['language'].'/')) {
		// if we are passing the language in the url
		if (isset($_GET['_lang']) && $_GET['_lang']) {
			
			$url_redirect = str_replace('/'.$_SESSION['customer']['language'].'/','/'.$_POST['language_main_site'].'/',$_SERVER['HTTP_REFERER']);		
			$_SESSION['customer']['language'] = $_POST['language_main_site'];
			
			header('Location: '.$url_redirect);
			exit;
		}
		
		$_SESSION['customer']['language'] = $_POST['language_main_site'];
	}	
	
	$_SESSION['customer']['currency'] = $config_site['currency'];	
	
	// store in cookie, current language
	set_cookie_value('language', $_SESSION['customer']['language']);
}

//Verify if a cookie exist for the language else used the defautlt value in config
if (!isset($_SESSION['customer']['id_customer_type'])) $_SESSION['customer']['id_customer_type'] = 0;


/* Image Config for CSS and Pages */
include_once(dirname(__FILE__) . '/image_config.php');


$images_sizes = get_images_sizes();
$current_datetime = date('Y-m-d H:i:s');
$url_prefix = '/'.$_SESSION['customer']['language'].'/';
$return_url = !isset($_GET['return']) ? trim($_SERVER["REQUEST_URI"]):trim($_GET['return']);
$return_url = (urldecode($return_url) == $url_prefix ? '/account':$return_url);
$return_url = urlencode(urldecode($return_url));

// set cart variable
$cart = new SC_Cart($mysqli);

if (isset($_GET['logout'])) {
	logout();
	//$return = trim($_GET['return']);
	//if ($return) { 
	//	header('Location: '.urldecode($return));  
	//} else { 
		header('Location: '.$url_prefix);
	//}
	exit;
} else if (!isset($_SESSION['customer']['id']) && $remember_me_key = get_cookie_value('remember_me_key')) {
	if ($result = $mysqli->query('SELECT 
	email
	FROM
	customer
	WHERE
	remember_me_key = "'.$mysqli->escape_string($remember_me_key).'"
	LIMIT 1')) {
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			
			login($row['email'], $row['password'],0,1);
		} 		
		
		$result->close();
	} else {
		throw new Exception('An error occured while trying to remember customer.'."\r\n\r\n".$mysqli->mysqli->error);		
	}
}
?>