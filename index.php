<?php 
include(dirname(__FILE__) . "/_includes/config.php");

// Link for the button Continue Shopping
$_SESSION['link_continue_shopping'] = $_SERVER['REQUEST_URI'];	

$products = new Products;

if (!stristr($_SERVER['REQUEST_URI'],'/'.$_SESSION['customer']['language'])) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$_SESSION['customer']['language'].$_SERVER['REQUEST_URI']);
	exit;	
}

// GET Page Infos
$page = new Page;
if (!$page->load_id(1)) {
	header("HTTP/1.0 404 Not Found");
	exit;	
}
$meta_keywords = $page->get_meta_keywords();
$meta_description = $page->get_meta_description();
$home_page_description = $page->get_description();

// GET Banners Infos
$banners = new Banners;
$arr_banners = $banners->get_banners();
$total_banner = sizeof($arr_banners);

$sql = 'SELECT news.id, news.date_news, news_description.name, 
  				 news_description.short_desc, news_description.filename
    	  FROM news 
  		  INNER JOIN news_description ON (news.id = news_description.id_news AND news_description.language_code = "'.$_SESSION['customer']['language'].'")
  		  WHERE news.active = 1 
  		  ORDER BY date_news DESC
		  LIMIT 2';
if (!$news = $mysqli->query($sql)) throw new Exception('An error occured while trying to load news.');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>" />
<meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>" />

<title><?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/_includes/template/header.php");?> 
<?php include(dirname(__FILE__) . "/_includes/template/google_analytics.php");?>
</head>
<body class="bv3">
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1&appId=204228649657220";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<?php include(dirname(__FILE__) . "/_includes/template/top.php");?>
 
<div class="main-container home-container">
  <!-- START Main Slider -->
  <div class="main-slider">
  	<div id="homeslider-sequence">
    	<ul class="sequence-canvas">
    		<?php
              foreach($arr_banners as $banner){
            ?>
  			<li class="block_homeslider1 animate-in">
                <div class="slider-bg" style="">
                	<img src="<?php echo $banner["image"];?>" alt="" width="<?php echo $config_site['banner_width'];?>" height="<?php echo $config_site['banner_height'];?>">
                </div>
                <div class="slider-wrap container"> 
                <a href="<?php echo ($banner['url']!='')?$banner['url']:'javascript:void(0);';?>" <?php echo ($banner["open_new_window"])?'target="new"':''?> style="width:100%; height:100%;<?php echo ($banner['url']!='')?'':'cursor: default;';?>">&nbsp;</a>            	
                </div>
  			</li>
            <?php }?>                  
      	</ul>
      	<?php if(count($arr_banners)>1) {?>
      	<ul class="sequence-pagination" style="display: block;">
    		<?php for($bi=1;$bi<=count($arr_banners);$bi++) {?>
            <li>
                <a><?php echo $bi;?></a>
            </li>
            <?php }?>                    
        </ul>
        <span href="#" class="sequence-prev slider-arrow prev"></span> 
        <span href="#" class="sequence-next slider-arrow next"></span>
        <?php }?>
      </div>
      <script type="text/javascript">
      //<![CDATA[
      jQuery(document).ready(function($){
          jQuery('#homeslider-sequence .slider-wrap').addClass('container');
          var options = {
              nextButton: true,
              prevButton: true,
              pagination: true,
              theme: 'slide',
              speed: 500,
              autoPlay: true,
              autoPlayDelay: <?php echo $config_site['banner_delay_between'];?>,
              pauseOnHover: true,
              preloader: true,
              animateStartingFrameIn: true
          };
          
          var homeSequence = $("#homeslider-sequence").sequence(options).data("sequence");
      });
      //]]>
      </script>
  </div>
  <!-- END main slider -->
  
  <section>
		<div class="box-fb" style="width:340px; margin:auto;">
			<span style="float: left;margin:3px 10px 0 0;">rejoingez la bande</span>
			<div class="fb-like" data-href="<?php echo $config_site['facebook'];?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
		</div>
		<ul class="grid-service">
			<li><a href="<?php echo $mainCats[0]["url"];?>"><img src="/_images/middle-boite-1.jpg" /> <div><img src="/_images/fleche.png" class="visu" /><br /><b><?php echo str_replace(" ","<br>",$mainCats[0]["name"]);?></b> </div></a></li>
			<li class="col-fin"><a href="<?php echo $pagesCms[27]["url"];?>"><img src="/_images/middle-boite-2.jpg" /> <div><img src="/_images/fleche.png" class="visu" /><br /><b><?php echo $pagesCms[27]["name"];?></b> </div></a></li>
			<li class="col-loc"><a href="<?php echo $pagesCms[28]["url"];?>"><img src="/_images/middle-boite-3.jpg" /> <div><img src="/_images/fleche.png" class="visu" /><br /><b><?php echo $pagesCms[28]["name"];?></b> </div></a></li>
		</ul>								
	</section>
	<section class="box-inscription">				
		<form id="frm-inscription" method="post" action="" onsubmit="return false">
			<div class="alert alert-success success-msg" style="display:none;" id="nl_email_text_container">
            	<button type="button" class="close" data-dismiss="alert">×</button>
            	<div id="newsletter_email_text"></div>
            </div>
			<span class="lab-insc">
				inscrivez-vous<br />&#224; l'infolettre
			</span>
			<span class="col-desc">
				recevez en primeur <br />Nos promotions et <br />les événements à venir
			</span>
			<div>
				
				<input type="text"  id="newsletter" name="form_values[email]"  class="input-txt" />
				<input type="submit" onclick="register_newsletter('index')" name="btn-inscription" id="btn-inscription" value="inscription" />				
			</div>						
		</form>
	</section>
	<!-- SECTION News -->
	<section class="box-news clear">
		<div class="col-news floatR">
			<h2><span>quoi de<br />neuf</span></h2>
			<ul class="list-news">
				<?php while($row = $news->fetch_assoc()) {
				  $news_path = dirname(__FILE__).'/images/news/';
                  $newstitle = htmlspecialchars($row['name']);
                  $newsurl = $url_prefix.'news/'.makesafetitle($row['name']).'-'.$row['id'];		
                  $newsshort_desc = htmlspecialchars($row['short_desc']);
				?>					
					<li>
    					<p>
    						<a href="<?php echo $newsurl;?>"><strong><?php echo $newstitle;?></strong></a>
    						<?php echo $newsshort_desc;?>
    					</p>
    				</li>
				<?php }?>				
			</ul>
			<p class="link"><a href="/news">toutes les nouvelles <img src="/_images/fleche2.png" /></a></p>					
		</div>
		<div class="col-news floatL">
			<img alt="" src="/_images/img3.jpg" class="picto-leo"/>
			<img alt="" src="/_images/img03.jpg" class="picto-leo-tab"/>
			<img alt="" src="/_images/img003.jpg" class="picto-leo-mobile"/>
			<div class="box-right">
				<h2><span>pourquoi<br />ont-ils<br />choisi</span><img src="/_images/leo.jpg" alt="léo" /></h2>
				<p class="link"><a href="<?php echo $pagesCms[30]["url"];?>"><?php echo $pagesCms[30]["name"];?> <img src="/_images/fleche2.png" /></a></p>	
			</div>					
		</div>		
	</section>
	<!--  section   -->
	<section class="toto">
		<div class="boxes">
		<div class="box1">
			<h3>des <br />vrais bikers <br />au service <br /> des bikers</h3>
			<p>
				En 2014, votre concessionnaire célèbre son 40e anniversaire.  C'est en 1974 que Monsieur Léo Bouchard ouvre sa première concession sur la rue Verchères à Longueuil.. Depuis novembre 2005 nous sommes situé au 8705 Boulevard Taschereau à Brossard.  Nous sommes le plus vieux concessionnaire exclusif au Québec de Harley-Davidson!<br />
<br />
Notre personnel dévoué saura répondre à toutes vos questions.  Que ce soit pour l'acquisition de votre moto neuve ou usagée, à propos de pièces dont elle a besoin, de ses besoins mécaniques ou simplement pour vous vêtir de la tête aux pieds!!<br />
<br />
Passez nous voir en magasin et faites de votre rêve une réalité...

			</p>
		</div>
		<div class="box2">
		<?php
		// Adds (Publicity)
		if (!$result_pub = $mysqli->query('SELECT 
		pub.id,
		pub.width,
		pub.display_in_column,
		pub.display_in_page,
		pub_description.url_type,
		pub_description.url,
		pub_description.target_blank,
		pub_description.filename,
		pub_description.id_cmspage,
		cmspage.external_link,
		cmspage_description.external_link_link,
		cmspage_description.external_link_target_blank,
		cmspage_description.alias,
		pub_description.id_subscription_contest
		FROM pub
		INNER JOIN
		pub_description
		ON
		(pub.id = pub_description.id_pub AND pub_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
		LEFT JOIN
		(cmspage_description CROSS JOIN cmspage)
		ON
		(pub_description.id_cmspage = cmspage_description.id_cmspage AND cmspage_description.language_code = pub_description.language_code AND cmspage_description.id_cmspage = cmspage.id)
		WHERE 
		pub.active = 1
		AND
		pub_description.filename <> "" 
		AND 
		pub.display_start_date <= "'.$mysqli->escape_string($current_datetime).'"
		AND
		(pub.display_end_date = "0000-00-00 00:00:00" OR pub.display_end_date > "'.$mysqli->escape_string($current_datetime).'")
		ORDER BY RAND()
		LIMIT 1')) throw new Exception('An error occured while trying to get pub.'."\r\n\r\n".$mysqli->error);
		
		$row_pub = $result_pub->fetch_assoc();
		
		if ($row_pub['id']) {
			$image_path = '/_images/pub/'.$row_pub['filename'];	
			
			switch ($row_pub['url_type']) {
				// none
				case 0:
					$url = '';
					break;
				// url
				case 1:
					$url = $row_pub['url'];
					$target_blank = $row_pub['target_blank'];
					break;
				// cmspage
				case 2:
					$url = $row_pub['external_link'] ? $row_pub['external_link_link']:$url_prefix.'page/'.$row_pub['alias'];
					$target_blank = $row_pub['external_link_target_blank'];
					break;
				// subscription contest
				case 3:
					$url = $url_prefix.'registration?id='.$row_pub['id_subscription_contest'];
					$target_blank = 0;
					break;	
			}		
			
			echo ($url ? '<a href="'.$url.'" '.($target_blank ? 'target="_blank"':'').' style="top:0; left:0;">':'').'<img src="'.$image_path.'" width="400" />'.($url ? '</a>':'');
		}		
		?>        
		</div>
		</div>
	</section>
</div>  
<script type="text/javascript">
var ratio = <?php echo $config_site['banner_width']/$config_site['banner_height'];?>;
jQuery(window).resize(function() {
	ajustbanner();
});
function ajustbanner() {
	var width = jQuery(window).width(); 
	var height = width/ratio; 
	jQuery('#homeslider-sequence').height(height);
}
jQuery(function($) {
	ajustbanner();
})
</script>
<?php include(dirname(__FILE__) . "/_includes/template/tag.php");?>	
<?php include(dirname(__FILE__) . "/_includes/template/review.php");?>
<?php include(dirname(__FILE__) . "/_includes/template/bottom.php");?>
</body>
</html>