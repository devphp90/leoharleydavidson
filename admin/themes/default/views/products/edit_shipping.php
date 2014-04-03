<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();
// variables used to check modification
'.$containerObj.'.og_form = [];

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.toolbar = new Object();

'.$containerObj.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.toolbar.obj;
	
	obj.clearAll();	
	obj.detachAllEvents();
	obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "save":	
				$.ajax({
					url: "'.CController::createUrl('save_shipping',array('container'=>$containerObj)).'",
					type: "POST",
					data: $("#'.$containerLayout.'").serialize(),
					dataType: "json",
					beforeSend: function(){			
						// clear all errors					
						$("#'.$containerLayout.' span.error").html("");
						$("#'.$containerLayout.' *").removeClass("error");	
					
						obj.disableItem(id);			
					},
					complete: function(){
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
	url: "'.CController::createUrl('edit_shipping_options',array('container'=>$containerObj,'containerLayout'=>$containerLayout,'id'=>$id)).'",
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
';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>