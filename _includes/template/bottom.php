<?php $arr_company_info = get_company_info(); ?>
<!-- Footer -->
<footer>
	<div class="header">
		<div class="container-footer">
		<p class="adresse">
			<strong><?php echo $arr_company_info['name']?></strong><br />
			<?php echo $arr_company_info['address']?><br />
			<?php echo $arr_company_info['city']?> (<?php echo $arr_company_info['state']?>)<br />
			<?php echo $arr_company_info['zip']?>
		</p>
		<div class="box-logo">
			<a href=""><img src="/_images/logo-cmqc.png" alt="chapitre montérégie quebec canada" /></a>
			<a href=""><img src="/_images/logo-ccqc.png" alt="chapitre chateauguay quebec canada" /></a>	
		</div>
		
		<ul class="box-utils">
			<?php if($config_site['display_telephone']){ ?>
				<li class="phone"><?php echo $config_site['company_telephone'];?></li>
			<?php }?>
			<li class="mail"><a href="mailto:<?php echo $config_site['company_email'];?>"><?php echo $config_site['company_email'];?></a></li>
			<li class="gmap">
				<a href="http://maps.google.com/maps?z=12&t=m&q=loc:<?php echo  $config_site['store_locations_default_lat'];?>+<?php echo  $config_site['store_locations_default_lng'];?>" target="new">Google map</a>
				<div class="col-adr">
					<strong><?php echo $arr_company_info['name']?></strong><br />
					<?php echo $arr_company_info['address']?><br />
					<?php echo $arr_company_info['city']?> (<?php echo $arr_company_info['state']?>)<br />
					<?php echo $arr_company_info['zip']?>
				</div>
			</li>
		</ul>
		</div>
	</div>
	<div class="footer">
		<div class="footer-content">
            <div style="float:right" id="powered_by">
            <div style="float:right; padding-top:5px;"><a href="http://www.simplecommerce.com" target="_blank"><img src="../../_images/logo_simple_commerce.png" width="78" height="25" alt="Simple Commerce" /></a></div>
            <div style="float:right; width:130px; margin-right:10px; font-size:11px; color: #a7a7a7; text-align:right;"><?php echo language('global','LABEL_POWERED_BY');?></div>
            <div style="clear:both; margin-bottom:10px"></div>
            <div style="float:right; padding-top:5px;"><a href="http://www.trinergie.ca" target="_blank"><img src="../../_images/logo_trinergie.png" width="62" height="18" alt="Trinergie" /></a></div>
            <div style="float:right; width:130px; margin-right:26px; font-size:11px; color: #a7a7a7; text-align:right;"><?php echo language('global','LABEL_GRAPHIC_DESIGN');?></div>
            <div style="clear:both"></div>
            </div>
		<ul>
			<li><a href="<?php echo $pagesCms[33]["url"];?>"><?php echo $pagesCms[33]["name"];?></a></li>
			<li><a href="<?php echo $mainCats[0]["url"]?>"><?php echo $mainCats[0]["name"];?></a></li>
			<li><a href="<?php echo $pagesCms[28]["url"];?>"><?php echo $pagesCms[28]["name"];?></a></li>
			<li><a href="<?php echo $pagesCms[29]["url"];?>"><?php echo $pagesCms[29]["name"];?></a></li>
			<li><a href="<?php echo $pagesCms[18]["url"];?>"><?php echo $pagesCms[18]["name"];?></a></li>
		</ul>
		<ul>
			<li><a href="<?php echo $pagesCms[30]["url"];?>"><?php echo $pagesCms[30]["name"];?></a></li>
			<li><a href="/news"><?php echo language('global','LABEL_ALL_NEWS');?></a></li>
		</ul>
		<div class="col-adr">
			<strong><?php echo $arr_company_info['name']?></strong><br />
			<?php echo $arr_company_info['address']?><br />
			<?php echo $arr_company_info['city']?> (<?php echo $arr_company_info['state']?>)<br />
			<?php echo $arr_company_info['zip']?>
		</div>
		<div class="box-logotab">
			<a href="http://www.hogmonteregie.ca/index.shtml" target="_blank"><img src="/_images/logo-cmqc2.png" alt="chapitre montérégie quebec canada" /></a>
			<a href="http://www.hogchateauguay.com/Pages/default.aspx" target="_blank"><img src="/_images/logo-ccqc2.png" alt="chapitre chateauguay quebec canada" /></a>	
		</div>
		</div>		
	</div>
</footer>
</div>
<script type="text/javascript">
  //<![CDATA[
  jQuery(function($) {
      
      var venedor_sidebar_timer;
      
      function venedor_sidebar_resize() {
          if (VENEDOR_RESPONSIVE) {
              var winWidth = $(window).innerWidth();
  
              if (winWidth > 750 && ((!$('body').hasClass('bv3') && winWidth < 963) || ($('body').hasClass('bv3') && winWidth < 975))) {
                                      $('.footer-top .footer_column').removeClass('col-sm-3');
                      $('.footer-top .footer_column').addClass('col-sm-4');
                                  if ($('.sidebar').hasClass('col-sm-3')) {
                      $('.sidebar').removeClass('col-sm-3');
                      $('.sidebar').addClass('col-sm-4');
                      $('.main-content').removeClass('col-sm-9');
                      $('.main-content').addClass('col-sm-8');
                      venedor_resize();
                  }                
              } else {
                                      $('.footer-top .footer_column').removeClass('col-sm-4');
                      $('.footer-top .footer_column').addClass('col-sm-3');
                                  if ($('.sidebar').hasClass('col-sm-4')) {
                      $('.sidebar').removeClass('col-sm-4');
                      $('.sidebar').addClass('col-sm-3');
                      $('.main-content').removeClass('col-sm-8');
                      $('.main-content').addClass('col-sm-9');
                      venedor_resize();
                  }
              }
          }
          if (venedor_sidebar_timer) clearTimeout(venedor_sidebar_timer);
      }
  
      $(window).load(venedor_sidebar_resize);
      
      $(window).resize(function() { 
          clearTimeout(venedor_sidebar_timer); 
          venedor_sidebar_timer = setTimeout(venedor_sidebar_resize, 200); 
      });
      
      var venedor_timer;
      
      function venedor_resize() {
          $('.subtitle .line').each(function() {
              w = $(this).prev().width();
              $(this).css('left', (w + 30) + 'px');
          });
          if (VENEDOR_RESPONSIVE) {
              var winWidth = $(window).innerWidth();
              if ($('.flexslider').length) {
                  $('.flexslider').each(function() {
                      var $slider = $(this).data('flexslider');
                      if ($slider) {
                          break_default = $slider.data('break_default');
                          var resized = false;
                          if (break_default) {
                              minItems = break_default[0];
                              maxItems = break_default[0];
                              itemWidth = break_default[1];
                              break_points = $slider.data('break_points');
                              if (break_points) {
                                  for (i = 0; i < break_points.length; i++) {
                                      if (winWidth < break_points[i][0]) {
                                          minItems = break_points[i][1];
                                          maxItems = break_points[i][1];
                                          itemWidth = break_points[i][2];
                                      }
                                  }
                                  if ($slider.move != minItems) {
                                      $slider.setOptions({
                                          minItems: minItems,
                                          maxItems: maxItems,
                                          itemWidth: itemWidth,
                                          move: minItems
                                      });
                                      resized = true;
                                      setTimeout(function() {
                                          if ($slider.w > 0 && $slider.h > 0)
                                              $slider.resize();
                                      }, 400);
                                  }
                              }
                          }
                          if (!resized)
                              $slider.resize();
                      }
                  });
              }
          }
          if (venedor_timer) clearTimeout(venedor_timer); 
      }
  
      $(window).load(venedor_resize);
      $(window).resize(function() {
          clearTimeout(venedor_timer); 
          venedor_timer = setTimeout(venedor_resize, 400); 
      });
  });
  //]]>
</script>	