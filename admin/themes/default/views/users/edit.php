<?php
$containerJS = 'Tab'.$container;
$language = Yii::app()->language;

$script = '
var '.$containerJS.' = new Object();
'.$containerJS.'.id_user = '.($model->id ? $model->id:0).';

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = tabs.obj.cells("'.$container.'").attachLayout("2U"); 

'.$containerJS.'.layout.toolbar = new Object();
'.$containerJS.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	obj.clearAll();	
	obj.detachAllEvents();

	if (current_id) {
		obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
	}
	
	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "delete":
				if (confirm("'.Yii::t('views/users/edit','LABEL_ALERT_DELETE').'")) {
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
							if (window["'.$containerJS.'_obj"] && typeof(window["'.$containerJS.'_obj"].load_og_form) == "function") {
								window["'.$containerJS.'_obj"].og_form = [];
								window["'.$containerJS.'_obj"].load_og_form();
							}
						
							tabs.close_tab(tabs.obj, "'.$container.'", true);
							load_grid(tabs.list.grid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});					
				}
				break;	
		}
	});	
};

'.$containerJS.'.layout.toolbar.obj = '.$containerJS.'.layout.obj.attachToolbar();
'.$containerJS.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.toolbar.load('.$containerJS.'.id_user);

'.$containerJS.'.layout.A = new Object();
'.$containerJS.'.layout.A.obj = '.$containerJS.'.layout.obj.cells("a");
'.$containerJS.'.layout.A.obj.hideHeader();
'.$containerJS.'.layout.A.obj.setWidth(250);	
'.$containerJS.'.layout.A.obj.fixSize(true,false);	

'.$containerJS.'.layout.A.dataview = new Object();

'.$containerJS.'.layout.A.dataview.enableItems = function(i,id){
	var obj = '.$containerJS.'.layout.A.dataview.obj;
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

'.$containerJS.'.layout.A.dataview.obj = '.$containerJS.'.layout.A.obj.attachDataView({
    type: {
        template: function(obj){
			if (obj.disabled) {
				return \'<div style="color: #d2d2d2; width:100%; height:100%;"><strong>\'+obj.Title+\'</strong><br /><em>\'+obj.Description+\'</em></div>\';
			} else {
				return \'<div style="width:100%; height:100%;"><strong>\'+obj.Title+\'</strong><br /><em>\'+obj.Description+\'</em></div>\';
			}
		},		
        height: 40,
		width: 211
    },
	select:true
});
'.$containerJS.'.layout.A.dataview.obj.load("'.CController::createUrl('xml_list_user_section').'?id="+'.$containerJS.'.id_user,function(){ 
'.$containerJS.'.layout.A.dataview.obj.select('.$containerJS.'.layout.A.dataview.obj.first());
}, "xml");

'.$containerJS.'.layout.A.dataview.obj.attachEvent("onItemClick", function (id, ev, html){
	var obj = this;
	
	if (obj.get(id).disabled) {
		return false;
	}
	
	// on click check if modifications were made and prompt for save
	if (!this.isSelected(id) && window["'.$containerJS.'_obj"] && window["'.$containerJS.'_obj"].og_form && typeof(window["'.$containerJS.'_obj"].has_modifications) == "function") {		
		if (window["'.$containerJS.'_obj"].has_modifications() && !confirm("'.Yii::t('global','LABEL_CONFIRM_CONTINUE_WITHOUT_SAVING').'")) return false;
	}	
	
	return true;
});

'.$containerJS.'.layout.A.dataview.ajaxRequests = 0;
'.$containerJS.'.layout.A.dataview.ajaxComplete = function(){
	'.$containerJS.'.layout.A.dataview.ajaxRequests--;
	
	if ('.$containerJS.'.layout.A.dataview.ajaxRequests == 0) {		
		if ('.$containerJS.'.id_user) {
			'.$containerJS.'.layout.A.dataview.enableItems(1);
		}
	}
};

'.$containerJS.'.layout.A.dataview.obj.attachEvent("onBeforeSelect", function (id){	
	'.$containerJS.'.layout.A.dataview.enableItems(0,id);	

	$.ajax({
		url: "'.CController::createUrl('edit_section',array('container'=>$container,'containerJS'=>$containerJS)).'",
		type: "POST",
		data: { "id":id, "id_user":'.$containerJS.'.id_user },
		beforeSend: function(){
			'.$containerJS.'.layout.A.dataview.ajaxRequests++;
		},
		success: function(data){
			'.$containerJS.'.layout.B.obj.attachHTMLString(data);		
		}
	});		
	
	//any custom logic here
	return true;
});

'.$containerJS.'.layout.B = new Object();
'.$containerJS.'.layout.B.obj = '.$containerJS.'.layout.obj.cells("b");
'.$containerJS.'.layout.B.obj.hideHeader();

function get_province_list(current_id, list_state_name){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list').'",
		type: "POST",
		data: { "country":current_id },
		success: function(data) {					
			$("#" + list_state_name).html("").append(data);	
			//$("#'.$id_state_code.'").focus();
		},
		error: function(xhr, status, thrown) {
			alert("'.Yii::t('global','ERROR_AJAX_REQUEST_FAILED').'");	
		}
	});		
}';

echo Html::script($script);
?>