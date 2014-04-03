<?php
$containerJS = 'Tab'.$container;
$containerLayout = $containerJS.'_layout';
$language = Yii::app()->language;
$arr_language = Tbl_Language::model()->active()->findAll();	

$script = '
var '.$containerJS.' = new Object();
// variables used to check modification
'.$containerJS.'.og_form = [];

'.$containerJS.'.id_cmspage = '.($model->id ? $model->id:0).';

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "2U");

'.$containerJS.'.layout.toolbar = new Object();

'.$containerJS.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
	obj.clearAll();	
	obj.detachAllEvents();
	obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
	obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif");   
	
	if (current_id && '.$protected.'==0) {
		obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
	}
	obj.addSeparator("sep01", null);
	obj.addButton("preview",null,"'.Yii::t('global','LABEL_PREVIEW').'","toolbar/preview.png","toolbar/preview_dis.png"); 
	

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "save":	
				'.$containerJS.'.layout.toolbar.save(id);
				break;
			case "save_close":
				'.$containerJS.'.layout.toolbar.save(id,true);
				break;
			case "delete":
				if (confirm("'.Yii::t('views/cmspages/edit','LABEL_ALERT_DELETE').'")) {
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
							'.$containerJS.'.og_form = [];
							'.$containerJS.'.load_og_form();
											
							tabs.close_tab(tabs.obj, "'.$container.'", true);
							load_grid(tabs.list.treegrid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});					
				}
				break;	
			case "preview":
				//window.open("'.CController::createUrl('preview').'/"+'.$containerJS.'.id_cmspage);
				window.open("'.CController::createUrl('preview').'?id="+'.$containerJS.'.id_cmspage+"&language="+'.$containerJS.'.layout.A.tabs.obj.getActiveTab());
				break;
				
				
		}
	});	
};

'.$containerJS.'.layout.toolbar.save = function(id,close){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
	// get id_parent checked
	var id_parent=0;
	
	var checked = '.$containerJS.'.layout.B.tree.obj.getAllChecked();
	checked = checked.split(",");
	
	if (checked) {
		id_parent = checked[0];	
	}

	
	$.ajax({
		url: "'.CController::createUrl('save',array('container'=>$containerJS)).'",
		type: "POST",
		data: $("#'.$containerLayout.'").serialize()+"&id_parent="+id_parent,
		dataType: "json",
		beforeSend: function(){			
			// clear all errors					
			$("#'.$containerLayout.' span.error").html("");
			$("#'.$containerLayout.' *").removeClass("error");
				
			'.$containerJS.'.highlight_tab_errors('.$containerJS.'.layout.A.tabs.obj);			
		
			obj.disableItem(id);			
		},
		complete: function(jqXHR, textStatus){
			'.$containerJS.'.highlight_tab_errors('.$containerJS.'.layout.A.tabs.obj);
			
			if (typeof obj.enableItem == "function") obj.enableItem(id);
			
			if (close && !jQuery.parseJSON(jqXHR.responseText).errors) tabs.close_tab(tabs.obj, "'.$container.'", true);	
		},
		success: function(data){						
			// Remove class error to the background of the main div
			$(".div_'.$containerJS.'").removeClass("error_background");
			if (data) {
				if (data.errors) {
					$.each(data.errors, function(key, value){
						var id_tag_container = "'.$containerJS.'_"+key;
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
					$(".div_'.$containerJS.'").addClass("error_background");																															
				} else {					
					var id_tag_container = "'.$containerJS.'_cmspage_description['.Yii::app()->language.'][name]";
					var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
				
					// if name changed, rename
					if (tabs.obj.getLabel("'.$container.'") != $(id_tag_selector).val()) {
						tabs.obj.setLabel("'.$container.'",$(id_tag_selector).val());	
					}
										
					load_grid(tabs.list.treegrid.obj);
					
					if (!'.$containerJS.'.id_cmspage) {								
						'.$containerJS.'.id_cmspage=data.id;
						'.$containerJS.'.layout.toolbar.load('.$containerJS.'.id_cmspage);	
						$("#'.$containerJS.'_id").val(data.id);																
						
						// when we create a new product and save, store its id and container value in array of opened tabs
						tabs.totalOpened[data.id] = "'.$container.'";									
					}
					
					// If external link, disabled button Preview
					var name_tag_container = "'.$model_name.'[cmspage_description]["+'.$containerJS.'.layout.A.tabs.obj.getActiveTab()+"][external_link]";
					var name_tag_selector = name_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
					//alert($("input[name="+name_tag_selector+"]:checked").val());
				
					if ((($("input[name="+name_tag_selector+"]:checked").val()==1) && '.$containerJS.'.id_cmspage>0) || '.$containerJS.'.id_cmspage==0) {
						'.$containerJS.'.layout.toolbar.obj.disableItem("preview");
					}else{
						'.$containerJS.'.layout.toolbar.obj.enableItem("preview");
					}
					
					alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
					
					'.$containerJS.'.og_form = [];
					'.$containerJS.'.load_og_form();
				}
			} else {
				alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
			}
		}
	});					
};

'.$containerJS.'.layout.toolbar.obj = '.$containerJS.'.layout.obj.attachToolbar();
'.$containerJS.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.toolbar.load('.$model->id.');

'.$containerJS.'.layout.A = new Object();
'.$containerJS.'.layout.A.obj = '.$containerJS.'.layout.obj.cells("a");
'.$containerJS.'.layout.A.obj.setWidth(650);
'.$containerJS.'.layout.A.obj.hideHeader();

'.$containerJS.'.layout.A.tabs = new Object();
'.$containerJS.'.layout.A.tabs.obj = '.$containerJS.'.layout.A.obj.attachTabbar();
'.$containerJS.'.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
'.$containerJS.'.layout.A.tabs.obj.clearAll();
'.$containerJS.'.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_cmspage_description',array('container'=>$containerJS,'id'=>$model->id)).'", function(){
	
	load_ckeditor("'.$containerLayout.'");	
	
	// listen to creation event of any CKEditor
	$( "#'.$containerLayout.' .editor" ).off("instanceReady.ckeditor");
	$( "#'.$containerLayout.' .editor" ).on( "instanceReady.ckeditor", function( editor ){
		'.$containerJS.'.load_og_form();
	});

});


'.$containerJS.'.layout.A.tabs.obj.attachEvent("onSelect", function(id,last_id){
	
	var name_tag_container = "'.$model_name.'[cmspage_description]["+id+"][external_link]";
	var name_tag_selector = name_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
	//alert($("input[name="+name_tag_selector+"]:checked").val());

	if ((($("input[name="+name_tag_selector+"]:checked").val()==1) && '.$containerJS.'.id_cmspage>0) || '.$containerJS.'.id_cmspage==0) {
		'.$containerJS.'.layout.toolbar.obj.disableItem("preview");
	}else{
		'.$containerJS.'.layout.toolbar.obj.enableItem("preview");
	}
	
	return true;
});


'.$containerJS.'.layout.B = new Object();
'.$containerJS.'.layout.B.obj = '.$containerJS.'.layout.obj.cells("b");
'.$containerJS.'.layout.B.obj.fixSize(false, true);
'.$containerJS.'.layout.B.obj.hideHeader();

$.ajax({
	url: "'.CController::createUrl('edit_options',array('container'=>$containerJS,'id'=>$model->id)).'",
	type: "POST",
	success: function(data){
		'.$containerJS.'.layout.B.obj.attachHTMLString(data);
		'.$containerJS.'.load_og_form();		
	}
});

// clear event for this tab and reset
$(window).off("resize.'.$containerJS.'");
$(window).on("resize.'.$containerJS.'",function(){
	setTimeout("'.$containerJS.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.highlight_tab_errors = function(tabObj,cssStyle){
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

// load original form values
'.$containerJS.'.load_og_form = function()
{
	// tabbar
	$("#'.$containerLayout.' .dhx_tabbar_zone_top [name]").each(function(){		
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) '.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			'.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		}				
	});	
	
	// layout b
	$("[name]",'.$containerJS.'.layout.B.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) '.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			'.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		}	
	});
};

// check if any modifications has been made
'.$containerJS.'.has_modifications = function()
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
	$("[name]",'.$containerJS.'.layout.B.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) str_array[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			str_array[$(this).attr("name")] = $(this).val();	
		}	
	});
	
	return (count(array_diff_assoc('.$containerJS.'.og_form,str_array)) || count(array_diff_assoc(str_array,'.$containerJS.'.og_form)) ? 1:0);
};
';

echo Html::script($script);
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>