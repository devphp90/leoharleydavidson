<?php
return array (
	'CREATE_ACCOUNT_SUBJECT' => 'Registration and Activation',
	'CREATE_ACCOUNT_PLAIN' => "Hi {0},
Thank you for registering with {1}. 

In order to complete this registration process you will need to verify your account. 
Please visit this link to activate your account.

{2}

{signature}

* Please do not reply to this email address as this is an automated service.",
	
	'CREATE_ACCOUNT_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Hi {0},<br />
	Thank you for registering with {1}.<br /> 
	<br />
	In order to complete this registration process you will need to verify your account.<br /> 
	Please click the following link to activate your account.<br />
	<br />
	<a href="{2}">Click here to Validate your account</a><br />
	{signature}	
	<br />
	* Please do not reply to this email address as this is an automated service.
	</body>',	
	

	'FORGOT_PASSWORD_SUBJECT' => 'Reset your password',
	'FORGOT_PASSWORD_PLAIN' => 'Hi {0},
A request was made on {1} to have your account password reset.

In order to reset your password, follow the link.

{2}

{signature}

If you did not make this request, please ignore this email.

* Please do not reply to this email address as this is an automated service.',
	
	'FORGOT_PASSWORD_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Hi {0},<br />
	A request was made on {1} to have your account password reset.<br /> 
	<br />
	In order to reset your password, click the following link.
	<br />
	<a href="{2}">Click here to reset your password</a><br />
	{signature}
	<br />
	* Please do not reply to this email address as this is an automated service.
	</body>',	
	
	'PRICE_ALERT_CRON_SUBJECT' => 'Price Alert',
	
	'PRICE_ALERT_CRON_PLAIN' => "Hi {0},
A request was made on {1} to receive an alert on price reductions for the following products:

{2}

{signature}

* Please do not reply to this email address as this is an automated service.",

	'PRICE_ALERT_CRON_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Hi {0},<br /><br />
A request was made on {1} to receive an alert on price reductions for the following products:<br /><br />
{2}
<br />
{signature}
<br />
* Please do not reply to this email address as this is an automated service.
</body>',


'REGISTRATION_FORM_SUBJECT' => 'Registration Confirmation',
	'REGISTRATION_FORM_PLAIN' => "Hi {0},
	
Thank you for registering to: {name} with {1}. 

{coupon_description}

{2}

{signature}

* Please do not reply to this email address as this is an automated service.",
	
	'REGISTRATION_FORM_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Hi {0},<br /><br />
	Thank you for registering to: <strong>{name}</strong> with {1}.<br /> 
	<br />
	{coupon_description}
	<br />
	{2}
	<br />
	<br />
	{signature}	
	<br />
	* Please do not reply to this email address as this is an automated service.
	</body>',



	'CONTACT_FORM_SUBJECT' => 'Contact Form - {1}',
		

); 
?>