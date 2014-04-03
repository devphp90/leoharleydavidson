<?php
return array (
	// used in /admin/protected/controllers/GiftcertificatesController.php

	'GIFT_CERTIFICATE_SUBJECT' => 'Gift certificate from: {customer_name}',
	'GIFT_CERTIFICATE_PLAIN' => "GIFT CERTIFICATE
					 
To: {person_name}
From: {customer_name}
Amount of: {amount}
Gift certificate code: {code}

To use your gift certificate, you must enter the gift certificate code in the payment page of our online store. (http://".$_SERVER['HTTP_HOST'].")
{person_message}

For information or questions, please contact us.

{signature}

* Please do not reply to this email address as this is an automated service.",
	'GIFT_CERTIFICATE_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;"><strong>GIFT CERTIFICATE</strong>
<br />
<br />
To: {person_name}<br />
From: {customer_name}<br />
Amount of: {amount}<br />
Gift certificate code: {code}<br />
<br />
To use your gift certificate, you must enter the gift certificate code in the payment page of our online store. (http://'.$_SERVER['HTTP_HOST'].')
<br /><br />
For information or questions, please contact us.
{person_message}
<br />
<br />
{signature}
<br /><br />
* Please do not reply to this email address as this is an automated service.
</body>',
	
	// used in /admin/protected/controllers/OrdersController.php
	
	'ORDERS_ADD_COMMENT_SUBJECT' => 'A new comment was added to your order.',
	'ORDERS_ADD_COMMENT_PLAIN' => "Dear {person_name},
A new comment was added to your order #{id_orders}.

{comment}

To reply to this comment, please login to your account and select this order from the list. (http://".$_SERVER['HTTP_HOST'].")

{signature}

* Please do not reply to this email address as this is an automated service.",

	'ORDERS_ADD_COMMENT_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Dear {person_name},<br />
A new comment was added to your order #{id_orders}.<br /><br />
{comment}<br /><br />
To reply to this comment, please login to your account and select this order from the list. (http://'.$_SERVER['HTTP_HOST'].')
<br /><br />
{signature}
<br /><br />
* Please do not reply to this email address as this is an automated service.
</body>',	
	
);
?>