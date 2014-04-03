<?php 
function domain($domainb) 
{ 
$bits = explode('/', $domainb); 
if ($bits[0]=='http:' || $bits[0]=='https:') 
{ 
$domainb= $bits[2]; 
} else { 
$domainb= $bits[0]; 
} 
unset($bits); 
$bits = explode('.', $domainb); 
$idz=count($bits); 
$idz-=3; 
if (strlen($bits[($idz+2)])==2) { 
$url=$bits[$idz].'.'.$bits[($idz+1)].'.'.$bits[($idz+2)]; 
} else if (strlen($bits[($idz+2)])==0) { 
$url=$bits[($idz)].'.'.$bits[($idz+1)]; 
} else { 
$url=$bits[($idz+1)].'.'.$bits[($idz+2)]; 
} 
return $url; 
} 

if (preg_match('/UA-([0-9]+)-([0-9]+)/',$config_site['google_analytics'])) { ?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo $config_site['google_analytics']; ?>']);
_gaq.push(['_setDomainName', '<?php echo domain($_SERVER['HTTP_HOST']); ?>']);
_gaq.push(["_setCustomVar",1,"LANGUAGE","<?php echo $_SESSION['customer']['language']; ?>", 2]);
<?php echo (isset($google_analytics_content) && $google_analytics_content) ? $google_analytics_content:''; ?>
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
<?php } ?>