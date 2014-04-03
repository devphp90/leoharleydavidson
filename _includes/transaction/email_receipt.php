<style>
body, tr, td{
	font-family:Verdana, Geneva, sans-serif; 
	font-size:11px;
}
table.shopping_cart {
	width:100%;
}

table.shopping_cart td, table.shopping_cart th {
	padding:3px 2px;
	font-size:11px;
}

table.shopping_cart th {
	text-align:left;
	border-bottom:solid 1px #5e5e5e;
	font-weight:bold;
}

table.shopping_cart td.options   { 
	padding:3px 2px;
}

table.shopping_cart td.options th {
	text-align:left;
	border-bottom:solid 1px #4f4f4f;
	font-weight:bold;
	font-size:10px;
	padding-left:5px; 
	padding-right:5px;
}

table.shopping_cart td.options td   { 
	font-size:10px; 
	padding-left:5px; 
	padding-right:5px; }
	
#taxe_number{
	font-size:10px;
	color:#999;
	border-top: 1px #5e5e5e solid;
	padding-top: 10px;
	margin-top:10px;
	margin-bottom: 30px;
}
#policy_term{
	font-size:10px;
	margin-top:10px; 
	padding-top:5px;
}
h1, h2, h3{
	font-size:14px;
}
</style>

<body>

<div style="padding-bottom:8px; margin-bottom:8px; border-bottom:solid 1px #5e5e5e;">
        <?php echo $config_site['site_name'];?><br />
        <?php echo $config_site['company_address']?' ' . $config_site['company_address']:'';?>
        <?php echo $config_site['company_city']?'<br />' . $config_site['company_city']:'';?>
        <?php
        $country_name = '';
        $state_name = ''; 
        if($config_site['company_country_code']){
            $query = 'SELECT 
                country_description.name
                FROM country_description
                WHERE country_description.country_code = "'.$mysqli->escape_string($config_site['company_country_code']).'" AND country_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"';
        
                if ($result = $mysqli->query($query)) {
                    $row = $result->fetch_assoc();
                    $country_name = $row['name'];						
                }
                $result->free();
                
        }
        if($config_site['company_state_code']){
            $query = 'SELECT 
                state_description.name
                FROM state_description
                WHERE state_description.state_code = "'.$mysqli->escape_string($config_site['company_state_code']).'" AND state_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'"';
        
                if ($result = $mysqli->query($query)) {
                    $row = $result->fetch_assoc();
                    $state_name = $row['name'];						
                }
                $result->free();
        }
        echo $state_name?' ' . $state_name:'';?>
        <?php echo $country_name?' ' . $country_name:'';?>
        <?php echo $config_site['company_zip']?' ' . $config_site['company_zip']:'';?>
        <br />
        <?php echo $config_site['company_telephone']?'<strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_T').'</strong> ' . $config_site['company_telephone']:'';?>
        <?php echo $config_site['company_fax']?' <strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_F').'</strong> ' . $config_site['company_fax']:'';?>
        <?php echo $config_site['company_email']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_E').'</strong> <a href="mailto:' . $config_site['company_email'].'">' . $config_site['company_email'] . '</a>':'';?>
</div>


<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
  <td><strong style="text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_DATE');?></strong> <?php echo df_date($order->date_order,1,-1);?></td>
  <td align="right"><strong style="text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_INVOICE_NO');?></strong> <?php echo $order->id; ?></td>
</tr>
</table><br />
<?php if ($order->payment_method != 3) { ?>
<div style="margin-bottom:10px;"><strong style="text-transform:uppercase;"><?php echo language('cart/print_version', 'LABEL_METOHD_PAYMENT');?></strong> 
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
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
  <td width="50%"><div><strong style="text-transform:uppercase"><?php echo language('global', 'TITLE_BILLING_ADDRESS');?></strong></div>
            <div>
                <?php
                echo 
                ($order->billing_company?$order->billing_company.'<br />':'').
                ($order->billing_company?$order->billing_firstname. ' ' .$order->billing_lastname.'<br />':$order->billing_firstname. ' ' .$order->billing_lastname.'<br />').
                $order->billing_address.'<br />'.
                $order->billing_city.
                ($order->billing_state?' '.$order->billing_state:'').'<br />'.$order->billing_country.' '.strtoupper($order->billing_zip);
                ?> 
            </div>  </td>
   <td>&nbsp;&nbsp;&nbsp;</td>
  <td align="right">
  <div style="text-align:left;">
  <div><strong style="text-transform:uppercase"><?php echo language('global', 'TITLE_SHIPPING_ADDRESS');?></strong></div>
            <div>
                <?php
                if(!$order->local_pickup){
                echo 
                ($order->shipping_company?$order->shipping_company.'<br />':'').
                ($order->shipping_company?$order->shipping_firstname. ' ' .$order->shipping_lastname.'<br />':$order->shipping_firstname. ' ' .$order->shipping_lastname.'<br />').
                $order->shipping_address.'<br />'.
                $order->shipping_city.
                ($order->shipping_state?' '.$order->shipping_state:'').'<br />'.$order->shipping_country.' '.strtoupper($order->shipping_zip);
                }else{
                echo '<div>'.language('global', 'TITLE_LOCAL_PICKUP').'</div>';   
                }
                ?> 
            </div>
  </div></td>
</tr>
</table><br />

  <table border="0" cellpadding="0" cellspacing="0" width="100%" class="shopping_cart">
      <tr>
          <th style="text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_SKU');?></th>
          <th style="text-transform:uppercase; width:60%"><?php echo language('cart/print_version', 'LABEL_DESCRIPTION');?></th>
          <th style="text-align:center;text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_QTY');?></th>
          <th style="text-align:right;text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_PRICE');?></th>
          <th style="text-align:right;text-transform:uppercase"><?php echo language('cart/print_version', 'LABEL_TOTAL');?></th>
      </tr>
      <?php
        $counter_product = 0;		
        $products = $order->get_products();
        $total_product = count($products);			
        
        foreach ($products as $row_product) {
            $counter_product++; 				
            echo '<tr>
            <td valign="top" width="20%" nowrap>'.(!empty($row_product['variant_sku']) ? $row_product['variant_sku']:$row_product['sku']).'</td>
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
                        $row_product_discount['description'].'<br />
                        <span>'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_discount['end_date']).'</span>
                        </div>
                    </td>
                    
                    <td valign="top" align="right" nowrap>'.($row_product_discount['amount'] > 0 ? '<span>'.nf_currency(-$row_product_discount['amount']).'</span>':'&nbsp;').'
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
                        <td align="right" valign="top" nowrap>'.nf_currency($row_product_option['subtotal']).'</td>
                        </tr>';
                        
                        // get discounts applied to this option							
                        if (sizeof($product_option_discounts = $order->get_product_option_discounts($row_product_option['id']))) {
                            foreach ($product_option_discounts as $row_product_option_discount) {
                                echo '<tr>
                                <td>&nbsp;</td>
                                <td valign="top" colspan="3">
                                    <div>'.
                                    $row_product_option_discount['description'].'<br />
                                    <span>'.language('cart/index', 'LABEL_OFFER_UNTIL').' '.df_date($row_product_option_discount['end_date']).'</span>
                                    </div>
                                </td>
                                
                                <td valign="top" align="right"  nowrap>'.($row_product_option_discount['amount'] > 0 ? '<span>'.nf_currency(-$row_product_option_discount['amount']).'</span>':'&nbsp;').'
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
<div style="border-top: 1px #5e5e5e solid; padding-top:3px;padding-bottom:3px">&nbsp;</div>




<div align="right">
  <table border="0" cellpadding="3" cellspacing="2">
      <tr>
          <th align="right"><strong><?php echo language('cart/print_version', 'LABEL_SUBTOTAL');?></strong></th>
          <td align="right" nowrap><?php echo nf_currency($order->subtotal);?></td>
      </tr>
      <tr>
          <th align="right"><strong><?php echo language('cart/print_version', 'LABEL_SHIPPING');?></strong></th>
          <td align="right"><div id="shipping_cost_display"><?php echo nf_currency($order->shipping);?></div></td>
      </tr>
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
<div align="right" style="font-size:18px;">
  <div><strong><?php echo language('cart/print_version', 'LABEL_BIG_TOTAL');?></strong> <?php echo nf_currency($order->grand_total);?></div>
</div>

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
          echo strtoupper($row['name']) . ' : ' . $row['tax_number'] . '&nbsp;&nbsp;';
      }
  }
  $result->free();
  }
  ?>
  
  </div>
<?php
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
	
	if ($downloadable) {
		echo '<div style="color:#FF0000; font-size:14px; margin-top:10px; margin-bottom:10px; font-weight:bold;">'.language('cart/print_version','LABEL_DOWNLOADABLES').'</div>';
	}
?>  
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
</body>



  
  
  
