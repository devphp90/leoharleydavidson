<?php 
include(dirname(__FILE__) . "/_includes/config.php");

if (isset($_GET['task']) && $_GET['task'] == 'list_locations') {
	
	// Get parameters from URL
	$center_lat = trim($_GET["lat"]);
	$center_lat = $center_lat ? $center_lat:$config_site['store_locations_default_lat'];
	$center_lng = trim($_GET["lng"]);
	$center_lng = $center_lng ? $center_lng:$config_site['store_locations_default_lng'];
	$radius = (int)$_GET["radius"];
	
	if (!$result = $mysqli->query('SELECT 
	*, 
	TIME_FORMAT(open_mon_start_time,"%H:%i") AS open_mon_start_time,
	TIME_FORMAT(open_mon_end_time,"%H:%i") AS open_mon_end_time,
	TIME_FORMAT(open_tue_start_time,"%H:%i") AS open_tue_start_time,
	TIME_FORMAT(open_tue_end_time,"%H:%i") AS open_tue_end_time,
	TIME_FORMAT(open_wed_start_time,"%H:%i") AS open_wed_start_time,
	TIME_FORMAT(open_wed_end_time,"%H:%i") AS open_wed_end_time,
	TIME_FORMAT(open_thu_start_time,"%H:%i") AS open_thu_start_time,
	TIME_FORMAT(open_thu_end_time,"%H:%i") AS open_thu_end_time,
	TIME_FORMAT(open_fri_start_time,"%H:%i") AS open_fri_start_time,
	TIME_FORMAT(open_fri_end_time,"%H:%i") AS open_fri_end_time,
	TIME_FORMAT(open_sat_start_time,"%H:%i") AS open_sat_start_time,
	TIME_FORMAT(open_sat_end_time,"%H:%i") AS open_sat_end_time,
	TIME_FORMAT(open_sun_start_time,"%H:%i") AS open_sun_start_time,
	TIME_FORMAT(open_sun_end_time,"%H:%i") AS open_sun_end_time,	
	( ( 3959 * acos( cos( radians("'.$center_lat.'") ) * cos( radians( lat ) ) * cos( radians( lng ) - radians("'.$center_lng.'") ) + sin( radians("'.$center_lat.'") ) * sin( radians( lat ) ) ) ) * 1.60934 ) AS distance 
	FROM store_locations 
	WHERE active = 1
	HAVING distance < "'.$radius.'" 
	ORDER BY distance ASC')) throw new Exception('An error occured while trying to get store locations.');	
	
	// Start XML file, create parent node
	$dom = new DOMDocument("1.0");
	$node = $dom->createElement("markers");
	$parnode = $dom->appendChild($node);
	
	header("Content-type: text/xml");
	
	// Iterate through the rows, adding XML nodes for each
	while ($row = $result->fetch_assoc()) {
		if ($row['url'] && !stristr($row['url'],'http')) $row['url'] = 'http://'.$row['url'];
		if ($row['image'] && !is_file(dirname(__FILE__).'/images/stores/'.$row['image'])) $row['image'] = '';
		
		$node = $dom->createElement("marker");
		$newnode = $parnode->appendChild($node);
		$newnode->setAttribute("hide_address", $row['hide_address']);
		$newnode->setAttribute("name", $row['name']);
		$newnode->setAttribute("address", $row['address']);
		$newnode->setAttribute("city", $row['city']);
		$newnode->setAttribute("state", $row['state_code']);
		$newnode->setAttribute("zip", $row['zip']);
		$newnode->setAttribute("telephone", $row['telephone']);
		$newnode->setAttribute("fax", $row['fax']);
		$newnode->setAttribute("email", $row['email']);
		$newnode->setAttribute("url", $row['url']);
		$newnode->setAttribute("image", $row['image']);
		$newnode->setAttribute("lat", $row['lat']);
		$newnode->setAttribute("lng", $row['lng']);
		$newnode->setAttribute("distance", $row['distance']);
		$newnode->setAttribute("open_mon", $row['open_mon']);
		$newnode->setAttribute("open_mon_start_time", $row['open_mon_start_time']);
		$newnode->setAttribute("open_mon_end_time", $row['open_mon_end_time']);
		$newnode->setAttribute("open_tue", $row['open_tue']);
		$newnode->setAttribute("open_tue_start_time", $row['open_tue_start_time']);
		$newnode->setAttribute("open_tue_end_time", $row['open_tue_end_time']);		
		$newnode->setAttribute("open_wed", $row['open_wed']);
		$newnode->setAttribute("open_wed_start_time", $row['open_wed_start_time']);
		$newnode->setAttribute("open_wed_end_time", $row['open_wed_end_time']);		
		$newnode->setAttribute("open_thu", $row['open_thu']);
		$newnode->setAttribute("open_thu_start_time", $row['open_thu_start_time']);
		$newnode->setAttribute("open_thu_end_time", $row['open_thu_end_time']);		
		$newnode->setAttribute("open_fri", $row['open_fri']);
		$newnode->setAttribute("open_fri_start_time", $row['open_fri_start_time']);
		$newnode->setAttribute("open_fri_end_time", $row['open_fri_end_time']);		
		$newnode->setAttribute("open_sat", $row['open_sat']);
		$newnode->setAttribute("open_sat_start_time", $row['open_sat_start_time']);
		$newnode->setAttribute("open_sat_end_time", $row['open_sat_end_time']);		
		$newnode->setAttribute("open_sun", $row['open_sun']);
		$newnode->setAttribute("open_sun_start_time", $row['open_sun_start_time']);
		$newnode->setAttribute("open_sun_end_time", $row['open_sun_end_time']);		
	}
	
	echo $dom->saveXML();
	exit;	
}

$name = language('store_locations','TITLE');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex" />
<title><?php echo $name;?> - <?php echo $config_site['site_name']; ?></title>
<?php include(dirname(__FILE__) . "/_includes/template/header.php");?>
<?php include(dirname(__FILE__) . "/_includes/template/google_analytics.php");?>
<style type="text/css">
	#map {
		border:1px solid #ccc;	
	}
	.locations {
		padding-top: 20px;
		padding-bottom: 20px;
		padding-left: 10px;
		padding-right: 10px;
		cursor: pointer;
		border-bottom:1px solid #ccc;
	}
	.locations:hover {
		background-color:#EAEAEA;
	}
</style>
<script src="//maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
var map;
var markers = [];
var infoWindow;
var locationSelect;
var center_lat = <?php echo $config_site['store_locations_default_lat'] ? $config_site['store_locations_default_lat']:0; ?>;
var center_lng = <?php echo $config_site['store_locations_default_lng'] ? $config_site['store_locations_default_lng']:0; ?>; 

jQuery(function(){
	load();
	
	jQuery("body").on("click",".locations",function(){
		var id = jQuery(this).prop("id");
		if (id) {
			id = parseInt(id.replace("location_",""));
		
			if (!isNaN(id)) google.maps.event.trigger(markers[id], 'click');
			jQuery.scrollTo( '#map_container', 1000);
		}
	});
	
	searchLocationsNear(center_lat,center_lng);
});

// load map
function load() {
  map = new google.maps.Map(document.getElementById("map"), {
	<?php echo $config_site['store_locations_default_lat'] && $config_site['store_locations_default_lng'] ? 'center: new google.maps.LatLng('.$config_site['store_locations_default_lat'].', '.$config_site['store_locations_default_lng'].'),':''; ?>
	zoom: 4,
	mapTypeId: 'roadmap',
	mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
  });
  infoWindow = new google.maps.InfoWindow();
}

// on click button, search using city and zip code
function searchLocations() {
 var address = document.getElementById("city").value+" "+document.getElementById("zip").value;
 var geocoder = new google.maps.Geocoder();
 geocoder.geocode({address: address}, function(results, status) {
   if (status == google.maps.GeocoderStatus.OK) {
		searchLocationsNear(results[0].geometry.location.lat(), results[0].geometry.location.lng());
   } else {
	 	alert(address + ' <?php echo language('store_locations','ERROR_ADDRESS_NOT_FOUND'); ?>');
   }
 });
}

// clear map
function clearLocations() {
 infoWindow.close();
 for (var i = 0; i < markers.length; i++) {
   markers[i].setMap(null);
 }
 markers.length = 0;
 jQuery("#map_locations").html("");
}

// search using found lat lng from on click
function searchLocationsNear(center_lat,center_lng) {
 clearLocations();
 
 var radius = document.getElementById('radius').value;
 
 if (center_lat && center_lng) {
	 var searchUrl = '<?php echo $_SERVER['PHP_SELF']; ?>?task=list_locations&lat=' + center_lat + '&lng=' + center_lng + '&radius=' + radius + '&'+(new Date().getTime());
	
	 var locations = '<div>';
	 
	 downloadUrl(searchUrl, function(data) {
	   var xml = parseXml(data);
	   var markerNodes = xml.documentElement.getElementsByTagName("marker");
	   
	   if (!markerNodes.length) {		   
		    jQuery("#map").css('position','absolute').css('top','-800px');
			
			alert("<?php echo language('store_locations','ERROR_NO_STORES_FOUND'); ?>");
			return false;   
	   }
	   
	   var bounds = new google.maps.LatLngBounds();
	   for (var i = 0; i < markerNodes.length; i++) {
		 var hide_address = markerNodes[i].getAttribute("hide_address");
		 var name = markerNodes[i].getAttribute("name");
		 var address = markerNodes[i].getAttribute("address");
		 var city = markerNodes[i].getAttribute("city");
		 var state = markerNodes[i].getAttribute("state");
		 var zip = markerNodes[i].getAttribute("zip");
		 var lat = parseFloat(markerNodes[i].getAttribute("lat"));
		 var lng = parseFloat(markerNodes[i].getAttribute("lng"))
		 var distance = parseFloat(markerNodes[i].getAttribute("distance"));
		 var telephone = markerNodes[i].getAttribute("telephone");
		 var fax = markerNodes[i].getAttribute("fax");
		 var email = markerNodes[i].getAttribute("email");
		 var url = markerNodes[i].getAttribute("url");
		 var image = markerNodes[i].getAttribute("image");
		 var open_mon = markerNodes[i].getAttribute("open_mon"); 
		 var open_mon_start_time = markerNodes[i].getAttribute("open_mon_start_time"); 
		 var open_mon_end_time = markerNodes[i].getAttribute("open_mon_end_time"); 
		 var open_tue = markerNodes[i].getAttribute("open_tue"); 
		 var open_tue_start_time = markerNodes[i].getAttribute("open_tue_start_time"); 
		 var open_tue_end_time = markerNodes[i].getAttribute("open_tue_end_time"); 	 
		 var open_wed = markerNodes[i].getAttribute("open_wed"); 
		 var open_wed_start_time = markerNodes[i].getAttribute("open_wed_start_time"); 
		 var open_wed_end_time = markerNodes[i].getAttribute("open_wed_end_time"); 	 	 
		 var open_thu = markerNodes[i].getAttribute("open_thu"); 
		 var open_thu_start_time = markerNodes[i].getAttribute("open_thu_start_time"); 
		 var open_thu_end_time = markerNodes[i].getAttribute("open_thu_end_time"); 
		 var open_fri = markerNodes[i].getAttribute("open_fri"); 
		 var open_fri_start_time = markerNodes[i].getAttribute("open_fri_start_time"); 
		 var open_fri_end_time = markerNodes[i].getAttribute("open_fri_end_time"); 	
		 var open_sat = markerNodes[i].getAttribute("open_sat"); 
		 var open_sat_start_time = markerNodes[i].getAttribute("open_sat_start_time"); 
		 var open_sat_end_time = markerNodes[i].getAttribute("open_sat_end_time"); 		  	 	 
		 var open_sun = markerNodes[i].getAttribute("open_sun"); 
		 var open_sun_start_time = markerNodes[i].getAttribute("open_sun_start_time"); 
		 var open_sun_end_time = markerNodes[i].getAttribute("open_sun_end_time"); 		  	 	 
	
		 var latlng = new google.maps.LatLng(lat,lng);
	
		createMarker(latlng, hide_address, name, address, city, state, zip, telephone, fax, email, url, open_mon, open_mon_start_time, open_mon_end_time, open_tue, open_tue_start_time, open_tue_end_time, open_wed, open_wed_start_time, open_wed_end_time, open_thu, open_thu_start_time, open_thu_end_time, open_fri, open_fri_start_time, open_fri_end_time, open_sat, open_sat_start_time, open_sat_end_time, open_sun, open_sun_start_time, open_sun_end_time);		 
		
		bounds.extend(latlng);
		 
		 locations += '<div class="locations" '+(hide_address == 0 ? 'id="location_'+i+'"':'')+'>';
		 
		 if (image && image.length) {
			locations += '<div class="fl" style="margin-right: 20px;"><img src="<?php echo $url_prefix.'images/stores/'; ?>'+image+'" border="0" /></div>';		 
		 }
		 
		 locations += '<div class="fl" style="margin-right:150px;"><b style="font-size:16px;">'+name+'</b> <span style="font-size:14px;">( '+distance.toFixed(1)+' km )</span><br /><span style="font-size:11px;">';
		 
		 if (hide_address == 0) {
			 locations += address;
		 
			if (city && city.length) {
			  locations += '<br />'+city;
			  if (state && state.length) locations += ', '+state;
			}
			if (zip && zip.length) locations += '<br />'+zip;
		 }
		
		locations += '<br />';
		
		if (telephone && telephone.length) locations += '<br /><b><?php echo language('store_locations','LABEL_TELEPHONE'); ?></b> '+telephone;
		if (fax && fax.length) locations += '<br /><b><?php echo language('store_locations','LABEL_FAX'); ?></b> '+fax;
		if (email && email.length) locations += '<br /><b><?php echo language('store_locations','LABEL_EMAIL'); ?></b> <a href="mailto:'+email+'">'+email+'</a>';
		if (url && url.length) locations += '<br /><b><?php echo language('store_locations','LABEL_URL'); ?></b> <a href="'+url+'" target="_blank">'+url+'</a>';
		
		locations += '</span></div>';
		
		if (open_mon == 1 || open_tue == 1 || open_wed == 1 || open_thu == 1 || open_fri == 1 || open_sat == 1 || open_sun == 1) {
			locations += "<div class='fl'><b style='font-size:14px;'><?php echo language('store_locations','LABEL_OPENING_HOURS'); ?></b><span style='font-size:11px;'>";
			
			if (open_mon == 1) { 
				locations += '<br /><b><?php echo language('store_locations','LABEL_OPEN_MON'); ?></b> '+open_mon_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_mon_end_time;
			}
			
			if (open_tue == 1) { 
				locations += '<br /><b><?php echo language('store_locations','LABEL_OPEN_TUE'); ?></b> '+open_tue_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_tue_end_time;
			}
			
			if (open_wed == 1) { 
				locations += '<br /><b><?php echo language('store_locations','LABEL_OPEN_WED'); ?></b> '+open_wed_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_wed_end_time;
			}
			
			if (open_thu == 1) { 
				locations += '<br /><b><?php echo language('store_locations','LABEL_OPEN_THU'); ?></b> '+open_thu_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_thu_end_time;
			}
			
			if (open_fri == 1) { 
				locations += '<br /><b><?php echo language('store_locations','LABEL_OPEN_FRI'); ?></b> '+open_fri_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_fri_end_time;
			}
			
			if (open_sat == 1) { 
				locations += '<br /><b><?php echo language('store_locations','LABEL_OPEN_SAT'); ?></b> '+open_sat_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_sat_end_time;
			}
			
			if (open_sun == 1) { 
				locations += '<br /><b><?php echo language('store_locations','LABEL_OPEN_SUN'); ?></b> '+open_sun_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_sun_end_time;
			}
			
			locations += '</span></div>';
		}
		 if (hide_address == 0) {
			 locations += '<div class="fr"><a href="https://www.google.ca/maps?saddr='+center_lat+','+center_lng+'&daddr='+lat+','+lng+'" target="_blank"><?php echo language('store_locations','LABEL_DIRECTIONS'); ?></a></div>';
		 }
		 
		 locations += '<div style="clear: both;"></div></div>';
	   }
	   map.fitBounds(bounds);
	   
	   locations += '</div>';
	   
	   jQuery("#map_locations").append(locations);   
	   jQuery("#map").css('position','relative').css('top','0');
	  });	  	  	  	  
 } else jQuery("#map").css('position','absolute').css('top','-800px');
}

// create marker on map and configure display data
function createMarker(latlng, hide_address, name, address, city, state, zip, telephone, fax, email, url, open_mon, open_mon_start_time, open_mon_end_time, open_tue, open_tue_start_time, open_tue_end_time, open_wed, open_wed_start_time, open_wed_end_time, open_thu, open_thu_start_time, open_thu_end_time, open_fri, open_fri_start_time, open_fri_end_time, open_sat, open_sat_start_time, open_sat_end_time, open_sun, open_sun_start_time, open_sun_end_time) {
  var html = "<b style='font-size:14px;'>" + name + "</b><span style='font-size:11px;'><br/>" + address;
  if (city && city.length) {
	  html += '<br />'+city;
	  if (state && state.length) html += ', '+state;
  }
  if (zip && zip.length) html += '<br />'+zip;
  
  html += '<br />';
  
  if (telephone && telephone.length) html += '<br /><b><?php echo language('store_locations','LABEL_TELEPHONE'); ?></b> '+telephone;
  if (fax && fax.length) html += '<br /><b><?php echo language('store_locations','LABEL_FAX'); ?></b> '+fax;
  if (email && email.length) html += '<br /><b><?php echo language('store_locations','LABEL_EMAIL'); ?></b> <a href="mailto:'+email+'">'+email+'</a>';
  if (url && url.length) html += '<br /><b><?php echo language('store_locations','LABEL_URL'); ?></b> <a href="'+url+'" target="_blank">'+url+'</a>';
  
  html += '</span>';
  
	if (open_mon == 1 || open_tue == 1 || open_wed == 1 || open_thu == 1 || open_fri == 1 || open_sat == 1 || open_sun == 1) {
		html += "<div style='margin-top:10px;'><b style='font-size:14px;'><?php echo language('store_locations','LABEL_OPENING_HOURS'); ?></b><span style='font-size:11px;'>";
		
		if (open_mon == 1) { 
			html += '<br /><b><?php echo language('store_locations','LABEL_OPEN_MON'); ?></b> '+open_mon_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_mon_end_time;
		}
		
		if (open_tue == 1) { 
			html += '<br /><b><?php echo language('store_locations','LABEL_OPEN_TUE'); ?></b> '+open_tue_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_tue_end_time;
		}
		
		if (open_wed == 1) { 
			html += '<br /><b><?php echo language('store_locations','LABEL_OPEN_WED'); ?></b> '+open_wed_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_wed_end_time;
		}
		
		if (open_thu == 1) { 
			html += '<br /><b><?php echo language('store_locations','LABEL_OPEN_THU'); ?></b> '+open_thu_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_thu_end_time;
		}
		
		if (open_fri == 1) { 
			html += '<br /><b><?php echo language('store_locations','LABEL_OPEN_FRI'); ?></b> '+open_fri_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_fri_end_time;
		}
		
		if (open_sat == 1) { 
			html += '<br /><b><?php echo language('store_locations','LABEL_OPEN_SAT'); ?></b> '+open_sat_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_sat_end_time;
		}
		
		if (open_sun == 1) { 
			html += '<br /><b><?php echo language('store_locations','LABEL_OPEN_SUN'); ?></b> '+open_sun_start_time+' <?php echo language('store_locations','LABEL_OPEN_UNTIL'); ?> '+open_sun_end_time;
		}
		
		html += '</span></div>';
	}  
 
  var marker = new google.maps.Marker({
	map: map,
	position: latlng,
	visible: hide_address == 0 ? true:false
  });
  google.maps.event.addListener(marker, 'click', function() {
	infoWindow.setContent(html);
	infoWindow.open(map, marker);
  });
  markers.push(marker);
}

// ajax request to xml source
function downloadUrl(url, callback) {
  var request = window.ActiveXObject ?
	  new ActiveXObject('Microsoft.XMLHTTP') :
	  new XMLHttpRequest;

  request.onreadystatechange = function() {
	if (request.readyState == 4) {
	  request.onreadystatechange = doNothing;
	  callback(request.responseText, request.status);
	}
  };

  request.open('GET', url, true);
  request.send(null);
}

// parse xml response
function parseXml(str) {
  if (window.ActiveXObject) {
	var doc = new ActiveXObject('Microsoft.XMLDOM');
	doc.loadXML(str);
	return doc;
  } else if (window.DOMParser) {
	return (new DOMParser).parseFromString(str, 'text/xml');
  }
}

function doNothing() {}
//]]>
</script>
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
            	<!-- <span>&gt;</span> -->
            </li>
            <li><?php echo $breadcrumb;?></li>
          </ul>
        </div>
    </div>
    <!-- END breadcrumbs -->
	<div class="main">
    <div class="container">
    <div class="col-sm-9 main-content">
        	<h2 class="subtitle"><?php echo $name;?></h2>
        	
            <div style="margin-bottom:20px;">
            <form method="post" action="">
            	<div class="fl" style="margin-right:10px;">
                	<strong><?php echo language('store_locations','LABEL_CITY'); ?></strong><br />
                    <input type="text" value="" id="city" />
                </div>
            	<div class="fl" style="margin-right:10px;">
                	<strong><?php echo language('store_locations','LABEL_ZIP'); ?></strong><br />
                    <input type="text" value="" id="zip" />
                </div>         
            	<div class="fl" style="margin-right:10px;">
                	<strong><?php echo language('store_locations','LABEL_DISTANCE'); ?></strong><br />
                    <select id="radius">
                    	<option value="1">1 km</option>
                        <option value="5">5 km</option>
                        <option value="10">10 km</option>
                        <option value="25">25 km</option>
                        <option value="50">50 km</option>
                        <option value="100" selected="selected">100 km</option>
                        <option value="500">500 km</option>
                        <option value="1000">1000 km</option>
                    </select>
                </div>  
                <div class="fl" style="margin-top:20px;">
                      <input type="button" class="regular button" value="<?php echo language('store_locations','LABEL_BTN_SEARCH'); ?>" onclick="javascript:searchLocations();" />                                               
				</div>                    
                <div style="clear:both;"></div>
            </form>
            </div>
            
            <div id="map_container" style="width: 100%; height: 400px;"><div id="map" style="width: 100%; height: 400px;"></div></div>
            <div id="map_locations" style="width:100%; margin-top:10px;"></div>               
        </div>
        <div class="col-sm-3 sidebar sidebar-right">
        	<!-- START block newsletter -->
			<?php if ($config_site['show_newsletter_form']) { ?>		
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
	      	<?php }?>
	      	<!-- END block newsletter -->
	      	
	      	<div class="block block-pub">
	      	<?php 
	        	$page_home = true;
	        	include("_includes/template/pub.php");
	      	?>
	      	</div>
        </div>
    </div>
    </div>    
</div>
<?php include(dirname(__FILE__) . "/_includes/template/bottom.php");?>
</body>
</html>