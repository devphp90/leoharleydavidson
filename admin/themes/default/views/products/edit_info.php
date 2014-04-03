<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';
$include_path = Yii::app()->params['includes_js_path'];	

$script = '

'.$containerObj.' = new Object();
// variables used to check modification
'.$containerObj.'.og_form = [];

'.$containerObj.'.highlight_tab_errors = function(tabObj,cssStyle){
	var errors=0;
	
	if (!cssStyle) { cssStyle = "color:#FF0000;"; }

	$.each(tabObj._tabs, function(key, value) {									
		if ($("*",$("#'.$containerLayout.' [tab_id=\'"+key+"\']")).hasClass("error")) {
			tabObj.setCustomStyle(key,null,null,cssStyle);
			
			errors++;
		} else {
			tabObj.setCustomStyle(key,null,null,null);
		}
	});
	
	if (errors) {
		tabs.obj.setCustomStyle("'.$container.'",null,null,cssStyle);
	} else {
		tabs.obj.setCustomStyle("'.$container.'",null,null,null);
	}
};

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "2U");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.B = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.B.obj = '.$containerObj.'.layout.obj.cells("b");

'.$containerObj.'.layout.A.obj.hideHeader();
'.$containerObj.'.layout.B.obj.hideHeader();

'.$containerObj.'.layout.toolbar = new Object();

'.$containerObj.'.layout.toolbar.load = function(current_id){
	'.$containerObj.'.layout.toolbar.obj.clearAll();	
	'.$containerObj.'.layout.toolbar.obj.detachAllEvents();
	'.$containerObj.'.layout.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  

	'.$containerObj.'.layout.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "save":
				// We have to remove focus from dhtmlx dropdown else the dropdown doesnt save
				$("#save").focus();	
				$.ajax({
					url: "'.CController::createUrl('save_info',array('container'=>$containerObj)).'",
					type: "POST",
					data: $("#'.$containerLayout.'").serialize()+"&pass=0",
					dataType: "json",
					beforeSend: function(){
						// clear all errors					
						$("#'.$containerLayout.' span.error").html("");
						$("#'.$containerLayout.' *").removeClass("error");
							
						'.$containerObj.'.highlight_tab_errors('.$containerObj.'.layout.A.tabbar.obj);			
					
						obj.disableItem(id);			
					},
					complete: function(){
						if (typeof obj.enableItem == "function") obj.enableItem(id);
						
						'.$containerObj.'.highlight_tab_errors('.$containerObj.'.layout.A.tabbar.obj);
					},
					success: function(data){						
						if(data.in_other_product==1 && confirm("'.Yii::t('views/products/edit','LABEL_ALERT_ONE_PRODUCT_DELETE_COMBO_BUNDLED').'") || data.in_other_product!=1) {
							
							// Remove class error to the background of the main div
							$(".div_'.$containerObj.'").removeClass("error_background");
							
							$.ajax({
								url: "'.CController::createUrl('save_info',array('container'=>$containerObj)).'",
								type: "POST",
								data: $("#'.$containerLayout.'").serialize()+"&pass=1",
								dataType: "json",
								beforeSend: function(){
									// clear all errors					
									$("#'.$containerLayout.' span.error").html("");
									$("#'.$containerLayout.' *").removeClass("error");
										
									'.$containerObj.'.highlight_tab_errors('.$containerObj.'.layout.A.tabbar.obj);			
								
									obj.disableItem(id);			
								},
								complete: function(){
									if (typeof obj.enableItem == "function") obj.enableItem(id);
									
									'.$containerObj.'.highlight_tab_errors('.$containerObj.'.layout.A.tabbar.obj);
								},
								success: function(data){						
									// Remove class error to the background of the main div
									$(".div_'.$containerObj.'").removeClass("error_background");
									if (data) {
										if (data.errors) {
											$.each(data.errors, function(key, value){
												var id_tag_container = "'.$containerObj.'_"+key;
												var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
												if (!$(id_tag_selector).hasClass("error")) { 
													$(id_tag_selector).addClass("error");
													
													if (value) {		
														value = String(value);
														var id_errormsg_container = id_tag_container+"_errorMsg";
														var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
														
														
														if (!$(id_errormsg_selector).hasClass("error")) { 
															$(id_errormsg_selector).addClass("error");
														}
														
														if ($(id_errormsg_selector).length) { 
															$(id_errormsg_selector).html(value); 
														}
													}						
												}
											});	
											// Apply class error to the background of the main div
											$(".div_'.$containerObj.'").addClass("error_background");
																																								
										} else {					
											var id_tag_container = "'.$containerObj.'_product_description['.Yii::app()->language.'][name]";
											var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
										
											// if name changed, rename
											if (tabs.obj.getLabel("'.$container.'") != $(id_tag_selector).val()) {
												tabs.obj.setLabel("'.$container.'",$(id_tag_selector).val());	
											}
																
											load_grid(tabs.list.grid.obj);		
											
											if (!'.$containerJS.'.id_product) {								
												'.$containerJS.'.id_product=data.id;
												'.$containerJS.'.layout.toolbar.load(data.id);																			
												$("#'.$containerObj.'_id").val(data.id);
												//'.$containerJS.'.layout.A.dataview.reloadData();
												'.$containerJS.'.layout.A.dataview.enableItems(1);
												
												// when we create a new product and save, store its id and container value in array of opened tabs
												tabs.totalOpened[data.id] = "'.$container.'";									
											}
											
											'.$containerJS.'.product_type=data.product_type;		
											
											alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
											
											'.$containerObj.'.og_form = [];
											'.$containerObj.'.load_og_form();
											
											
											if (data.product_type == 0) {
												'.$containerJS.'.layout.A.dataview.load();
											}												
										}
									} else {
										alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
									}
								}
							});	
						}
					}
				});								
				break;
		}
	});	
};

'.$containerObj.'.layout.toolbar.obj = '.$containerObj.'.layout.obj.attachToolbar();
'.$containerObj.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.toolbar.load('.$id.');

'.$containerObj.'.layout.A.tabbar = new Object();
'.$containerObj.'.layout.A.tabbar.obj = '.$containerObj.'.layout.A.obj.attachTabbar();
'.$containerObj.'.layout.A.tabbar.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.tabbar.obj.clearAll();
'.$containerObj.'.layout.A.tabbar.obj.loadXML("'.CController::createUrl('xml_list_info_description',array('container'=>$containerObj,'id'=>$id)).'", function(){
	load_ckeditor("'.$containerLayout.'");	

	// listen to creation event of any CKEditor
	$( "#'.$containerLayout.' .editor" ).off("instanceReady.ckeditor");
	$( "#'.$containerLayout.' .editor" ).on( "instanceReady.ckeditor", function( editor ){
		'.$containerObj.'.load_og_form();
	});
});




'.$containerObj.'.layout.A.tabbar.obj.attachEvent("onSelect", function(id,last_id){

	if ('.$containerJS.'.id_product==0) {
		'.$containerJS.'.layout.toolbar.obj.disableItem("preview");
	}else{
		'.$containerJS.'.layout.toolbar.obj.enableItem("preview");
	}
	
	return true;
});










$.ajax({
	url: "'.CController::createUrl('edit_info_options',array('container'=>$containerObj,'id'=>$id,'product_type'=>$product_type)).'",
	type: "POST",
	beforeSend: function(){
		'.$containerJS.'.layout.A.dataview.ajaxRequests++;
	},	
	success: function(data){
		'.$containerJS.'.layout.A.dataview.ajaxComplete();
		'.$containerObj.'.layout.B.obj.attachHTMLString(data);
		$("#'.$containerObj.'_active_1").focus();	
		'.$containerObj.'.load_og_form();	
	}
});

// clear event for this tab and reset
$(window).off("resize.'.$containerObj.'");
$(window).on("resize.'.$containerObj.'",function(){
	setTimeout("'.$containerObj.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();

// load original form values
'.$containerObj.'.load_og_form = function()
{
	// tabbar
	$("#'.$containerLayout.' .dhx_tabbar_zone_top [name]").each(function(){		
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) '.$containerObj.'.og_form[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			'.$containerObj.'.og_form[$(this).attr("name")] = $(this).val();	
		}				
	});
	
	// layout b
	$("[name]",'.$containerObj.'.layout.B.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) '.$containerObj.'.og_form[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			'.$containerObj.'.og_form[$(this).attr("name")] = $(this).val();	
		}	
	});
};

// check if any modifications has been made
'.$containerObj.'.has_modifications = function()
{
	// check for modifications
	var str_array=[];
	
	// tabbar
	$("#'.$containerLayout.' .dhx_tabbar_zone_top [name]").each(function(){		
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) str_array[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			str_array[$(this).attr("name")] = $(this).val();	
		}				
	});
	
	// layout b
	$("[name]",'.$containerObj.'.layout.B.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) str_array[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			str_array[$(this).attr("name")] = $(this).val();	
		}	
	});
	
	return (count(array_diff_assoc('.$containerObj.'.og_form,str_array)) || count(array_diff_assoc(str_array,'.$containerObj.'.og_form)) ? 1:0);
};

';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>