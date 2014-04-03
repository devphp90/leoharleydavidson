<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "2E");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.setText("'.Yii::t('views/products/edit_combo_bundled','LABEL_TITLE_COMBO').'");

'.$containerObj.'.layout.B = new Object();
'.$containerObj.'.layout.B.obj = '.$containerObj.'.layout.obj.cells("b");
'.$containerObj.'.layout.B.obj.setText("'.Yii::t('views/products/edit_combo_bundled','LABEL_TITLE_BUNDLED').'");

'.$containerObj.'.layout.A.toolbar = new Object();
'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.A.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/products/edit_combo_bundled','LABEL_EXPORT_COMBO_LIST').' "+tabs.obj.getLabel("'.$container.'");

	switch (id) {
		case "export_pdf":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"pdf","",title);
			break;	
		case "export_excel":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"excel","",title);
			break;		
		case "print":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"printview","",title);
			break;				
	}
});

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.A.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').'",null,["text-align:left;","text-align:left;"]);

'.$containerObj.'.layout.A.grid.obj.setInitWidthsP("60,40");
'.$containerObj.'.layout.A.grid.obj.setColAlign("left,left");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("true,true");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableDragAndDrop(false);
'.$containerObj.'.layout.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
//'.$containerObj.'.layout.A.grid.obj.enableRowsHover(false,dhx_rowhover);

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

'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_combo',array('id'=>$id)).'";
'.$containerObj.'.layout.A.grid.obj.loadXML('.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl);


'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});


'.$containerObj.'.layout.B.toolbar = new Object();
'.$containerObj.'.layout.B.toolbar.obj = '.$containerObj.'.layout.B.obj.attachToolbar();
'.$containerObj.'.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.B.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.B.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.B.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.B.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/products/edit_combo_bundled','LABEL_EXPORT_BUNDLED_LIST').' "+tabs.obj.getLabel("'.$container.'");

	switch (id) {
		case "export_pdf":
			printGridPopup('.$containerObj.'.layout.B.grid.obj,"pdf","",title);
			break;	
		case "export_excel":
			printGridPopup('.$containerObj.'.layout.B.grid.obj,"excel","",title);
			break;		
		case "print":
			printGridPopup('.$containerObj.'.layout.B.grid.obj,"printview","",title);
			break;				
	}
});

'.$containerObj.'.layout.B.grid = new Object();
'.$containerObj.'.layout.B.grid.obj = '.$containerObj.'.layout.B.obj.attachGrid();
'.$containerObj.'.layout.B.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.B.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').'",null,["text-align:left;","text-align:left;"]);

'.$containerObj.'.layout.B.grid.obj.setInitWidthsP("60,40");
'.$containerObj.'.layout.B.grid.obj.setColAlign("left,left");
'.$containerObj.'.layout.B.grid.obj.setColSorting("na,na");
'.$containerObj.'.layout.B.grid.obj.enableResizing("true,true");
'.$containerObj.'.layout.B.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.B.grid.obj.enableDragAndDrop(false);
'.$containerObj.'.layout.B.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
//'.$containerObj.'.layout.B.grid.obj.enableRowsHover(false,dhx_rowhover);

//Paging
'.$containerObj.'.layout.B.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea2\'></div>");
'.$containerObj.'.layout.B.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerObj.'.layout.B.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea2");
'.$containerObj.'.layout.B.grid.obj.setPagingSkin("toolbar", dhx_skin);
'.$containerObj.'.layout.B.grid.obj.i18n.paging={
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
'.$containerObj.'.layout.B.grid.obj.init();

'.$containerObj.'.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_bundled',array('id'=>$id)).'";
'.$containerObj.'.layout.B.grid.obj.loadXML('.$containerObj.'.layout.B.grid.obj.xmlOrigFileUrl);


'.$containerObj.'.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>