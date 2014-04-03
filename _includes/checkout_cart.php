<script type="text/javascript">

jQuery(document).ready(function() {
	//jQuery('#cart_content').scrollelement({'animate': true, 'duration': 'slow', 'offset': '0'});
});
</script>
<div class="order-total std-bottommargin" id="cart_content">
    <div class="order-total-wrapper" style="padding-top:15px; clear:both;">
        <div class="op_block_title">
          <?php echo language('global', 'TITLE_CHECKOUT_CART');?>
          <input type="button" value="<?php echo language('global', 'BTN_MODIFY');?>" class="button" name="btn_modify_cart" onClick="document.location.href='/cart/'" style="float:right;" />
        </div>        
        <div class="cb"></div>
        <div class="cart-item">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="data-table order-products-table">
                <thead>
                <tr class="first">
                    <th><?php echo language('global', 'TITLE_CHECKOUT_PRODUCT');?></th>
                    <th style="text-align:center; width:10%"><?php echo language('global', 'TITLE_CHECKOUT_QTY');?></th>
                    <th style="text-align:right; width:10%"><?php echo language('global', 'TITLE_CHECKOUT_TOTAL');?></th>
                </tr>
                </thead>
                <?php
                $counter_product = 0;
				$total_product = count($cart->get_products());
				$products = $cart->get_products();
                foreach ($products as $row_product) {
                    $counter_product++; 				
                    echo '<tr>
                    <td valign="top">'.$row_product['name']. ($row_product['used']?'<span style="font-style:italic;">'.language('global', 'LABEL_USED').'</span>':'').(!empty($row_product['variant'])?'<br /><em>' . $row_product['variant'] . '</em>':'');
					
					/*if (sizeof($row_product['sub_products'])) {
						echo '<div style="font-size:10px; margin-left: 10px;">';
						
						foreach ($row_product['sub_products'] as $row_sub_product) {
							echo '<div style="margin-bottom:2px;">'.$row_sub_product['qty'].'x '.$row_sub_product['name'].'</div>';
						}			
						
						echo '</div>';
					}*/						
					
					echo '</td>
                    <td align="center" valign="top">'.$row_product['qty'].'</td>
                    <td align="right" valign="top" nowrap="nowrap">'.nf_currency($row_product['subtotal']).'</td>
                    </tr>';
					

					// get discounts applied to this product
					if (sizeof($product_discounts = $cart->get_product_discounts($row_product['id_cart_item_product']))) {
						foreach ($product_discounts as $row_product_discount) {
							echo '<tr>
							<td valign="top" colspan="2">
								<div style="color:#090;">'.
				$row_product_discount['description'].($row_product_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span style="color:#F00;">'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_discount['end_date']).'</span>':'').'
								</div>
							</td>
							
							<td valign="top" align="right" nowrap="nowrap">'.($row_product_discount['amount'] > 0 ? '<span style="color:#F00;">'.nf_currency(-$row_product_discount['amount']).'</span>':'&nbsp;').'
							</td>
							</tr>';
						}
					}						
					
					
					if(sizeof($cart->get_product_options($row_product['id']))){
						echo '<tr><td colspan="3" class="options">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<th style="">Options</th>
										<th style="width:10%;text-align:center;">'.language('global', 'TITLE_CHECKOUT_QTY').'</th>
										<th style="text-align:right; width:10%;">'.language('global', 'TITLE_CHECKOUT_TOTAL').'</th>
									</tr>';
						$total_option = count($cart->get_product_options($row_product['id']));
						foreach ($cart->get_product_options($row_product['id']) as $row_product_option_group) { 
							$option_group_name = '';
							$counter_option++;
							foreach ($row_product_option_group['options'] as $row_product_option) { 
								 if($option_group_name != $row_product_option_group['name']){
								 	echo '<tr><td colspan="3">'.$row_product_option_group['name'].'</td></tr>';
									$option_group_name = $row_product_option_group['name'];
								 }
								 
								 echo '<tr>
								<td valign="top"><em>'.$row_product_option['name'].'</em></td>
								<td align="center" valign="top">'.$row_product_option['qty'].'</td>
								<td align="right" valign="top" nowrap="nowrap">'.nf_currency($row_product_option['subtotal']).'</td>
								</tr>';
								
								// get discounts applied to this option
								if (sizeof($product_option_discounts = $cart->get_product_option_discounts($row_product_option['id']))) {
									foreach ($product_option_discounts as $row_product_option_discount) {
										echo '<tr>
										<td valign="top" colspan="2">
											<div style="color:#090;">'.
				$row_product_option_discount['description'].($row_product_option_discount['end_date'] != "0000-00-00 00:00:00" ? '<br />
				<span style="color:#F00;">'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_option_discount['end_date']).'</span>':'').'
											</div>
										</td>
										
										<td valign="top" align="right">'.($row_product_option_discount['amount'] > 0 ? '<span style="color:#F00;">-'.nf_currency($row_product_option_discount['amount']).'</span>':'&nbsp;').'
										</td>
										</tr>';
										
										$total_save += $row_product_option_discount['amount'];
									}
								}									
							}
							echo (($counter_option < $total_option)?'<tr><td colspan="3"><hr /></td></tr>':'');
							
						}
						echo '</table></td></tr>';
					}
						
					//echo (($counter_product < $total_product)?'<tr><td colspan="3"><hr /></td></tr>':'');

                }
                ?>
            </table>
        </div>
        <br>
        <div style="float:right;clear: both; min-width:250px;">
            <table border="0" cellpadding="3" cellspacing="2" style="width: 100%;">
                <tr>
                    <th align="right"><strong><?php echo language('global', 'TITLE_CHECKOUT_SUBTOTAL');?></strong></th>
                    <td align="right" width="60%"><?php echo nf_currency($cart->subtotal);?></td>
                </tr>
                <?php
                if($config_site['enable_shipping']){
				?>
                <tr>
                    <th align="right"><strong><?php echo language('global', 'TITLE_CHECKOUT_SHIPPING');?></strong></th>
                    <td align="right" width="60%"><div id="shipping_cost_display"><?php echo nf_currency($cart->shipping);?></div></td>
                </tr>
                <?php }?>
                <tr>
                    <td colspan="2" align="right">
                    <div id="total_tax_display">
                    <?php
                    if(sizeof($cart->get_taxes())){
                    echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
                    foreach($cart->get_taxes() as $value){
                    echo '<tr>
                    <th align="right" style="padding-bottom:3px; padding-right:6px;"><strong>'.$value['name'].'</strong></th>
                    <td align="right" width="60%" style="padding-bottom:3px">'.$value['total_taxes'].'</td>
                    </tr>';
                    }
                    echo '</table>';
                    }
                    ?>
                    </div>
                    </td>
                </tr>
                <?php
					if (isset($step) && $step == 'payment' && $cart->gift_certificates > 0) {
				?>
                <tr>
                    <th align="right"><strong><?php echo language('global', 'TITLE_CHECKOUT_BIG_TOTAL');?></strong></th>
                    <td align="right" width="60%"><div id="total_before_certificates_display"><?php echo nf_currency($cart->total);?></div></td>
                </tr>                 
                <tr>
                    <th align="right"><strong>Certificat Cadeaux</strong></th>
                    <td align="right" width="60%"><div id="total_gift_certificates_display">-<?php echo nf_currency($cart->gift_certificates);?></div></td>
                </tr>                
                <?php
					}
				?>
            </table>
        </div>        
        <div style="float:right; font-size:18px;clear: both; min-width:250px;margin-top: 10px;">
        	<table border="0" cellpadding="3" cellspacing="2" style="width: 100%;">
                <tr>
                    <th><strong><?php echo language('global', 'TITLE_CHECKOUT_BIG_TOTAL');?></strong></th>
            		<td style="margin-left:9px; text-align:right; font-weight:bold"><div id="total_display"><?php echo nf_currency((isset($step) && $step == 'payment' ? $cart->grand_total:$cart->total));?></div></td>
            	</tr>
            </table>
        </div>
        <div class="cb"></div>
    </div>
</div>