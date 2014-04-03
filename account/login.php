<?php
require('../_includes/config.php');

$return = trim($_GET['return']);
if ($_SESSION['customer']['id']) {
	if ($return) { 
		header('Location: '.urldecode($return)); 
		exit; 
	} else { 
		header('Location: '.$url_prefix); 
		exit;
	}
}

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$success = trim($_GET['success']);
		
		break;
	case 'POST':
		if (isset($_POST['sign_in'])) {			
			// validation rules
			$validation = array(
				'email' => array(
					'required' => 1,
				),
				'password' => array(
					'required' => 1,			
				),
			);		
			
			if (!sizeof($errors = validate_fields($_POST['form_values'], $validation))) {	
				// loop through each post value and trim
				foreach ($_POST['form_values'] as $key => $value) {
					$$key = $value;
				}			
			
				
				if (login($email, $password, $remember_me)) {					
					if ($return) { 
						header('Location: '.urldecode($return)); 
						exit; 
					} else { 
						header('Location: '.$url_prefix); 
						exit;
					}
				} else {
					$access_denied = 1;
				}	
			}
		}
		break;	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>
</head>
<body>
<?php include("../_includes/template/top.php");?>
<div class="main-container">
	<div class="main nobc">
        <div class="container">
            <div class="main-content">                                        
              <div class="account-login">
                  <div class="page-title">
                      <h1><?php echo language('account/login', 'LOGIN_OR_CREATE_ACCOUNT');?></h1>
                  </div>
                  <?php 
                  $r_msg = '';
                  $r_class = '';
                  switch ($success) {
                      case 'activation':
                          $r_msg = language('account/login', 'SUCCESS_ACTIVATED');
                          $r_class = 'alert-success';
                          break;
                      case 'logout':
                          $r_msg= language('account/login', 'SUCCESS_SIGN_OUT');	
                          $r_class = 'alert-success';
                          break;
      				  case 'reset_password':
      					  $r_msg = language('account/login', 'SUCCESS_RESET');
      					  $r_class = 'alert-success';
      					break;
                  }                              
                  if ($access_denied) {
                      $r_msg = language('account/login', 'ERROR_INVALID');	
                      $r_class = 'alert-danger';
                  }
                  ?>
                  <?php if($r_msg != '') {?>
                  <div class="messages">
                  	<div class="alert <?php echo $r_class?>">
                  		<button type="button" class="close" data-dismiss="alert">×</button>
                  		<ul><li><span><?php echo $r_msg;?></span></li></ul>
                  	</div>
                  </div>    
                  <?php }?>
                  <form method="post" enctype="multipart/form-data" action="<?php echo $return?'?return='.$return:''?>">
                      <input name="form_key" type="hidden" value="pjIVDVJFGFzV5cwt">
                      <div class="row">
                          <div class="col-sm-6 registered-users">
                              <div class="content clearfix">
                                  <h2><?php echo language('account/login', 'TITLE_ALREADY_HAVE_ACCOUNT');?> <?php echo language('account/login', 'TEXT_ALREADY_HAVE_ACCOUNT');?></h2>
                                  
                                  <ul class="form-list">
                                      <li>
                                          <label for="email" class="required"><i class="icon-email"></i><?php echo language('account/login', 'LABEL_EMAIL_ADDRESS');?><em>*</em></label>
                                          <div class="input-box">
                                              <input type="text" name="form_values[email]" value="<?php echo htmlspecialchars($_POST['form_values']['email']); ?>" id="email" class="input-text required-entry validate-email<?php echo $errors['email'] ? ' error':''; ?>" title="<?php echo language('account/login', 'LABEL_EMAIL_ADDRESS');?>">
                                          </div>
                                      </li>
                                      <li>
                                          <label for="pass" class="required"><i class="icon-password"></i><?php echo language('account/login', 'LABEL_PASSWORD');?><em>*</em></label>
                                          <div class="input-box">
                                              <input type="password" name="form_values[password]" class="input-text required-entry validate-password<?php echo $errors['password'] ? ' error':''; ?>" id="pass" title="<?php echo language('account/login', 'LABEL_PASSWORD');?>">
                                          </div>
                                          <a href="forgot-password<?php echo $return ? '?return='.$return:''; ?>" class="f-left forgot-password"><?php echo language('account/login', 'LINK_FORGOT_PASSWORD');?></a>
                                      </li>
                                      <li>
                                      	<input type="checkbox" name="form_values[remember_me]" id="remember_me" value="1" />&nbsp;<?php echo language('account/login', 'INPUT_CHECK_REMEMBER');?>
                                      </li>
                              	</ul>                 
              				</div>
                              <div class="buttons-set">
                                  <button type="submit" class="button" title="<?php echo language('account/login', 'BTN_SIGN_IN');?>" name="sign_in" id="send2">
                                  	<span><span><?php echo language('account/login', 'BTN_SIGN_IN');?></span></span>
                                  </button>
                              </div>
                          </div>
                          
                          
                          
                          <div class="col-sm-6 new-users">
                              <div class="content clearfix">
                                  <h2><?php echo language('account/login', 'TITLE_DONT_HAVE_ACCOUNT');?></h2>
                                  <p><?php echo language('account/login', 'TITLE_DONT_HAVE_ACCOUNT_DESCRIPTION');?></p>
                              </div>
                              <div class="buttons-set">
                                 <button type="button" title="<?php echo language('account/login', 'LINK_SIGN_UP');?>" class="button" onclick="window.location='create-account<?php echo $return?'?return='.$return:''?>';"><span><span><?php echo language('account/login', 'LINK_SIGN_UP');?></span></span></button>
                              </div>
                          </div>
                          
                      </div>
              	</form>                      
              </div>
            </div>
        </div>
	</div>       
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>