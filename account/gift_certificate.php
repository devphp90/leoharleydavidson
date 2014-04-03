<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>
</head>
<body class="bv3">
<?php include("../_includes/template/top.php");?>
<div class="main-container">	
    <div class="breadcrumbs">   	
		<div class="container">
          <ul>
          	<li>
            	<a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<a href="/account" title="<?php echo language('global', 'BREADCRUMBS_YOUR_ACCOUNT');?>"><?php echo language('global', 'BREADCRUMBS_YOUR_ACCOUNT');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<strong><?php echo language('global', 'BREADCRUMBS_GIFT_CERTIFICATE');?></strong>
            </li>
          </ul>
      	</div>            
    </div>
    <div class="main">
    <div class="container">
    <div class="main-content"> 
    	<h2 class="subtitle"><?php echo language('account/gift_certificate','TITLE_PAGE');?></h2>        

        <div class="title_bg_text_box">
    	<div style="margin-bottom:10px"><?php echo language('account/gift_certificate','TITLE_PAGE_DESCRIPTION');?></div>
            
           
                    <?php
                    // check if gift certificate exists
		if (!$result = $mysqli->query('SELECT
		gift_certificate.date_created,
		gift_certificate.code,
		(gift_certificate.price-IFNULL((SELECT 
			SUM(amount)
			FROM
			orders_gift_certificate
			INNER JOIN
			(orders)
			ON
			(orders_gift_certificate.id_orders = orders.id AND orders.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'" AND  (orders.status<>-1 AND orders.status<>4))
			WHERE
			code = gift_certificate.code),0)) AS amount
		FROM
		gift_certificate
		INNER JOIN
		(orders_gift_certificate CROSS JOIN orders)
		ON
		(gift_certificate.code = orders_gift_certificate.code AND orders_gift_certificate.id_orders = orders.id AND orders.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'" AND (orders.status<>-1 AND orders.status<>4))
		WHERE
		gift_certificate.active = 1
		GROUP BY gift_certificate.code
		')) throw new Exception('An error occured while trying to get gift certificate info.'."\r\n\r\n".$mysqli->error);
		
		if ($result->num_rows) {?>
			 <table cellpadding="0" cellspacing="0" width="100%" class="shopping_cart table">
                <tbody>
                	<thead>
                    <tr>
                        <th><?php echo language('account/gift_certificate','LABEL_STARTING_DATE');?></th>
                        <th><?php echo language('account/gift_certificate','LABEL_GIFT_CERTIFICATE_CODE');?></th>
                        <th style="text-align:right"><?php echo language('account/gift_certificate','LABEL_GIFT_CERTIFICATE_REMAINING');?></th>
                    </tr>
                    </thead>
                    <?php
                    while($row = $result->fetch_assoc()){
						echo '<tr>
							<td>'.df_date($row['date_created'],1,-1).'</td>
							<td>'.$row['code'].'</td>
							<td style="text-align:right">'.nf_currency($row['amount']).'</td>
						</tr>';
					}
					?>
            	</tbody>
            </table>
		<?php
        }else{
			echo '<div style="padding-top: 10px">'.language('account/gift_certificate','TITLE_EMPTY').'</div>';
		}
		?>
        </div>

	</div>        
</div>
</div>
</div>

<?php include("../_includes/template/bottom.php");?>
</body>
</html>