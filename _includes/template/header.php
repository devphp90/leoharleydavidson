<?php 
if (strstr($_SERVER['HTTP_USER_AGENT'],'compatible; MSIE 7.0;')) echo '<meta http-equiv="X-UA-Compatible" content="IE=8" >';
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

<!--
<link type="text/css" rel="stylesheet" href="/_css/style.php" /> 
 

<link type="text/css" rel="stylesheet" href="/_includes/js/jquery/superfish-1.4.8/css/superfish.css" />
<link type="text/css" rel="stylesheet" href="/_includes/classes/zebra_pagination/zebra_pagination.css" />
 -->

<!-- New CSS -->
<link type="text/css" rel="stylesheet" href="/_css/style.css" />
<link type="text/css" rel="stylesheet" media="screen and (max-width:1024px)" href="/_css/ipad.css" />
<link type="text/css" rel="stylesheet" media="screen and (max-width:640px)" href="/_css/mobile.css" />

<link type="text/css" rel="stylesheet" href="/_css/print.css" media="print" /> 
<link type="text/css" rel="stylesheet" href="/_css/bootstrap.min.css" />
<link type="text/css" rel="stylesheet" href="/_css/bootstrap-theme.min.css" />
<link type="text/css" rel="stylesheet" href="//fonts.googleapis.com/css?family=Oswald:300,400,400italic,500,600,700,700italic|Gudea:300,400,400italic,500,600,700,700italic|PT Sans:300,400,400italic,500,600,700,700italic|&subset=latin,greek-ext,cyrillic,latin-ext,greek,cyrillic-ext,vietnamese">
<link type="text/css" rel="stylesheet" href="/_css/style.php" />
<link type="text/css" rel="stylesheet" href="/_includes/js/jquery/jquery-ui/css/jquery-ui.css">
<link type="text/css" rel="stylesheet" href="/includes/js/jquery/timepicker/styles.css" />
<!-- END New CSS -->

<!-- 
<script type="text/javascript" src="/includes/js/jquery/jquery.easing-1.3.min.js"></script>
<script type="text/javascript" src="/includes/js/jquery/jquery.scrollTo-1.4.2-min.js"></script>
<script type="text/javascript" src="/includes/js/jquery/superfish-1.4.8/js/hoverIntent.js"></script>
<script type="text/javascript" src="/includes/js/jquery/superfish-1.4.8/js/superfish.js"></script>
<script type="text/javascript" src="/includes/js/jquery/superfish-1.4.8/js/supersubs.js"></script>

-->


 
<!-- NEW JS -->
<script type="text/javascript">
	var LABEL_FIELD_REQUIRED = "<?php echo language("global","LABEL_FIELD_REQUIRED");?>";
	var LABEL_RATING_REQUIRED = "<?php echo language("global","LABEL_RATING_REQUIRED");?>";
	var LABEL_LOADING_TEXT = "<?php echo language("global","LABEL_LOADING_TEXT");?>";
</script>
<script type="text/javascript" src="/_includes/js/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="/_includes/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/includes/js/jquery/jquery.easing-1.3.min.js"></script>
<script type="text/javascript" src="/_includes/js/function.js"></script>
<script type="text/javascript" src="/_includes/js/jqueryslidemenu.js"></script>

<?php if(!(isset($is_product_page) && $is_product_page)) {?>
<script type="text/javascript">
	jQuery.noConflict();
</script>
<script type="text/javascript" src="/_includes/js/prototype-misc.js"></script>
<script type="text/javascript" src="/_includes/js/jquery/jquery-ui/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="/_includes/js/jquery/timepicker/jquery-ui-timepicker-addon.js"></script>
<?php echo $_SESSION['customer']['language']!='en'?'<script type="text/javascript" src="/_includes/js/jquery/i18n/jquery.ui.datepicker-'.$_SESSION['customer']['language'].'.js"></script>':'';?>

<?php }?>

<script type="text/javascript">
  var VENEDOR_RESPONSIVE = true;
  var SPAN_CSS = 'col-sm-';
  var VENEDOR_HEADER_FIXED = 1;
  var VENEDOR_HEADER_FIXED_LOGO = 0;
  var VENEDOR_HEADER_OFFSET = 0;
  var VENEDOR_MENU_PADDING = 15;
</script>
<script type="text/javascript">
  // Register to the newsletter
  function register_newsletter(from) {
	var from = typeof from !== 'undefined' ? from : '';
	var datasend = jQuery("#newsletter-validate-detail").serialize();
	if(from == 'index') var datasend = jQuery("#frm-inscription").serialize();
  	jQuery.ajax({
  		url: "/_includes/ajax/newsletter.php",
  		type: "POST",
  		data: datasend,
  		dataType: "json",
  		complete: function(){
  
  		},								
  		success: function(data){
  	  		if(from == 'index') {
  	  	  		alert(data.message);
  	  		} else {
    	  		var cls = (data.error=="true")?'danger':'success';
    			var msgHtml = '<div class="messages"><div class="alert alert-'+cls+'"><button type="button" class="close" data-dismiss="alert">×</button><ul><li><span>'+data.message+'</span></li></ul></div></div> ';
    			/*if(data.error=="true"){
    				jQuery("#newsletter_email_text").removeClass('success');
    				jQuery("#newsletter_email_text").addClass('error');
    			}else{
    				jQuery("#newsletter_email_text").removeClass('error');
    				jQuery("#newsletter_email_text").addClass('success');
    			}*/
    			//jQuery("#newsletter_email_text").html(msgHtml).show();
    			jQuery("#nl_email_text_container").html(msgHtml).show();
  	  		}
  			jQuery("#newsletter_email").val('');			
  		},
  		error:function (xhr, ajaxOptions, thrownError){
  		  alert(xhr.status);
  		}
  	});	
  	
  	return false;
  }
  function clear_form(form){
	  jQuery(':input','#' + form)
	  .not(':button, :submit, :reset, :hidden, :radio, :checkbox')
	  .val('');
	  jQuery(':input','#' + form)
	  .removeAttr('checked')
	  .removeAttr('selected');
	  }   
</script>   
<style>
#nl_email_text_container {background:none; border:none;}
</style>  
<!-- END NEW JS -->

