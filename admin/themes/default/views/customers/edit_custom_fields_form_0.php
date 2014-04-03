<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();
// variables used to check modification
'.$containerObj.'.og_form = [];

'.$containerObj.'.highlight_tab_errors = function(cssStyle){
	var errors=0;
	
	if (!cssStyle) { cssStyle = "color:#FF0000;"; }

	if ($("#'.$containerLayout.' *").hasClass("error")) {
		errors=1;
	}
	
	if (errors) {
		tabs.obj.setCustomStyle("'.$container.'",null,null,cssStyle);
	} else {
		tabs.obj.setCustomStyle("'.$container.'",null,null,null);
	}
};

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.toolbar = new Object();

'.$containerObj.'.layout.toolbar.load = function(current_id){
	'.$containerObj.'.layout.toolbar.obj.clearAll();	
	'.$containerObj.'.layout.toolbar.obj.detachAllEvents();
	'.$containerObj.'.layout.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  

	'.$containerObj.'.layout.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "save":	
				$.ajax({
					url: "'.CController::createUrl('save_custom_fields_form_0',array('container'=>$containerObj, 'id'=>$id)).'",
					type: "POST",
					data: $("#'.$containerLayout.'").serialize(),
					dataType: "json",
					beforeSend: function(){			
						// clear all errors					
						$("#'.$containerLayout.' span.error").html("");
						$("#'.$containerLayout.' *").removeClass("error");
							
						'.$containerObj.'.highlight_tab_errors();			
					
						obj.disableItem(id);			
					},
					complete: function(){
						if (typeof obj.enableItem == "function") obj.enableItem(id);
						
						'.$containerObj.'.highlight_tab_errors();
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
		}
	});	
};

'.$containerObj.'.layout.toolbar.obj = '.$containerObj.'.layout.obj.attachToolbar();
'.$containerObj.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.toolbar.load('.$id.');

$.ajax({
	url: "'.CController::createUrl('edit_custom_fields_form_0_options',array('container'=>$containerObj,'id'=>$id)).'",
	type: "POST",
	beforeSend: function(){
		'.$containerJS.'.layout.A.dataview.ajaxRequests++;
	},	
	success: function(data){
		'.$containerJS.'.layout.A.dataview.ajaxComplete();
		'.$containerObj.'.layout.A.obj.attachHTMLString(data);
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
	// layout a
	$("[name]",'.$containerObj.'.layout.A.obj.vs.def.dhxcont).each(function(){
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
	
	// layout a
	$("[name]",'.$containerObj.'.layout.A.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) str_array[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			str_array[$(this).attr("name")] = $(this).val();	
		}	
	});
	
	return (count(array_diff_assoc('.$containerObj.'.og_form,str_array)) || count(array_diff_assoc(str_array,'.$containerObj.'.og_form)) ? 1:0);
};

$("#'.$containerLayout.'").on("change","select",function(){
	var id = $(this).prop("id");
	if ($(":selected",this).hasClass("add_extra")) $("input[id="+id+"_extra]").show();
	else $("input[id="+id+"_extra]").val("").hide();
	
});

$("#'.$containerLayout.'").on("click",":checkbox",function(){
	
	var id = $(this).prop("id");
	if ($(this).prop("checked")) $("input[id="+id+"_extra]").prop("disabled",false);
	else $("input[id="+id+"_extra]").val("").prop("disabled",true);
	
});

$("#'.$containerLayout.'").on("click",":radio",function(){
	var id = $(this).prop("id");
	var i = id.replace("'.$containerObj.'_custom_fields_radio_","");
	var extra = $("#'.$containerObj.'_custom_fields_radio_options_"+i);
	var ids = i.split("_");
	
	// disable extras
	$(\'input[id^="'.$containerObj.'_custom_fields_radio_options_"]\').prop("disabled",true);
	$(\'input[id^="'.$containerObj.'_custom_fields_radio_options_"]\').val("");

	if (extra.length) extra.prop("disabled",false);

});

	





';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>