<?php
// register css/script files and scripts
Html::include_dhtmlx();
Html::include_dhtmlx_custom_filters();

$help_hint_path = '/';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>
<?php if (!Yii::app()->user->isGuest) { ?>
<div id="template_search">
    <div style="float:left;">
	    <div id="page_title" style="float:left; margin-right:10px;"></div>
        <div id="how-to-link" style="float:left;"><a href="javascript:void(0);" title=""><?php echo CHtml::image(Html::imageUrl('help.png'),'',array('border'=>0,'title'=>Yii::t('global', 'LABEL_HINT_HELP'),'id'=>"page_title_hint",'style'=>'margin-top:8px;')); ?></a></div>
		<div style="clear:both;"></div>
    </div>

	<div style="float:right">
    <table border="0" cellpadding="0" cellspacing="0">
    	<tr>
        	<?php if (sizeof($linked_stores = Html::generateLinkedStoreList())) { ?>
            <td width="225">
            	<strong><?php echo Yii::t('views/site/login_form', 'LABEL_STORE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'store'); ?><br />
                <select id="select_store">
                <option value=""><?php echo $_SERVER['HTTP_HOST']; ?></option>
                <?php
				foreach ($linked_stores as $k => $store) {
					echo '<option value="'.$k.'">'.$store.'</option>';	
				}
				?>
                </select>
                <script type="text/javascript">
				$(function(){
					$("#select_store").on("change",function(){
						var url = $(this).val();
						
						if (url.length) {
							window.location.replace(url);
						}						
					});
				});
				</script>
            </td>
            <?php } ?>
        	<td width="225">
                <strong><?php echo Yii::t('views/layouts/main', 'LABEL_SEARCH_INVOICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'search-invoice'); ?><br />
                <div id="template_search_invoice"></div>
			</td> 
        	<td width="225">
                <strong><?php echo Yii::t('views/layouts/main', 'LABEL_SEARCH_CUSTOMER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'search-customer'); ?><br />
                <div id="template_search_customer"></div>
			</td>             
		</tr>
	</table>
    </div>
    <div style="clear:both"></div>                        
</div>

<?php } ?>
<?php echo $content; ?>
<?php if (!Yii::app()->user->isGuest) { 

$app = Yii::app();

?>
<script type="text/javascript">
<!--
var templateLayout, 
templateMenu, 
templateLayout_A, 
templateLayout_B, 
templateHeader,
templateContent;

// set images path for dhtmlxcombo, we also use this global variable for set path
window.dhx_globalImgPath="<?php echo Yii::app()->params['dhtmlx_path']; ?>imgs/";
// css for row hover
window.dhx_rowhover='rowhover';
window.dhx_rowhover_pointer='rowhover_pointer';
window.dhx_skin='<?php echo Yii::app()->params['dhtmlx_skin']; ?>'; // dhx_skyblue dhx_terrace dhx_web
// template attached to body for fullscreen
templateLayout = new dhtmlXLayoutObject(document.body, "2E");
templateLayout_A = templateLayout.cells("a");
templateLayout_B = templateLayout.cells("b");

// template menu main navigation attached to top 
templateMenu = templateLayout.attachMenu();
templateMenu.setTopText('<a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>" target="_blank" style="text-decoration:none;"><span style="color:#0966ff; font-weight: bold;"><?php echo(empty($app->params['site_name'])?Yii::t('views/layouts/main', 'LABEL_YOUR_SITE'):addslashes($app->params['site_name']));?></span></a>&nbsp;&nbsp;<strong><?php echo file_get_contents(getcwd().'/version-sc') . (file_exists($_SERVER['DOCUMENT_ROOT'].'/exclude-file.txt')?'-m':'');?></strong>');
// set template menu image path
templateMenu.setIconsPath(dhx_globalImgPath);	
// load menu
templateMenu.loadXML("<?php echo CController::createUrl('template_menu'); ?>");
// hide header  
templateLayout_A.hideHeader();
// set max height
templateLayout_A.setHeight(60);
// disable resizing horz, vert
templateLayout_A.fixSize(false,true);
// hide header
templateLayout_B.hideHeader();

// template header under menu
templateHeader = templateLayout_A.attachObject("template_search");

// convert div to combobox
var templateHeader_invoice_search=new dhtmlXCombo("template_search_invoice", "invoice", 200);
// enable autocomplete mode
templateHeader_invoice_search.enableFilteringMode(true, "<?php echo CController::createUrl('site/xml_list_invoice'); ?>", false);
templateHeader_invoice_search.attachEvent("onChange", function(){
	if(templateHeader_invoice_search.getActualValue() != ""){
		$.ajax({
			url: "<?php echo CController::createUrl('site/search_orders_validate'); ?>",
			data: { "id":templateHeader_invoice_search.getSelectedValue() },
			type: "POST",
			success: function(data){											
				if (!data) alert("<?php echo Yii::t('views/layouts/main', 'LABEL_ALERT_SEARCH_INVOICE');?>");
				else goto_url("<?php echo CController::createAbsoluteUrl('orders/');?>?id_orders="+templateHeader_invoice_search.getSelectedValue());
			}
		});
	}
});

// convert div to combobox
var templateHeader_customer_search=new dhtmlXCombo("template_search_customer", "customer", 200)
// enable autocomplete mode
templateHeader_customer_search.enableFilteringMode(true, "<?php echo CController::createUrl('site/xml_list_customer'); ?>", false);
templateHeader_customer_search.attachEvent("onChange", function(){
	if(templateHeader_customer_search.getActualValue() != ""){
		$.ajax({
			url: "<?php echo CController::createUrl('site/search_customers_validate'); ?>",
			data: { "id":templateHeader_customer_search.getSelectedValue() },
			type: "POST",
			success: function(data){											
				if (!data){  
					alert("<?php echo Yii::t('views/layouts/main', 'LABEL_ALERT_SEARCH_CUSTOMER');?>");
				} else { 
					var uri_encode_name=encodeURIComponent(templateHeader_customer_search.getSelectedText());
		goto_url("<?php echo CController::createAbsoluteUrl('customers/');?>?id_customer="+templateHeader_customer_search.getSelectedValue()+"&name_customer="+uri_encode_name);
				}
			}
		});
	}	
});

$(function(){
	$("#how-to-link a, #help_menu_id a").on("click",function(){		
			open_help_docs($(this).prop("title"));
	 });	
});

function open_help_docs(help_link){
	var dhxWins = new dhtmlXWindows();
	dhxWins.enableAutoViewport(true);
	dhxWins.setImagePath(dhx_globalImgPath);		
	
	var wins = new Object();
	wins.obj = dhxWins.createWindow("_loadTemplateWindow", 10, 10, 900, 620);
	
	wins.obj.setText((help_link!=0)?$("#page_title").html():'<?php echo Yii::t('components/controller', 'LABEL_MENU_HELP');?>');
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);	
	wins.obj.attachURL("http://www.simplecommerce.com/docs/help/how-to/<?php echo Yii::app()->language; ?>"+((help_link!=0)?help_link:''));	
}

// EVENTS
dhtmlxError.catchError("LoadXML", function(type, desc, erData){
	$.ajax({
		url: "<?php echo CController::createUrl('site/is_logged_in'); ?>",
		success: function(data){											
			if (data == "false") window.location.href = "<?php echo CController::createAbsoluteUrl('site/login'); ?>";
			else if (erData[0].responseText) alert(erData[0].responseText);
		}
	});			
});
//}
-->
</script>
<?php } ?>
</body>
</html>