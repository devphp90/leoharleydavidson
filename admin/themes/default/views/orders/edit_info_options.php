<?php 
$model_name = get_class($model);
//$connection=Yii::app()->db;   // assuming you have configured a "db" connection

//$columns = Html::getColumnsMaxLength(Tbl_Product::tableName());	

$transaction_details = $order['transaction_details'] ? unserialize(base64_decode($order['transaction_details'])):array();

//echo '<pre>'.print_r($order,1).'</pre>';

$help_hint_path = '/sales/orders/information/';
?>
<div style="width:100%; height:100%; overflow:auto;" id="div_<?php echo $container;?>">	
<div style="padding:10px;">	
	<div style="float:left;">
    <?php 
		echo '<h1>'.Yii::t('views/orders/edit_info_options','LABEL_STATUS').': <span id="'.$container.'_order_status" style="font-weight:normal">';
		switch ($order['status']) {
			case -1:
				echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_CANCELLED').'</span>';
				break;
			case 0:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_INCOMPLETE');
				break;					
			case 1:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_PENDING');
				break;
			case 2:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_PAYMENT_REVIEW');
				break;
			case 3:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_SUSPECTED_FRAUD');
				break;
			case 4:
				echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_DECLINED').'</span>';
				break;
			case 5:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_PROCESSING');
				break;
			case 6:
				echo Yii::t('controllers/OrdersController','LABEL_STATUS_ON_HOLD');
				break;
			case 7:
				echo '<span style="color:#090;">'.Yii::t('controllers/OrdersController','LABEL_STATUS_COMPLETED').'</span>';
				break;
		}
		echo '</span></h1>';		
	?>
    </div>	
    
    <div style="float:right;">
	<?php
		echo '<h1>'.Yii::t('views/orders/edit_info_options','LABEL_PRIORITY').': <span id="'.$container.'_order_priority" style="font-weight:normal">';
		switch ($order['priority']) {
			case 0:
				echo Yii::t('controllers/OrdersController','LABEL_PRIORITY_NORMAL');
				break;
			case 1:
				echo '<span style="color:#E839D7;">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_ATTENTION').'</span>';
				break;
			case 2:	
				echo '<span style="color:#F00;">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_URGENT').'</span>';
				break;					
		}	
		echo '</span></h1>';		
	?>    
    </div>
	
    <div style="clear:both;"></div>
    
    <div style="margin-bottom:20px">
        <div style="float:left;"><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_ORDER_DATE');?></strong>: <?php echo $order['date_order'];?></div>
        <div style="float:right;"><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_INVOICE_NO');?></strong>: <?php echo $order['id']; ?></div>
        <div style="clear:both;"></div>
    </div>
  <div style="margin-bottom:20px">
  	<?php if ($order['payment_method'] != 3) { ?>
      <div style="margin-bottom:20px;">
          <div style="margin-bottom:3px;"><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD');?></strong>: 
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
		  ?></div>
          
          
          <?php if($order['payment_method'] == 2 || $order['payment_method'] == 5){
			  
			  echo '<div style="float:left; margin-top: 5px;"><strong style="text-transform:uppercase">'.Yii::t('views/orders/edit_info_options','LABEL_ORDER_DATE_PAYMENT').'</strong>:</div> <div style="float:left; margin-left: 3px;"><input type="text" name="'.$container.'_order_date_payment" id="'.$container.'_order_date_payment" value="'.$order['date_payment'].'" style=" padding-left:2px; width: 70px;"></div><div style="clear:both;"></div>';
			  
		  }?>
          
          
          
      </div>
      <?php } ?>
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
        <div style="float:left; margin-left:20px; padding-left:20px; border-left: solid 1px #666;">
       	  <div><strong style="text-transform:uppercase"><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_SLIP');?></strong></div>
           	<?php 
			if ($order['payment_method'] == 0 || $order['payment_method'] == 1) {
			// beanstream
			if (isset($transaction_details['SCtrnId'])) {
			?>
            <div style="float:left;">
            	<div><?php echo Yii::t('views/orders/edit_info_options','LABEL_AMOUNT');?> <?php echo Html::nf($order['grand_total']);?></div>
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_TRANSACTION_DATE');?> <?php echo $transaction_details['SCtrnDate'];?></div>
               <?php echo $transaction_details['SCtrnType']?'<div>'.Yii::t('views/orders/edit_info_options','LABEL_TYPE').' ' . $transaction_details['SCtrnType'].'</div>':'';?>
            		<?php echo $transaction_details['SCcardType']?'<div>'.Yii::t('views/orders/edit_info_options','LABEL_CC').' ' . $transaction_details['SCcardType'].'</div>':'';?>
                    <?php echo $transaction_details['SClastFourDigits']?'<div>'.Yii::t('views/orders/edit_info_options', 'LABEL_CC_NUMBER').' ' . $transaction_details['SClastFourDigits'].'</div>':'';?>
            </div>
            <div style="float:left;margin-left:8px;">
            	<div><?php echo Yii::t('views/orders/edit_info_options','LABEL_HOLDER');?> <?php echo $transaction_details['SCtrnCardOwner']; ?></div>
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_AUTHORIZATION_NO');?> <?php echo $transaction_details['SCauthCode']; ?></div>
                
                
                
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_RESPONSE');?> <span class="<?php echo($transaction_details['SCtrnApproved']?'success':'error');?>"><strong><?php echo Yii::t('transaction/'.$transaction_details['SCcompanyName'],'ID_'.$transaction_details['SCmessageId']); ?></strong></span></div>
                
                
            </div> 
            <div style="clear:both;"></div>
            <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_REFERENCE_NO');?> <?php echo $transaction_details['SCtrnId']; ?></div>           
           <?php 
			}        	
			} else if ($order['payment_method'] == 4) {
			?>
            <div style="float:left;">
            	<div><?php echo Yii::t('views/orders/edit_info_options','LABEL_AMOUNT');?> <?php echo Html::nf($order['grand_total']);?></div>
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_DATE');?> <?php echo $order['date_order'];?></div>
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_TYPE');?> <?php echo $transaction_details['PAYMENTINFO_0_TRANSACTIONTYPE']; ?></div>
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYPAL_TRANSACTION_ID');?> <?php echo $transaction_details['PAYMENTINFO_0_TRANSACTIONID']; ?></div>
                <div><?php echo Yii::t('views/orders/edit_info_options','LABEL_PAYPAL_EMAIL');?> <?php echo $transaction_details['EMAIL']; ?></div>
            </div>        
			<?php } ?>
           <div style="clear:both;"></div>
          </div>
            <div style="clear:both;"></div>
      </div>
      
      
      
     
  </div>
  <div>
  
      <div class="cart-item">
      <table border="0" cellpadding="2" cellspacing="2" width="100%">
          <tr>
              <th style="text-transform:uppercase" width="10%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_SKU');?></th>
              <th style="text-transform:uppercase;" width="60%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_DESCRIPTION');?></th>
              <th style="text-align:center;text-transform:uppercase" width="5%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_QTY');?></th>
              <th style="text-align:right;text-transform:uppercase" width="10%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_PRICE');?></th>
              <th style="text-align:right;text-transform:uppercase" width="15%"><?php echo Yii::t('views/orders/edit_info_options','LABEL_TOTAL');?></th>
          </tr>
          <?php
			$counter_product = 0;		
			$heavy_weight_display = 0;
			foreach ($order['products'] as $row_product) {
				$counter_product++; 
				if($row_product['heavy_weight'])$heavy_weight_display = 1;				
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
							<div style="color:#090;">
							
							'.
				$row_product_discount['description'].
				
				($row_product_discount['coupon'] && $row_product_discount['coupon_code'] ? '<br />
				<span style="color:#000;">'.Yii::t('views/orders/edit_info_options', 'LABEL_CODE_COUPON').' '.$row_product_discount['coupon_code'].'</span>':'')
				
				
				.($row_product_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span style="color:#F00;">'.Yii::t('views/orders/edit_info_options','LABEL_OFFER_UNTIL').' '.$row_product_discount['end_date'].'</span>':'').'
							
				
							</div>
						</td>
						
						<td valign="top" align="right">'.($row_product_discount['amount'] > 0 ? '<span style="color:#F00;">-'.Html::nf($row_product_discount['amount']).'</span>':'&nbsp;').'
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
									echo (!empty($row_product_option['date_end']) ? Yii::t('views/orders/edit_info_options','LABEL_START_DATE').' '.Html::df_date($row_product_option['date_start'],1,-1):Yii::t('views/orders/edit_info_options','LABEL_DATE').' '.Html::df_date($row_product_option['date_start'],1,-1)).'<br />';
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
										<div style="color:#090;">
										
										'.
				$row_product_option_discount['description'].($row_product_option_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span style="color:#F00;">'.Yii::t('views/orders/edit_info_options','LABEL_OFFER_UNTIL').' '.$row_product_option_discount['end_date'].'</span>':'').'
										
										</div>
									</td>
									
									<td valign="top" align="right">'.($row_product_option_discount['amount'] > 0 ? '<span style="color:#F00;">-'.Html::nf($row_product_option_discount['amount']).'</span>':'&nbsp;').'
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
      <?php if($heavy_weight_display && !$order['local_pickup'] && !$order['free_shipping'])echo'<div style="margin-top:10px; color: #ff0000">'.Yii::t('views/orders/edit_info_options','LABEL_HEAVY_WEIGHT').'</div>';?>
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
              <td align="right">-<?php echo Html::nf($order['gift_certificates']);?></td>
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
          echo $value['tax_number'] ? $value['name'].': '.$value['tax_number'].'&nbsp;':'';
      }
  }
  ?>  
  </div> 
  
	<?php

	echo '<br /><h3>'.Yii::t('views/orders/edit_info_options','LABEL_COMMENTS').'</h3>
	<div style="margin-bottom:10px;">
		<div style="float:left; margin-right:5px;"><textarea name="comment" rows="2" id="'.$container.'_comment" style="width:450px;"></textarea></div>
		<div style="float:left;">
			<input type="checkbox" name="hidden_from_customer" value="1" id="'.$container.'_hidden_from_customer" />&nbsp;<label for="'.$container.'_hidden_from_customer" style="display:inline;">'.Yii::t('views/orders/edit_info_options','LABEL_HIDDEN_FROM_CUSTOMER').'</label>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'hidden-from-customer').'<div style="margin-top:13px;">
			<input type="button" id="'.$container.'_add_comment" value="'.Yii::t('views/orders/edit_info_options','BTN_ADD_COMMENT').'" /></div></div>
		<div style="clear:both;"></div>
	</div>
	<div id="'.$container.'_comments">
	';

	if (sizeof($order['comments'])) {		
		$i=0;
		foreach ($order['comments'] as $row_comment) {
			if ($i==1) $i=0;
			else $i=1;
			
			echo '<div style="padding:10px; background-color:'.((!$row_comment['read_comment'] and !$row_comment['id_user_created'])?'#FFFFCC':($i?'#EBEBEB':'#FFF')).';" id="mark_as_read_'.$row_comment['id'].'_comment">
			<div><strong>'.Yii::t('views/orders/edit_info_options','LABEL_FROM').':</strong> '.$row_comment['name'].' <strong>'.Yii::t('views/orders/edit_info_options','LABEL_DATE').':</strong> '.$row_comment['date_created'].($row_comment['hidden_from_customer'] ? ' (<span style="color:#F00;">'.Yii::t('views/orders/edit_info_options','LABEL_HIDDEN_FROM_CUSTOMER').'</span>)':'').'</div>
			<div style="margin-top:5px; float: left;">'.nl2br($row_comment['comments']).'</div>
			'.((!$row_comment['id_user_created'])?'<div style="float:right"><input type="button" id="mark_as_read_'.$row_comment['id'].'" value="'.(!$row_comment['read_comment']?Yii::t('views/orders/edit_info_options','LABEL_BTN_MARK_AS_READ'):Yii::t('views/orders/edit_info_options','LABEL_BTN_MARK_AS_UNREAD')).'" class="mark_as_read" /><input type="hidden" id="mark_as_read_'.$row_comment['id'].'_id_comment" value="'.$row_comment['id'].'" /></div>':'').'
			
			<div style="clear:both"></div>
			</div>';
		}
	}
	
	echo '</div>';
    ?>
</div>
<?php

$script = '
$(function(){
	$("#'.$container.'_add_comment").click(function(){
		if ($("#'.$container.'_comment").val().length) {
			$.ajax({
				url: "'.CController::createUrl('add_comment',array('id'=>$order['id'])).'",
				data: $("#'.$containerLayout.'").serialize(),
				type: "POST",
				success: function(data){
					$("#'.$container.'_comments").html("").append(data);					
					$("#'.$container.'_comment").val("");
					$("#'.$container.'_hidden_from_customer").prop("checked",false);
					alert("'.Yii::t('views/orders/edit_info_options','LABEL_ALERT_COMMENT_ADDED').'");	
				}
			});						
		} else {
			alert("Please enter a comment!");	
		}
	});
	
	$(".mark_as_read").click(function(){
		var id_button = this.id;
		var id_comment = $("#"+id_button+"_id_comment").val();
		$.ajax({
			url: "'.CController::createUrl('mark_as_read_comment').'",
			data: {"id_comment":id_comment},
			type: "POST",
			success: function(data){
				if(data==1){
					$("#"+id_button+"").attr("value", "'.Yii::t('views/orders/edit_info_options','LABEL_BTN_MARK_AS_UNREAD').'");
					$("#"+id_button+"_comment").css("background-color", "#FFF");
				}else{
					$("#"+id_button+"").attr("value", "'.Yii::t('views/orders/edit_info_options','LABEL_BTN_MARK_AS_READ').'");
					$("#"+id_button+"_comment").css("background-color", "#FFFFCC");
				}	
			}
		});		
			
	});
	
	
	$("#'.$container.'_order_date_payment").datepicker({
			dateFormat: "yy-mm-dd",
			setDate: "'.$order['date_payment'].'",
			onSelect: function(dateText, inst){
				$.ajax({
					url: "'.CController::createUrl('change_date_payment',array('id'=>$order['id'])).'",
					data: {"date_payment":dateText},
					type: "POST",
					success: function(data){
						
					}
				});		
			}
	});	
	
});
';

echo Html::script($script); 
?>