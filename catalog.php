<?php 
/*
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;*/


$listing_css = 'center';
include(dirname(__FILE__) . "/_includes/config.php");

// Link for the button Continue Shopping
$_SESSION['link_continue_shopping'] = $_SERVER['REQUEST_URI'];

$products = new Products;
$category = new Category;

if (isset($_GET['alias'])) {
	$alias = trim($_GET['alias']);
	
	if (!$category->load_alias($alias)) {
		header("HTTP/1.0 404 Not Found");
		header('Location: '.$url_prefix.'error?error=invalid_category');
		exit;	
	}
	
	$id_category = $category->get_id();
	$category_description = $category->get_description();
}

//Select view by page
$view_1 = $image_listing_center_column_view;
$view_2 = $image_listing_center_column_view*2;
$view_3 = $image_listing_center_column_view*4;
$view_4 = $image_listing_center_column_view*8;



// check for language post
if (isset($_POST['language_main_site']) && $_POST['language_main_site']) {
	if (!$url = $category->get_url($_POST['language_main_site'])) {
		header("HTTP/1.0 404 Not Found");
		header('Location: '.$url_prefix.'error?error=invalid_category');
		exit;			
	}	
	
	if ($_SERVER['QUERY_STRING']) { 
		$_SERVER['QUERY_STRING'] = explode('&',$_SERVER['QUERY_STRING']);
		foreach ($_SERVER['QUERY_STRING'] as $key => $value) {
			if (stristr($value,'_lang') || stristr($value,'alias') || stristr($value,'page')) unset($_SERVER['QUERY_STRING'][$key]);
		}
		$_SERVER['QUERY_STRING'] = implode('&',$_SERVER['QUERY_STRING']);
	}
		
	header('Location: '.$url.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING']:''));
	exit;
}

$url_alias = $url_prefix.'catalog/'.$alias;

if ($id_category) {
	$filters = array();
	
	$filters['id_category'] = $id_category;
	
	if ($featured_products = (int)$_GET['featured_products']) $filters['featured_products'] = 1;
	if ($on_sale = (int)$_GET['on_sale']) $filters['on_sale'] = 1;
	if ($new_products = (int)$_GET['new_products']) $filters['new_products'] = 1;
	if ($top_sellers = (int)$_GET['top_sellers']) $filters['top_sellers'] = 1;
	if ($combo_deals = (int)$_GET['combo_deals']) $filters['combo_deals'] = 1;
	if ($bundled_products = (int)$_GET['bundled_products']) $filters['bundled_products'] = 1;
	
	if ($price = $_GET['price']) $filters['price'] = $price; 
	if ($brand = $_GET['brand']) $filters['brand'] = $brand;
	if ($rating = $_GET['rating']) $filters['rating'] = $rating;
	
	if ($s = trim($_GET['s'])) $filters['s'] = $s;	
	
	$orderby = (int)$_GET['orderby'];
	
	$page = (int)$_GET['page'];
	$page = !$page ? 1:$page;
	$limit = (int)$_GET['limit'];
	$limit = !$limit  ? $view_1:$limit;
	$offset = ($page-1)*$limit;
	$querystr_array = array();
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$s = trim($_POST['s']);
		if ($s != language('catalog', 'INPUT_SEARCH_WITHIN')) $filters['s'] = $s;	
		else $s = '';
		$orderby = (int)$_POST['orderby'];
		$offset = 0;
		$limit = (int)$_POST['limit'];	
		
		set_cookie_value('orderby', $orderby);
		set_cookie_value('limit', $limit);	
	} else {
		if(!$orderby = get_cookie_value('orderby')){
			$orderby = $category->get_product_sort_by();
		}
		if(!$limit = get_cookie_value('limit') or (($limit != $view_1) and ($limit != $view_2) and ($limit != $view_3) and ($limit != $view_4))){
			$limit = $view_1;
		}	
	}
	
	$querystr_array = array();
	if (!empty($s)) $querystr_array['s'] = $s;
	$querystr_array['limit'] = $limit;
	$querystr_array['orderby'] = $orderby;
	
	//$total_products = $products->count_products($filters);
	$arr_products = $products->get_products($filters, $orderby, $offset, $limit, 1);
	$total_products = $products->get_total_products();	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="description" content="<?php echo htmlspecialchars($category_meta_description); ?>" />
        <meta name="keywords" content="<?php echo htmlspecialchars($category_meta_keywords); ?>" />
        <link rel="canonical" href="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$url_prefix.$alias; ?>" /> 
        <title><?php echo ($id_category ? $category->get_name().' - ':'').$config_site['site_name']; ?></title>
        <?php include(dirname(__FILE__) . "/_includes/template/header.php");?>
        <script type="text/javascript">

        jQuery(document).ready(function() {
                jQuery("#side_menu_categories").css('padding-top', jQuery("#title_category").height()+18);

                <?php
                if(($_GET['review_on'] and is_numeric($_GET['review_on'])) and $_SESSION['customer']['id']){
                        echo 'open_review('.$_GET['review_on'].',\'login\',\'\');';
                }
                ?>


        });
        </script>
        <?php include(dirname(__FILE__) . "/_includes/template/google_analytics.php");?>
    </head>
    <body  class="bv3">
    <?php include(dirname(__FILE__) . "/_includes/template/top.php");?>
        <div class="main-container">
                <!-- START breadcrumbs -->
            <div class="breadcrumbs">   	
                <div class="container">
                    <ul>
                        <li>
                            <a href="/" title="<?php echo language('global', 'BREADCRUMBS_HOME');?>"><?php echo language('global', 'BREADCRUMBS_HOME');?></a>
                            <span>&gt;</span>
                        </li>
                        <li>
                            <a href="<?php echo $url_prefix.'/catalog'; ?>"><?php echo language('global', 'BREADCRUMBS_CATALOG');?></a>
                            <?php if(sizeof($breadcrumbs = $category->get_breadcrumb())) {?><span>&gt;</span><?php }?>
                        </li>
                        <?php 
                        $cpt = 0;
                        if (sizeof($breadcrumbs = $category->get_breadcrumb())) {
                                            foreach ($breadcrumbs as $crumb) {
                                              $cpt++;
                                ?>
                                <li>
                            <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['name'];?></a>
                            <?php if($cpt < sizeof($breadcrumbs)) {?><span>&gt;</span><?php }?>
                        </li>			
                        <?php } }?>
                    </ul>
                </div>
            </div>
            <!-- END breadcrumbs -->

            <div class="main">
                <div class="container">
                    <div class="col-sm-9 main-content">
                        <?php if($id_category){?> 
                                <h2 class="subtitle"><?php echo $category->get_name().' ('.$total_products.')'; ?></h2> 
                                <?php if(!empty($category_description)){?>
                                <p class="desc"><?php echo $category_description;?></p>
                                <?php } ?>             
                        <?php
                        //Verify if the array contain other filter then id_category
                                if (sizeof($filters = $products->get_filters())) {
                                ?>
                                <div class="filters">		
                                    <div class="title"><?php echo language('catalog', 'TITLE_FILTERING'); ?> &nbsp;<span class="fitering_close_btn"><a href="<?php echo $url_alias; ?>">X</a></span></div>
                                        <div class="list">
                                            <?php
                                            foreach ($filters as $row_filter) {
                                                switch ($row_filter['type']) {
                                                    case 'featured_products':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . language('catalog', 'FILTER_FEATURED_PRODUCT') . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['featured_products'] = 1;
                                                        break;
                                                    case 'on_sale':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . language('catalog', 'FILTER_ON_SALE') . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['on_sale'] = 1;
                                                        break;
                                                    case 'new_products':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . language('catalog', 'FILTER_NEW_PRODUCT') . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['new_products'] = 1;
                                                        break;
                                                    case 'top_sellers':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . language('catalog', 'FILTER_TOP_SELLER') . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['top_sellers'] = 1;
                                                        break;
                                                    case 'combo_deals':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . language('catalog', 'FILTER_COMBO_DEALS') . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['combo_deals'] = 1;
                                                        break;
                                                    case 'bundled_products':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . language('catalog', 'FILTER_BUNDLED_PRODUCTS') . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['bundled_products'] = 1;
                                                        break;
                                                    case 's':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . $row_filter['s'] . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['s'] = $row_filter['s'];
                                                        break;
                                                    case 'brand':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . $row_filter['brand'] . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['brand'] = $row_filter['brand'];
                                                        break;
                                                    case 'price':
                                                        echo '<div class="filtering_item"><div class="filtering_text">' . $row_filter['min'] . ' - ' . $row_filter['max'] . '</div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';

                                                        $querystr_array['price'] = $row_filter['min_raw'] . '-' . $row_filter['max_raw'];
                                                        break;
                                                    case 'rating':
                                                        //echo '<div class="filtering_item"><div class="filtering_text">'.get_rated_star($row_filter['rating']).'</div><div class="fitering_close_btn"><a href="'.$row_filter['url'].'">X</a></div></div>';
                                                        echo '<div class="filtering_item"><div class="filtering_text"><div class="reviews-wrap">
                                                            <div class="ratings" style="margin:0">
                                                                    <div class="rating-box">
                                                                            <div class="rating" style="width:' . ($row_filter['rating'] * 100 / 5) . '%"></div>
                                                                    </div>                  					
                                                            </div>
                                                      </div></div><div class="fitering_close_btn"><a href="' . $row_filter['url'] . '">X</a></div></div>';
                                                        $querystr_array['rating'] = $row_filter['rating'];
                                                        break;
                                                }
                                            }
                                            ?>
                                        <div class="cb"></div>
                                    </div>  
                                </div>              
                        <?php }?>        
                        <?php	
                                if (!$category_display_type) {
                        ?>
                    <div class="category-products">
                        <?php if ($total_products) {?>
                        <!-- Start Pagination TOP -->
                        <div class="toolbar clearfix"> 
                            <form method="post" name="form_search_product_bar" action="?<?php echo http_build_query($products->get_filters_array()); ?>">
                                <!-- 
                                <div class="fl">
                                    <input class="clearMeFocus" alt="<?php echo language('catalog', 'INPUT_SEARCH_WITHIN');?>" value="<?php echo language('catalog', 'INPUT_SEARCH_WITHIN');?>" title="<?php echo language('catalog', 'INPUT_SEARCH_WITHIN');?>" maxlength="50" name="s" size="20" type="text" style="padding-left:3px;">
                                </div>        
                                <div class="fl" style="margin-left:3px;">
                                    <input name="btn_go" type="image" value="GO" src="/_images/btn_go.png" alt="Go" />
                                </div>
                                  -->        
                                  <div class="sorter">
                                    <div class="sort-by clearfix">
                                        <label class="left" for="orderby"><?php echo language('catalog', 'TITLE_INPUT_SORT_BY'); ?> </label>
                                        <select class="left" id="orderby" name="orderby" onchange="this.form.submit();">
                                            <?php
                                            if ($config_site['cf_show_featured_products_menu']) {
                                                ?>
                                                <option value="0" <?php echo(($orderby == 0) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_FEATURED_ITEMS'); ?></option>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if ($config_site['display_price']) {
                                                ?>
                                                <option value="2" <?php echo(($orderby == 2) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_LOWEST_PRICE'); ?></option>
                                                <option value="3" <?php echo(($orderby == 3) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_HIGHEST_PRICE'); ?></option>
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if ($config_site['cf_show_ratings']) {
                                                ?>
                                                <option value="1" <?php echo(($orderby == 1) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_BEST_RATING'); ?></option>
                                                <option value="4" <?php echo(($orderby == 4) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_MOST_REVIEWS'); ?></option>
                                                <?php
                                            }
                                            ?>
                                            <option value="5" <?php echo(($orderby == 5) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_ASC'); ?></option>
                                            <option value="6" <?php echo(($orderby == 6) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_DESC'); ?></option>
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
                                    $pagination->base_url($url_alias . '?' . http_build_query(array_merge($querystr_array), 'flags_'), 0);

                                    // Set position of the next/previous page links
                                    $pagination->navigation_position(isset($_GET['navigation_position']) && in_array($_GET['navigation_position'], array('left', 'right')) ? $_GET['navigation_position'] : 'outside');

                                    // The number of total records is the number of records in the array
                                    $pagination->records($total_products);

                                    $pagination->labels(language('global', 'PAGINATION_PREVIOUS'), language('global', 'PAGINATION_NEXT'));

                                    // Records per page
                                    $pagination->records_per_page($limit);

                                    $current_offset_from = ($pagination->get_page() > 1 ? (($pagination->get_page() - 1) * $limit) + 1 : 1);
                                    $current_offset_to = 0;
                                    $current_offset_to = $current_offset_from + $limit - 1;
                                    if ($current_offset_to > $total_products)
                                        $current_offset_to = $total_products;

                                    //echo '<div style="float:left; margin-top: 4px;"><strong style="color:#333">'.language('global', 'TITLE_SHOWING').'</strong> '.$current_offset_from.' '.language('global', 'TITLE_SHOWING_TO').' '. $current_offset_to.' '.language('global', 'TITLE_SHOWING_OF').' '. $total_products.' '.language('global', 'TITLE_SHOWING_RESULTS').'</div>';

                                    $pagination->render();
                                    // END ----------------- PAGINATION
                                    ?>			
                                    <div class="limiter">
                                        <label for="limit"><?php echo language('catalog', 'TITLE_INPUT_VIEW'); ?> </label>
                                        <select id="limit" class="left" name="limit" onchange="this.form.submit();">
                                            <option value="<?php echo $view_1; ?>" <?php echo(($limit == $view_1) ? 'selected="selected"' : ''); ?>><?php echo $view_1; ?></option>
                                            <option value="<?php echo $view_2; ?>" <?php echo(($limit == $view_2) ? 'selected="selected"' : ''); ?>><?php echo $view_2; ?></option>
                                            <option value="<?php echo $view_3; ?>" <?php echo(($limit == $view_3) ? 'selected="selected"' : ''); ?>><?php echo $view_3; ?></option>
                                            <option value="<?php echo $view_4; ?>" <?php echo(($limit == $view_4) ? 'selected="selected"' : ''); ?>><?php echo $view_4; ?></option>
                                        </select>
                                    </div>	
                                </div>
                                <input type="hidden" value="form_search_product_bar" name="task">
                            </form>
                       </div>
                       <!-- END Pagination TOP -->

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
                            foreach ($arr_products as $row) {
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
                                    <!-- 
                                    <div class="fl">
                                        <input class="clearMeFocus" alt="<?php echo language('catalog', 'INPUT_SEARCH_WITHIN');?>" value="<?php echo language('catalog', 'INPUT_SEARCH_WITHIN');?>" title="<?php echo language('catalog', 'INPUT_SEARCH_WITHIN');?>" maxlength="50" name="s" size="20" type="text" style="padding-left:3px;">
                                    </div>        
                                    <div class="fl" style="margin-left:3px;">
                                        <input name="btn_go" type="image" value="GO" src="/_images/btn_go.png" alt="Go" />
                                    </div>
                                      -->        
                                    <div class="sorter">
                                        <div class="sort-by clearfix">
                                            <label class="left" for="orderby"><?php echo language('catalog', 'TITLE_INPUT_SORT_BY'); ?> </label>
                                            <select class="left" id="orderby" name="orderby" onchange="this.form.submit();">
                                                <?php
                                                if ($config_site['cf_show_featured_products_menu']) {
                                                    ?>
                                                    <option value="0" <?php echo(($orderby == 0) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_FEATURED_ITEMS'); ?></option>
                                                    <?php
                                                }
                                                ?>
                                                <?php
                                                if ($config_site['display_price']) {
                                                    ?>
                                                    <option value="2" <?php echo(($orderby == 2) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_LOWEST_PRICE'); ?></option>
                                                    <option value="3" <?php echo(($orderby == 3) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_HIGHEST_PRICE'); ?></option>
                                                    <?php
                                                }
                                                ?>

                                                <?php
                                                if ($config_site['cf_show_ratings']) {
                                                    ?>
                                                    <option value="1" <?php echo(($orderby == 1) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_BEST_RATING'); ?></option>
                                                    <option value="4" <?php echo(($orderby == 4) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_MOST_REVIEWS'); ?></option>
                                                    <?php
                                                }
                                                ?>
                                                <option value="5" <?php echo(($orderby == 5) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_ASC'); ?></option>
                                                <option value="6" <?php echo(($orderby == 6) ? 'selected="selected"' : ''); ?>><?php echo language('catalog', 'INPUT_SORT_BY_NAME_DESC'); ?></option>
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
                                        $pagination->base_url($url_alias . '?' . http_build_query(array_merge($querystr_array), 'flags_'), 0);

                                        // Set position of the next/previous page links
                                        $pagination->navigation_position(isset($_GET['navigation_position']) && in_array($_GET['navigation_position'], array('left', 'right')) ? $_GET['navigation_position'] : 'outside');

                                        // The number of total records is the number of records in the array
                                        $pagination->records($total_products);

                                        $pagination->labels(language('global', 'PAGINATION_PREVIOUS'), language('global', 'PAGINATION_NEXT'));

                                        // Records per page
                                        $pagination->records_per_page($limit);

                                        $current_offset_from = ($pagination->get_page() > 1 ? (($pagination->get_page() - 1) * $limit) + 1 : 1);
                                        $current_offset_to = 0;
                                        $current_offset_to = $current_offset_from + $limit - 1;
                                        if ($current_offset_to > $total_products)
                                            $current_offset_to = $total_products;

                                        //echo '<div style="float:left; margin-top: 4px;"><strong style="color:#333">'.language('global', 'TITLE_SHOWING').'</strong> '.$current_offset_from.' '.language('global', 'TITLE_SHOWING_TO').' '. $current_offset_to.' '.language('global', 'TITLE_SHOWING_OF').' '. $total_products.' '.language('global', 'TITLE_SHOWING_RESULTS').'</div>';

                                        $pagination->render();
                                        // END ----------------- PAGINATION
                                        ?>			
                                        <div class="limiter">
                                            <label for="limit"><?php echo language('catalog', 'TITLE_INPUT_VIEW'); ?> </label>
                                            <select id="limit" class="left" name="limit" onchange="this.form.submit();">
                                                <option value="<?php echo $view_1; ?>" <?php echo(($limit == $view_1) ? 'selected="selected"' : ''); ?>><?php echo $view_1; ?></option>
                                                <option value="<?php echo $view_2; ?>" <?php echo(($limit == $view_2) ? 'selected="selected"' : ''); ?>><?php echo $view_2; ?></option>
                                                <option value="<?php echo $view_3; ?>" <?php echo(($limit == $view_3) ? 'selected="selected"' : ''); ?>><?php echo $view_3; ?></option>
                                                <option value="<?php echo $view_4; ?>" <?php echo(($limit == $view_4) ? 'selected="selected"' : ''); ?>><?php echo $view_4; ?></option>
                                            </select>
                                        </div>	
                                    </div>
                                    <input type="hidden" value="form_search_product_bar" name="task">
                                </form>
                            </div>
                        </div>
                        <!-- END pagination BOTTOM -->	 		
                           <?php 
                           echo $pagination_menu; 
                        }?>

                    </div> 	       
                    <?php }} ?> 
                </div>
                    <div class="col-sm-3 sidebar sidebar-right">
                        <!-- START Filter results -->
                        <div class="block block-layered-nav" style="margin-bottom:20px;">
                            <div class="block-title">
                                <strong><span><?php echo language('catalog', 'TITLE_NARROW_RESULT'); ?></span></strong>
                            </div>
                            <div class="block-content">
                              <!-- <p class="block-subtitle">Shopping Options</p>-->
                                <dl id="narrow-by-list">
                                    <!-- START filter by category -->
                                    <?php if (sizeof($souscategories = Categories::get_categories($category->get_id()))) { ?>
                                        <dt class="odd"><?php echo language('catalog', 'LABEL_CATEGORY'); ?><a class="button-arrow open" href="#"></a></dt>
                                        <dd class="odd" style="display:block;">
                                            <ol>
                                                <?php foreach ($souscategories as $row_category) { ?>
                                                    <li><a href="<?php echo $row_category['url']; ?>"><?php echo $row_category['name']; ?></a></li>
                                                <?php } ?>
                                            </ol>
                                        </dd>
                                    <?php } ?>
                                    <!-- END filter by category -->

                                    <!-- START filter by useful links -->
                                    <?php
                                    $featured_products = $products->get_filter_by_featured_products();
                                    $products_on_sale = $products->get_filter_by_products_on_sale();
                                    $new_products = $products->get_filter_by_new_products();
                                    $top_sellers = $products->get_filter_by_top_sellers();
                                    $combo_deals = $products->get_filter_by_combo_deals();
                                    $bundled_products = $products->get_filter_by_bundled_products();
                                    if ($featured_products['total'] || $products_on_sale['total'] || $new_products['total'] || $top_sellers['total'] || $combo_deals['total'] || $bundled_products['total']) {
                                        ?>
                                        <dt class="odd"><?php echo language('catalog', 'TITLE_USEFUL_LINKS'); ?><a class="button-arrow open" href="#"></a></dt>
                                        <dd class="odd" style="display:block;">
                                            <ol>
                                                <?php
                                                echo $featured_products['total'] ? '<li><a href="' . $featured_products['url'] . '"' . ($featured_products['selected'] ? ' class="filter_in"' : '') . '>' . language('catalog', 'FILTER_FEATURED_PRODUCT') . ' (' . $featured_products['total'] . ')</a></li>' : '';
                                                echo $products_on_sale['total'] ? '<li><a href="' . $products_on_sale['url'] . '"' . ($products_on_sale['selected'] ? ' class="filter_in"' : '') . '>' . language('catalog', 'FILTER_ON_SALE') . ' (' . $products_on_sale['total'] . ')</a></li>' : '';
                                                echo $new_products['total'] ? '<li><a href="' . $new_products['url'] . '"' . ($new_products['selected'] ? ' class="filter_in"' : '') . '>' . language('catalog', 'FILTER_NEW_PRODUCT') . ' (' . $new_products['total'] . ')</a></li>' : '';
                                                echo $top_sellers['total'] ? '<li><a href="' . $top_sellers['url'] . '"' . ($top_sellers['selected'] ? ' class="filter_in"' : '') . '>' . language('catalog', 'FILTER_TOP_SELLER') . ' (' . $top_sellers['total'] . ')</a></li>' : '';
                                                echo $combo_deals['total'] ? '<li><a href="' . $combo_deals['url'] . '"' . ($combo_deals['selected'] ? ' class="filter_in"' : '') . '>' . language('catalog', 'FILTER_COMBO_DEALS') . ' (' . $combo_deals['total'] . ')</a></li>' : '';
                                                echo $bundled_products['total'] ? '<li><a href="' . $bundled_products['url'] . '"' . ($bundled_products['selected'] ? ' class="filter_in"' : '') . '>' . language('catalog', 'FILTER_BUNDLED_PRODUCTS') . ' (' . $bundled_products['total'] . ')</a></li>' : '';
                                                ?>
                                            </ol>
                                        </dd>
                                    <?php } ?>
                                    <!-- END filter by useful link -->

                                    <!-- START filter by brand -->
                                    <?php if (sizeof($brands = $products->get_filter_by_brand())) { ?>
                                        <dt class="odd"><?php echo language('catalog', 'TITLE_BRANDS'); ?><a class="button-arrow open" href="#"></a></dt>
                                        <dd class="odd" style="display:block;">
                                            <ol>
                                                <?php
                                                foreach ($brands as $row_brand) {
                                                    echo '<li><a href="' . $row_brand['url'] . '" ' . ($row_brand['selected'] ? 'class="filter_in"' : '') . '>' . $row_brand['brand'] . ' (' . $row_brand['total'] . ')</a></li>';
                                                }
                                                ?>
                                            </ol>
                                        </dd>            		
                                    <?php } ?>
                                    <!-- END filter by brand -->

                                    <!-- START filter by price -->
                                    <?php if (sizeof($prices = $products->get_filter_by_price())) { ?>
                                        <dt class="odd"><?php echo language('catalog', 'TITLE_PRICES'); ?><a class="button-arrow open" href="#"></a></dt>
                                        <dd class="odd" style="display:block;">
                                            <ol>
                                                <?php
                                                foreach ($prices as $row_price) {
                                                    echo '<li><a href="' . $row_price['url'] . '" ' . ($row_price['selected'] ? 'class="filter_in"' : '') . '>' . $row_price['min'] . ' - ' . $row_price['max'] . ' (' . $row_price['total'] . ')</a></li>';
                                                }
                                                ?>
                                            </ol>
                                        </dd>            		
                                    <?php } ?>
                                    <!-- END filter by price -->

                                    <!-- START filter by rating -->
                                    <?php if (sizeof($ratings = $products->get_filter_by_rating())) { ?>
                                        <dt class="odd"><?php echo language('catalog', 'TITLE_RATINGS'); ?><a class="button-arrow open" href="#"></a></dt>
                                        <dd class="odd" style="display:block;">
                                            <ol>

                                                <?php foreach ($ratings as $row_rating) { ?>
                                                    <li>
                                                        <div class="reviews-wrap">
                                                            <div class="ratings">
                                                                <a href="<?php echo $row_rating['url']; ?>">
                                                                    <div class="rating-box">
                                                                        <div class="rating" style="width:<?php echo $row_rating['rating'] * 100 / 5; ?>%"></div>
                                                                    </div>
                                                                    <span class="amount">(<?php echo $row_rating['total']; ?>)</span></a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php } ?>
                                            </ol>
                                        </dd>            		
                                    <?php } ?>
                                    <!-- END filter by rating -->

                                </dl>
                            </div>       	
                        </div>
                        <!-- END Filter results -->

                        <!-- START block newsletter -->
                        <?php if ($config_site['show_newsletter_form']) { ?>		
                            <div class="block block-subscribe">
                                <div class="block-title">
                                    <strong><span><?php echo language('index', 'LABEL_NL_BLOCK_TITLE'); ?></span></strong>
                                </div>
                                <form id="newsletter-validate-detail" method="post" action="" onsubmit="return false">
                                    <div class="alert alert-success success-msg" style="display:none;" id="nl_email_text_container">
                                        <button type="button" class="close" data-dismiss="alert">×</button>
                                        <div id="newsletter_email_text"></div>
                                    </div>
                                    <div class="block-content">
                                        <div class="form-subscribe-header">
                                            <label for="newsletter"><?php echo language('index', 'LABEL_NL_BLOCK_TEXT'); ?></label>
                                        </div>
                                        <div class="input-box">
                                            <input type="text" placeholder="<?php echo language('index', 'LABEL_NL_EMAIL_PLACEHOLDER'); ?>" class="input-text required-entry validate-email" title="<?php echo language('index', 'LABEL_NL_EMAIL_TITLE'); ?>" id="newsletter" name="form_values[email]" style="text-align:center;">
                                        </div>
                                        <div class="actions">
                                            <button class="button button-inverse btn-lg btn-large" title="<?php echo language('index', 'LABEL_NL_SUBMIT'); ?>" type="submit" onclick="register_newsletter()"><span><span><?php echo language('index', 'LABEL_NL_SUBMIT'); ?></span></span></button>
                                        </div>
                                    </div>
                                </form>                   
                            </div>     	         	
                        <?php } ?>
                        <!-- END block newsletter -->

                        <div class="block block-pub">
                            <?php
                            $page_home = false;
                            include("_includes/template/pub.php");
                            ?>
                        </div>
                    </div>  
                </div>
            </div>
        </div>
        <script type="text/javascript">
        //<![CDATA[
            decorateDataList('narrow-by-list');
            jQuery(function($) {
                /*$('#narrow-by-list > dd > ol').each(function() {
                    var h = $(this).height();
                    if (h > 300) {
                        $(this).css('height', '300px');
                        $(this).jScrollPane({
                            autoReinitialise: true,
                            mouseWheelSpeed: 60
                        });
                    }                        
                });*/
                if ($('.block-layered-nav .button-arrow').length) {
                    $('.block-layered-nav .button-arrow').unbind('click').click(function(e) {
                        e.preventDefault();
                        $(this).parents('dt').next().stop().slideToggle();
                        if ($(this).hasClass('open'))
                            $(this).removeClass('open');
                        else
                            $(this).addClass('open');
                    });
                }
            });
        //]]>
        </script>
    <?php include(dirname(__FILE__) . "/_includes/template/bottom.php");?>
    </body>
</html>