<?php 
$script = '
edit = new Object();
edit.layout = new Object();
edit.layout.og_form = [];
edit.layout.obj = new dhtmlXLayoutObject("shipping_layout", "1C");

edit.layout.A = new Object();
edit.layout.A.obj = edit.layout.obj.cells("a");
edit.layout.A.obj.hideHeader();

$.ajax({
	url: "'.CController::createUrl('edit_shipping_options').'",
	type: "POST",
	beforeSend: function(){
		layout.A.dataview.ajaxRequests++;
	},	
	success: function(data){
		layout.A.dataview.ajaxComplete();
		edit.layout.A.obj.attachHTMLString(data);		
		edit.layout.load_og_form();
	}
});

edit.layout.A.toolbar = new Object();
edit.layout.A.toolbar.obj = edit.layout.A.obj.attachToolbar();
edit.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
edit.layout.A.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  

edit.layout.A.toolbar.obj.attachEvent("onClick",function(id){
	var obj = this;
	
	switch (id) {
		case "save":	
			$.ajax({
				url: "'.CController::createUrl('save').'",
				type: "POST",
				data: $("#shipping_layout").serialize(),
				dataType: "json",
				beforeSend: function(){			
					// clear all errors					
					$("#shipping_layout span.error").html("");
					$("#shipping_layout *").removeClass("error");		
				
					obj.disableItem(id);			
				},
				complete: function(){
					obj.enableItem(id);
				},
				success: function(data){						
					// Remove class error to the background of the main div
					$(".div_'.$containerObj.'").removeClass("error_background");
					if (data) {
						if (data.errors) {
							$.each(data.errors, function(key, value){
								var id_tag_container = key;
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
							alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
							
							edit.layout.og_form = [];
							edit.layout.load_og_form();
						}
					} else {
						alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
					}
				}
			});									
			break;
	}
});	



layout.A.dataview.ajaxComplete();

// load original form values
edit.layout.load_og_form = function()
{
	// layout
	$("[name]",edit.layout.A.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) edit.layout.og_form[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			edit.layout.og_form[$(this).attr("name")] = $(this).val();	
		}	
	});
};

// check if any modifications has been made
edit.layout.has_modifications = function()
{
	// check for modifications
	var str_array=[];

	// layout 
	$("[name]",edit.layout.A.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) str_array[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			str_array[$(this).attr("name")] = $(this).val();	
		}	
	});
	
	return (count(array_diff_assoc(edit.layout.og_form,str_array)) || count(array_diff_assoc(str_array,edit.layout.og_form)) ? 1:0);
};

';

echo Html::script($script); 
?>
<form id="shipping_layout" style="width:100%; height:100%; padding:0; margin:0;"></form>