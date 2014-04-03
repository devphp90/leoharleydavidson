<?php 
include(dirname(__FILE__) . "/../../_includes/config.php");
include(dirname(__FILE__) . "/../../_includes/validate_session.php");

$id = (int)$_GET['id'];
?>
<html>
<head>
	<title><?php echo $config_site['site_name'];?></title>
</head>
<frameset frameborder="0" framespacing="0" border="0" rows="50,*" cols="*">
	<frame src="api.php?id=<?php echo $id; ?>" name="API" noresize>
	<frame src="index.html" name="course" id="course">
</frameset><noframes></noframes>
</html>