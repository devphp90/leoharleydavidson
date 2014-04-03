<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();
'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");
'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerObj.'.wins = new Object();

'.$containerObj.'.layout.A.toolbar = new Object();
'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.obj.addButton("add", null, "'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");  	

'.$containerObj.'.layout.A.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$containerObj.'.layout.A.toolbar.obj.addSeparator("sep1", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.A.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/products/edit_price_tiers','LABEL_BTN_ADD_PRICE_TIERS_UNDER').' "+tabs.obj.getLabel("'.$container.'")+" - Products";

	switch (id) {
		case "add":
			'.$containerObj.'.layout.A.toolbar.add();		
			break;
		case "delete":			
			var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/products/edit_price_tiers','LABEL_ALERT_DELETE_PRICE_TIERS').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_price_tier').'",
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
				alert("'.Yii::t('views/products/edit_price_tiers','LABEL_ALERT_NO_CHECKED_PRICE_TIERS').'");	
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

'.$containerObj.'.layout.A.toolbar.add = function(current_id){
		if(current_id){name = "'.Yii::t('global','LABEL_EDIT').'";} else{ name="'.Yii::t('views/products/edit_price_tiers','LABEL_TITLE').'";} 
	  
	  '.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addWindow", 10, 10, 360, 200);
	  '.$containerObj.'.wins.obj.setText(name);
	  '.$containerObj.'.wins.obj.button("park").hide();
	  '.$containerObj.'.wins.obj.keepInViewport(true);
	  '.$containerObj.'.wins.obj.setModal(true);
				  
	  '.$containerObj.'.wins.toolbar = new Object();
	  
	  '.$containerObj.'.wins.toolbar.load = function(){
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
					  if (confirm("'.Yii::t('views/products/edit_price_tiers','LABEL_ALERT_DELETE_PRICE_TIERS').'")) {
						  $.ajax({
							  url: "'.CController::createUrl('delete_price_tier').'",
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
	  '.$containerObj.'.wins.toolbar.load();
  
	  
	  '.$containerObj.'.wins.layout = new Object();
	  '.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");				
	  
	  '.$containerObj.'.wins.layout.A = new Object();
	  '.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");			
	  '.$containerObj.'.wins.layout.A.obj.hideHeader();	
	  
	  $.ajax({
		  url: "'.CController::createUrl('edit_price_tiers_options',array('container'=>$containerObj,'id_product'=>$id)).'",
		  type: "POST",
		  data: { "id":current_id },					
		  success: function(data){
			  '.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
		  }
	  });	
	  
	  // clean variables
	  '.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		  '.$containerObj.'.wins = new Object();
		  load_grid('.$containerObj.'.layout.A.grid.obj);
		  return true;
	  });	
		  

	  '.$containerObj.'.wins.toolbar.save = function(id,close){	
			  var obj = '.$containerObj.'.wins.toolbar.obj;
			$.ajax({
				url: "'.CController::createUrl('save_price_tier').'",
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

							alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
							// Verify if popup window is close
							if(!close){		
								$("#'.$containerObj.'_id").val(data.id);
								'.$containerObj.'.wins.toolbar.load(data.id);
							}
	  
							'.$containerObj.'.layout.A.grid.obj.selectRowById(data.id);
						}
					} else {
						alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
					}
				}
			});	
	  }	
}

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/products/edit_price_tiers','LABEL_CUSTOMER_TYPE').','.Yii::t('views/products/edit_price_tiers','LABEL_QYT_TO_BUY').','.Yii::t('views/products/edit_price_tiers','LABEL_PRICE_UNIT').'",null,["text-align:center;",,"text-align:center;","text-align:right"]);
// custom text filter input
'.$containerObj.'.layout.A.grid.obj.setInitWidths("40,*,100,100");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,center,right");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false,false,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

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
'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_price_tiers',array('id'=>$id)).'";
'.$containerObj.'.layout.A.grid.obj.loadXML('.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});


'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>