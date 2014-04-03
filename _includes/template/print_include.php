<?php 
if ($order->payment_method != 0) { 
	$transaction_details['SCcompanyName'] = 'all'; 
	$transaction_details['SCmessageId'] = 1; 
}

if ($order->payment_method == 4 && empty($transaction_details['PAYMENTINFO_0_TRANSACTIONID'])){ 
	$transaction_details['SCmessageId'] = 3; 
}

// SHOW THE COMPANY HEADER
if($show_company_header){
    include("header_print.php");
}?>
  <div style="margin-bottom:20px">
    <div class="fl"><strong style="text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_DATE');?></strong> <?php echo df_date($order->date_order,1,-1);?></div>
    <div class="fr"><strong style="text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_INVOICE_NO');?></strong> <?php echo $order->id; ?></div>
    <div class="cb"></div>
  </div>
  <div style="margin-bottom:20px">
  <div style="margin-bottom:20px;">
      <div style="text-transform:uppercase; font-size:16px; margin-bottom:10px;"><strong><?php echo language('cart/print_version', 'LABEL_TRANSACTION_STATUS');?></strong> <span class="<?php echo($order->payment_method == 3 || ($order->payment_method == 4 && !empty($transaction_details['PAYMENTINFO_0_TRANSACTIONID'])) || $transaction_details['SCtrnApproved']?'success':'error');?>"><?php echo language('transaction/'.$transaction_details['SCcompanyName'],'ID_'.$transaction_details['SCmessageId']); ?></span>
      </div>
      <?php if ($order->payment_method != 3) { ?>
      <div><strong style="text-transform:uppercase;"><?php echo language('cart/print_version', 'LABEL_METOHD_PAYMENT');?></strong> 
      <?php
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
      ?>
      </div>
      <?php } ?>

  </div>
  <div>
        <div class="col-sm-6" style="padding-left:0; width:30%; float:left;">
            <div class="op_block_title"><strong style="text-transform:uppercase"><?php echo language('global', 'TITLE_BILLING_ADDRESS');?></strong></div>
            <div class="op_block_detail">
                <?php
                echo 
                ($order->billing_company?$order->billing_company.'<br />':'').
                ($order->billing_company?$order->billing_firstname. ' ' .$order->billing_lastname.'<br />':$order->billing_firstname. ' ' .$order->billing_lastname.'<br />').
                $order->billing_address.'<br />'.
                $order->billing_city.($order->billing_state?' '.$order->billing_state:'').' '.strtoupper($order->billing_zip).'<br />'.
				$order->billing_country.'<br /><strong>'.
				language('global','LABEL_CONTACT_US_T') . '</strong> '.$order->billing_telephone;
				
                ?> 
            </div>  
        </div>
        <?php if($config_site['enable_shipping']){?>
        <div class="col-sm-6" style="width:30%; float:left;">
            <div class="op_block_title"><strong style="text-transform:uppercase"><?php echo language('global', 'TITLE_SHIPPING_ADDRESS');?></strong></div>
            <div class="op_block_detail">
                <?php
                if(!$order->local_pickup){
                echo 
                ($order->shipping_company?$order->shipping_company.'<br />':'').
                ($order->shipping_company?$order->shipping_firstname. ' ' .$order->shipping_lastname.'<br />':$order->shipping_firstname. ' ' .$order->shipping_lastname.'<br />').
                $order->shipping_address.'<br />'.
				$order->shipping_city.($order->shipping_state?' '.$order->shipping_state:'').' '.strtoupper($order->shipping_zip).'<br />'.
				$order->shipping_country.'<br />'.
				($order->shipping_service?'<strong>'.language('global','LABEL_SHIPPING_METHOD') . '</strong> '.$order->shipping_service.'<br />':'').'
				<strong>'.language('global','LABEL_CONTACT_US_T') . '</strong> '.$order->shipping_telephone;
				
				
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
        <?php if ($order->payment_method == 0 || $order->payment_method == 4) { ?>
    <div class="col-sm-6" style="width:40%;padding-right:0; float:left;">
      <div class="op_block_title"><strong style="text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_PAYMENT_SLIPS');?></strong></div>
        <div class="op_block_detail">
        <?php if ($order->payment_method == 0) { ?>
        <div class="fl">
            <div><?php echo language('cart/print_version', 'LABEL_AMOUNT');?> <?php echo nf_currency($order->grand_total);?></div>
            <div><?php echo language('cart/print_version', 'LABEL_DATE');?> <?php echo $transaction_details['SCtrnDate'];?></div>
            <?php echo $transaction_details['SCtrnType']?'<div>'.language('cart/print_version', 'LABEL_TYPE').' ' . $transaction_details['SCtrnType'].'</div>':'';?>
            <?php echo $transaction_details['SCcardType']?'<div>'.language('cart/print_version', 'LABEL_CC').' ' . $transaction_details['SCcardType'].'</div>':'';?>
             <?php echo $transaction_details['SClastFourDigits']?'<div>'.language('cart/print_version', 'LABEL_CC_NUMBER').' XXXX XXXX XXXX ' . $transaction_details['SClastFourDigits'].'</div>':'';?>
        </div>
        <div class="fl" style="margin-left:8px;">
            <?php echo $transaction_details['SCtrnCardOwner']?'<div>'.language('cart/print_version', 'LABEL_HOLDER').' ' . $transaction_details['SCtrnCardOwner'].'</div>':'';?>
            <div><?php echo language('cart/print_version', 'LABEL_AUTORISATION_NUMBER');?> <?php echo $transaction_details['SCauthCode']; ?></div>
            
            <div><?php echo language('cart/print_version', 'LABEL_RESPONSE');?> <strong><span class="<?php echo($transaction_details['SCtrnApproved']?'success':'error');?>"><?php echo language('transaction/'.$transaction_details['SCcompanyName'],'ID_'.$transaction_details['SCmessageId']); ?></span></strong></div>
        </div>
        <div class="cb"></div>
        <div><?php echo language('cart/print_version', 'LABEL_REFERENCE_NUMBER');?> <?php echo $transaction_details['SCtrnId']; ?></div>
        <div class="cb"></div>
       <?php } else if ($order->payment_method == 4) { ?>
        <div class="fl">
            <div><?php echo language('cart/print_version','LABEL_AMOUNT');?> <?php echo nf_currency($order->grand_total);?></div>
            <div><?php echo language('cart/print_version','LABEL_DATE');?> <?php echo $order->date_order;?></div>
            <div><?php echo language('cart/print_version','LABEL_TYPE');?> <?php echo $transaction_details['PAYMENTINFO_0_TRANSACTIONTYPE']; ?></div>
            <div><?php echo language('cart/print_version','LABEL_PAYPAL_TRANSACTION_ID');?> <?php echo $transaction_details['PAYMENTINFO_0_TRANSACTIONID']; ?></div>
            <div><?php echo language('cart/print_version','LABEL_PAYPAL_EMAIL');?> <?php echo $transaction_details['EMAIL']; ?></div>
        </div>     
        <?php } ?>
        </div>
      </div>        
      <?php } ?>
        <div class="cb"></div>
  </div>
  
  
  
  
  </div>
  <div>
  
  <div class="cart-item">
  <table border="0" cellpadding="0" cellspacing="0" width="100%" class="shopping_cart data-table">
      <thead>
      <tr>
          <th style="text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_SKU');?></th>
          <th style="text-transform:uppercase; width:70%"><?php echo language('cart/print_version', 'LABEL_DESCRIPTION');?></th>
          <th style="text-align:center;text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_QTY');?></th>
          <th style="text-align:right;text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_PRICE');?></th>
          <th style="text-align:right;text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_TOTAL');?></th>
      </tr>
      </thead>
      <?php
        $counter_product = 0;		
        $products = $order->get_products();
        $total_product = count($products);			
        
        foreach ($products as $row_product) {
            $counter_product++; 				
            echo '<tr>
            <td valign="top">'.(!empty($row_product['variant_sku']) ? $row_product['variant_sku']:$row_product['sku']).'</td>
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
            <td align="right" valign="top" nowrap>'.nf_currency($row_product['sell_price']).'</td>
            <td align="right" valign="top" nowrap>'.nf_currency($row_product['subtotal']).'</td>
            </tr>';
            
            // get discounts applied to this product
            if (sizeof($product_discounts = $order->get_product_discounts($row_product['id_orders_item_product']))) {
                foreach ($product_discounts as $row_product_discount) {
                    echo '<tr>
                    <td>&nbsp;</td>
                    <td valign="top" colspan="3">
                        <div>'.
				$row_product_discount['description'].
				
				($row_product_discount['coupon'] && $row_product_discount['coupon_code'] ? '<br />
				<span>'.language('cart/index', 'LABEL_CODE_COUPON').' '.$row_product_discount['coupon_code'].'</span>':'')
				
				.($row_product_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span>'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_discount['end_date']).'</span>':'').'
                        </div>
                    </td>
                    
                    <td valign="top" align="right">'.($row_product_discount['amount'] > 0 ? '<span>'.nf_currency(-$row_product_discount['amount']).'</span>':'&nbsp;').'
                    </td>
                    </tr>';
                    
                    $total_save += $row_product_discount['amount'];
                }
            }					
            
            
            if(sizeof($order->get_product_options($row_product['id']))){
                $options = $order->get_product_options($row_product['id']);
                $total_option = count($options);
                foreach ($options as $row_product_option_group) { 
                    $option_group_name = '';
                    $counter_option++;
                    foreach ($row_product_option_group['options'] as $row_product_option) { 
                       if($option_group_name != $row_product_option_group['name']){
                          echo '<tr><td>&nbsp;</td><td colspan="3"><strong>'.$row_product_option_group['name'].'</strong></td></tr>';
                          $option_group_name = $row_product_option_group['name'];
                       }
                       
                        echo '<tr>
                        <td valign="top">'.$row_product_option['sku'].'</td>
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
                        <td align="right" valign="top" nowrap>'.nf_currency($row_product_option['sell_price']).'</td>
                        <td align="right" valign="top" nowrap="nowrap">'.nf_currency($row_product_option['subtotal']).'</td>
                        </tr>';
                        
                        // get discounts applied to this option							
                        if (sizeof($product_option_discounts = $order->get_product_option_discounts($row_product_option['id']))) {
                            foreach ($product_option_discounts as $row_product_option_discount) {
                                echo '<tr>
                                <td>&nbsp;</td>
                                <td valign="top" colspan="3">
                                    <div>'.
				$row_product_option_discount['description'].($row_product_option_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span>'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_option_discount['end_date']).'</span>':'').'
                                    </div>
                                </td>
                                
                                <td valign="top" align="right">'.($row_product_option_discount['amount'] > 0 ? '<span>'.nf_currency(-$row_product_option_discount['amount']).'</span>':'&nbsp;').'
                                </td>
                                </tr>';
                                
                                $total_save += $row_product_option_discount['amount'];
                            }
                        }									
                    }
                }
            }
      }
      ?>
  </table>
  </div>
  <div style="float:right; width:250px;">
  <table border="0" cellpadding="3" cellspacing="2" width="100%">
      <tr>
          <th align="right"><strong><?php echo language('cart/print_version', 'LABEL_SUBTOTAL');?></strong></th>
          <td align="right" ><?php echo nf_currency($order->subtotal);?></td>
      </tr>
     <?php if($config_site['enable_shipping']){?>
      <tr>
          <th align="right"><strong><?php echo language('cart/print_version', 'LABEL_SHIPPING');?></strong></th>
          <td align="right"><div id="shipping_cost_display"><?php echo nf_currency($order->shipping);?></div></td>
      </tr>
      <?php }?>
      <?php
      if(sizeof($taxes = $order->get_taxes())){
          foreach($taxes as $value){
              echo '<tr>
              <th align="right"><strong>'.$value['name'].'</strong></th>
              <td align="right">'.$value['total_taxes'].'</td>
              </tr>';
          }
      }
      ?>
      <?php if ($order->gift_certificates > 0) { ?>
      <tr>
          <th align="right"><strong><?php echo language('cart/print_version','LABEL_GIFT_CERTIFICATES'); ?></strong></th>
          <td align="right"><?php echo nf_currency(-$order->gift_certificates);?></td>
      </tr>          
      <?php } ?>
  </table>
  
  </div>
  <div class="cb">&nbsp;</div>
  <div style="float:right; font-size:18px; width:250px">
  <div class="fl" style=""><strong><?php echo language('cart/print_version', 'LABEL_BIG_TOTAL');?></strong></div>
  <div id="total_display" class="fl" style="float:right; margin-left:9px; text-align:right"><strong><?php echo nf_currency($order->grand_total);?></strong></div>
  <div class="cb"></div>
  </div>
  <div class="cb"></div> 
  <div id="taxe_number">
  <?php
  $query = 'SELECT 
          orders_tax_description.name,
          orders_tax.tax_number
          FROM orders_tax
          INNER JOIN orders_tax_description ON orders_tax.id = orders_tax_description.id_orders_tax AND orders_tax_description.language_code = "'.$_SESSION['customer']['language'].'"
          WHERE orders_tax.id_orders = ' . $id_orders;
  if ($result = $mysqli->query($query)) {
  
  if($result->num_rows){
      while($row = $result->fetch_assoc()){
		  echo $row['tax_number']?strtoupper($row['name']) . ' : ' . $row['tax_number'] . '&nbsp;&nbsp;':'';
      }
  }
  $result->free();
  }
  ?>
  
  </div>
<?php
if (!$error_page) {
	$downloadable = 0;
	if ($result_downloadable = $mysqli->query('SELECT
	COUNT(orders_item_product_downloadable_videos.id) AS total
	FROM 
	orders_item_product_downloadable_videos 
		
	LEFT JOIN
	(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
	ON
	(orders_item_product_downloadable_videos.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
	
	LEFT JOIN
	(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
	ON
	(orders_item_product_downloadable_videos.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
	
	WHERE
	(orders_item.id IS NOT NULL AND orders_item.id_orders = "'.$mysqli->escape_string($id_orders).'")
	OR
	(oi.id IS NOT NULL AND oi.id_orders = "'.$mysqli->escape_string($id_orders).'")
	LIMIT 1')) {
		$row_downloadable = $result_downloadable->fetch_assoc();
		if ($row_downloadable['total']) $downloadable = 1;
	}
	$result_downloadable->free();
	
	if (!$downloadable) {
		if ($result_downloadable = $mysqli->query('SELECT
		COUNT(orders_item_product_downloadable_files.id) AS total
		FROM 
		orders_item_product_downloadable_files 
			
		LEFT JOIN
		(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
		ON
		(orders_item_product_downloadable_files.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
		
		LEFT JOIN
		(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
		ON
		(orders_item_product_downloadable_files.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
		
		WHERE
		(orders_item.id IS NOT NULL AND orders_item.id_orders = "'.$mysqli->escape_string($id_orders).'")
		OR
		(oi.id IS NOT NULL AND oi.id_orders = "'.$mysqli->escape_string($id_orders).'")
		LIMIT 1')) {
			$row_downloadable = $result_downloadable->fetch_assoc();
			if ($row_downloadable['total']) $downloadable = 1;
		}
		$result_downloadable->free();
	}
	
	if ($downloadable) {
		echo '<div style="color:#FF0000; font-size:14px; margin-top:10px; margin-bottom:10px; font-weight:bold;">'.language('cart/print_version','LABEL_DOWNLOADABLES').'</div>';
	}
}
?>  
  
  <div class="cb page-break">&nbsp;</div>  
  <div id="policy_term">
            <?php
            $query = 'SELECT 
                        cmspage_description.name,
                        cmspage_description.description
                        FROM cmspage
                        INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'"
                        WHERE cmspage.active = 1 AND cmspage.id = 24';
            
            if ($result = $mysqli->query($query)) {
                if($result->num_rows){
                    $row = $result->fetch_assoc();
                    $description = $row['description'];
                    $name = $row['name'];
                }
                $result->free();
            }
            
            echo '<h1>'.$name.'</h1>';
            echo '<div style="font-size:10px">'.$description.'</div>';
            ?>
  
            <?php
            $query = 'SELECT 
                        cmspage_description.name,
                        cmspage_description.description
                        FROM cmspage
                        INNER JOIN cmspage_description ON cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$_SESSION['customer']['language'].'"
                        WHERE cmspage.active = 1 AND cmspage.id = 25';
            
            if ($result = $mysqli->query($query)) {
                if($result->num_rows){
                    $row = $result->fetch_assoc();
                    $description = $row['description'];
                    $name = $row['name'];
                }
                $result->free();
            }
            
            echo '<h1>'.$name.'</h1>';
            echo '<div style="font-size:10px">'.$description.'</div>';
            ?>
  
  </div>             
  </div>
