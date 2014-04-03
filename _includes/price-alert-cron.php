<?php
session_start();
// get config information from admin config file
$admin_config = require(dirname(__FILE__).'/../admin/protected/config/main.php');
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

require_once(dirname(__FILE__).'/function.php');

include_mailer();

/* Config */
$query = 'SELECT * FROM config';
if ($result = $mysqli->query($query)) {
	$config_site = array();
	while($obj = $result->fetch_object()){
		$config_site[$obj->name] = $obj->value;
    } 
    $result->free();
}

$_SESSION['customer']['language'] = $config_site['language'];
$_SESSION['customer']['currency'] = $config_site['currency'];

$current_datetime = date('Y-m-d H:i:s');

// check all price alerts
if (!$result = $mysqli->query('SELECT 
customer.id,
customer.firstname,
customer.lastname,
customer.email,
customer.language_code,
product_description.name,
product_description.alias,
product_description.short_desc,
product_image.filename,
IF(product_variant.id IS NOT NULL,GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", "),"") AS variant_name,
calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS sell_price,
customer_price_alert.id AS id_customer_price_alert,
customer_price_alert.original_price,
customer_price_alert.last_updated_price,
customer_price_alert.type,
customer_price_alert.price AS alert_price
FROM
customer_price_alert

INNER JOIN
customer
ON
(customer_price_alert.id_customer = customer.id)

INNER JOIN
product
ON
(customer_price_alert.id_product = product.id)

INNER JOIN
product_description
ON
(product.id = product_description.id_product AND customer.language_code = product_description.language_code)

LEFT JOIN
product_image
ON
(product.id = product_image.id_product AND product_image.cover = 1)

LEFT JOIN
(product_variant
CROSS JOIN product_variant_option 
CROSS JOIN product_variant_group 
CROSS JOIN product_variant_group_option 
CROSS JOIN product_variant_group_option_description
CROSS JOIN product_variant_group_description)
ON
(customer_price_alert.id_product_variant = product_variant.id
AND product_variant.id = product_variant_option.id_product_variant 
AND product_variant_option.id_product_variant_group = product_variant_group.id 
AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
AND product_variant_group_option_description.language_code = product_description.language_code
AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
AND product_variant_group_description.language_code = product_description.language_code)

LEFT JOIN
customer_type
ON
(customer.id_customer_type = customer_type.id)

LEFT JOIN
rebate_coupon
ON
(product.id_rebate_coupon = rebate_coupon.id)

WHERE
product.display_in_catalog = 1
AND 
product.date_displayed <= "'.$mysqli->escape_string($current_datetime).'"
AND
is_product_in_stock(product.id,IF(product_variant.id IS NOT NULL,product_variant.id,0),0) = 1

GROUP BY 
customer_price_alert.id

HAVING 
(last_updated_price = 0 AND original_price != sell_price OR last_updated_price > 0 AND last_updated_price > sell_price AND original_price != sell_price)
AND
(type = 0 OR type = 1 AND alert_price >= sell_price)

ORDER BY
customer.id,
customer_price_alert.id
')) throw new Exception('An error occured while trying to get price alerts statement.'."\r\n\r\n".$mysqli->error);	

$customers = array();

while ($row = $result->fetch_assoc()) {
	if (!isset($customers[$row['id']])) {
    	$customers[$row['id']]['info'] = array(
        	'name' => $row['firstname'].' '.$row['lastname'],
            'email' => $row['email'],
			'language_code' => ($row['language_code'] ? $row['language_code']:$config_site['language']),
        );
    }

	$customers[$row['id']]['products'][] = array(
		'id' => $row['id_customer_price_alert'],
    	'name' => $row['name'],
        'variant_name' => $row['variant_name'],
		'price' => $row['price'],
        'sell_price' => $row['sell_price'],
		'short_desc' => $row['short_desc'],
		'alias' => $row['alias'],
		'filename' => $row['filename'],
    );
}

if (sizeof($customers)) {
	if (!$stmt_upd_price_alert = $mysqli->prepare('UPDATE
	customer_price_alert 
	SET
	customer_price_alert.last_updated_price = ?
	WHERE
	customer_price_alert.id = ?
	LIMIT 1')) throw new Exception('An error occured while trying to get price alerts statement.'."\r\n\r\n".$mysqli->error);	
	
	foreach ($customers as $row) {
        // send email to customer with activation link
        $mail = new PHPMailer(); // defaults to using php "mail()"
        $mail->CharSet = 'UTF-8';
        
        // text only
        //$mail->IsHTML(false);
        
        $mail->SetFrom($config_site['no_reply_email'], $config_site['site_name']);
        
        $mail->AddAddress($row['info']['email']);
        
		$mail->Subject = language('emails', 'PRICE_ALERT_CRON_SUBJECT',array(),$row['info']['language_code']);
        
        $product_list = '<table border="0" cellpadding="2" cellspacing="4" style="border-bottom: 1px solid #CCCCCC;"><tr><td colspan="2" style="border-bottom: 1px solid #CCCCCC;">'.language('_include/price-alert-cron','TEXT_TH_PRORDUCTS',array(),$row['info']['language_code']).'</td><td align="right" style="border-bottom: 1px solid #CCCCCC;">'.language('_include/price-alert-cron','TEXT_TH_PRICE',array(),$row['info']['language_code']).'</td></tr>'; 
		
		$product_list_plain = '';
    	foreach ($row['products'] as $row_product) {
        	$product_list .= '<tr>
			<td valign="top"><a href="http://'.$_SERVER['HTTP_HOST'].'/'.$row['info']['language_code'].'/product/'.$row_product['alias'].'"><img src="http://'.$_SERVER['HTTP_HOST'].'/images/products/thumb/'.$row_product['filename'].'"></a></td>
			<td valign="top"><a href="http://'.$_SERVER['HTTP_HOST'].'/'.$row['info']['language_code'].'/product/'.$row_product['alias'].'">'.$row_product['name'].($row_product['variant_name'] ? ' ('.$row_product['variant_name'].')':'').'</a><br />'.$row_product['short_desc'].'</td>
            <td valign="top" align="right" nowrap="nowrap"><div style="font-size:10px"><strong>'.language('_include/price-alert-cron','TEXT_REGULAR_PRICE',array(),$row['info']['language_code']).' </strong>'.nf_currency($row_product['price']).'</div>
            <div style="color: #990000"><strong>'.language('_include/price-alert-cron','TEXT_CURRENT_PRICE',array(),$row['info']['language_code']).' </strong>'.nf_currency($row_product['sell_price']).'</div>
            </td>
			</tr>';
        }
        $product_list .= '</table>';

		/*$mail->AltBody = language('emails', 'PRICE_ALERT_CRON_HTML',array(0=>$row['info']['name'],1=>$config_site['site_name'],2=>$product_list_plain,
		'signature'=>get_company_signature(0,$row['info']['language_code'])),$row['info']['language_code']);*/

		$mail->MsgHTML(language('emails', 'PRICE_ALERT_CRON_HTML',array(0=>$row['info']['name'],1=>'<a href="http://'.$_SERVER['HTTP_HOST'].'/'.$row['info']['language_code'].'">'.$config_site['site_name'].'</a>',2=>$product_list,
		'signature'=>get_company_signature(1,$row['info']['language_code'])),$row['info']['language_code']));      
		
		if ($mail->Send()) {
			foreach ($row['products'] as $row_product) {
				if (!$stmt_upd_price_alert->bind_param("di", $row_product['sell_price'], $row_product['id'])) throw new Exception('An error occured while trying to bind params to update price alerts statement.'."\r\n\r\n".$this->mysqli->error);
				
				/* Execute the statement */
				if (!$stmt_upd_price_alert->execute()) throw new Exception('An error occured while trying to update price alerts.'."\r\n\r\n".$this->mysqli->error);	
			}
		}
    }
}
?>