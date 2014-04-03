<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerObj.'.wins = new Object();

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();


'.$containerObj.'.layout.A.toolbar = new Object();

'.$containerObj.'.layout.A.toolbar.add = function(current_id, name){
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/customers/edit_addresses','LABEL_BTN_ADD_ADDRESS').'";	
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addAddressWindow", 10, 10, 600, 380);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
				
	'.$containerObj.'.wins.toolbar = new Object();
	
	'.$containerObj.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png");
			obj.addSeparator("sep1", null);
			obj.addButton("stores_retailers",null,"'.Yii::t('global','LABEL_BTN_TRANSFERT_STORE_LOCATION').'","toolbar/transfert.png","toolbar/transfert_dis.png"); 
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
					if (confirm("'.Yii::t('views/customers/edit_addresses','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_address',array('id'=>$id)).'",
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
				case "stores_retailers":
					if (confirm("'.Yii::t('views/customers/edit_addresses','LABEL_ALERT_TRANSFERT').'")) {
						$.ajax({
							url: "'.CController::createUrl('transfer_store_retailer_address',array('id'=>$id)).'",
							type: "POST",
							data: { "id":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								
							},
							success: function(data){
								if (data) {
									alert("'.Yii::t('global','LABEL_ALERT_TRANSFERT_SUCCESS').'");
									'.$containerObj.'.wins.obj.close();	
								} else {
									alert("'.Yii::t('global','LABEL_ALERT_TRANSFERT_ERROR').'");
								}
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
		
		$.ajax({
			url: "'.CController::createUrl('save_address').'",
			type: "POST",
			data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize(),
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
						var id_tag_container = "'.$containerObj.'_name";
						var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");						
											
						load_grid('.$containerObj.'.layout.A.grid.obj);		
					
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						$("#'.$containerObj.'_id").val(data.id);
						'.$containerObj.'.wins.toolbar.load(data.id);
						
						'.$containerObj.'.wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}			
	
	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
	
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	'.$containerObj.'.wins.layout.A.obj.hideHeader();
											
	$.ajax({
		url: "'.CController::createUrl('edit_addresses_options',array('container'=>$containerObj,'id_customer'=>$id)).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		
		return true;
	});				
}

'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");  
'.$containerObj.'.layout.A.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$containerObj.'.layout.A.toolbar.obj.addSeparator("sep1", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.A.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/customers/edit_addresses','LABEL_ADDRESS_UNDER').' "+tabs.obj.getLabel("'.$container.'")+" - '.Yii::t('views/customers/edit_addresses','LABEL_CUSTOMER').'";

	switch (id) {
		case "add":
			'.$containerObj.'.layout.A.toolbar.add();
			break;
		case "delete":			
			var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/customers/edit_addresses','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_address',array('id'=>$id)).'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid('.$containerObj.'.layout.A.grid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/customers/edit_addresses','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "export_pdf":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"printview",[0],title);
			break;		
	}
});

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/customers/edit_addresses','LABEL_LIST_GRID_NAME').','.Yii::t('views/customers/edit_addresses','LABEL_LIST_GRID_PHONE').','.Yii::t('views/customers/edit_addresses','LABEL_LIST_GRID_ADDRESS').','.Yii::t('views/customers/edit_addresses','LABEL_LIST_GRID_DEFAULT_BILLING').','.Yii::t('views/customers/edit_addresses','LABEL_LIST_GRID_DEFAULT_SHIPPING').'",null,["text-align:center"]);
'.$containerObj.'.layout.A.grid.obj.attachHeader("&nbsp;,#text_filter_custom,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;");

// custom text filter input
'.$containerObj.'.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

'.$containerObj.'.layout.A.grid.obj.setInitWidths("40,*,*,*,100,100");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,left,center,center");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,server,na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false,false,false,false,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableMultiline(true);
'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
'.$containerObj.'.layout.A.grid.obj.init();

// set filter input names
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="fullname";

'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_addresses',array('id'=>$id)).'";
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
	load_grid(this, ind, dir);
	
	return false;
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	switch (cInd) {
		case 4:
			$.ajax({
				url: "'.CController::createUrl('toggle_default_billing').'",
				type: "POST",
				data: { "id":rId }
			});
			break;
		case 5:
			$.ajax({
				url: "'.CController::createUrl('toggle_default_shipping').'",
				type: "POST",
				data: { "id":rId }
			});		
			break;		
	}
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
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