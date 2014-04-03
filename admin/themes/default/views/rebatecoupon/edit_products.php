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

'.$containerObj.'.wins_list = new Object();

'.$containerObj.'.layout.A.toolbar = new Object();
'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.obj.addButton("add",null,"'.Yii::t('views/rebatecoupon/edit_products','LABEL_BTN_ADD_PRODUCT').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$containerObj.'.layout.A.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_REMOVE_FROM_LIST').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$containerObj.'.layout.A.toolbar.obj.addSeparator("sep1", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.A.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "Products under: "+tabs.obj.getLabel("'.$container.'");

	switch (id) {
		case "add":
			'.$containerObj.'.layout.A.toolbar.add('.$id.');
			break;
		case "delete":			
			var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/rebatecoupon/edit_products','LABEL_ALERT_REMOVE_PRODUCT').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_product',array('id'=>$id)).'",
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
							alert("'.Yii::t('global','LABEL_ALERT_REMOVE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/rebatecoupon/edit_products','LABEL_ALERT_NO_CHECKED').'");	
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

'.$containerObj.'.layout.A.toolbar.add = function(id_rebate_coupon){
	name = "'.Yii::t('views/rebatecoupon/edit_products','LABEL_BTN_ADD_PRODUCT').'";
	

	'.$containerObj.'.wins_list.obj = '.$containerObj.'.dhxWins.createWindow("addProductWindow", 10, 10, 800, 380);
	'.$containerObj.'.wins_list.obj.setText(name);
	'.$containerObj.'.wins_list.obj.button("park").hide();
	'.$containerObj.'.wins_list.obj.keepInViewport(true);
	'.$containerObj.'.wins_list.obj.setModal(true);
	//'.$containerObj.'.wins_list.obj.center();	
				
	'.$containerObj.'.wins_list.toolbar = new Object();

	'.$containerObj.'.wins_list.toolbar.load = function(id_rebate_coupon){
		var obj = '.$containerObj.'.wins_list.toolbar.obj;
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED').'","toolbar/save.gif","toolbar/save_dis.gif");
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins_list.toolbar.save(id);
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins_list.toolbar.obj = '.$containerObj.'.wins_list.obj.attachToolbar();
	'.$containerObj.'.wins_list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins_list.toolbar.load(id_rebate_coupon);

	'.$containerObj.'.wins_list.layout = new Object();
	'.$containerObj.'.wins_list.layout.obj = '.$containerObj.'.wins_list.obj.attachLayout("1C");
	'.$containerObj.'.wins_list.layout.A = new Object();
	'.$containerObj.'.wins_list.layout.A.obj = '.$containerObj.'.wins_list.layout.obj.cells("a");
	
	'.$containerObj.'.wins_list.layout.A.obj.hideHeader();
	
	'.$containerObj.'.wins_list.layout.A.grid = new Object();
	
	'.$containerObj.'.wins_list.layout.A.grid.obj = '.$containerObj.'.wins_list.layout.A.obj.attachGrid();
	'.$containerObj.'.wins_list.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
	'.$containerObj.'.wins_list.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,"text-align:right"]);
	'.$containerObj.'.wins_list.layout.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
	// custom text filter input
	'.$containerObj.'.wins_list.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
	
	'.$containerObj.'.wins_list.layout.A.grid.obj.setInitWidths("40,*,200,100");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setColAlign("center,left,left,right");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setColSorting("na,na,na,na");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setSkin(dhx_skin);
	'.$containerObj.'.wins_list.layout.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
	'.$containerObj.'.wins_list.layout.A.grid.obj.enableDragAndDrop(false);
	'.$containerObj.'.wins_list.layout.A.grid.obj.enableRowsHover(false,dhx_rowhover);
	
	//Paging
	'.$containerObj.'.wins_list.layout.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_win\'></div>");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	'.$containerObj.'.wins_list.layout.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_win");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
	'.$containerObj.'.wins_list.layout.A.grid.obj.i18n.paging={
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
	'.$containerObj.'.wins_list.layout.A.grid.obj.init();
	
	// set filter input names
	'.$containerObj.'.wins_list.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	'.$containerObj.'.wins_list.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
	'.$containerObj.'.wins_list.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="price";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	'.$containerObj.'.wins_list.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_product_add').'?id_rebate_coupon="+id_rebate_coupon;
	
	// load the initial grid
	load_grid('.$containerObj.'.wins_list.layout.A.grid.obj);		
	
	'.$containerObj.'.wins_list.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	'.$containerObj.'.wins_list.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});	
	
	// clean variables
	'.$containerObj.'.wins_list.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins_list = new Object();
		return true;
	});			
	
	'.$containerObj.'.wins_list.toolbar.save = function(id){
		var obj = '.$containerObj.'.wins_list.toolbar.obj;
		var checked = '.$containerObj.'.wins_list.layout.A.grid.obj.getCheckedRows(0);
		if (checked) {											
				checked = checked.split(",");
				var ids=[];
				
				for (var i=0;i<checked.length;++i) {
					if (checked[i]) {
						ids.push("ids[]="+checked[i]);									
					}
				}	
								
				$.ajax({
					url: "'.CController::createUrl('add_product',array('id'=>$id)).'",
					type: "POST",
					data: ids.join("&"),
					beforeSend: function(){		
						obj.disableItem(id);
					},
					complete: function(){
						'.$containerObj.'.wins_list.obj.close();
					},
					success: function(data){	
						load_grid('.$containerObj.'.layout.A.grid.obj);
						alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
					}
				});							
		} else {
			alert("'.Yii::t('views/rebatecoupon/edit_products','LABEL_ALERT_NO_CHECKED').'");		
		}	
		
	};

}

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,,"text-align:right"]);
'.$containerObj.'.layout.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
// custom text filter input
'.$containerObj.'.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

'.$containerObj.'.layout.A.grid.obj.setInitWidths("40,*,200,100");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,right");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false,false,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableDragAndDrop(false);
'.$containerObj.'.layout.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
//'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover);

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
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="price";

'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_products',array('id'=>$id)).'";
'.$containerObj.'.layout.A.grid.obj.loadXML('.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl);


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
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>