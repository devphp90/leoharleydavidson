<?php
require('_includes/config.php');

header("HTTP/1.0 404 Not Found");

$error = trim($_GET['error']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="NOINDEX, NOFOLLOW" />
<title><?php echo $config_site['site_name'] . ' - ' . language('global','TITLE_NOT_FOUND');?></title>
<?php include("_includes/template/header.php");?>
</head>
<body>
<?php include("_includes/template/top.php");?>
<div class="main-container">
<div class="main">	
    <div class="container">
    <div class="main-content">
        <h2 class="subtitle"><?php echo language('global','TITLE_NOT_FOUND');?></h2>
        <div style="font-size:15px">
        <?php
			switch ($error) {
				default:
					echo language('global','MESSAGE_NOT_FOUND');				
					break;
				case 'invalid_product':
					echo language('global','MESSAGE_PRODUCT_NOT_FOUND');	
					break;
				case 'invalid_category':
					echo language('global','MESSAGE_CATEGORY_NOT_FOUND');	
					break;		
				case 'invalid_download':
					echo language('global','MESSAGE_DOWNLOAD_NOT_FOUND');	
					break;										
				case 'invalid_video':
					echo language('global','MESSAGE_VIDEO_NOT_FOUND');	
					break;										
					
			}
		?>
        <br /><br />
        <a href="/"><?php echo language('global', 'LINK_GOTO_HOME_PAGE');?></a>
        </div>
	</div>        
</div>
</div>
</div>

<?php include("_includes/template/bottom.php");?>
</body>
</html>