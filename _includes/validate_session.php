<?php
if(!$_SESSION['customer']['id']){
	$return = trim($_GET['return']);
	header("Location: /account/login".($return?'?return='.urldecode($return):''));
	exit;
}
?>