<?php 
/*   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime;*/

include(dirname(__FILE__) . "/_includes/config.php");

// Link for the button Continue Shopping
$_SESSION['link_continue_shopping'] = $_SERVER['REQUEST_URI'];

$products = new Products;


//Select view by page
$view_1 = $image_listing_center_column_view;
$view_2 = $image_listing_center_column_view*2;
$view_3 = $image_listing_center_column_view*4;
$view_4 = $image_listing_center_column_view*8;

$s = trim($_GET['s']);
$no_result = 0;

if(isset($_GET['name_filter'])) {
	$name_filter = trim($_GET['name_filter']);			
} else {
	$name_filter = '';
}

$orderby = (int)$_GET['orderby'];
$page = (int)$_GET['page'];
$page = !$page ? 1:$page;
$limit = (int)$_GET['limit'];
$limit = !$limit  ? $view_1:$limit;
$offset = ($page-1)*$limit;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$orderby = (int)$_POST['orderby'];
	$offset = 0;
	$limit = (int)$_POST['limit'];		
	set_cookie_value('orderby', $orderby);
	set_cookie_value('limit', $limit);	
}else {
	if(!$orderby = get_cookie_value('orderby')){
		$orderby = $config_site['product_sort_by'];
	}
	if(!$limit = get_cookie_value('limit') or (($limit != $view_1) and ($limit != $view_2) and ($limit != $view_3) and ($limit != $view_4))){
		$limit = $view_1;
	}	
}

if(($search == language('_include/template/top', 'LABEL_SEARCH_TEXTBOX') or empty($s) or strlen($s)<=1) && !$name_filter){
	//echo $search . ' - ' . language('_include/template/top', 'LABEL_SEARCH_TEXTBOX') . strlen($search);
	header('Location: /');
	exit;
	//$search = $search . '  ';
}

// Product Listing
$filters=array('s'=>$s);

$querystr_array = array();
if (!empty($s)) $querystr_array['s'] = $s;
$querystr_array['limit'] = $limit;
$querystr_array['orderby'] = $orderby;

$title =  language('search', 'TITLE_SEARCH');
switch ($name_filter) {
	case 'on-sale':
		$title = language('_include/template/top', 'LINK_ON_SALE');
		$filters['on_sale'] = 1;
		break;
	case 'new-products':
		$title = language('_include/template/top', 'LINK_NEW_PRODUCT');
		$filters['new_products'] = 1;
		break;
	case 'top-sellers':
		$title = language('_include/template/top', 'LINK_TOP_SELLER');
		$filters['top_sellers'] = 1;
		break;
	case 'featured':
		$title = language('_include/template/top', 'LINK_FEATURED');
		$filters['featured_products'] = 1;
		break;
}

//$total_products = $products->count_products($filters);
$results = $products->get_products($filters, $orderby, $offset, $limit, 1);	
$total_products = $products->get_total_products();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow" />
<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/_includes/template/header.php");?>
<script type="text/javascript">
jQuery(document).ready(function() {
	<?php
	if(($_GET['review_on'] and is_numeric($_GET['review_on'])) and $_SESSION['customer']['id']){
		echo 'open_review('.$_GET['review_on'].',\'login\',\'\');';
	}
	?>	
});
</script>
<?php include(dirname(__FILE__) . "/_includes/template/google_analytics.php");?>

</head>
<body class="bv3">
<?php include(dirname(__FILE__) . "/_includes/template/top.php");?>
<div class="main-container">    
    <div class="breadcrumbs">   	
		<div class="container">
          <ul>
          	<li>
            	<a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
            	<span>&gt;</span>
            </li>
            <?php
    		if ($name_filter) {
    			if ($txt_search_within) echo '<li><strong> <a href="?name_filter='.$name_filter.''.($id_tag?'&id_tag='.$id_tag:'').'">'.$title.'</a><strong></li>';
    			else echo ' <li><strong> '.$title.'</a><strong></li>';
    		}
    		
            if($txt_search_within){
    			echo $search ? '<li><strong> <a href="'.$url_search.'">' . htmlspecialchars($search) . '</a><strong></li>':'';
    			echo $txt_search_within ? '<li><strong> ' . htmlspecialchars($txt_search_within).'<strong></li>':'';
    		}else if ($s){
    			echo '<li><strong> '. $s .'<strong></li>';
    		}
    		?>            
          </ul>
      	</div>            
    </div>
    <div class="main">
    <div class="container">    
    <div class="col-sm-9 main-content">
    <?php if($title) {?>
    <h2 class="subtitle"><?php echo $title;?></h2>
    <?php }?>
    <?php if($s) {?>
    <p class="desc"><?php echo language('search', 'TITLE_SEARCH_RESULT_FOR').' '.htmlspecialchars($s);?></p>
    <?php }?>
    <div class="category-products">
	<?php
        if ($total_products) {	?>
        <!-- Start Pagination TOP -->
        <div class="toolbar clearfix"> 
        <form method="post" name="form_search_product_bar" action="?<?php echo http_build_query($products->get_filters_array()); ?>">
          <div class="sorter">
          	<div class="sort-by clearfix">
              <label class="left" for="orderby"><?php echo language('catalog', 'TITLE_INPUT_SORT_BY');?> </label>
              <select class="left" id="orderby" name="orderby" onchange="this.form.submit();">
              <?php
              if($config_site['cf_show_featured_products_menu']){
  			?>
              <option value="0" <?php echo(($orderby==0)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_FEATURED_ITEMS');?></option>
              <?php
  			}
  			?>
             
              <?php
              if($config_site['display_price']){
  			?>
              <option value="2" <?php echo(($orderby==2)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_LOWEST_PRICE');?></option>
              <option value="3" <?php echo(($orderby==3)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_HIGHEST_PRICE');?></option>
              <?php
  			}
  			?>
              
               <?php
              if($config_site['cf_show_ratings']){
  			?>
              <option value="1" <?php echo(($orderby==1)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_BEST_RATING');?></option>
              <option value="4" <?php echo(($orderby==4)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_MOST_REVIEWS');?></option>
              <?php
  			}
  			?>
              <option value="5" <?php echo(($orderby==5)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_ASC');?></option>
              <option value="6" <?php echo(($orderby==6)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_DESC');?></option>
              </select>
            </div>
          </div>
          <div class="pager" style="font-weight: normal;"> 
          	  <?php
			// ----------------- PAGINATION
			// Instantiate the pagination object
			$pagination = new Zebra_Pagination();
			$pagination->variable_name('page');
			
			// Pass current page url and do not include query string
			$pagination->base_url($url_alias.'?'.http_build_query(array_merge($querystr_array),'flags_'),0);
			
			// Set position of the next/previous page links
			$pagination->navigation_position(isset($_GET['navigation_position']) && in_array($_GET['navigation_position'], array('left', 'right')) ? $_GET['navigation_position'] : 'outside');
			
			// The number of total records is the number of records in the array
			$pagination->records($total_products);
			
			$pagination->labels(language('global', 'PAGINATION_PREVIOUS'), language('global', 'PAGINATION_NEXT'));
			
			// Records per page
			$pagination->records_per_page($limit);

			$current_offset_from = ($pagination->get_page()>1?(($pagination->get_page()-1)*$limit)+1:1);
			$current_offset_to = 0;
			$current_offset_to = $current_offset_from+$limit-1;
			if ($current_offset_to > $total_products) $current_offset_to = $total_products;
			
			//echo '<div style="float:left; margin-top: 4px;"><strong style="color:#333">'.language('global', 'TITLE_SHOWING').'</strong> '.$current_offset_from.' '.language('global', 'TITLE_SHOWING_TO').' '. $current_offset_to.' '.language('global', 'TITLE_SHOWING_OF').' '. $total_products.' '.language('global', 'TITLE_SHOWING_RESULTS').'</div>';
			
			$pagination->render();
			// END ----------------- PAGINATION
			
			?>
          	  <div class="limiter">
                <label for="limit"><?php echo language('catalog', 'TITLE_INPUT_VIEW');?> </label>
                <select id="limit" class="left" name="limit" onchange="this.form.submit();">
                  <option value="<?php echo $view_1;?>" <?php echo(($limit==$view_1)?'selected="selected"':'');?>><?php echo $view_1;?></option>
                  <option value="<?php echo $view_2;?>" <?php echo(($limit==$view_2)?'selected="selected"':'');?>><?php echo $view_2;?></option>
                  <option value="<?php echo $view_3;?>" <?php echo(($limit==$view_3)?'selected="selected"':'');?>><?php echo $view_3;?></option>
                  <option value="<?php echo $view_4;?>" <?php echo(($limit==$view_4)?'selected="selected"':'');?>><?php echo $view_4;?></option>
                </select>
              </div>
          </div>        
          <input type="hidden" value="form_search_product_bar" name="task">
        </form>              
    	</div>
    	<!-- End Pagination TOP -->
    	<!-- Start product listing -->
		<ul class="products-grid row <?php echo $config_site['images_orientation'];?>">		
			<?php
			if($config_site['images_orientation'] == 'landscape') {
          		$colsm = 'col-sm-6';
          		$nbItemLine = 2;
          	} else {
            	$colsm = 'col-sm-4';
            	$nbItemLine = 3;                	
          	}			
			$counter=0;
			foreach ($results as $row) {
			  ++$counter;	
			?>	
			<li class="<?php echo $colsm;?> item col2-1 col3-1 col4-1 <?php if(($counter-1)%$nbItemLine == 0) echo 'item-first';?>">		
			  <?php echo dsp_product($row);?>
			</li>
			<?php }?>		
		</ul>
		<!-- End products listing -->
		<!-- Start pagination BOTTOM -->
		<div class="toolbar-bottom">
			<div class="toolbar clearfix"> 
            <form method="post" name="form_search_product_bar" action="?<?php echo http_build_query($products->get_filters_array()); ?>">
              <div class="sorter">
              	<div class="sort-by clearfix">
                  <label class="left" for="orderby"><?php echo language('catalog', 'TITLE_INPUT_SORT_BY');?> </label>
                  <select class="left" id="orderby" name="orderby" onchange="this.form.submit();">
                  <?php
                  if($config_site['cf_show_featured_products_menu']){
      			?>
                  <option value="0" <?php echo(($orderby==0)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_FEATURED_ITEMS');?></option>
                  <?php
      			}
      			?>
                 
                  <?php
                  if($config_site['display_price']){
      			?>
                  <option value="2" <?php echo(($orderby==2)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_LOWEST_PRICE');?></option>
                  <option value="3" <?php echo(($orderby==3)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_HIGHEST_PRICE');?></option>
                  <?php
      			}
      			?>
                  
                   <?php
                  if($config_site['cf_show_ratings']){
      			?>
                  <option value="1" <?php echo(($orderby==1)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_BEST_RATING');?></option>
                  <option value="4" <?php echo(($orderby==4)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_MOST_REVIEWS');?></option>
                  <?php
      			}
      			?>
                  <option value="5" <?php echo(($orderby==5)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_ASC');?></option>
                  <option value="6" <?php echo(($orderby==6)?'selected="selected"':'');?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_DESC');?></option>
                  </select>
                </div>
              </div>
              <div class="pager" style="font-weight: normal;"> 
              	  <?php
    			// ----------------- PAGINATION
    			// Instantiate the pagination object
    			$pagination = new Zebra_Pagination();
    			$pagination->variable_name('page');
    			
    			// Pass current page url and do not include query string
    			$pagination->base_url($url_alias.'?'.http_build_query(array_merge($querystr_array),'flags_'),0);
    			
    			// Set position of the next/previous page links
    			$pagination->navigation_position(isset($_GET['navigation_position']) && in_array($_GET['navigation_position'], array('left', 'right')) ? $_GET['navigation_position'] : 'outside');
    			
    			// The number of total records is the number of records in the array
    			$pagination->records($total_products);
    			
    			$pagination->labels(language('global', 'PAGINATION_PREVIOUS'), language('global', 'PAGINATION_NEXT'));
    			
    			// Records per page
    			$pagination->records_per_page($limit);
    
    			$current_offset_from = ($pagination->get_page()>1?(($pagination->get_page()-1)*$limit)+1:1);
    			$current_offset_to = 0;
    			$current_offset_to = $current_offset_from+$limit-1;
    			if ($current_offset_to > $total_products) $current_offset_to = $total_products;
    			
    			//echo '<div style="float:left; margin-top: 4px;"><strong style="color:#333">'.language('global', 'TITLE_SHOWING').'</strong> '.$current_offset_from.' '.language('global', 'TITLE_SHOWING_TO').' '. $current_offset_to.' '.language('global', 'TITLE_SHOWING_OF').' '. $total_products.' '.language('global', 'TITLE_SHOWING_RESULTS').'</div>';
    			
    			$pagination->render();
    			// END ----------------- PAGINATION
    			
    			?>
              	  <div class="limiter">
                    <label for="limit"><?php echo language('catalog', 'TITLE_INPUT_VIEW');?> </label>
                    <select id="limit" name="limit" onchange="this.form.submit();">
                      <option value="<?php echo $view_1;?>" <?php echo(($limit==$view_1)?'selected="selected"':'');?>><?php echo $view_1;?></option>
                      <option value="<?php echo $view_2;?>" <?php echo(($limit==$view_2)?'selected="selected"':'');?>><?php echo $view_2;?></option>
                      <option value="<?php echo $view_3;?>" <?php echo(($limit==$view_3)?'selected="selected"':'');?>><?php echo $view_3;?></option>
                      <option value="<?php echo $view_4;?>" <?php echo(($limit==$view_4)?'selected="selected"':'');?>><?php echo $view_4;?></option>
                    </select>
                  </div>
              </div>        
              <input type="hidden" value="form_search_product_bar" name="task">
            </form>              
    	</div>
		</div>
		<!-- End pagination Bottom -->
	<?php } else echo '<p style="margin-top:30px;font-size:18px;">'.language('global', 'EMPTY_SEARCH').'</p>';?> 	
	</div> 
	</div>
	<div class="col-sm-3 sidebar sidebar-right">
		<?php if ($config_site['show_newsletter_form']) { ?>
		<!-- Block newsletter -->
		<div class="block block-subscribe">
          <div class="block-title">
              <strong><span><?php echo language('index', 'LABEL_NL_BLOCK_TITLE');?></span></strong>
          </div>
          <form id="newsletter-validate-detail" method="post" action="" onsubmit="return false">
              <div class="alert alert-success success-msg" style="display:none;" id="nl_email_text_container">
              	<button type="button" class="close" data-dismiss="alert">×</button>
              	<div id="newsletter_email_text"></div>
              </div>
              <div class="block-content">
                  <div class="form-subscribe-header">
                      <label for="newsletter"><?php echo language('index', 'LABEL_NL_BLOCK_TEXT');?></label>
                  </div>
                  <div class="input-box">
                     <input type="text" placeholder="<?php echo language('index', 'LABEL_NL_EMAIL_PLACEHOLDER');?>" class="input-text required-entry validate-email" title="<?php echo language('index', 'LABEL_NL_EMAIL_TITLE');?>" id="newsletter" name="form_values[email]" style="text-align:center;">
                  </div>
                  <div class="actions">
                      <button class="button button-inverse btn-lg btn-large" title="<?php echo language('index', 'LABEL_NL_SUBMIT');?>" type="submit" onclick="register_newsletter()"><span><span><?php echo language('index', 'LABEL_NL_SUBMIT');?></span></span></button>
                  </div>
              </div>
          </form>                   
      	</div>          	
      	<!-- END Block newsletter -->          	
      	<?php }?>
      	<div class="block block-pub">
      	<?php 
        	$page_home = false;
        	include("_includes/template/pub.php");
      	?>
      	</div>
	</div>
    </div>   
</div>
<?php include(dirname(__FILE__) . "/_includes/template/bottom.php");?>
</body>
</html>