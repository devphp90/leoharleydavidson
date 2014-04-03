<?php
// register css/script files and scripts
Html::include_uploadify();
Html::include_timepicker();
Html::include_googlemaps_api();

// Client Script
$cs=Yii::app()->clientScript;  


$script = '
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/config/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?configurations_generales.html");

// add layout to our main content 
var layout = new Object();
layout.obj = templateLayout_B.attachLayout("2U");

layout.A = new Object();
layout.A.obj = layout.obj.cells("a");
layout.A.obj.hideHeader();
layout.A.obj.setWidth(250);

layout.A.dataview = new Object();

layout.A.dataview.obj = layout.A.obj.attachDataView({
    type: {
        template: function(obj){
			if (obj.disabled) {
				return \'<div style="color: #d2d2d2; width:100%; height:100%;"><strong>\'+obj.Title+\'</strong><br /><em>\'+obj.Description+\'</em></div>\';
			} else {
				return \'<div style="width:100%; height:100%;"><strong>\'+obj.Title+\'</strong><br /><em>\'+obj.Description+\'</em></div>\';
			}
		},		
        height: 40,
		width: 211,
		padding: 10
    },
	select:true
});

layout.A.dataview.obj.load("'.CController::createUrl('xml_list_section').'",function(){ 
layout.A.dataview.obj.select(layout.A.dataview.obj.first());
}, "xml");

layout.A.dataview.obj.attachEvent("onItemClick", function (id, ev, html){';
		// To correct a Bug in SWF Uploadify
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			$script .= '
			var swfuploadify = window["uploadify_banner_description_'.$value->code.'_image_upload_button"];
			if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();';
		}
		$script .= '
	
	
	var obj = this;
	
	if (obj.get(id).disabled) {
		return false;
	}
	
	// on click check if modifications were made and prompt for save
	if (!this.isSelected(id) && edit.layout && edit.layout.og_form && typeof(edit.layout.has_modifications) == "function") {		
		if (edit.layout.has_modifications() && !confirm("'.Yii::t('global','LABEL_CONFIRM_CONTINUE_WITHOUT_SAVING').'")) return false;
	}	
	
	return true;
});

layout.A.dataview.ajaxRequests = 0;
layout.A.dataview.ajaxComplete = function(){
	layout.A.dataview.ajaxRequests--;
	
	if (layout.A.dataview.ajaxRequests == 0) {		
		layout.A.dataview.enableItems(1);
	}
};

layout.A.dataview.enableItems = function(i,id){
	var obj = layout.A.dataview.obj;
	var itemCount = obj.dataCount();
	
	for (var x=0; x<itemCount; x++) {
		var current_id = obj.idByIndex(x);
		
		if (id != current_id) {
			if (!i) {
				obj.get(current_id).disabled = 1;
				$("[dhx_f_id=\'"+current_id+"\'] div").css("color","#d2d2d2");
			} else {
				obj.get(current_id).disabled = 0;
				$("[dhx_f_id=\'"+current_id+"\'] div").css("color","#000000")				
			}		
		}
	}
}

layout.A.dataview.obj.attachEvent("onBeforeSelect", function (id){	
	layout.A.dataview.enableItems(0,id);	

	$.ajax({
		url: "'.CController::createUrl('edit').'",
		type: "POST",
		data: { "id":id },
		beforeSend: function(){
			layout.A.dataview.ajaxRequests++;
		},
		success: function(data){
			layout.B.obj.attachHTMLString(data);		
		}
	});		
	
	//any custom logic here
	return true;
});

layout.B = new Object();
layout.B.obj = layout.obj.cells("b");
layout.B.obj.hideHeader();
';


$cs->registerScript('dhtmlx',$script,CClientScript::POS_END);
?>