<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';
$include_path = Yii::app()->params['includes_js_path'];	

$script = '

'.$containerObj.' = new Object();

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerObj.'.wins = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();


'.$containerObj.'.layout.A.toolbar = new Object();
'.$containerObj.'.layout.A.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.A.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/orders/edit_shipments','LABEL_BTN_CREATE_SHIPMENT').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");		
	obj.addSeparator("sep01", null);


	obj.attachEvent("onClick",function(id){		
		switch (id) {
			case "add":		
				'.$containerObj.'.layout.A.toolbar.add();											
				break;							
			case "delete":
				var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
				
				if (checked) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						ids.push("ids[]="+checked[i]);									
					}						
															
					if (confirm("'.Yii::t('views/orders/edit_shipments','LABEL_ALERT_DELETE_SHIPMENT').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_shipment').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								'.$containerObj.'.wins.obj.close();
							},
							success: function(data){														
								load_grid('.$containerObj.'.layout.A.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}				
				} else {
					alert("'.Yii::t('views/orders/edit_shipments','LABEL_ALERT_NO_CHECKED_SHIPMENT').'");		
				}
				break;
		}
	});	
};

'.$containerObj.'.layout.A.toolbar.add = function(current_id, name){
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/orders/edit_shipments','LABEL_BTN_CREATE_SHIPMENT').'";	
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_createShipmentWindow", 10, 10, 650, 440);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	//'.$containerObj.'.wins.obj.maximize();	
				
	'.$containerObj.'.wins.toolbar = new Object();
	
	'.$containerObj.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins.toolbar.save(id);
					break;
				case "save_close":
					'.$containerObj.'.wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/orders/edit_shipments','LABEL_ALERT_DELETE_SHIPMENT').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_shipment').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								'.$containerObj.'.wins.obj.close();
							},
							success: function(data){														
								load_grid('.$containerObj.'.layout.A.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
	'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins.toolbar.load(current_id);

	'.$containerObj.'.wins.toolbar.save = function(id,close){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		var checked = '.$containerObj.'.wins.layout.B.grid.obj.getCheckedRows(0);
			
		if (checked.length) {			
			checked = checked.split(",");
			var ids=[];
			
			for (var i=0;i<checked.length;++i) {
				ids.push("ids[]="+checked[i]);									
			}		
			
			$.ajax({
				url: "'.CController::createUrl('save_shipment',array('id_orders'=>$id)).'",
				type: "POST",
				data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize()+"&"+ids.join("&"),
				dataType: "json",
				beforeSend: function(){			
					// clear all errors					
					$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
					$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");
			
					obj.disableItem(id);			
				},
				complete: function(jqXHR, textStatus){
					if (typeof obj.enableItem == "function") obj.enableItem(id);
	
					if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$containerObj.'.wins.obj.close();
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
	
							load_grid('.$containerObj.'.layout.A.grid.obj);		
							
							alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
							
							// Verify if popup window is close
							if(!close){
								var id_tag_container = "'.$containerObj.'_shipment_no";
								var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
								$("#'.$containerObj.'_id").val(data.id);
								'.$containerObj.'.wins.toolbar.load(data.id);
								'.$containerObj.'.wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
							}
						}
					} else {
						alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
					}
				}
			});	
		} else {
			alert("'.Yii::t('views/orders/edit_shipments','LABEL_ALERT_NO_CHECKED').'");		
		}
	}			
	
	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("2E");

	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	'.$containerObj.'.wins.layout.A.obj.hideHeader();	
	'.$containerObj.'.wins.layout.A.obj.setHeight(210);
	
	$.ajax({
		url: "'.CController::createUrl('edit_shipments_options',array('container'=>$containerObj,'id_orders'=>$id)).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
		}
	});					
	
	'.$containerObj.'.wins.layout.B = new Object();
	'.$containerObj.'.wins.layout.B.obj = '.$containerObj.'.wins.layout.obj.cells("b");
	'.$containerObj.'.wins.layout.B.obj.hideHeader();
//	'.$containerObj.'.wins.layout.B.obj.setHeight(300);
	'.$containerObj.'.wins.layout.B.obj.fixSize(false,false);
											
	'.$containerObj.'.wins.layout.B.grid = new Object();
	'.$containerObj.'.wins.layout.B.grid.obj = '.$containerObj.'.wins.layout.B.obj.attachGrid();

	'.$containerObj.'.wins.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
	'.$containerObj.'.wins.layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/orders/edit_shipments','LABEL_NAME').','.Yii::t('views/orders/edit_shipments','LABEL_SKU').','.Yii::t('views/orders/edit_shipments','LABEL_QTY').','.Yii::t('views/orders/edit_shipments','LABEL_QTY_IN_SHIPMENT').'",null,["text-align:center",,"text-align:center","text-align:center"]);
	//'.$containerObj.'.wins.layout.B.grid.obj.attachHeader("&nbsp;,#text_filter_custom,#text_filter_custom,#text_filter_custom,&nbsp;,&nbsp;");
	
	// custom text filter input
	'.$containerObj.'.wins.layout.B.grid.obj._in_header_text_filter_custom=text_filter_custom;
	
	'.$containerObj.'.wins.layout.B.grid.obj.setInitWidthsP("8,52,15,10,10");
	'.$containerObj.'.wins.layout.B.grid.obj.setColAlign("center,left,left,center,center");
	'.$containerObj.'.wins.layout.B.grid.obj.setColSorting("na,na,na,na,na");
	'.$containerObj.'.wins.layout.B.grid.obj.enableResizing("false,true,true,true,true");
	'.$containerObj.'.wins.layout.B.grid.obj.setSkin(dhx_skin);
	'.$containerObj.'.wins.layout.B.grid.obj.enableMultiline(true);
	'.$containerObj.'.wins.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	'.$containerObj.'.wins.layout.B.grid.obj.init();
	
	// set filter input names
	//'.$containerObj.'.wins.layout.B.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	//'.$containerObj.'.wins.layout.B.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
	//'.$containerObj.'.wins.layout.B.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="qty";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	'.$containerObj.'.wins.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_shipments_products_left',array('container'=>$containerObj,'id_orders'=>$id)).'&id="+current_id;
	// load the initial grid
	load_grid('.$containerObj.'.wins.layout.B.grid.obj);	
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		
		return true;
	});
}

'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.load();

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/orders/edit_shipments','LABEL_SHIPMENT_NO').','.Yii::t('views/orders/edit_shipments','LABEL_SHIPMENT_DATE').','.Yii::t('views/orders/edit_shipments','LABEL_TRACKING_NO').','.Yii::t('views/orders/edit_shipments','LABEL_COMMENTS').'",null,["text-align:center"]);

'.$containerObj.'.layout.A.grid.obj.setInitWidthsP("5,20,15,20,40");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,left,left");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,true,true,true,true");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
'.$containerObj.'.layout.A.grid.obj.enableMultiline(true);

//Paging
'.$containerObj.'.layout.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea\'></div>");
'.$containerObj.'.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerObj.'.layout.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea");
'.$containerObj.'.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
'.$containerObj.'.layout.A.grid.obj.i18n.paging={
      results:"'.Yii::t('global','LABEL_GRID_PAGING_RESULTS').'",
      records:"'.Yii::t('global','LABEL_GRID_PAGING_RECORDS_FROM').'",
      to:"'.Yii::t('global','LABEL_GRID_PAGING_TO').'",
      page:"'.Yii::t('global','LABEL_GRID_PAGING_PAGE').'",
      perpage:"'.Yii::t('global','LABEL_GRID_PAGING_ROWS_PER_PAGE').'",
      first:"'.Yii::t('global','LABEL_GRID_PAGING_TO_FIRST_PAGE').'",
      previous:"'.Yii::t('global','LABEL_GRID_PAGING_PREVIOUS_PAGE').'",
      found:"'.Yii::t('global','LABEL_GRID_PAGING_FOUND_RECORDS').'",
      next:"'.Yii::t('global','LABEL_GRID_PAGING_NEXT_PAGE').'",
      last:"'.Yii::t('global','LABEL_GRID_PAGING_TO_LAST_PAGE').'",
      of:"'.Yii::t('global','LABEL_GRID_PAGING_OF').'",
      notfound:"'.Yii::t('global','LABEL_GRID_PAGING_NO_RECORDS_FOUND').'" }
'.$containerObj.'.layout.A.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_shipments',array('id_orders'=>$id)).'";
// load the initial grid
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

// clear event for this tab and reset
$(window).off("resize.'.$containerObj.'");
$(window).on("resize.'.$containerObj.'",function(){
	setTimeout("'.$containerObj.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>