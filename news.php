<?php 
include(dirname(__FILE__) . "/_includes/config.php");

$news_path = dirname(__FILE__).'/images/news/';

$alias = trim($_GET['alias']);
if (!empty($alias)) $id_news = (int)array_pop(explode('-',$alias));

if ($id_news) {
	if (!$result = $mysqli->query('SELECT
	news.id,
	news.date_news,
	news_description.filename,
	news_description.name,
	news_description.short_desc,
	news_description.description
	FROM
	news 
	INNER JOIN
	news_description
	ON
	(news.id = news_description.id_news AND news_description.language_code = "'.$_SESSION['customer']['language'].'")
	WHERE
	news.id = "'.$id_news.'"
	AND 
	news.active = 1
	LIMIT 1')) throw new Exception('An error occured while trying to load news.');	
	
	if(!$result->num_rows){				
		header("HTTP/1.0 404 Not Found");
		header('Location: /404.php?error=invalid_news');
		exit;	
	}	
	
	$row = $result->fetch_assoc();
	$result->free();
	
	$date_news = $row['date_news'];
	$title = $row['name'];
	$short_desc = htmlspecialchars($row['short_desc']);
	$description = $row['description'];
	$filename = $row['filename'];
	$filename = is_file($news_path.$row['filename']) ? 'http://'.$_SERVER['HTTP_HOST'].'/images/news/'.$row['filename']:'';
	
	$breadcrumb =  '<li><span>&gt;</span><strong>'.htmlspecialchars($title).'</strong></li>';
	$meta_description = $short_desc;
} else {
	$title = language('news','TITLE');
	$meta_description = language('news','META_DESCRIPTION');
	$meta_keywords = language('news','META_KEYWORDS');
	$canonical_link = '';
	
	$q = trim($_GET['q']);
	$archive_year = (int)$_GET['archive_year'];
	$archive_month = (int)$_GET['archive_month'];
	
	if (!empty($q)) $breadcrumb = '<li><span>&gt;</span><strong>'.language('news','LABEL_SEARCH').' : '.htmlspecialchars($q).'</strong></li>';
	else if (!empty($archive_year)) {
		$breadcrumb = '<li><span>&gt;</span><strong>'.language('news','LABEL_ARCHIVE').' - '.language('news','LABEL_YEAR').' : '.$archive_year;
		
		if (!empty($archive_month)) $breadcrumb .= ', '.language('news','LABEL_MONTH').' : '.write_month($archive_month,$_SESSION['customer']['language']);
		$breadcrumb .='</strong></li>';
	}
	
	$page = isset($_GET['page']) ? (int)$_GET['page']:1;
	$view = 20;
}

$page_url = $url_prefix.'news';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php if($id_news) {?>
<meta property="og:title" content="<?php echo $title; ?>"/>
<meta property="og:description" name="description" content="<?php echo $short_desc; ?>" />
<meta property="og:image" content="<?php echo $filename?$filenam:''; ?>" />
<?php } else {?>
<meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>" />
<meta property="og:title" content="<?php echo $title;?> - <?php echo $config_site['site_name']; ?>"/>
<meta property="og:site_name" content="<?php echo $config_site['site_name']; ?>"/>
<?php }?>
<meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>" />

<title><?php echo $title;?> - <?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/_includes/template/header.php");?>
<?php include(dirname(__FILE__) . "/_includes/template/google_analytics.php");?>
<base href="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/'; ?>" />
<script type="text/javascript">
jQuery(function(){
	jQuery("#archive_year").on("change",function(){
		jQuery("#form_archive").submit();
	});
});
</script>
<style type="text/css">
	ul.archive_months > li {
		padding: 5px;
		list-style-type: circle;
		margin-left: 15px;
	}
	
	.image_listing{
		width: <?php echo $config_site['news_width']; ?>px;
		background-image: none;
	}
	
	a.news_link { 
		text-decoration:none;
	}
	
</style>
</head>
<body class="bv3">
<?php include(dirname(__FILE__) . "/_includes/template/top.php");?>
<div class="main-container ">
	<div class="breadcrumbs">
    	<div class="container">
        	<ul>
            	<li class="home">
                	<a title="<?php echo language('global', 'BREADCRUMBS_HOME');?>" href="/"><?php echo language('global', 'BREADCRUMBS_HOME');?></a> 
                	<span>&gt;</span>
                </li>
                <li class="product">
                	<?php echo $breadcrumb ? '<a href="'.$page_url.'">'.language('news','TITLE').'</a>':'<strong>'.language('news','TITLE').'</strong>'; ?>               	
                </li>
                <?php echo $breadcrumb; ?>
            </ul>
	    </div>
	</div>
    <div class="main">
    	<div class="container">
           <div class="main-content withblock" style="overflow: hidden;">		    	
            
            <?php if ($id_news){ ?>            
			<div class="col-sm-9 main-content" style="overflow: hidden;margin-bottom: 20px;">
				<div class="page-title">
                    <h1 style="margin-top:10px;"><?php echo language('news','TITLE');?></h1>
                </div>
                <h3 class="subtitle">
					<span class="inline-title"><?php echo (!$id_news)?language('news','TITLE'):htmlspecialchars($title);?></span>
				</h3>
            	<div style="margin-bottom:10px;"><strong><?php echo df_date($date_news,1,-1); ?></strong></div>
            	<?php echo $description; ?>
            	
                <br />
                <!-- Social bookmarks from http://www.addthis.com/get/sharing  -->
				<div class="addthis-icons clearfix">
					<span style="float:left; margin:3px 5px 0 0;"><?php echo language('global','LABEL_SHARE');?>: </span>
					<!-- AddThis Button BEGIN -->
					<div style="float:left;width: 280px;" class="addthis_toolbox addthis_default_style addthis_32x32_style">
					<a class="addthis_button_preferred_1"></a>
					<a class="addthis_button_preferred_2"></a>
					<a class="addthis_button_preferred_3"></a>
					<a class="addthis_button_preferred_4"></a>
					<a class="addthis_button_compact"></a>
					<a class="addthis_counter addthis_bubble_style"></a>
					</div>
					<script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
					<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52e30bd47068ebfa"></script>
					<!-- AddThis Button END -->
				</div>
				<script type="text/javascript">
					if (typeof addthis_config !== "undefined") {
					addthis_config.services_exclude = 'print'
					} else {
					var addthis_config = {
					services_exclude: 'print'
					};
					}
				</script>
				<br>
	            <div class="button_previous_step"><input type="button" value="<?php echo language('news', 'BTN_BACK');?>" class="previous_step button"  onclick="javascript:history.back();" /></div>    
	           <p>&nbsp;</p>
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
                         <input type="text" placeholder="<?php echo language('index', 'LABEL_NL_EMAIL_PLACEHOLDER');?>" class="input-text required-entry validate-email" title="<?php echo language('index', 'LABEL_NL_EMAIL_TITLE');?>" id="newsletter" name="form_values[email]">
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
            	$page_home = true;
            	include("_includes/template/pub.php");
          	?>
          	</div>
    	</div>        
		 	<?php } ?>            
       
    
    
    <?php if (!$id_news) { ?>
   <div>
   		
    	<div class="col-sm-9 main-content" style="overflow: hidden;margin-bottom: 20px;">
        <div class="page-title">
        	<h1 style="margin-top:10px;"><?php echo language('news','TITLE');?></h1>
        </div>
        <?php
		$where = array();
		
		if (!empty($q)) $where[] = '(news_description.name LIKE "%'.$mysqli->escape_string($q).'%" OR news_description.short_desc LIKE "%'.$mysqli->escape_string($q).'%" OR news_description.description LIKE "%'.$mysqli->escape_string($q).'%")';
		if (!empty($archive_year)) $where[] = 'YEAR(news.date_news) = "'.$archive_year.'"';
		if (!empty($archive_month)) $where[] = 'MONTH(news.date_news) = "'.$archive_month.'"';
		
		$sql = 'SELECT 
		news.id,
		news.date_news,
		news_description.name,
		news_description.short_desc,
		news_description.filename
		FROM 
		news 
		INNER JOIN
		news_description
		ON
		(news.id = news_description.id_news AND news_description.language_code = "'.$_SESSION['customer']['language'].'")
		WHERE
		news.active = 1 '.
		(!empty($where) ? ' AND '.implode(' AND ',$where):'').'
		ORDER BY
		date_news DESC';
		
		if (!$result = $mysqli->query($sql)) throw new Exception('An error occured while trying to load news.');
		
		// get count
		$total_news = $result->num_rows;
		
		$sql .= ' LIMIT '.(($page-1)*$view).','.$view;
		
		if (!$result = $mysqli->query($sql)) throw new Exception('An error occured while trying to load news.');
		
		if ($result->num_rows) {		
			$total_news_page = $result->num_rows;
			
			while ($row = $result->fetch_assoc()) {
				$title = htmlspecialchars($row['name']);
				$url = 'http://'.$_SERVER['HTTP_HOST'].$page_url.'/'.makesafetitle($row['name']).'-'.$row['id'];		
				$filename = is_file($news_path.$row['filename']) ? 'http://'.$_SERVER['HTTP_HOST'].'/images/news/'.$row['filename']:'';
				$short_desc = htmlspecialchars($row['short_desc']);
		?>
        <div style="margin-bottom:20px;overflow: hidden;">
        	<div><a href="<?php echo $url; ?>" class="news_link"><h2><?php echo $row['name']; ?></h2></a></div>	
            <div style="margin-bottom:5px; font-weight:bold;"><?php echo df_date($row['date_news'],1,-1); ?></div>
            <div style="margin-bottom:10px;">            	
                <?php if ($filename) { ?>
				<a href="<?php echo $url; ?>"><img class="image_listing" src="<?php echo $filename; ?>" width="<?php echo $config_site['news_width']; ?>" border="0" align="left" style="margin-right:5px;" /></a>
                <?php } ?>
				<?php echo nl2br($row['short_desc']); ?>   
                <div style="margin-top:10px;">	
	           		<a href="<?php echo $url; ?>" class="news_link"><strong><?php echo language('global','LABEL_READ_NEWS'); ?></strong></a>
    	        </div>            
                <div class="cb"></div>             
            </div>
        </div>
        <?php					
			}
		
		
		?>
        
        <?php
			
			
			$tmp_array = array();
			
			if (!empty($q)) $tmp_array['q'] = $q;
			if (!empty($archive_year)) $tmp_array['archive_year'] = $archive_year;
			if (!empty($archive_mont)) $tmp_array['archive_mont'] = $archive_mont;
			
			
			
			
			
			
			
			
			
			
			
			
			// ----------------- PAGINATION
			// Instantiate the pagination object
			$pagination = new Zebra_Pagination();
			$pagination->variable_name('page');
			
			// Pass current page url and do not include query string
			$pagination->base_url($page_url.'?'.http_build_query(array_merge($tmp_array),'flags_'),0);
			
			// Set position of the next/previous page links
			$pagination->navigation_position(isset($_GET['navigation_position']) && in_array($_GET['navigation_position'], array('left', 'right')) ? $_GET['navigation_position'] : 'outside');
			
			// The number of total records is the number of records in the array
			$pagination->records($total_news);
			
			$pagination->labels(language('global', 'PAGINATION_PREVIOUS'), language('global', 'PAGINATION_NEXT'));
			
			// Records per page
			$pagination->records_per_page($view);

			$current_offset_from = ($pagination->get_page()>1?(($pagination->get_page()-1)*$view)+1:1);
			$current_offset_to = 0;
			$current_offset_to = $current_offset_from+$view-1;
			if ($current_offset_to > $total_news) $current_offset_to = $total_news;
			
			echo '<div style="float:left; margin-top: 4px;"><strong style="color:#333">'.language('global', 'TITLE_SHOWING').'</strong> '.$current_offset_from.' '.language('global', 'TITLE_SHOWING_TO').' '. $current_offset_to.' '.language('global', 'TITLE_SHOWING_OF').' '. $total_news.' '.language('global', 'TITLE_SHOWING_RESULTS').'</div>';
			
			$pagination->render();
			// END ----------------- PAGINATION
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			} else echo language('news','NO_NEWS');					
		?>        
        <p>&nbsp;</p>
        </div>
        
        
        <div class="col-sm-3">
        	<div class="op_block_title"><?php echo language('news','LABEL_SEARCH'); ?></div>
            <div class="op_block_detail" style="overflow: hidden;">
            <form method="get" action="<?php echo $page_url; ?>">
            <div>
                <div class="fl" style="margin-top:2px;">
                <input type="text" name="q" value="" style="width: 70%;"/>&nbsp;
                <input type="submit" class="button button-inverse" name="_search" value="OK"></div>
            </div>
            </form>
            <div class="cb"></div>
            </div>
        <?php 
		$sql = 'SELECT YEAR(date_news) AS year_name FROM news WHERE news.active = 1 GROUP BY YEAR(date_news) ORDER BY year_name DESC';
		
		if (!$result = $mysqli->query($sql)) throw new Exception('An error occured while trying to get year list.');
		
		if ($result->num_rows) {
		?>
        	<div class="op_block_title" style="clear: both; margin-top:20px;"><?php echo language('news','LABEL_ARCHIVE'); ?></div>  
            <form id="form_archive" method="get" action="<?php echo $page_url; ?>">
            <div class="op_block_detail">
            <div>
            	<strong><?php echo language('news','LABEL_YEAR'); ?></strong><br />
                <select name="archive_year" id="archive_year">
                	<option value="">--</option>
                	<?php while ($row = $result->fetch_assoc()) { ?>
    				<option value="<?php echo $row['year_name']; ?>" <?php echo $archive_year == $row['year_name'] ? 'selected="selected"':''; ?>><?php echo $row['year_name']; ?></option>            
	                <?php } ?>
                </select>
            </div>
            <?php
			if ($archive_year) {
				if (!$stmt_month = $mysqli->prepare('SELECT COUNT(news.id) FROM news WHERE news.date_news BETWEEN ? AND ? AND news.active = 1')) throw new Exception('An error occured trying to prepare get months statement.');
				
				$timestamp = strtotime($archive_year.'-01-01');
				
				echo '<ul class="archive_months" style="margin-top:10px;">';
				
				// january
				$start_date = date('Y-m-01',$timestamp);
				$end_date = date('Y-m-t',$timestamp);
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=1">'.write_month(1,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(1,$_SESSION['customer']['language']).' ('.$total.')</li>';
				
				// february
				$start_date = date('Y-m-01',strtotime('+1 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+1 month',$timestamp));		
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=2">'.write_month(2,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(2,$_SESSION['customer']['language']).' ('.$total.')</li>';		
				
				// march
				$start_date = date('Y-m-01',strtotime('+2 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+2 month',$timestamp));			

				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=3">'.write_month(3,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(3,$_SESSION['customer']['language']).' ('.$total.')</li>';					
				
				// april
				$start_date = date('Y-m-01',strtotime('+3 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+3 month',$timestamp));			
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=4">'.write_month(4,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(4,$_SESSION['customer']['language']).' ('.$total.')</li>';						
				
				// may
				$start_date = date('Y-m-01',strtotime('+4 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+4 month',$timestamp));			
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=5">'.write_month(5,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(5,$_SESSION['customer']['language']).' ('.$total.')</li>';					
				
				// june
				$start_date = date('Y-m-01',strtotime('+5 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+5 month',$timestamp));		
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=6">'.write_month(6,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(6,$_SESSION['customer']['language']).' ('.$total.')</li>';						
				
				// july
				$start_date = date('Y-m-01',strtotime('+6 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+6 month',$timestamp));		
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=7">'.write_month(7,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(7,$_SESSION['customer']['language']).' ('.$total.')</li>';							
				
				// august
				$start_date = date('Y-m-01',strtotime('+7 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+7 month',$timestamp));		
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=8">'.write_month(8,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(8,$_SESSION['customer']['language']).' ('.$total.')</li>';							
				
				// september
				$start_date = date('Y-m-01',strtotime('+8 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+8 month',$timestamp));		
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=9">'.write_month(9,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(9,$_SESSION['customer']['language']).' ('.$total.')</li>';						
				
				// october
				$start_date = date('Y-m-01',strtotime('+9 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+9 month',$timestamp));		
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=10">'.write_month(10,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(10,$_SESSION['customer']['language']).' ('.$total.')</li>';						
				
				// november
				$start_date = date('Y-m-01',strtotime('+10 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+10 month',$timestamp));					
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=11">'.write_month(11,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(11,$_SESSION['customer']['language']).' ('.$total.')</li>';			
				
				// december
				$start_date = date('Y-m-01',strtotime('+11 month',$timestamp));
				$end_date = date('Y-m-t',strtotime('+11 month',$timestamp));
				
				if (!$stmt_month->bind_param("ss", $start_date, $end_date)) throw new Exception('An error occured while trying to bind params to get months statement.'."\r\n\r\n".$mysqli->error);
			
				/* Execute the statement */
				if (!$stmt_month->execute()) throw new Exception('An error occured while trying to get months.'."\r\n\r\n".$mysqli->error);								
				/* store result */
				$stmt_month->store_result();	

				/* bind result variables */
				$stmt_month->bind_result($total);									
				
				$stmt_month->fetch();
				
				echo $total ? '<li><a href="'.$page_url.'?archive_year='.$archive_year.'&archive_month=12">'.write_month(12,$_SESSION['customer']['language']).' ('.$total.')</a></li>':'<li>'.write_month(12,$_SESSION['customer']['language']).' ('.$total.')</li>';								
				
				echo '</ul>';
			}
			?>
            <div class="cb"></div>             
            </div>
            </form>
            
          
             
        <?php } ?>
        </div> 
        <div class="cb"></div>
    </div>
    <?php } ?>
</div>
</div>
</div>
</div>
<?php include(dirname(__FILE__) . "/_includes/template/bottom.php");?>
</body>
</html>