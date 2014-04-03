<?php
include(dirname(__FILE__) . "/../_includes/config.php");
include(dirname(__FILE__) . "/../_includes/validate_session.php");

if(isset($_GET['page'])){
	$page = (int)$_GET['page'];
}else{
	$page = 1;
}

if(isset($_GET['success'])){
	$success = $_GET['success'];
}else{
	$success = "";
}	

if (isset($_GET['select_search_order'])) {
	$select_search_order = (int)$_GET['select_search_order'];
} else {
	$select_search_order = '';	
}

if (isset($_GET['search_order_number'])) {
	$search_order_number = (int)$_GET['search_order_number'];
} else {
	$search_order_number = '';	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name'];?></title>
<?php include("../_includes/template/header.php");?>
<script type='text/javascript' src='/includes/js/mediaplayer/jwplayer.js'></script>
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
            	<strong><?php echo language('global', 'BREADCRUMBS_YOUR_ACCOUNT');?></strong>
            </li>
          </ul>
      	</div>            
    </div>
    <div class="main">
    <div class="container">
    <div class="main-content withblock" style="overflow: hidden;"> 
    	<h2 class="subtitle"><?php echo language('account/index', 'TITLE_PAGE');?></h2>         
        <div>
        	<p><?php echo language('account/index', 'TITLE_PAGE_DESCRIPTION');?></p>
        </div>        
 		<?php
          $msg_success = '';	
          switch ($success) {
              case 'modify_password':
                  $msg_success = language('account/index', 'SUCCESS_PASSWORD');
                  break;
			case 'modify_account':
                  $msg_success = language('account/index', 'SUCCESS_PERSONNAL_INFO');
                  break;
			case 'modify_email':
                  $msg_success = language('account/index', 'SUCCESS_EMAIL');
                  break;
          }
        ?>
        <?php if(!empty($msg_success)) {?>
        <div class="messages">
          <div class="alert alert-success success-msg">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <ul><li><span><?php echo $msg_success;?></span></li></ul>
          </div>
        </div>
        <?php }?>
        <div class="col-sm-12" style="overflow: hidden;padding-bottom: 20px; padding-left:0">
      		<div class="op_block_title"><?php echo language('account/index', 'TITLE_YOUR_ORDERS');?></div>
            <div class="op_block_detail">
            <form method="get" action="?" style="padding-bottom: 10px;overflow: hidden;">
            <div style="padding:10px 0;">
            <div class="fl" style="float:left;">
            <select name="select_search_order" id="select_search_order" style="margin:0; color:#999">
                <option value="1" <?php echo ($select_search_order == 1) ? 'selected="selected"':''; ?>><?php echo language('account/index', 'INPUT_SELECT_SEARCH_ORDER_1');?></option>
                <option value="2" <?php echo (!$select_search_order || $select_search_order == 2) ? 'selected="selected"':''; ?>><?php echo language('account/index', 'INPUT_SELECT_SEARCH_ORDER_2');?></option>
                <option value="3" <?php echo ($select_search_order == 3) ? 'selected="selected"':''; ?>><?php echo language('account/index', 'INPUT_SELECT_SEARCH_ORDER_3');?></option>
                <option value="4" <?php echo ($select_search_order == 4) ? 'selected="selected"':''; ?>><?php echo language('account/index', 'INPUT_SELECT_SEARCH_ORDER_4');?></option>
                <option value="5" <?php echo ($select_search_order == 5) ? 'selected="selected"':''; ?>><?php echo language('account/index', 'INPUT_SELECT_SEARCH_ORDER_5');?></option>
            </select>
            </div>
            <div class="fl" style="float:left;margin-left:5px;">
            <input type="submit" value="GO" class="button" style="padding: 6px 12px; font-size:14px;"/>
            </div>
            <div class="fr" style="float:right;">
            <div class="fl" style="float:left;margin-left:50px;">
           <input type="text" style="width: 180px; color:#999" class="clearMeFocus" title="<?php echo language('account/index', 'INPUT_ORDER_NUMBER');?>" value="<?php echo language('account/index', 'INPUT_ORDER_NUMBER');?>" name="search_order_number" id="search_order_number" />
            </div>
            <div class="fl" style="float:left;margin-left:5px;">
            <input type="submit" class="button" value="GO" style="padding: 6px 12px; font-size:14px;"/>
            </div>
            </div>
            <div class="cb"></div>
            </div>
            </form>
            <?php 
			$where = array();
			switch ($select_search_order) {				
				// all
				case 1:
					break;
				// last 3 onths				
				default:												
				case 2:
					$where[] = 'DATE(orders.date_order) BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 3 MONTH) AND DATE(NOW())';
					break;
				// last 6 months				
				case 3:
					$where[] = 'DATE(orders.date_order) BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 6 MONTH) AND DATE(NOW())';
					break;
				// last 12 months				
				case 4:
					$where[] = 'DATE(orders.date_order) BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 12 MONTH) AND DATE(NOW())';
					break;
				// over 12 months
				case 5:
					$where[] = 'DATE(orders.date_order) < DATE_SUB(DATE(NOW()), INTERVAL 12 MONTH)';
					break;
			}
			
			if ($search_order_number) $where[] = 'orders.id LIKE "%'.$mysqli->escape_string($search_order_number).'%"';
			
			
			if (!$result_orders_count = $mysqli->query('SELECT 
			COUNT(orders.id) AS total
			FROM 
			orders 
			WHERE
			orders.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'" '.
			(sizeof($where) ? ' AND '.implode(' AND ',$where):''))) throw new Exception('An error occured while trying to get orders count.'."\r\n\r\n".$mysqli->error);
			
			$row_orders_count = $result_orders_count->fetch_assoc();
			$total_records = $row_orders_count['total'];
			$result_orders_count->free();
			
			if ($total_records > 0) {	
				$tmp_array = array(					
					'page'=>$page,
				);
				
				if ($select_search_order) $tmp_array['select_search_order'] = $select_search_order;
				if ($search_order_number) $tmp_array['search_order_number'] = $search_order_number;
				
				$filter_url = '?'.(sizeof($tmp_array) ? http_build_query($tmp_array,'flags_'):'');
				
				$limit = 10;		
			
							
			
				if (!$result_orders = $mysqli->query('SELECT 
				orders.*
				FROM
				orders
				WHERE
				orders.id_customer = "'.$mysqli->escape_string($_SESSION['customer']['id']).'" '.
				(sizeof($where) ? ' AND '.implode(' AND ',$where):'').'
				ORDER BY '.
				($search_order_number ? 'IF(orders.id LIKE "'.$mysqli->escape_string($search_order_number).'%",0,1) ASC ':'orders.id DESC ')
				)) throw new Exception('An error occured while trying to get orders.'."\r\n\r\n".$mysqli->error); 
				
				
					
					
					
					
					
					
					
					
					/*
					
					// ----------------- PAGINATION
					// Instantiate the pagination object
					$pagination = new Zebra_Pagination();
					$pagination->variable_name('page');
					
					// Pass current page url and do not include query string
					$pagination->base_url('?'.http_build_query(array_merge($tmp_array),'flags_'),0);
					
					// Set position of the next/previous page links
					$pagination->navigation_position(isset($_GET['navigation_position']) && in_array($_GET['navigation_position'], array('left', 'right')) ? $_GET['navigation_position'] : 'outside');
					
					// The number of total records is the number of records in the array
					$pagination->records($total_records);
					
					$pagination->labels(language('global', 'PAGINATION_PREVIOUS'), language('global', 'PAGINATION_NEXT'));
					
					// Records per page
					$pagination->records_per_page($limit);
		
					$current_offset_from = ($pagination->get_page()>1?(($pagination->get_page()-1)*$limit)+1:1);
					$current_offset_to = 0;
					$current_offset_to = $current_offset_from+$limit-1;
					if ($current_offset_to > $total_records) $current_offset_to = $total_records;
					
					echo '<div style="float:left; margin-top: 4px;"><strong style="color:#333">'.language('global', 'TITLE_SHOWING').'</strong> '.$current_offset_from.' '.language('global', 'TITLE_SHOWING_TO').' '. $current_offset_to.' '.language('global', 'TITLE_SHOWING_OF').' '. $total_records.' '.language('global', 'TITLE_SHOWING_RESULTS').'</div>';
					
					$pagination->render();
					// END ----------------- PAGINATION
					
					*/
					
					
					
					
					
					
					
					
					
				echo '<table cellpadding="0" cellspacing="0" width="100%" class="data-table">
				<thead>
				<tr>
					<th width="25%">'.language('account/index', 'LABEL_DATE').'</th>
					<th width="25%">'.language('account/index', 'LABEL_ORDER_NUMBER').'</th>
					<th width="25%">'.language('account/index', 'LABEL_STATUS').'</th>
					<th width="25%" style="text-align:right">'.language('account/index', 'LABEL_ORDER_TOTAL').'</th>
				</tr></thead>';					
				
				while ($row_order = $result_orders->fetch_assoc()) {
					echo '
                    <tr>
                        <td>'.df_date($row_order['date_order'],1).'</td>
                        <td><a href="/account/order?id='.$row_order['id'].'">'.$row_order['id'].'</a></td>
					<td>';

						switch ($row_order['status']) {
							case -1:
								echo '<span style="color:#F00;">'.language('account/index','LABEL_STATUS_CANCELLED').'</span>';
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
								echo '<span style="color:#F00;">'.language('account/index','LABEL_STATUS_DECLINED').'</span>';
								break;
							case 5:
								echo language('account/index','LABEL_STATUS_PROCESSING');
								break;
							case 6:
								echo language('account/index','LABEL_STATUS_ON_HOLD');
								break;
							case 7:
								echo '<span style="color:#090;">'.language('account/index','LABEL_STATUS_COMPLETED').'</span>';
								break;
						}
						
					echo '</td>
                        <td style="text-align:right">'.nf_currency($row_order['grand_total']).'</td>
                    </tr>';	
					
					
					
					
					
					
					
					
					
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
				(orders_item.id IS NOT NULL AND orders_item.id_orders = "'.$mysqli->escape_string($row_order['id']).'" AND orders.status IN (1,7))
				OR
				(oi.id IS NOT NULL AND oi.id_orders = "'.$mysqli->escape_string($row_order['id']).'" AND o.status IN (1,7))
				ORDER BY 
				orders_item_product_downloadable_videos.sort_order ASC')) {
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
				(orders_item.id IS NOT NULL AND orders_item.id_orders = "'.$mysqli->escape_string($row_order['id']).'" AND orders.status IN (1,7))
				OR
				(oi.id IS NOT NULL AND oi.id_orders = "'.$mysqli->escape_string($order->id).'" AND o.status IN (1,7))
				ORDER BY 
				orders_item_product_downloadable_files.sort_order ASC')) {
					while ($row_downloadable_file = $result_downloadable_files->fetch_assoc()) {
						$downloadable_videos_files[] = $row_downloadable_file;
					}					
				}
				
				
				if (sizeof($downloadable_videos_files)) {
				
				?>
	            <tr>
                <td colspan="4" class="options">
					<table border="0" cellpadding="5" cellspacing="0" width="100%">
                    	<tr>
                        	<th align="left"><strong><?php echo language('account/order', 'LABEL_LINKS');?></strong></th>
                            <th align="center" style="text-align:center"><strong><?php echo language('account/order', 'LABEL_CURRENT_NO_DOWNLOADS');?></strong></th>
                            <th align="center" style="text-align:center"><strong><?php echo language('account/order', 'LABEL_NO_DOWNLOADS');?></strong></th>
						</tr>
                	<?php
					$date_order_timestamp = strtotime($row_order['date_order']);
					
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
							<td valign="top" align="center" style="text-align:center">'.$row['current_no_downloads'].'</td>
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
                </td>
                </tr>                
                <?php	
				}
                ?>                
         
					
					
					
					
					
					
					
					
					
									
				<?php
                }

				echo '</table>'.$pagination_menu;
			} ?>


        </div>
          </div>  

	 <div class="col-sm-4" style=" padding-left:0">
        <div class="op_block_title"><?php echo language('account/index', 'TITLE_ACCOUNT_MODIFY');?></div>
        <div class="op_block_detail">
        <ul>            
            <li><a href='/account/modify-password'><?php echo language('global', 'BREADCRUMBS_MODIFY_PASSWORD');?></a></li>
            <li><a href='/account/modify-email'><?php echo language('global', 'BREADCRUMBS_MODIFY_EMAIL');?></a></li>
            <li><a href='/account/modify-address'><?php echo language('global', 'BREADCRUMBS_MODIFY_ADDRESS');?></a></li>
            <li><a href='/account/modify-account'><?php echo language('global', 'BREADCRUMBS_MODIFY_PERSONNAL_INFOS');?></a></li>
            <?php
          	if ($result = $mysqli->query('SELECT COUNT(custom_fields.id) AS total FROM custom_fields WHERE custom_fields.form = 0')){
          		$row = $result->fetch_assoc();
          		$result->free();
          		
          		if ($row['total']) {
          	?>
                      <li><a href='/account/modify-additional-info'><?php echo language('global', 'BREADCRUMBS_MODIFY_ADDITIONAL_INFO');?></a></li>
                      <?php
          		}
          	}
            ?>                                       
        </ul>
        </div>
    </div>
    
	<div class="col-sm-4">
      <div class="op_block_title"><?php echo language('account/index', 'TITLE_LISTS_AND_ALERTS');?></div>
      <div class="op_block_detail">
      <ul>            
        	<li><a href='/account/gift_certificate'><?php echo language('global', 'BREADCRUMBS_GIFT_CERTIFICATE');?></a></li>
          <li><a href="/account/wishlist"><?php echo language('global', 'BREADCRUMBS_MY_WISHLIST');?></a></li>
          <li><a href="/account/price-alert"><?php echo language('global', 'BREADCRUMBS_MY_PRICE_ALERTS');?></a></li>                    
      </ul>
      </div>
    </div>    
    
    <div class="col-sm-4">
        <div class="title_bg title_bg_3">
            <div class="op_block_title"><?php echo language('account/index', 'TITLE_CONTACT_US');?></div></div>
            <div class="op_block_detail title_bg_text_box" >
				  	<div class="fl">
                    <strong><?php echo $config_site['company_company'];?></strong><br />
                    <?php echo $config_site['company_address']?' ' . $config_site['company_address']:'';?>
                    <?php echo $config_site['company_city']?'<br />' . $config_site['company_city']:'';?>
                    <?php
                    $country_name = '';
                    $state_name = ''; 
                    if($config_site['company_country_code']){
                        $query = 'SELECT 
                            country_description.name
                            FROM country_description
                            WHERE country_description.country_code = "'.$config_site['company_country_code'].'" AND country_description.language_code = "'.$_SESSION['customer']['language'].'"';
                    
                            if ($result = $mysqli->query($query)) {
                                $row = $result->fetch_assoc();
                                $country_name = $row['name'];
                            }
                    }
                    if($config_site['company_state_code']){
                        $query = 'SELECT 
                            state_description.name
                            FROM state_description
                            WHERE state_description.state_code = "'.$config_site['company_state_code'].'" AND state_description.language_code = "'.$_SESSION['customer']['language'].'"';
                    
                            if ($result = $mysqli->query($query)) {
                                $row = $result->fetch_assoc();
                                $state_name = $row['name'];
                            }
                    }
                    echo $state_name?' ' . $state_name:'';?>
                    <?php echo $country_name?' ' . $country_name:'';?>
                    <?php echo $config_site['company_zip']?'<br />' . $config_site['company_zip']:'';?>
					</div><div class="cb"></div>
                    <div class="fl" style="margin-top:10px;">
                    <?php echo $config_site['company_telephone']?'<strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_T').'</strong> ' . $config_site['company_telephone']:'';?>
                    <?php echo $config_site['company_fax']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_F').'</strong> ' . $config_site['company_fax']:'';?>
                    <?php echo $config_site['company_email']?'<br /><strong>'.language('cart/step_payment', 'LABEL_CONTACT_US_E').'</strong> <a href="mailto:' . $config_site['company_email'].'">' . $config_site['company_email'] . '</a>':'';?>
                    </div>
                    <div class="cb"></div>
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
<?php include("../_includes/template/bottom.php");?>
</body>
</html>