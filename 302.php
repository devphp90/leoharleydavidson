<?php
$page_maintenance = 1;
require('_includes/config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="NOINDEX, NOFOLLOW" />
<title><?php echo $config_site['site_name'] . ' - ' . language('global','TITLE_MAINTENANCE_MODE');?></title>
</head>
<body style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
<?php
list($width_logo, $height_logo, $type_logo, $attr_logo) = getimagesize(dirname(__FILE__) . "/_images/".$config_site['company_logo_file']);
?>
<div style="text-align:center; padding-top:50px;">
    <a href="/"><img border="0" src="/_images/<?php echo $config_site['company_logo_file'];?>" alt="<?php echo $config_site['site_name'];?>" name="logo" <?php echo $attr_logo;?> id="logo" /></a>
</div>
<div style="text-align:center; margin-top:30px;">
        <div style="font-size:16px">
        <?php 
		if (!$result = $mysqli->query('SELECT 
		code
		FROM language
		WHERE active = 1
		ORDER BY default_language DESC')) throw new Exception('An error occured while trying to get page id.'."\r\n\r\n".$mysqli->error);
		
		if(!$result->num_rows){				
			header("HTTP/1.0 404 Not Found");
			header('Location: /404');
			exit;	
		}
		
		while ($row = $result->fetch_assoc()) {
			echo '<div style="margin-bottom:20px; padding-bottom:20px;">'.language('global','MESSAGE_MAINTENANCE_MODE',array(),$row['code']).'</div>';
		}
		?>
        </div>        
</div>
</body>
</html>