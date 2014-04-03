<?php
$containerJS = 'Tab'.$container;
$language = Yii::app()->language;

$script = '

var '.$containerJS.' = new Object();
'.$containerJS.'.id_product = '.($model->id ? $model->id:0).';
'.$containerJS.'.product_type = '.($model->product_type ? $model->product_type:0).';

'.$containerJS.'.get_product_id = function() { return '.$containerJS.'.id_product; };

'.$containerJS.'.dhxWins = new dhtmlXWindows();
'.$containerJS.'.dhxWins.enableAutoViewport(false);
'.$containerJS.'.dhxWins.attachViewportTo(tabs.obj.entBox.id);
'.$containerJS.'.dhxWins.setImagePath(dhx_globalImgPath);

'.$containerJS.'.wins = new Object();

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
	
	obj.addButton("change_product_type",null,"'.Yii::t('views/products/edit','LABEL_CHANGE_PRODUCT_TYPE').' : '.$product_type_text.'","toolbar/save.gif","toolbar/save_dis.gif");  
	obj.addSeparator("sep01", null);
	obj.addButton("preview",null,"'.Yii::t('global','LABEL_PREVIEW').'","toolbar/preview.png","toolbar/preview_dis.png");
	
	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "delete":
				if (confirm("'.Yii::t('views/products/edit','LABEL_ALERT_DELETE').'")) {
					$.ajax({
							url: "'.CController::createUrl('delete').'",
							type: "POST",
							data: { "ids[]":current_id,"pass":0 },
							dataType: "json",
							success: function(data){
								
								if(data.in_other_product==1 && confirm("'.Yii::t('views/products/edit','LABEL_ALERT_ONE_PRODUCT_DISABLED_COMBO_BUNDLED').'") || data.in_other_product!=1){
									$.ajax({
										url: "'.CController::createUrl('delete').'",
										type: "POST",
										data: { "ids[]":current_id,"pass":1 },
										beforeSend: function(){		
											obj.disableItem(id);
										},
										complete: function(){
											obj.enableItem(id);	
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
							}
						});	
				}
				break;	
			case "change_product_type":
				'.$containerJS.'.wins.obj = '.$containerJS.'.dhxWins.createWindow("addProductSelectTypeWindow", 0, 0, 500, 310);
				'.$containerJS.'.wins.obj.setText("'.Yii::t('views/products/edit','LABEL_SELECT_PRODUCT_TYPE').'");
				'.$containerJS.'.wins.obj.button("park").hide();
				'.$containerJS.'.wins.obj.button("minmax1").hide();
				'.$containerJS.'.wins.obj.keepInViewport(true);
				'.$containerJS.'.wins.obj.setModal(true);
				'.$containerJS.'.wins.obj.denyResize();
				'.$containerJS.'.wins.obj.center();		
				var pos = '.$containerJS.'.wins.obj.getPosition();
				'.$containerJS.'.wins.obj.setPosition(pos[0],10);		
			
				$.ajax({
					url: "'.CController::createUrl('add_product',array('container'=>$containerJS,'id'=>$model->id)).'",
					type: "POST",
					success: function(data){
						'.$containerJS.'.wins.obj.attachHTMLString(data);		
					}
				});			
				
				'.$containerJS.'.wins.toolbar = new Object();
				'.$containerJS.'.wins.toolbar.obj = '.$containerJS.'.wins.obj.attachToolbar();
				'.$containerJS.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerJS.'.wins.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
				
				'.$containerJS.'.wins.toolbar.obj.attachEvent("onClick",function(id){
					switch (id) {
						case "save":		
							var product_type = $("input[name=\'product_type\']:checked").val();
							
							if ('.$containerJS.'.product_type != product_type) {
								if (confirm("'.Yii::t('views/products/edit','LABEL_ALERT_APPLYING_NEW_PRODUCT_TYPE').'")) {
									$.ajax({
										url: "'.CController::createUrl('save_product_type',array('id'=>$model->id)).'",
										type: "POST",
										data: { "product_type":product_type },
										success: function(data){
											tabs.obj.setContentHref("'.$container.'","'.CController::createUrl('edit',array('id'=>$model->id)).'?container='.$container.'&product_type="+product_type);
											'.$containerJS.'.wins.obj.close();
										}
									});											
								}
								
								return false;															
							}
							
							'.$containerJS.'.wins.obj.close();
							break;							
					}
				});	
				
				// clean variables
				'.$containerJS.'.wins.obj.attachEvent("onClose",function(win){
					'.$containerJS.'.wins = new Object();						
					
					return true;
				});	
				break;
			case "preview":
				var langue;
				langue = "'.$language.'";
				if(typeof '.$containerJS.'_obj.layout.A.tabbar === "object"){
					langue = '.$containerJS.'_obj.layout.A.tabbar.obj.getActiveTab();
				}
				window.open("'.CController::createUrl('preview').'?id="+'.$containerJS.'.id_product+"&language="+langue);
				
				break;
		}
	});	
};

'.$containerJS.'.layout.toolbar.obj = '.$containerJS.'.layout.obj.attachToolbar();
'.$containerJS.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.toolbar.load('.$containerJS.'.id_product);

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


var '.$containerJS.'error_dataview;
'.$containerJS.'error_dataview = 0;
'.(($model->id and !$model->product_type)?'
function '.$containerJS.'_validate_variants(){
	'.$containerJS.'error_dataview = 0; 
	$.ajax({
		  url: "'.CController::createUrl('count_variants_and_count_options_in_group',array('id'=>$model->id)).'",
		  type: "POST",
		  dataType: "json",
		  success: function(data){														
			  if(data != ""){
				  if(data.group != ""){
					  '.$containerJS.'error_dataview = 1;
					  alert("'.Yii::t('views/products/edit','LABEL_ALERT_VARIANT_GROUP').'\n\n(" + data.group + ")");  
				  }else if(data.variants == 0){
					  '.$containerJS.'error_dataview = 1;
					  alert("'.Yii::t('views/products/edit','LABEL_ALERT_VARIANT').'");
				  }
			  }
		  }
	});
}
'.$containerJS.'_validate_variants();
':'').'


'.$containerJS.'.layout.A.dataview.obj = '.$containerJS.'.layout.A.obj.attachDataView({
    type: {
        template: function(obj){
			if (obj.disabled) {
				return \'<div style="color: #d2d2d2; width:100%; height:100%;"><strong>\'+obj.Title+\'</strong><br /><em>\'+obj.Description+\'</em></div>\';
			} else {
				//alert(error_dataview);
				if('.$containerJS.'error_dataview == 1 && obj.id == "edit_variants"){
					return \'<div style="color: #900; width:100%; height:100%;"><strong>\'+obj.Title+\'</strong><br /><em>\'+obj.Description+\'</em></div>\';
				}else{
					return \'<div style="width:100%; height:100%;"><strong>\'+obj.Title+\'</strong><br /><em>\'+obj.Description+\'</em></div>\';
				}
				
			}
		},		
        height: 40,
		width: 211,
		padding: 10
    },
	select:true
});

'.$containerJS.'.layout.A.dataview.load = function() {
	'.$containerJS.'.layout.A.dataview.obj.clearAll();
	'.$containerJS.'.layout.A.dataview.obj.load("'.CController::createUrl('xml_list_product_section').'?id="+'.$containerJS.'.id_product+"&product_type="+'.$containerJS.'.product_type,function(){ 
		'.$containerJS.'.layout.A.dataview.obj.select('.$containerJS.'.layout.A.dataview.obj.first());
}, "xml");
}

'.$containerJS.'.layout.A.dataview.load();

'.$containerJS.'.layout.A.dataview.obj.attachEvent("onItemClick", function (id, ev, html){
	var obj = this;

	if (obj.get(id).disabled) {
		return false;
	}
	
	// on click check if modifications were made and prompt for save
	if (!this.isSelected(id) && window["'.$containerJS.'_obj"] && window["'.$containerJS.'_obj"].og_form && typeof(window["'.$containerJS.'_obj"].has_modifications) == "function") {		
		if (window["'.$containerJS.'_obj"].has_modifications() && !confirm("'.Yii::t('global','LABEL_CONFIRM_CONTINUE_WITHOUT_SAVING').'")) return false;
	}

	'.(($model->id and !$model->product_type)?'
	
	
	if(id != "edit_variants"){
		'.$containerJS.'_validate_variants();
		//obj.refresh();
	}
	':'').'

	return true;
});


'.$containerJS.'.layout.A.dataview.ajaxRequests = 0;
'.$containerJS.'.layout.A.dataview.ajaxComplete = function(){
	'.$containerJS.'.layout.A.dataview.ajaxRequests--;
	
	if ('.$containerJS.'.layout.A.dataview.ajaxRequests == 0) {		
		if ('.$containerJS.'.id_product) {
			'.$containerJS.'.layout.A.dataview.enableItems(1);
		}
	}
};

'.$containerJS.'.layout.A.dataview.obj.attachEvent("onBeforeSelect", function (id){	
	'.$containerJS.'.layout.A.dataview.enableItems(0,id);	

	$.ajax({
		url: "'.CController::createUrl('edit_section',array('container'=>$container,'containerJS'=>$containerJS)).'",
		type: "POST",
		data: { "id":id, "id_product":'.$containerJS.'.id_product, "product_type":'.$containerJS.'.product_type },
		beforeSend: function(){
			'.$containerJS.'.layout.A.dataview.ajaxRequests++;
		},
		success: function(data){
			'.$containerJS.'.layout.B.obj.attachHTMLString(data);		
		}
	});		
	
	//any custom logic here
	return true;
});';

echo Html::script($script);
?>