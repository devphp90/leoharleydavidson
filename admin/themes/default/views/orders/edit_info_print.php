<?php 
$model_name = get_class($model);
//$connection=Yii::app()->db;   // assuming you have configured a "db" connection

//$columns = Html::getColumnsMaxLength(Tbl_Product::tableName());	

$transaction_details = $order['transaction_details'] ? unserialize(base64_decode($order['transaction_details'])):array();

//echo '<pre>'.print_r($config_site,1).'</pre>';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="/_css/print_version_order.css" />
</head>
<body>
<div id="content">    
    
  <div style="margin-bottom:20px">
       <div style="border-bottom: solid 1px #CCC; padding-bottom: 10px; margin-bottom: 10px;">
        <div class="fl"><img src="/_images/<?php echo $config_site['company_logo_print_file'];?>" alt="<?php echo $config_site['site_name'];?>" name="logo" id="logo" height="70" /></div>
        <div class="fr" style="padding-top:8px;" align="right">
        	<?php echo $config_site['site_name'];?><br />
            <?php echo $config_site['company_address']?' ' . $config_site['company_address']:'';?>
            <?php echo $config_site['company_city']?'<br />' . $config_site['company_city']:'';?>
            <?php
            $country_name = $config_site['country_name'];
            $state_name = $config_site['state_name']; 
            echo $state_name?' ' . $state_name:'';?>
			<?php echo $country_name?' ' . $country_name:'';?>            
            <?php echo $config_site['company_zip']?' ' . $config_site['company_zip']:'';?>
            
            <br />
            <?php echo $config_site['company_telephone']?'<strong>'.Yii::t('views/orders/edit_info_print','LABEL_CONTACT_US_T').'</strong> ' . $config_site['company_telephone']:'';?>
            <?php echo $config_site['company_fax']?' <strong>'.Yii::t('views/orders/edit_info_print','LABEL_CONTACT_US_F').'</strong> ' . $config_site['company_fax']:'';?>
            <?php echo $config_site['company_email']?'<br /><strong>'.Yii::t('views/orders/edit_info_print','LABEL_CONTACT_US_E').'</strong> <a href="mailto:' . $config_site['company_email'].'">' . $config_site['company_email'] . '</a>':'';?>
        </div>
        <div class="cb"></div>
    </div> 
    <div style="margin-bottom:20px">
        <div style="float:left;"><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_ORDER_DATE');?></strong>: <?php echo Yii::app()->dateFormatter->formatDateTime(strtotime($order['date_order']),'long','null'); ?></div>
        <div style="float:right;"><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_INVOICE_NO');?></strong>: <?php echo $order['id']; ?></div>
        <div style="clear:both;"></div>
    </div> 
      <div style="margin-bottom:20px;">
		
        <?php 
		if ($order['payment_method']!=0) { 
			$transaction_details['SCcompanyName'] = 'all'; 
			$transaction_details['SCmessageId'] = 1; 
		}
		if ($order['payment_method'] == 4 && empty($transaction_details['PAYMENTINFO_0_TRANSACTIONID'])){ 
			$transaction_details['SCmessageId'] = 3; 
		}
		?>
        
         <div style="text-transform:uppercase; font-size:16px; margin-bottom:10px;"><strong><?php echo Yii::t('views/orders/edit_info_options','LABEL_TRANSACTION_STATUS');?></strong> <span class="<?php echo($order['payment_method'] == 3 ||  ($order['payment_method'] == 4 && !empty($transaction_details['PAYMENTINFO_0_TRANSACTIONID'])) || $transaction_details['SCtrnApproved']?'success':'error');?>"><?php echo Yii::t('transaction/'.$transaction_details['SCcompanyName'],'ID_'.$transaction_details['SCmessageId']); ?></span>
      </div>
      <div><strong style="text-transform:uppercase;"><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD');?></strong> 
       <?php
			switch ($order['payment_method']) {
				//cc
				case 0:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CREDIT_CARD');
					break;
				// interact
				case 1:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_INTERACT');
					break;
				// cheque
				case 2:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CHEQUE');
					break;
				// paypal
				case 4:
					echo 'PayPal';
					break;
				// cash
				case 5:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CASH');
					break;					
			}
		  ?>
      </div>
          
      </div>
      <div>
      		<div style="float:left;">
                <div><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_BILLING_ADDRESS');?></strong></div>
                <div>
                    <?php
                    echo 
                    ($order['billing_company']?$order['billing_company'].'<br />':'').
                    $order['billing_firstname']. ' ' .$order['billing_lastname'].'<br />'.
                    $order['billing_address'].'<br />'.
                    $order['billing_city'].($order['billing_state']?' '.$order['billing_state']:'').' '.strtoupper($order['billing_zip']).'<br />'.
					$order['billing_country'].'<br />
					<strong>'.Yii::t('global','LABEL_CONTACT_US_T') . '</strong> '.$order['billing_telephone'].'<br />
					<strong>'.Yii::t('global','LABEL_CONTACT_US_E') . '</strong> '.$order['email'];
                    ?> 
                </div>  
            </div>
            <div style="float:left; padding-left:20px;">
                <div><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_SHIPPING_ADDRESS');?></strong></div>
                <div>
                    <?php
                    if(!$order['local_pickup']){
                    echo 
                    ($order['shipping_company']?$order['shipping_company'].'<br />':'').
                    $order['shipping_firstname']. ' ' .$order['shipping_lastname'].'<br />'.
                    $order['shipping_address'].'<br />'.
                   $order['shipping_city'].($order['shipping_state']?' '.$order['shipping_state']:'').' '.strtoupper($order['shipping_zip']).'<br />'.
					$order['shipping_country'].'<br />'.
					($order['shipping_service']?'<strong>'.Yii::t('global','LABEL_SHIPPING_METHOD') . '</strong> '.$order['shipping_service'].'<br />':'').'
					<strong>'.Yii::t('global','LABEL_CONTACT_US_T') . '</strong> '.$order['shipping_telephone'];
					
                    }else{
                    	echo '<div><strong>'.Yii::t('views/orders/edit_info_options','LABEL_LOCAL_PICKUP').'</strong></div>'; 
						echo 
						$order['local_pickup_address']?
							$order['local_pickup_address'].'<br />'.
							$order['local_pickup_city'].' '.$order['local_pickup_state'].' '.strtoupper($order['local_pickup_zip']).'<br />'.
							$order['local_pickup_country']
						:'';    
                    }
                    ?> 
                </div>
            </div>
            <?php if ($order['payment_method'] == 0 || $order['payment_method'] == 4) { ?>
        <div style="float:left; margin-left:20px; padding-left:20px; border-left: solid 1px #666;">
       	  <div><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_SLIP');?></strong></div>
           	<?php 
				if ($order['payment_method'] == 0) {
				// beanstream
				if (isset($transaction_details['SCtrnId'])) {
				?>
				<div style="float:left;">
					<div><?php echo Yii::t('views/orders/edit_info_options','LABEL_AMOUNT');?> <?php echo Html::nf($order['grand_total']);?></div>
					<div><?php echo Yii::t('views/orders/edit_info_options','LABEL_DATE');?> <?php echo $transaction_details['SCtrnDate'];?></div>
                    <?php echo $transaction_details['SCtrnType']?'<div>'.Yii::t('views/orders/edit_info_options','LABEL_TYPE').' ' . $transaction_details['SCtrnType'].'</div>':'';?>
            		<?php echo $transaction_details['SCcardType']?'<div>'.Yii::t('views/orders/edit_info_options','LABEL_CC').' ' . $transaction_details['SCcardType'].'</div>':'';?>
                    
                    <?php echo $transaction_details['SClastFourDigits']?'<div>'.Yii::t('views/orders/edit_info_options', 'LABEL_CC_NUMBER').' ' . $transaction_details['SClastFourDigits'].'</div>':'';?>
				</div>
				<div style="float:left;margin-left:8px;">
                    <?php echo $transaction_details['SCtrnCardOwner']?'<div>'.Yii::t('views/orders/edit_info_options','LABEL_HOLDER').' ' . $transaction_details['SCtrnCardOwner'].'</div>':'';?>
					<div><?php echo Yii::t('views/orders/edit_info_options','LABEL_AUTHORIZATION_NO');?> <?php echo $transaction_details['SCauthCode']; ?></div>
                    
					<div><?php echo Yii::t('views/orders/edit_info_options','LABEL_RESPONSE');?> <span class="<?php echo($transaction_details['SCtrnApproved']?'success':'error');?>"><strong><?php echo Yii::t('transaction/'.$transaction_details['SCcompanyName'],'ID_'.$transaction_details['SCmessageId']); ?></strong></span></div>
				</div> 
                <div style="clear:both;"></div>
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_REFERENCE_NO');?> <?php echo $transaction_details['SCtrnId']; ?></div>                   
			   <?php 		
				}
				} else if ($order['payment_method'] == 4) {
				?>
        <div class="fl">
            <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_AMOUNT');?> <?php echo Html::nf($order['grand_total']);?></div>
            <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_DATE');?> <?php echo $order['date_order'];?></div>
            <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_TYPE');?> <?php echo $transaction_details['PAYMENTINFO_0_TRANSACTIONTYPE']; ?></div>
            <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYPAL_TRANSACTION_ID');?> <?php echo $transaction_details['PAYMENTINFO_0_TRANSACTIONID']; ?></div>
            <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYPAL_EMAIL');?> <?php echo $transaction_details['EMAIL']; ?></div>
        </div>                 
                <?php					
				}
		   ?>   
           <div style="clear:both;"></div>
          </div>
          <?php } ?>
            <div style="clear:both;"></div>
      </div>
      
      
      
     
  </div>
  <div>
  
      <div class="cart-item">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" class="shopping_cart">
          <tr>
              <th style="text-transform:uppercase" width="10%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_SKU');?></th>
              <th style="text-transform:uppercase;" width="60%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_DESCRIPTION');?></th>
              <th style="text-align:center;text-transform:uppercase" width="5%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_QTY');?></th>
              <th style="text-align:right;text-transform:uppercase" width="10%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_PRICE');?></th>
              <th style="text-align:right;text-transform:uppercase" width="15%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_TOTAL');?></th>
          </tr>
          <?php
			$counter_product = 0;		
		
			foreach ($order['products'] as $row_product) {
				$counter_product++; 				
				echo '<tr>
				<td valign="top">'.(!empty($row_product['variant_sku']) ? $row_product['variant_sku']:$row_product['sku']).'</td>
				<td valign="top">'.$row_product['name'].(!empty($row_product['variant_name'])?'<br /><em>' . $row_product['variant_name'] . '</em>':'<br />');
			  
				if (sizeof($row_product['sub_products'])) {
					echo '<div style="font-size:10px; margin-left: 10px;">';
					
					foreach ($row_product['sub_products'] as $row_sub_product) {
						echo '<div style="margin-bottom:2px;">'.$row_sub_product['qty'].'x '.$row_sub_product['name'].'</div>';
					}			
					
					echo '</div>';
				}	
			  
				echo '</td>
				<td align="center" valign="top">'.$row_product['qty'].'</td>
				<td align="right" valign="top" nowrap>'.Html::nf($row_product['sell_price']).'</td>
				<td align="right" valign="top" nowrap>'.Html::nf($row_product['subtotal']).'</td>
				</tr>';
				
				// get discounts applied to this product
				if (sizeof($row_product['discounts'])) {
					foreach ($row_product['discounts'] as $row_product_discount) {
						echo '<tr>
						<td>&nbsp;</td>
						<td valign="top" colspan="3">
							<div>'.
				$row_product_discount['description'].
				
				($row_product_discount['coupon'] && $row_product_discount['coupon_code'] ? '<br />
				<span>'.Yii::t('views/orders/edit_info_options', 'LABEL_CODE_COUPON').' '.$row_product_discount['coupon_code'].'</span>':'')
				
				
				.($row_product_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span>'.Yii::t('views/orders/edit_info_options','LABEL_OFFER_UNTIL').' '.$row_product_discount['end_date'].'</span>':'').'
							</div>
						</td>
						
						<td valign="top" align="right">'.($row_product_discount['amount'] > 0 ? '<span>'.Html::nf(-$row_product_discount['amount']).'</span>':'&nbsp;').'
						</td>
						</tr>';
						
						$total_save += $row_product_discount['amount'];
					}
				}					
				
				
				if(sizeof($row_product['option_groups'])){
					foreach ($row_product['option_groups'] as $row_product_option_group) { 
						echo '<tr><td>&nbsp;</td><td colspan="3"><strong>'.$row_product_option_group['name'].'</strong></td></tr>';
					   
						foreach ($row_product_option_group['options'] as $row_product_option) { 
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
									$file_uploads_dir = Yii::app()->params['root_url'].'file_uploads/';		
									
									echo '<div style="margin-top:5px;"><a href="/file_uploads/'.$row_product_option['filename'].'" target="_blank">'.$row_product_option['filename'].'</a></div>';
									break;
								// date
								case 8:
									echo (!empty($row_product_option['date_end']) ? Yii::t('views/orders/edit_info_options','LABEL_START_DATE').' '.Html::df_date($row_product_option['date_start'],1,-1):''.Yii::t('views/orders/edit_info_options','LABEL_DATE').' '.Html::df_date($row_product_option['date_start'],1,-1)).'<br />';
									echo !empty($row_product_option['date_end']) ? Yii::t('views/orders/edit_info_options','LABEL_END_DATE').' '.Html::df_date($row_product_option['date_end'],1,-1):'';
									break;
								// datetime
								case 9:
									echo (!empty($row_product_option['datetime_end']) ? Yii::t('views/orders/edit_info_options','LABEL_START_DATE_TIME').' '.Html::df_date($row_product_option['datetime_start'],1,3):''.Yii::t('views/orders/edit_info_options','LABEL_DATE_TIME').' '.Html::df_date($row_product_group['datetime_start'],1,3)).'<br />';
									echo !empty($row_product_option['datetime_end']) ? Yii::t('views/orders/edit_info_options','LABEL_END_DATE_TIME').' '.Html::df_date($row_product_option['datetime_end'],1,3):'';						
									break;
								// time
								case 10:
									echo (!empty($row_product_option['time_end']) ? Yii::t('views/orders/edit_info_options','LABEL_START_TIME').' '.$row_product_option['time_start']:''.Yii::t('views/orders/edit_info_options','LABEL_TIME').' '.$row_product_option['time_start']).'<br />';
									echo !empty($row_product_option['time_end']) ? Yii::t('views/orders/edit_info_options','LABEL_END_TIME').' '.$row_product_option['time_end']:'';								
									break;
									
							}							
							
							echo '</td>
							<td align="center" valign="top">'.$row_product_option['qty'].'</td>
							<td align="right" valign="top" nowrap>'.Html::nf($row_product_option['sell_price']).'</td>
							<td align="right" valign="top" nowrap="nowrap">'.Html::nf($row_product_option['subtotal']).'</td>
							</tr>';
							
							// get discounts applied to this option							
							if (sizeof($row_product_option['discounts'])) {
								foreach ($row_product_option['discounts'] as $row_product_option_discount) {
									echo '<tr>
									<td>&nbsp;</td>
									<td valign="top" colspan="3">
										<div>'.
				$row_product_option_discount['description'].($row_product_option_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span>'.Yii::t('views/orders/edit_info_options','LABEL_OFFER_UNTIL').' '.$row_product_option_discount['end_date'].'</span>':'').'
										</div>
									</td>
									
									<td valign="top" align="right">'.($row_product_option_discount['amount'] > 0 ? '<span>'.Html::nf(-$row_product_option_discount['amount']).'</span>':'&nbsp;').'
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
  <div style="float:right;">
      <table border="0" cellpadding="3" cellspacing="2">
          <tr>
              <th align="right"><strong><?php echo Yii::t('views/orders/edit_info_options','LABEL_SUBTOTAL');?></strong></th>
              <td align="right" width="80"><?php echo Html::nf($order['subtotal']);?></td>
          </tr>
          <tr>
              <th align="right"><strong><?php echo Yii::t('views/orders/edit_info_options','LABEL_SHIPPING');?></strong></th>
              <td align="right"><div id="shipping_cost_display"><?php echo Html::nf($order['shipping']);?></div></td>
          </tr>
		  <?php
          if(sizeof($order['taxes'])){
              foreach($order['taxes'] as $value){
                  echo '<tr>
                  <th align="right"><strong>'.$value['name'].'</strong></th>
                  <td align="right">'.Html::nf($value['total_taxes']).'</td>
                  </tr>';
              }
          }
          ?>
          <?php if ($order['gift_certificates'] > 0) { ?>
          <tr>
              <th align="right"><strong><?php echo Yii::t('views/orders/edit_info_options','LABEL_GIFT_CERTIFICATES'); ?></strong></th>
              <td align="right"><?php echo Html::nf(-$order['gift_certificates']);?></td>
          </tr>          
          <?php } ?>
      </table>

  </div>
  <div style="clear:both;">&nbsp;</div>
  <div style="float:right; font-size:18px">
      <div style="float:left;"><strong><?php echo Yii::t('views/orders/edit_info_options','LABEL_GRAND_TOTAL');?></strong></div>
      <div id="total_display" style="float:left;margin-left:10px;"><?php echo Html::nf($order['grand_total']);?></div>
      <div style="clear:both;"></div>
  </div>
  <div style="clear:both;"></div> 
  <div id="taxe_number">
  <?php
  if(sizeof($order['taxes'])){
      foreach($order['taxes'] as $value){
          echo $value['tax_number'] ? $value['name'].' : '.$value['tax_number'].'&nbsp;':'';
      }
  }
  ?>  
  </div> 
  <div class="cb page-break">&nbsp;</div>  
  <div id="policy_term">
	<?php
    if (sizeof($config_site['policy'])) {
		foreach ($config_site['policy'] as $row) {
			echo '<h1>'.$row['name'].'</h1>';
			echo '<div style="font-size:10px;">'.$row['description'].'</div>
			<div style="clear:both;">&nbsp;</div>';
		}
	}
    ?>   
   </div>        
</div>