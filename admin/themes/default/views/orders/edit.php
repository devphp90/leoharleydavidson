<?php
$containerJS = 'Tab'.$container;
$language = Yii::app()->language;

$script = '
var '.$containerJS.' = new Object();
'.$containerJS.'.id_order = '.($id ? $id:0).';
'.$containerJS.'.status = '.($status ? $status:0).';

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = tabs.obj.cells("'.$container.'").attachLayout("2U"); 
'.$containerJS.'.layout.toolbar = new Object();

'.$containerJS.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	obj.clearAll();	
	obj.detachAllEvents();
	
	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "delete":
				
				break;	
		}
	});	
};

'.$containerJS.'.layout.toolbar.obj = '.$containerJS.'.layout.obj.attachToolbar();
'.$containerJS.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.toolbar.load('.$containerJS.'.id_order);

'.$containerJS.'.layout.A = new Object();
'.$containerJS.'.layout.B = new Object();

'.$containerJS.'.layout.A.obj = '.$containerJS.'.layout.obj.cells("a");
'.$containerJS.'.layout.B.obj = '.$containerJS.'.layout.obj.cells("b");
'.$containerJS.'.layout.A.obj.hideHeader();
'.$containerJS.'.layout.B.obj.hideHeader();
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
		width: 211,
		padding: 10
    },
	select:true
});

'.$containerJS.'.layout.A.dataview.obj.load("'.CController::createUrl('xml_list_section').'?id="+'.$containerJS.'.id_order,function(){
	'.$containerJS.'.layout.A.dataview.obj.select('.$containerJS.'.layout.A.dataview.obj.first());
}, "xml");

'.$containerJS.'.layout.A.dataview.obj.attachEvent("onItemClick", function (id, ev, html){
	var obj = this;
	
	if (obj.get(id).disabled) {
		return false;
	}
	
	return true;
});

'.$containerJS.'.layout.A.dataview.ajaxRequests = 0;
'.$containerJS.'.layout.A.dataview.ajaxComplete = function(){
	'.$containerJS.'.layout.A.dataview.ajaxRequests--;
	
	if ('.$containerJS.'.layout.A.dataview.ajaxRequests == 0) {		
		if ('.$containerJS.'.id_order) {
			'.$containerJS.'.layout.A.dataview.enableItems(1);
		}
	}
};

'.$containerJS.'.layout.A.dataview.obj.attachEvent("onBeforeSelect", function (id){	
	'.$containerJS.'.layout.A.dataview.enableItems(0,id);	

	$.ajax({
		url: "'.CController::createUrl('edit_section',array('container'=>$container,'containerJS'=>$containerJS)).'",
		type: "POST",
		data: { "id":id, "id_order":'.$containerJS.'.id_order },
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
';

echo Html::script($script);
?>