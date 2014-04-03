<?php
return array (
	// used in /admin/protected/controllers/GiftcertificatesController.php

	'GIFT_CERTIFICATE_SUBJECT' => 'Chèque cadeau de la part de : {customer_name}',
	'GIFT_CERTIFICATE_PLAIN' => "CHÈQUE CADEAU
					 
À l\'attention de : {person_name}
De la part de : {customer_name}
Au montant de : {amount}
Code du chèque cadeau : {code}

Pour utiliser votre chèque cadeau, vous devez entrer le code du chèque cadeau dans l\'étape de paiement de notre boutique en ligne. (http://".$_SERVER['HTTP_HOST'].")
{person_message}

Pour toute informations ou question, communiquez avec nous.

{signature}

* S\'il vous plaît ne répondez pas à cette adresse car ceci un service automatisé.",
	'GIFT_CERTIFICATE_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;"><strong>CHÈQUE CADEAU</strong>
<br />
<br />
À l\'attention de : {person_name}<br />
De la part de :  {customer_name}<br />
Au montant de :  {amount}<br />
Code du chèque cadeau : {code}<br />
<br />
Pour utiliser votre chèque cadeau, vous devez entrer le code du chèque cadeau dans l\'étape de paiement de notre boutique en ligne. (http://'.$_SERVER['HTTP_HOST'].')
{person_message}
<br /><br />
{signature}
<br /><br />
* S\'il vous plaît ne répondez pas à cette adresse car ceci un service automatisé. 
</body>',
	
	// used in /admin/protected/controllers/OrdersController.php
	
	'ORDERS_ADD_COMMENT_SUBJECT' => 'Un nouveau commentaire a été ajouté à votre commande.',
	'ORDERS_ADD_COMMENT_PLAIN' => "Cher {person_name},
Un nouveau commentaire a été ajouté à votre commande #{id_orders}.

{comment}

Pour répondre à ce commentaire, veuillez vous connecter à votre compte et sélectionnez cette commande à partir de la liste. (http://".$_SERVER['HTTP_HOST'].")

{signature}

* S\'il vous plaît ne répondez pas à cette adresse car ceci un service automatisé.",

	'ORDERS_ADD_COMMENT_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Cher {person_name},<br />
Un nouveau commentaire a été ajouté à votre commande #{id_orders}.<br /><br />
{comment}<br /><br />
Pour répondre à ce commentaire, veuillez vous connecter à votre compte et sélectionnez cette commande à partir de la liste. (http://'.$_SERVER['HTTP_HOST'].')
<br /><br />
{signature}
<br /><br />
* S\'il vous plaît ne répondez pas à cette adresse car ceci un service automatisé.
</body>',	
	
);
?>