<?php
return array (
	'CREATE_ACCOUNT_SUBJECT' => 'Enregistrement et activation',
	'CREATE_ACCOUNT_PLAIN' => "Bonjour {0}, 
Merci de vous enregistrer avec {1}. 

Afin de compléter le processus d'inscription, vous devez activer votre compte.
S'il vous plaît cliquez sur ce lien pour activer votre compte.

{2}

{signature}

* S'il vous plaît ne pas répondre à ce courriel car il s'agit d'un service automatisé.",
	
	'CREATE_ACCOUNT_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Bonjour {0},<br />
	Merci de vous enregistrer avec {1}.<br /> 
	<br />
	Afin de compléter le processus d\'inscription, vous devez activer votre compte.<br /> 
	S\'il vous plaît cliquez sur ce lien pour activer votre compte.<br />
	<br />
	<a href="{2}">Cliquez ici pour activer votre compte</a><br />
	{signature}
	<br />
	* S\'il vous plaît ne pas répondre à ce courriel car il s\'agit d\'un service automatisé.
	</body>',	

	'FORGOT_PASSWORD_SUBJECT' => 'Réinitialiser votre mot de passe',
	'FORGOT_PASSWORD_PLAIN' => "Bonjour {0},
Une demande a été faite sur {1} pour faire réinitialiser votre mot de passe.

Afin de réinitialiser votre mot de passe, cliquez sur le lien suivant.

{2}

Si vous n'avez pas fait cette demande, veuillez ignorer ce courriel.

* S'il vous plaît ne pas répondre à ce courriel car il s'agit d'un service automatisé.",
	
	'FORGOT_PASSWORD_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Bonjour {0},<br />
	Une demande a été faite sur {1} pour faire réinitialiser votre mot de passe.<br /> 
	<br />
	Afin de réinitialiser votre mot de passe, cliquez sur le lien suivant.
	<br />
	<a href="{2}">Cliquez ici pour réinitialiser votre mot de passe</a><br />
	<br />
	Si vous n\'avez pas fait cette demande, veuillez ignorer ce courriel.<br /><br />
	{signature}
	<br />
	* S\'il vous plaît ne pas répondre à ce courriel car il s\'agit d\'un service automatisé.
	</body>',	
	
	'PRICE_ALERT_CRON_SUBJECT' => 'Alerte de prix',
	
	'PRICE_ALERT_CRON_PLAIN' => "Bonjour {0},
Une demande a été faite sur {1} afin de recevoir une alerte de diminution de prix pour les produits suivants :

{2}

{signature}

* S'il vous plaît ne pas répondre à ce courriel car il s'agit d\'un service automatisé.",

	'PRICE_ALERT_CRON_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Bonjour {0},<br />
Une demande a été faite sur {1} afin de recevoir une alerte lors d\'une diminution de prix pour les produits suivants :
{2}
<br />
{signature}
<br />
* S\'il vous plaît ne pas répondre à ce courriel car il s\'agit d\'un service automatisé.
</body>',


'REGISTRATION_FORM_SUBJECT' => 'Confirmation d\'enregistrement',
	'REGISTRATION_FORM_PLAIN' => "Bonjour {0},
	
Merci de vous enregistrer à : {name} avec {1}. 

{coupon_description}

{2}

{signature}

* S'il vous plaît ne pas répondre à ce courriel car il s'agit d'un service automatisé.",
	
	'REGISTRATION_FORM_HTML' => '<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}</style><body style="margin: 10px;">Bonjour {0},<br /><br />
	Merci de vous enregistrer à : <strong>{name}</strong> avec {1}.<br /><br />
	{coupon_description}
	<br />
	{2}
	<br />
	<br />
	{signature}
	<br />
	* S\'il vous plaît ne pas répondre à ce courriel car il s\'agit d\'un service automatisé.
	</body>',
	
	'CONTACT_FORM_SUBJECT' => 'Formulaire de contact - {1}',	

); 
?>