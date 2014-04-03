<?php 
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");
include_once(dirname(__FILE__).'/../_includes/classes/SC_Order.php');

if ($id = (int)$_GET['id']) {
	$order = new SC_Order($mysqli);
	if (!$order->load($id)) {
		header("HTTP/1.0 404 Not Found");
		header('Location: /404?error=invalid_order');
		exit;	
	}
	
	$transaction_details = $order->transaction_details ? unserialize(base64_decode($order->transaction_details)):array();
	
	//echo '<pre>'.print_r($transaction_details,1).'</pre>';
} else {
	header("HTTP/1.0 404 Not Found");
	header('Location: /404?error=invalid_order');
	exit;	
}

if (isset($_GET['task']) && $_GET['task'] == 'add_comment') {
	if ($comment = trim($_GET['comment'])) {
		// check if comment exists
		if (!$result_comment = $mysqli->query('SELECT
		COUNT(orders_comment.id) AS total
		FROM
		orders_comment 
		WHERE
		orders_comment.id_orders = "'.$mysqli->escape_string($order->id).'"
		AND
		orders_comment.comments = "'.$mysqli->escape_string($comment).'"
		AND
		orders_comment.id_user_created = 0')) throw new Exception('An error occured while trying to check if comment exists.'."\r\n\r\n".$mysqli->error);
		
		$row_comment = $result_comment->fetch_assoc();
		$result_comment->free();
		
		if (!$row_comment['total']) {
			if (!$mysqli->query('INSERT INTO
			orders_comment
			SET
			orders_comment.id_orders = "'.$mysqli->escape_string($order->id).'",
			orders_comment.comments = "'.$mysqli->escape_string($comment).'",
			orders_comment.date_created = NOW()')) throw new Exception('An error occured while trying to add comment.'."\r\n\r\n".$mysqli->error);				
			
			echo '<script type="text/javascript" language="javascript">
			jQuery(function(){
				jQuery("#comment").val("");
				alert("'.language('account/order','SUCCESS_ADD_COMMENT').'");
			});
			</script>';
		} else {
			echo '<script type="text/javascript" language="javascript">
			jQuery(function(){
				alert("'.language('account/order','ERROR_COMMENT_EXISTS').'");
			});
			</script>';			
		}				
	} else {				
		echo '<script type="text/javascript" language="javascript">
		jQuery(function(){
			if (!jQuery("#comment").hasClass("error")) jQuery("#comment").addClass("error");
			alert("'.language('account/order','ERROR_NO_COMMENT').'");
		});
		</script>';			
	}
	
	// get comments
	if (!$result_comments = $mysqli->query('SELECT 
	orders_comment.*,
	CONCAT(user.firstname," ",user.lastname) AS name
	FROM 
	orders_comment
	LEFT JOIN
	user
	ON
	(orders_comment.id_user_created = user.id)				
	WHERE
	orders_comment.id_orders = "'.$mysqli->escape_string($order->id).'"
	AND
	orders_comment.hidden_from_customer = 0
	ORDER BY
	orders_comment.date_created DESC')) throw new Exception('An error occured while trying to get comments.'."\r\n\r\n".$mysqli->error);
	
	while ($row_comment = $result_comments->fetch_assoc()) {
		echo '<div style="margin-bottom:10px;">
			<div class="fl"><strong>'.language('account/order','LABEL_COMMENT_FROM').'</strong> '.($row_comment['id_user_created'] ? $row_comment['name']:language('account/order','LABEL_COMMENT_ME')).'</div>
			<div class="fr"><strong>'.language('account/order','LABEL_COMMENT_DATE').'</strong> '.df_date($row_comment['date_created'],1).'</div>
			<div class="cb"></div>
			<div style="margin-top:5px;">'.htmlspecialchars($row_comment['comments']).'</div>
			<div class="cb"></div>
		</div>';
	}			
	
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="none" />
<meta http-equiv="Pragma" content="no-cache">
<title><?php echo $config_site['site_name'];?></title>
<?php include(dirname(__FILE__) . "/../_includes/template/header.php");?>
<script type='text/javascript' src='/includes/js/mediaplayer/jwplayer.js'></script>
<style>
	.col-sm-6 {margin-bottom:20px;}
	.col-sm-6 .title_bg_text_box {padding:7px;border: 1px solid #dcdcdc;overflow: hidden;}
	.col-sm-6 .op_block_title{margin-bottom:0;}
	.cb{clear:both;}
</style>
</head>
<body class="bv3">
<?php include(dirname(__FILE__) . "/../_includes/template/top.php");?>
<div class="main-container">
	<div class="breadcrumbs">   	
		<div class="container">
          <ul>
          	<li>
            	<a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<a href="/account"><?php echo language('global', 'BREADCRUMBS_YOUR_ACCOUNT');?></a>
            	<span>&gt;</span>
            </li>
            <li>
            	<?php echo language('account/order', 'TITLE_PAGE');?>
            </li>    
          </ul>
      	</div>            
    </div>	
    <div class="main">
    <div class="container">
        <h2 class="subtitle"><?php echo language('account/order','TITLE_PAGE'); ?></h2>
        <div style="margin-bottom: 20px;overflow: hidden;">
            <div class="fl">
                <div style="font-size:14px; margin-bottom:5px;"><strong><?php echo language('account/order', 'LABEL_DATE');?>:</strong> <?php echo df_date($order->date_order,1);?></div>
                <div style="font-size:14px; margin-bottom:5px;"><strong><?php echo language('account/order', 'LABEL_ORDER_NO');?>:</strong> <?php echo $order->id; ?></div>
                <div style="font-size:14px; margin-bottom:5px;"><strong><?php echo language('account/order', 'LABEL_STATUS');?>:
                <?php
                    switch ($order->status) {
                        case -1:
                            echo '<span class="error">'.language('account/index','LABEL_STATUS_CANCELLED').'</span>';
                            break;
                        case 0:
                            echo language('account/index','LABEL_STATUS_INCOMPLETE');
                            break;					
                        case 1:
                            echo language('account/index','LABEL_STATUS_PENDING');
                            break;
                        case 2:
                            echo language('account/index','LABEL_STATUS_PAYMENT_REVIEW');
                            break;
                        case 3:
                            echo language('account/index','LABEL_STATUS_SUSPECTED_FRAUD');
                            break;
                        case 4:
                            echo '<span class="error">'.language('account/index','LABEL_STATUS_DECLINED').'</span>';
                            break;
                        case 5:
                            echo language('account/index','LABEL_STATUS_PROCESSING');
                            break;
                        case 6:
                            echo language('account/index','LABEL_STATUS_ON_HOLD');
                            break;
                        case 7:
                            echo '<span class="success">'.language('account/index','LABEL_STATUS_COMPLETED').'</span>';
                            break;
                    }			
                ?>
                </strong>
                </div>
            </div>
            <div class="fr">
                <a class="button" href="print_order?id_orders=<?php echo $id; ?>" target="_blank"><?php echo language('account/order','LABEL_PRINT_INVOICE'); ?></a>
                </div>
            </div>                
        
            <div class="cb" style="clear:both;"></div>
              
              
  		<div style="margin-bottom:20px;overflow: hidden;">
        	
            	<?php if($config_site['enable_shipping']){?>                       
	            <div class="col-sm-6" style="padding-left:0">
	            <div class="op_block_title"><?php echo language('account/order', 'TITLE_SHIPPING');?></div>
                <div class="title_bg_text_box">
                    <?php
                    if(!$order->local_pickup){
						echo '<div>
						<div class="fl" style="width:50%;">
						<div style="margin-bottom:5px;"><strong style="font-size:14px;">'.language('account/order','LABEL_SHIP_TO').'</strong></div>'.
						($order->shipping_company?'<div style="margin-bottom:5px;">'.$order->shipping_company.'</div>':'').
						'<div style="margin-bottom:5px;">'.$order->shipping_firstname. ' ' .$order->shipping_lastname.'</div>
						<div style="margin-bottom:5px;">'.$order->shipping_address.'</div>
						<div style="margin-bottom:5px;">'.$order->shipping_city.
						($order->shipping_state?' '.$order->shipping_state:'').' '.strtoupper($order->shipping_zip).'</div>
						<div style="margin-bottom:5px;">'.$order->shipping_country.'</div>';
						
						echo '</div>
						
							<div class="fr" style="width:50%; text-align:right;">
								<div style="margin-bottom:5px;">
									<strong style="font-size:14px;">'.language('account/order','LABEL_SHIP_USING').'</strong>
								</div>';
								if(!$order->free_shipping){
									echo $order->shipping_gateway_company.' '.$order->shipping_service;
								}else{
									echo language('global', 'TITLE_FREE_SHIPPING');
								}
							echo '</div>
							<div class="cb"></div>
						</div>';
						
						// get shipment
						if (!$result_shipment = $mysqli->query('SELECT 
						orders_shipment.*
						FROM
						orders_shipment
						WHERE
						orders_shipment.id_orders = "'.$mysqli->escape_string($order->id).'"
						ORDER BY
						orders_shipment.date_shipment ASC')) throw new Exception('An error occured while trying to get shipments.'."\r\n\r\n".$mysqli->error);
						
						if ($result_shipment->num_rows) {
							echo '<br /><br />';
							
							if (!$stmt_shipment_item = $mysqli->prepare('SELECT 
							orders_shipment_item.id,
							orders_shipment_item.id_orders_item_product,
							orders_shipment_item.id_orders_item_option,
							orders_item_product_description.name,
							orders_item_product_description.variant_name,
							orders_item_option_description.name AS option_name,
							orders_item_option_description.description AS option_description,
							orders_shipment_item.qty
							FROM
							orders_shipment_item
							LEFT JOIN 
							(orders_item_product CROSS JOIN orders_item_product_description)
							ON
							(orders_shipment_item.id_orders_item_product = orders_item_product.id AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = ?)
							
							LEFT JOIN 
							(orders_item_option CROSS JOIN orders_item_option_description)
							ON
							(orders_shipment_item.id_orders_item_option = orders_item_option.id AND orders_item_option.id = orders_item_option_description.id_orders_item_option AND orders_item_option_description.language_code = ?)
							
							WHERE
							orders_shipment_item.id_orders_shipment = ?
							ORDER BY 
							orders_shipment_item.id ASC')) throw new Exception('An error occured while trying to prepare get shipment items statement.'."\r\n\r\n".$mysqli->error);
							
							while ($row_shipment = $result_shipment->fetch_assoc()) {
								echo '<div style="border:1px solid #cccccc; padding:10px; margin-bottom:10px;"><div style="margin-bottom:10px;">
									<div class="fl">
										<div><strong style="font-size:14px;">'.language('account/order','LABEL_SHIPMENT_NO').':</strong> '.$row_shipment['shipment_no'].'</div>
										<div><strong style="font-size:14px;">'.language('account/order','LABEL_SHIPMENT_DATE').':</strong> '.df_date($row_shipment['date_shipment'],1,-1).'</div>
									</div>
									<div class="fr" style="text-align:right;">
										<div><strong style="font-size:14px;">'.language('account/order','LABEL_TRACKING_NO').':</strong> '.$row_shipment['tracking_no'].'</div>									
										'.($row_shipment['tracking_url'] ? '<div><a href="'.$row_shipment['tracking_url'].'" target="_blank">'.language('account/order','LABEL_TRACK_SHIPMENT').'</a></div>':'').'
									</div>
									<div class="cb"></div>
								</div>
								<table border="0" cellpadding="2" cellspacing="2" width="100%" class="shopping_cart" style="margin-bottom:20px;">
								<tr>
									<th width="5%">'.language('account/order','LABEL_QTY').'</th>
									<th>'.language('account/order','LABEL_DESCRIPTION').'</th>
								</tr>';
								
								/* Execute the statement */
								if (!$stmt_shipment_item->bind_param("ssi", $_SESSION['customer']['language'], $_SESSION['customer']['language'], $row_shipment['id'])) throw new Exception('An error occured while trying to bind params to get shipment items statement.'."\r\n\r\n".$this->mysqli->error);	
								if (!$stmt_shipment_item->execute()) throw new Exception('An error occured while trying to get shipment items.'."\r\n\r\n".$this->mysqli->error);
								
								/* store result */
								$stmt_shipment_item->store_result();
								
								/* bind result variables */
								$stmt_shipment_item->bind_result($id_orders_shipment_item,$id_orders_item_product,$id_orders_item_option,$name,$variant_name,$option_name,
								$option_description, $qty);

								while ($stmt_shipment_item->fetch()) {								
									echo '<tr>
									<td align="center" valign="top">'.$qty.'</td>
									<td valign="top">';
									
									if ($id_orders_item_product) {
										echo $name;
										
										if ($variant_name) echo '<div style="margin-top:5px;">'.$variant_name.'</div>';
										
									} else if ($id_orders_item_option) {
										echo $option_name;
										
										if ($option_description) echo '<div style="margin-top:5px;">'.$option_description.'</div>';
									}
									
									echo '
									</td>
									</tr>';
								}
								
								echo '</table></div>';								
							}
							
							$stmt_shipment_item->close();
							$result_shipment->free();
						} else {
							echo language('account/order','LABEL_NO_SHIPMENT');	
						}						
                    }else{
                   		echo '<div style="margin-bottom:10px; font-weight: bold; font-size: 14px;">'.language('global', 'TITLE_LOCAL_PICKUP').'</div>'; 
					echo $order->local_pickup_address?
                    $order->local_pickup_address.'<br />'.
                    $order->local_pickup_city.' '.$order->local_pickup_state.'<br />'.$order->local_pickup_country.' '.strtoupper($order->local_pickup_zip):'';  
                    }					
                    ?>                     
                </div>
                </div>
                <?php }?>
                <?php
				$downloadable_videos_files = array();
				
                // get downloadable videos & files
				if ($result_downloadable_videos = $mysqli->query('SELECT
				orders_item_product_downloadable_videos.*,
				orders_item_product_downloadable_videos_description.name
				FROM 
				orders_item_product_downloadable_videos 
				
				INNER JOIN
				orders_item_product_downloadable_videos_description
				ON
				(orders_item_product_downloadable_videos.id = orders_item_product_downloadable_videos_description.id_orders_item_product_downloadable_videos AND orders_item_product_downloadable_videos_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
				
				LEFT JOIN
				(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
				ON
				(orders_item_product_downloadable_videos.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
				
                LEFT JOIN
				(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
				ON
				(orders_item_product_downloadable_videos.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
				
				WHERE
				(orders_item.id IS NOT NULL AND orders_item.id_orders = "'.$mysqli->escape_string($order->id).'" AND orders.status IN (1,7))
				OR
				(oi.id IS NOT NULL AND oi.id_orders = "'.$mysqli->escape_string($order->id).'" AND o.status IN (1,7))
				ORDER BY 
				orders_item_product_downloadable_videos.id ASC')) {
					while ($row_downloadable_video = $result_downloadable_videos->fetch_assoc()) {
						$downloadable_videos_files[] = $row_downloadable_video;
					}
				}
				
				if ($result_downloadable_files = $mysqli->query('SELECT
				orders_item_product_downloadable_files.*,
				orders_item_product_downloadable_files_description.name
				FROM 
				orders_item_product_downloadable_files 
				
				INNER JOIN
				orders_item_product_downloadable_files_description
				ON
				(orders_item_product_downloadable_files.id = orders_item_product_downloadable_files_description.id_orders_item_product_downloadable_files AND orders_item_product_downloadable_files_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
				
				LEFT JOIN
				(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
				ON
				(orders_item_product_downloadable_files.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
				
                LEFT JOIN
				(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
				ON
				(orders_item_product_downloadable_files.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
				
				WHERE
				(orders_item.id IS NOT NULL AND orders_item.id_orders = "'.$mysqli->escape_string($order->id).'" AND orders.status IN (1,7))
				OR
				(oi.id IS NOT NULL AND oi.id_orders = "'.$mysqli->escape_string($order->id).'" AND o.status IN (1,7))')) {
					while ($row_downloadable_file = $result_downloadable_files->fetch_assoc()) {
						$downloadable_videos_files[] = $row_downloadable_file;
					}					
				}
				
				
				if (sizeof($downloadable_videos_files)) {
				
				?>
	            <div class="col-sm-6" style="padding-left:0;<?php echo $config_site['enable_shipping']?'margin-top:10px;':'';?>">
	            <div class="op_block_title"><?php echo language('account/order', 'TITLE_DOWNLOADS');?></div>
                <div class="title_bg_text_box">
					<table border="0" cellpadding="5" cellspacing="0" width="100%" class="table">
						<thead>
                    	<tr>
                        	<th align="left"><strong><?php echo language('account/order', 'LABEL_LINKS');?></strong></th>
                            <th align="center"><strong><?php echo language('account/order', 'LABEL_CURRENT_NO_DOWNLOADS');?></strong></th>
                            <th align="center"><strong><?php echo language('account/order', 'LABEL_NO_DOWNLOADS');?></strong></th>
						</tr>
						</thead>
                	<?php
					$date_order_timestamp = strtotime($order->date_order);
					
					foreach ($downloadable_videos_files as $row) {
						$date_expire_timestamp = strtotime('+'.$row['no_days_expire'].' day',$date_order_timestamp);
						$expired = 0;
						$no_link = 0;
						
						if ($row['no_days_expire'] && $date_expire_timestamp <= time()) { 
							$expired = 1; 
							$no_link = 1;
						}
						if ($row['no_downloads'] && $row['current_no_downloads'] >= $row['no_downloads']) $no_link = 1;
					
						
						if (isset($row['embed_code'])) {
							echo '<tr>
							<td valign="top">'.($no_link ? $row['name']:'<a href="javascript:void(0);" onclick="javascript:open_video('.$row['id'].',\''.addslashes(htmlspecialchars($row['name'])).'\');">'.$row['name'].'</a>')
							.($row['no_days_expire'] ? '<br />'.($expired ? language('account/order', 'LABEL_LINK_EXPIRED',array('date'=>date('Y-m-d H:i:s',$date_expire_timestamp))):language('account/order', 'LABEL_LINK_EXPIRE',array('date'=>date('Y-m-d H:i:s',$date_expire_timestamp)))):'').'</td>
							<td valign="top" align="center">'.$row['current_no_downloads'].'</td>
							<td valign="top" align="center">'.($row['no_downloads'] ? $row['no_downloads']:language('account/order', 'LABEL_UNLIMITED')).'</td>
							</tr>';
						} else {
							echo '<tr>
							<td valign="top">'.($no_link ? $row['name']:'<a href="download?id='.$row['id'].'" target="_blank">'.$row['name'].'</a>')
							.($row['no_days_expire'] ? '<br />'.($expired ? language('account/order', 'LABEL_LINK_EXPIRED',array('date'=>date('Y-m-d H:i:s',$date_expire_timestamp))):language('account/order', 'LABEL_LINK_EXPIRE',array('date'=>date('Y-m-d H:i:s',$date_expire_timestamp)))):'').'</td>
							<td valign="top" align="center">'.$row['current_no_downloads'].'</td>
							<td valign="top" align="center">'.($row['no_downloads'] ? $row['no_downloads']:language('account/order', 'LABEL_UNLIMITED')).'</td>
							</tr>';
						}						
					}
					?>
                    </table>
                </div>    
                </div>            
                <?php	
				}
                ?> 
                
                
                
                
                
                
                 <div class="col-sm-6" style="padding-left:0">
                 	<div class="op_block_title"><?php echo language('account/order', 'TITLE_DETAILS');?></div>
                 	<div class="title_bg_text_box">  
                      <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table shopping_cart">
                          <thead>
                          <tr>
                              <th style="text-transform:uppercase; width:90%"><?php echo language('cart/print_version', 'LABEL_DESCRIPTION');?></th>
                              <th style="text-align:center;text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_QTY');?></th>
                          </tr>
                          </thead>
                          <?php
                            $counter_product = 0;		
                            $products = $order->get_products();
                            $total_product = count($products);			
                            
                            foreach ($products as $row_product) {
                                $counter_product++; 				
                                echo '<tr>
                                <td valign="top">'.$row_product['name']. ($row_product['used']?'<span style="font-style:italic;">'.language('global', 'LABEL_USED').'</span>':'').(!empty($row_product['variant_name'])?'<br /><em>' . $row_product['variant_name'] . '</em>':'<br />');
                              
                                if (sizeof($row_product['sub_products'])) {
                                    echo '<div style="font-size:10px; margin-left: 10px;">';
                                    
                                    foreach ($row_product['sub_products'] as $row_sub_product) {
                                        echo '<div style="margin-bottom:2px;">'.$row_sub_product['qty'].'x '.$row_sub_product['name'].'</div>';
                                    }			
                                    
                                    echo '</div>';
                                }	
                              
                                echo '</td>
                                <td align="center" valign="top">'.$row_product['qty'].'</td>
                                </tr>';
                                
                                		
                                
                                
                                if(sizeof($order->get_product_options($row_product['id']))){
                                    $options = $order->get_product_options($row_product['id']);
                                    $total_option = count($options);
                                    foreach ($options as $row_product_option_group) { 
                                        $option_group_name = '';
                                        $counter_option++;
                                        foreach ($row_product_option_group['options'] as $row_product_option) { 
                                           if($option_group_name != $row_product_option_group['name']){
                                              echo '<tr><td colspan="2"><strong>'.$row_product_option_group['name'].'</strong></td></tr>';
                                              $option_group_name = $row_product_option_group['name'];
                                           }
                                           
                                            echo '<tr>
                                            <td valign="top"><div><em>'.$row_product_option['name'].'</em></div>';
                                            
                                            switch ($row_product_option_group['input_type']) {
                                                // dropwdown
                                                case 0:											
                                                    break;
                                                // radio
                                                case 1:
                                                    break;
                                                // checkbox
                                                case 3:
                                                    break;
                                                // multi select
                                                case 4:
                                                    break;
                                                // textfield
                                                case 5:
                                                    echo $row_product_option['textfield'];
                                                    break;
                                                // textarea
                                                case 6:
                                                    echo nl2br($row_product_option['textarea']);
                                                    break;
                                                // file
                                                case 7:
                                                    $file_uploads_dir = dirname(__FILE__).'/../file_uploads/';		
                                                    
                                                    echo '<div style="margin-top:5px;"><a href="/file_uploads/'.$row_product_option['filename'].'" target="_blank">'.$row_product_option['filename'].'</a></div>';
                                                    break;
                                                // date
                                                case 8:
                                                    echo (!empty($row_product_option['date_end']) ? language('cart/index', 'LABEL_START_DATE').' '.df_date($row_product_option['date_start'],1,-1):language('cart/index', 'LABEL_DATE').' '.df_date($row_product_option['date_start'],1,-1)).'<br />';
                                                    echo !empty($row_product_option['date_end']) ? language('cart/index', 'LABEL_END_DATE').' '.df_date($row_product_option['date_end'],1,-1):'';
                                                    break;
                                                // datetime
                                                case 9:
                                                    echo (!empty($row_product_option['datetime_end']) ? language('cart/index', 'LABEL_START_DATE_TIME').' '.df_date($row_product_option['datetime_start'],1,3):language('cart/index', 'LABEL_DATE_TIME').' '.df_date($row_product_group['datetime_start'],1,3)).'<br />';
                                                    echo !empty($row_product_option['datetime_end']) ? language('cart/index', 'LABEL_END_DATE_TIME').' '.df_date($row_product_option['datetime_end'],1,3):'';						
                                                    break;
                                                // time
                                                case 10:
                                                    echo (!empty($row_product_option['time_end']) ? language('cart/index', 'LABEL_START_TIME').' '.$row_product_option['time_start']:language('cart/index', 'LABEL_TIME').' '.$row_product_option['time_start']).'<br />';
                                                    echo !empty($row_product_option['time_end']) ? language('cart/index', 'LABEL_END_TIME').' '.$row_product_option['time_end']:'';								
                                                    break;
                                                    
                                            }							
                                            
                                            echo '</td>
                                            <td align="center" valign="top">'.$row_product_option['qty'].'</td>
                                            </tr>';
                    								
                                        }
                                    }
                                }
                          }
                          ?>
                      </table>

  					</div>

                               
            </div>      
            
            
	            <div class="col-sm-6" style="padding-left:0">
	            <div class="op_block_title"><?php echo language('account/order', 'TITLE_ORDER_TOTAL');?></div>
	            
                <div class="title_bg_text_box">
                	<div class="fl" style="margin-bottom:5px;"><strong><?php echo language('account/order','LABEL_SUBTOTAL'); ?></strong></div>
                    <div class="fr"><?php echo nf_currency($order->subtotal); ?></div>
                    <div class="cb"></div>
                    <div class="fl" style="margin-bottom:5px;"><strong><?php echo language('account/order','LABEL_SHIPPING'); ?></strong></div>
                    <div class="fr"><?php echo nf_currency($order->shipping); ?></div>
                    <div class="cb"></div>
                    <?php
						// get taxes
						if (!$result_taxes = $mysqli->query('SELECT
						orders_tax.id,
						orders_tax.tax_number,
						orders_tax.rate,
						orders_tax.stacked,
						orders_tax_description.name,
						IFNULL((SELECT
							SUM(orders_item_product_tax.amount)
							FROM
							orders_item_product_tax
							INNER JOIN
							orders_item_product	
							ON
							(orders_item_product_tax.id_orders_item_product = orders_item_product.id)
							INNER JOIN
							orders_item
							ON
							(orders_item_product.id_orders_item = orders_item.id)
							WHERE
							orders_item.id_orders = orders_tax.id_orders
							AND
							orders_item_product_tax.id_orders_tax = orders_tax.id),0)+IFNULL((SELECT
							SUM(orders_item_product_tax.amount)
							FROM
							orders_item_product_tax
							INNER JOIN
							orders_item_product	
							ON
							(orders_item_product_tax.id_orders_item_product = orders_item_product.id)
							INNER JOIN
							(orders_item_product AS cip CROSS JOIN orders_item AS ci)
							ON
							(orders_item_product.id_orders_item_product = cip.id AND cip.id_orders_item = ci.id)
							WHERE
							ci.id_orders = orders_tax.id_orders
							AND
							orders_item_product_tax.id_orders_tax = orders_tax.id),0)+IFNULL((SELECT
							SUM(orders_item_option_tax.amount)
							FROM
							orders_item_option_tax
							INNER JOIN
							orders_item_option	
							ON
							(orders_item_option_tax.id_orders_item_option = orders_item_option.id)
							INNER JOIN
							orders_item	
							ON
							(orders_item_option.id_orders_item = orders_item.id)
							WHERE
							orders_item.id_orders = orders_tax.id_orders
							AND
							orders_item_option_tax.id_orders_tax = orders_tax.id),0)+IFNULL((SELECT
							SUM(orders_shipping_tax.amount)
							FROM
							orders_shipping_tax
							WHERE
							orders_shipping_tax.id_orders = orders_tax.id_orders
							AND
							orders_shipping_tax.id_orders_tax = orders_tax.id),0) AS total_taxes
						FROM 
						orders_tax
						INNER JOIN
						(orders_tax_description)
						ON
						(orders_tax.id = orders_tax_description.id_orders_tax AND orders_tax_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
						WHERE
						orders_tax.id_orders = "'.$mysqli->escape_string($order->id).'"
						ORDER BY 
						orders_tax.sort_order ASC')) throw new Exception('An error occured while trying to get taxes.'."\r\n\r\n".$mysqli->error);
						
						while ($row_taxes = $result_taxes->fetch_assoc()) {
							echo '<div class="fl"><strong>'.$row_taxes['name'].'</strong></div>
							<div class="fr" style="margin-bottom:5px;">'.nf_currency($row_taxes['total_taxes']).'</div>
							<div class="cb"></div>';	
						}
						
						$result_taxes->free();
					?>                    
                    <div class="fl"><strong><?php echo language('account/order','LABEL_TOTAL'); ?></strong></div>
                    <div class="fr"><strong><?php echo nf_currency($order->total); ?></strong></div>
                    <div class="cb"></div>
                </div>
                </div>
                
                <?php if ($order->grand_total > 0) { ?>
            	
	            <div class="col-sm-6" style="padding-left:0">
	            <div class="op_block_title"><?php echo language('account/order', 'TITLE_PAYMENT');?></div>
                <div class="title_bg_text_box" style="margin:0px;">
                    <?php
					echo '<div class="fl" style="width:30%;"><strong>';
                    switch ($order->payment_method) {
						//cc
						case 0:
							echo language('cart/print_version','LABEL_PAYMENT_METHOD_CREDIT_CARD');
							break;
						// interact
						case 1:
							echo language('cart/print_version','LABEL_PAYMENT_METHOD_INTERACT');
							break;
						// cheque
						case 2:
							echo language('cart/print_version','LABEL_PAYMENT_METHOD_CHEQUE');
							break;
						// no balance to pay
						case 3:
							break;
						// paypal
						case 4:
							echo 'PayPal';
							break;
						// cash
						case 5:
							echo language('cart/print_version','LABEL_PAYMENT_METHOD_CASH');
							break;					
					}
					echo '</strong></div>';
					
					echo '<div class="fr" style="width:69%;">';
					
					// beanstream
					if (isset($transaction_details['SCtrnId'])) {
					?>
					<div>
						<div style="margin-bottom:5px;"><?php echo language('account/order','LABEL_AMOUNT');?>: <?php echo nf_currency($transaction_details['SCtrnAmount']);?></div>
						<div style="margin-bottom:5px;"><?php echo language('account/order','LABEL_TRANSACTION_DATE');?>: <?php echo $transaction_details['SCtrnDate'];?></div>
						<?php echo ($transaction_details['SCtrnType']?'<div style="margin-bottom:5px;">'.language('account/order','LABEL_TYPE').' : '.$transaction_details['SCtrnType'].'</div>':'');?>
						<?php echo ($transaction_details['SCcardType']?'<div style="margin-bottom:5px;">'.language('account/order','LABEL_CC').' : '.$transaction_details['SCcardType'].'</div>':'');?>
						<div style="margin-bottom:5px;"><?php echo language('account/order','LABEL_HOLDER');?>: <?php echo $transaction_details['SCtrnCardOwner']; ?></div>
						<div style="margin-bottom:5px;"><?php echo language('account/order','LABEL_AUTHORIZATION_NO');?>: <?php echo $transaction_details['SCauthCode']; ?></div>
						<div style="margin-bottom:5px;"><?php echo language('account/order','LABEL_REFERENCE_NO');?>: <?php echo $transaction_details['SCtrnId']; ?></div>
						<div style="margin-bottom:5px;"><?php echo language('account/order','LABEL_RESPONSE');?>: <span class="<?php echo(($transaction_details['SCtrnApproved'])?'success':'error');?>"><?php echo language('transaction/'.$transaction_details['SCcompanyName'],'ID_'.$transaction_details['SCmessageId']); ?></span></div>
					</div>      
				   <?php 			
					} 
					
					
					echo '</div>
					<div class="cb"></div>';
				?>
                </div>
                </div>
				<?php   
				}
				             					
				// get gift certificates
				if (!$result_gift_certificates = $mysqli->query('SELECT 
				orders_gift_certificate.*
				FROM
				orders_gift_certificate
				WHERE
				orders_gift_certificate.id_orders = "'.$mysqli->escape_string($order->id).'"
				ORDER BY
				orders_gift_certificate.id ASC')) throw new Exception('An error occured while trying to get gift certificates.'."\r\n\r\n".$mysqli->error);
				
				if ($result_gift_certificates->num_rows) {
					echo '<div class="title_bg_text_box" style="border-top:none;">
					<div class="fl" style="width:30%;"><strong>'.language('account/order','LABEL_GIFT_CERTIFICATES_APPLIED').'</strong></div>';
					
					echo '<div class="fr" style="width:69%;">';
					
					while ($row_gift_certificate = $result_gift_certificates->fetch_assoc()) {
						echo '<div class="fl" style="margin-bottom:5px;">'.$row_gift_certificate['code'].'</div>
						<div class="fr">'.nf_currency($row_gift_certificate['amount']).'</div>
						<div class="cb"></div>';
					}
					
					echo '</div>
						<div class="cb"></div>                  
					</div>';
				}
				$result_gift_certificates->free();				
				?>                    
                         
				<div class="col-sm-6" style="padding-left:0">
				<div class="op_block_title"><?php echo language('account/order', 'TITLE_COMMENTS');?></div>
                <div class="title_bg_text_box">
                	<form method="post" id="form_comment" enctype="multipart/form-data">
                    <textarea name="comment" id="comment" rows="3" style="width:100%;"></textarea>
                    <div class="button_regular fr">
                        <input type="button" style="margin: 10px 0;" name="_add_comment" id="_add_comment" value="<?php echo language('account/order','BTN_ADD_COMMENT');?>" class="button regular" />
                    </div>
                    </form>
                    <div class="cb"></div>                                    
                	<?php
					if (!$result_comments = $mysqli->query('SELECT 
					orders_comment.*,
					CONCAT(user.firstname," ",user.lastname) AS name
					FROM 
					orders_comment
					LEFT JOIN
					user
					ON
					(orders_comment.id_user_created = user.id)				
					WHERE
					orders_comment.id_orders = "'.$mysqli->escape_string($order->id).'"
					AND
					orders_comment.hidden_from_customer = 0
					ORDER BY
					orders_comment.date_created DESC')) throw new Exception('An error occured while trying to get comments.'."\r\n\r\n".$mysqli->error);
					
					echo '<div id="comments" style="margin-top:5px; border-top:1px solid #cccccc;">';
					
					while ($row_comment = $result_comments->fetch_assoc()) {
						echo '<div style="margin-top:10px;">
							<div class="fl"><strong>'.language('account/order','LABEL_COMMENT_FROM').'</strong> '.($row_comment['id_user_created'] ? $config_site['site_name']:language('account/order','LABEL_COMMENT_ME')).'</div>
							<div class="fr"><strong>'.language('account/order','LABEL_COMMENT_DATE').'</strong> '.df_date($row_comment['date_created'],1).'</div>
							<div class="cb"></div>
							<div style="margin-top:5px;">'.htmlspecialchars($row_comment['comments']).'</div>
							<div class="cb"></div>
						</div>';
					}			
					
					echo '</div>';		
					?>
                </div> 
                </div>                    	
			
            
            <div class="cb"></div>
		       
    </div>
</div>
	</div>	
	<p>&nbsp;</p>       
</div>
<script	type="text/javascript" language="javascript">
jQuery(function(){
	jQuery("#_add_comment").on("click",function(){
		if (jQuery("#comment").val()) {
			jQuery("#comment").removeClass("error");
			
			jQuery.ajax({
				url: "?id=<?php echo $id; ?>&task=add_comment",
				data: jQuery("#form_comment").serialize(),
				success: function(data) {					
					jQuery("#comments").html("").append(data);					
				}
			});					
		} else {
			if (!jQuery("#comment").hasClass("error")) jQuery("#comment").addClass("error");
			alert("<?php echo language('account/order','ERROR_NO_COMMENT'); ?>");	
		}
	});
	
	jQuery( "#video_dialog" ).dialog({
		autoOpen: false,
		show: "fade",
		hide: "fade",
		position: "center",
		width: "auto", 
		height: "auto",
		modal: true,
		resizable: false,
		beforeClose: function(event, ui) { 
			jQuery("#video_dialog").html("");
		}
	});	
});

function open_video(id,title){
	jQuery.ajax({
		url: "get_video",
		data: { "id":id },
		dataType: "json",
		success: function(data) {
			if (data) {
				if (data.errors) {
					alert(data.errors);																																		
				} else {
					jQuery("#video_dialog").dialog( "option", "title", title);
					jQuery("#video_dialog").html("").append(data.video).dialog( "open" );											
				}
			} else {
				alert("<?php echo language('global','ERROR_OCCURED'); ?>");	
			}						
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(jqXHR.responseText);
			alert("<?php echo language('global','ERROR_OCCURED'); ?>");	
		}
	});			
}
</script>
<div id="video_dialog" style="text-align:center;"></div>
<?php include(dirname(__FILE__) . "/../_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/../_includes/template/bottom.php");?>
</body>
</html>