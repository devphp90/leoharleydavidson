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
	var title = "'.Yii::t('views/products/edit_combo_products','LABEL_BTN_ADD_PRODUCT_TO').' "+tabs.obj.getLabel("'.$container.'")+" - Products";

	switch (id) {
		case "add":
			'.$containerObj.'.layout.A.toolbar.add('.$id.');
			break;
		case "delete":			
			var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/products/edit_combo_products','LABEL_ALERT_DELETE_PRODUCTS').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_combo_product',array('id'=>$id)).'",
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
				alert("'.Yii::t('views/products/edit_combo_products','LABEL_ALERT_NO_CHECKED_PRODUCT').'");	
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

'.$containerObj.'.layout.A.toolbar.add = function(id_product){
	name = "'.Yii::t('views/products/edit_combo_products','LABEL_BTN_ADD_PRODUCT').'";
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("addProductWindow", 10, 10, 600, 380);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	//'.$containerObj.'.wins.obj.center();	
				
	'.$containerObj.'.wins.toolbar = new Object();

	'.$containerObj.'.wins.toolbar.load = function(id_product){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addText("default_qty_text", null, "'.Yii::t('views/products/edit_combo_products','LABEL_DEFAULT_QTY').'");
		obj.addInput("default_qty", null, 1, 30);
		
		obj.objPull[obj.idPrefix+"default_qty"].obj.firstChild.style.textAlign = "center";
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins.toolbar.save(id);
					break;
				case "save_close":	
					'.$containerObj.'.wins.toolbar.save(id,1);
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
	'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins.toolbar.load(id_product);

	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	
	'.$containerObj.'.wins.layout.A.obj.hideHeader();
	
	'.$containerObj.'.wins.layout.A.grid = new Object();
	
	'.$containerObj.'.wins.layout.A.grid.obj = '.$containerObj.'.wins.layout.A.obj.attachGrid();
	'.$containerObj.'.wins.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
	'.$containerObj.'.wins.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRICE').'",null,[,,,,"text-align:right;"]);
	'.$containerObj.'.wins.layout.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
	// custom text filter input
	'.$containerObj.'.wins.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
	
	'.$containerObj.'.wins.layout.A.grid.obj.setInitWidths("40,*,*,120");
	'.$containerObj.'.wins.layout.A.grid.obj.setColAlign("center,left,left,right");
	'.$containerObj.'.wins.layout.A.grid.obj.setColSorting("na,server,server,server");
	'.$containerObj.'.wins.layout.A.grid.obj.enableResizing("false,false,false,false");
	'.$containerObj.'.wins.layout.A.grid.obj.setSkin(dhx_skin);
	'.$containerObj.'.wins.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	//Paging
	'.$containerObj.'.wins.layout.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_win\'></div>");
	'.$containerObj.'.wins.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	'.$containerObj.'.wins.layout.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_win");
	'.$containerObj.'.wins.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
	'.$containerObj.'.wins.layout.A.grid.obj.i18n.paging={
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
	
	'.$containerObj.'.wins.layout.A.grid.obj.init();
	
	// set filter input names
	'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
	'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="price";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	'.$containerObj.'.wins.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_combo_products_add',array('id'=>$id)).'";
	
	// load the initial grid
	load_grid('.$containerObj.'.wins.layout.A.grid.obj);		
	
	'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
		load_grid(this, ind, dir);
		
		return false;
	});	
	
	'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});			
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		load_grid('.$containerObj.'.layout.A.grid.obj);
		return true;
	});			
	
	'.$containerObj.'.wins.toolbar.save = function(id,close){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		var checked = '.$containerObj.'.wins.layout.A.grid.obj.getCheckedRows(0);
		if (checked) {											
			checked = checked.split(",");
			var ids=[];
			
			for (var i=0;i<checked.length;++i) {
				if (checked[i]) {
					ids.push("ids[]="+checked[i]);									
				}
			}	

			$.ajax({
				url: "'.CController::createUrl('add_combo_product',array('id'=>$id)).'",
				type: "POST",
				data: ids.join("&")+"&default_qty="+obj.getValue("default_qty"),
				beforeSend: function(){		
					obj.disableItem(id);
				},
				complete: function(){
					if(close){
						'.$containerObj.'.wins.obj.close();
					}else{
						if (typeof obj.enableItem == "function") obj.enableItem(id);
						load_grid('.$containerObj.'.wins.layout.A.grid.obj);	
					}
					
				},
				success: function(data){					
					
					alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
				}
			});				
		} else {
			alert("'.Yii::t('views/products/edit_combo_products','LABEL_ALERT_NO_CHECKED_PRODUCT').'");		
		}	
		
	};

}

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.A.grid.obj.setHeader(",'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').',,'.Yii::t('global','LABEL_PRICE').'",null,["text-align:center",,,"text-align:center",,"text-align:right"]);
'.$containerObj.'.layout.A.grid.obj.attachHeader("#master_checkbox,#text_filter_custom,#text_filter_custom,,,,");
	
// custom text filter input
'.$containerObj.'.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

'.$containerObj.'.layout.A.grid.obj.setInitWidths("40,*,*,80,0,100");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,center,center,right");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.setColTypes("ch,tree,ro,ro,ro,ro");
'.$containerObj.'.layout.A.grid.obj.enableTreeGridLines(true);
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false,false,false,false,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
//'.$containerObj.'.layout.A.grid.obj.enableDragAndDrop(true);
'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
'.$containerObj.'.layout.A.grid.obj.setColumnHidden(4,true);

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

// set filter input names
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";

'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_combo_products',array('id'=>$id)).'";
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	if (this.getParentId(rId)) return false;
	
	id_product_combo = rId;
	name = this.cellById(id_product_combo,1).getValue()+" "+this.cellById(id_product_combo,2).getValue();
	
	if (this.cellById(id_product_combo,4).getValue() > 0) {
		'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("editProductWindow", 10, 10, 600, 380);
	} else {
		'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("editProductWindow", 10, 10, 200, 130);
	}
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	//'.$containerObj.'.wins.obj.center();	
				
	'.$containerObj.'.wins.toolbar = new Object();

	'.$containerObj.'.wins.toolbar.load = function(){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins.toolbar.save(id);
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
	'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins.toolbar.load();

	'.$containerObj.'.wins.layout = new Object();

	if (this.cellById(id_product_combo,4).getValue() > 0) {
		'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("2E");
	} else {
		'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");		
	}
	
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");	
	'.$containerObj.'.wins.layout.A.obj.hideHeader();
	'.$containerObj.'.wins.layout.A.obj.setHeight(65);
	'.$containerObj.'.wins.layout.A.obj.fixSize(false,true);
	
	$.ajax({
		url: "'.CController::createUrl('edit_combo_products_edit_product',array('container'=>$containerObj,'id'=>$id)).'",
		type: "POST",
		data: { "id_product_combo":id_product_combo },
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
		}
	});
	
	if (this.cellById(id_product_combo,4).getValue() > 0) {	
		'.$containerObj.'.wins.layout.B = new Object();
		'.$containerObj.'.wins.layout.B.obj = '.$containerObj.'.wins.layout.obj.cells("b");	
		'.$containerObj.'.wins.layout.B.obj.hideHeader();	
		
		'.$containerObj.'.wins.layout.B.grid = new Object();
		'.$containerObj.'.wins.layout.B.grid.obj = '.$containerObj.'.wins.layout.B.obj.attachGrid();
		'.$containerObj.'.wins.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
		'.$containerObj.'.wins.layout.B.grid.obj.setHeader("'.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRICE').',,'.Yii::t('views/products/edit_combo_products','LABEL_DEFAULT').'",null,[,,"text-align:right;",,"text-align:center;"]);
		'.$containerObj.'.wins.layout.B.grid.obj.setInitWidths("*,*,120,80,80");
		'.$containerObj.'.wins.layout.B.grid.obj.setColAlign("left,left,right,center,center");
		'.$containerObj.'.wins.layout.B.grid.obj.setColSorting("na,na,na,na,na");
		'.$containerObj.'.wins.layout.B.grid.obj.enableResizing("false,false,false,false,false");
		'.$containerObj.'.wins.layout.B.grid.obj.setSkin(dhx_skin);
		'.$containerObj.'.wins.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover);
		'.$containerObj.'.wins.layout.B.grid.obj.init();
		
		// we create a variable to store the default url used to get our grid data, so we can reuse it later
		'.$containerObj.'.wins.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_combo_products_variants',array('id'=>$id)).'?id_product_combo="+id_product_combo;
		
		// load the initial grid
		load_grid('.$containerObj.'.wins.layout.B.grid.obj);		
		
		'.$containerObj.'.wins.layout.B.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
			var obj = '.$containerObj.'.wins.layout.B.grid.obj;
									
			if (cInd == 3) {
				$.ajax({
					url: "'.CController::createUrl('toggle_combo_product_variant',array('id'=>$id)).'?id_product_combo="+id_product_combo,
					type: "POST",
					data: { "ids":rId,"active":state }
				});	
				if(state){
					obj.setRowTextStyle(rId, "color: #000000");	
				}else{
					obj.setRowTextStyle(rId, "color: #B5B5B5;");		
				}		
			} else if (cInd == 4) {
				$.ajax({
					url: "'.CController::createUrl('toggle_combo_product_variant_default',array('id'=>$id)).'?id_product_combo="+id_product_combo,
					type: "POST",
					data: { "ids":rId }
				});	
			}
			
			// load the initial grid
			load_grid('.$containerObj.'.wins.layout.B.grid.obj);
						
		});						
	}
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		load_grid('.$containerObj.'.layout.A.grid.obj);
		return true;
	});			
	
	'.$containerObj.'.wins.toolbar.save = function(id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_combo_product_qty',array('id'=>$id)).'",
			type: "POST",
			data: $("#'.$containerObj.'_form").serialize(),
			dataType: "json",
			beforeSend: function(){	
				// clear all errors					
				$("#'.$containerObj.'_form span.error").html("");
				$("#'.$containerObj.'_form *").removeClass("error");			
				
				obj.disableItem(id);
			},
			complete: function(){
				'.$containerObj.'.wins.obj.close();
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
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});							
	};

});
/*
'.$containerObj.'.layout.A.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	var obj = this;
	
	var rows=this.getAllRowIds().split(",");
	var ids=[];

	for (var i=0;i<rows.length;++i) {
		if (rows[i]) {
			ids.push("ids[]="+rows[i]);									
		}
	}
	
	$.ajax({
		url: "'.CController::createUrl('save_combo_products_sort_order',array('id'=>$id)).'",
		type: "POST",
		data: ids.join("&")
	});	
});

'.$containerObj.'.layout.A.grid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}*/

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

$(window).resize(function(){
	setTimeout("'.$containerObj.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>