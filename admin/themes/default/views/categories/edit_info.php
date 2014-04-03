<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

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

'.$containerObj.'.layout.toolbar = new Object();
'.$containerObj.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.toolbar.obj;
	
	obj.clearAll();	
	obj.detachAllEvents();
	obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
	
	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "save":	
				// get id_parent checked
				var id_parent=0;
				var checked = '.$containerObj.'.layout.B.tree.obj.getAllChecked();
				checked = checked.split(",");
				
				if (checked) {
					id_parent = checked[0];	
				}
				
				$.ajax({
					url: "'.CController::createUrl('save',array('container'=>$containerObj)).'",
					type: "POST",
					data: $("#'.$containerLayout.'").serialize()+"&id_parent="+id_parent,
					dataType: "json",
					beforeSend: function(){			
						// clear all errors					
						$("#'.$containerLayout.' span.error").html("");
						$("#'.$containerLayout.' *").removeClass("error");
							
						'.$containerObj.'.highlight_tab_errors('.$containerObj.'.layout.A.tabs.obj);			
					
						obj.disableItem(id);			
					},
					complete: function(jqXHR, textStatus){
						'.$containerObj.'.highlight_tab_errors('.$containerObj.'.layout.A.tabs.obj);
						
						if (typeof obj.enableItem == "function") obj.enableItem(id);	
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
								var id_tag_container = "'.$containerObj.'_category_description['.Yii::app()->language.'][name]";
								var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							
								// if name changed, rename
								if (tabs.obj.getLabel("'.$container.'") != $(id_tag_selector).val()) {
									tabs.obj.setLabel("'.$container.'",$(id_tag_selector).val());	
								}
													
								load_grid(tabs.list.treegrid.obj);
								
								if (!'.$containerJS.'.id_category) {								
									'.$containerJS.'.id_category=data.id;
									'.$containerJS.'.layout.toolbar.load(data.id);	
									'.$containerJS.'.layout.A.dataview.enableItems(1);															
									$("#'.$containerObj.'_id").val(data.id);
									
									// when we create a new product and save, store its id and container value in array of opened tabs
									tabs.totalOpened[data.id] = "'.$container.'";									
								}
								
								alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
								
								'.$containerObj.'.og_form = [];
								'.$containerObj.'.load_og_form();
							}
						} else {
							alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
						}
					}
				});	
				break;
			case "delete":
				if (confirm("'.Yii::t('views/categories/edit','LABEL_ALERT_DELETE').'")) {
					obj.disableItem(id);
					
					$.ajax({
						url: "'.CController::createUrl('delete').'",
						type: "POST",
						data: { "ids[]":current_id },
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);
						},						
						success: function(data){						
							tabs.close_tab(tabs.obj, "'.$container.'", true);
							load_grid(tabs.list.treegrid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});					
				}
				break;	
		}
	});	
};

'.$containerObj.'.layout.toolbar.obj = '.$containerObj.'.layout.obj.attachToolbar();
'.$containerObj.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.toolbar.load('.$id.');

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.A.tabs = new Object();
'.$containerObj.'.layout.A.tabs.obj = '.$containerObj.'.layout.A.obj.attachTabbar();
'.$containerObj.'.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.tabs.obj.clearAll();
'.$containerObj.'.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_category_description',array('container'=>$containerObj,'id'=>$id)).'", function(){
	load_ckeditor("'.$containerLayout.'");	

	// listen to creation event of any CKEditor
	$( "#'.$containerLayout.' .editor" ).off("instanceReady.ckeditor");
	$( "#'.$containerLayout.' .editor" ).on( "instanceReady.ckeditor", function( editor ){
		'.$containerObj.'.load_og_form();
	});
});

'.$containerObj.'.layout.B = new Object();
'.$containerObj.'.layout.B.obj = '.$containerObj.'.layout.obj.cells("b");
'.$containerObj.'.layout.B.obj.hideHeader();

$.ajax({
	url: "'.CController::createUrl('edit_info_options',array('container'=>$containerObj,'id'=>$id)).'",
	type: "POST",
	beforeSend: function(){
		'.$containerJS.'.layout.A.dataview.ajaxRequests++;
	},	
	success: function(data){
		'.$containerJS.'.layout.A.dataview.ajaxComplete();
		'.$containerObj.'.layout.B.obj.attachHTMLString(data);		
		
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
	
	// tree
	var id_parent=0;
	if ('.$containerObj.'.layout.B.tree && '.$containerObj.'.layout.B.tree.obj) {
		var checked = '.$containerObj.'.layout.B.tree.obj.getAllChecked();	
	
		if (checked) {
			checked = checked.split(",");
			id_parent = checked[0];	
		}		
	}
	
	'.$containerObj.'.og_form["id_parent"] = id_parent;
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
	
	// tree
	var id_parent=0;
	if ('.$containerObj.'.layout.B.tree && '.$containerObj.'.layout.B.tree.obj) {
		var checked = '.$containerObj.'.layout.B.tree.obj.getAllChecked();	
	
		if (checked) {
			checked = checked.split(",");
			id_parent = checked[0];	
		}		
	}
	
	str_array["id_parent"] = id_parent;
	
	return (count(array_diff_assoc('.$containerObj.'.og_form,str_array)) || count(array_diff_assoc(str_array,'.$containerObj.'.og_form)) ? 1:0);
};

';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>