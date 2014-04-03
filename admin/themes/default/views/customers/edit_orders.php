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

'.$containerObj.'.layout.A.toolbar = new Object();
'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.A.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/customers/edit_addresses','LABEL_ADDRESS_UNDER').' "+tabs.obj.getLabel("'.$container.'")+" - '.Yii::t('views/customers/edit_addresses','LABEL_CUSTOMER').'";

	switch (id) {
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
'.$containerObj.'.layout.A.grid.obj.setHeader("Order no.,Order Date,Bill To,Ship To,Total,Status,Priority",null,["text-align:center"]);
'.$containerObj.'.layout.A.grid.obj.attachHeader("#text_filter_custom,#datetime_filter_custom,#text_filter_custom,#text_filter_custom,#numeric_filter,#select_filter_custom_status,#select_filter_custom_priority");

// custom text filter input
'.$containerObj.'.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
'.$containerObj.'.layout.A.grid.obj._in_header_datetime_filter_custom=datetime_filter_custom;

// custom select filter input
'.$containerObj.'.layout.A.grid.obj._in_header_select_filter_custom_priority=function(tag,index,data){
	var obj = this;
	
	tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"0\">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_NORMAL').'</option><option value=\"1\">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_ATTENTION').'</option><option value=\"2\">'.Yii::t('controllers/OrdersController','LABEL_PRIORITY_URGENT').'</option></select>";	
	tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.firstChild.onchange=function(){
		load_grid(obj);
	};	
}

'.$containerObj.'.layout.A.grid.obj._in_header_select_filter_custom_status=function(tag,index,data){
	var obj = this;
	
	tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"-1\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_CANCELLED').'</option><option value=\"1\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_PENDING').'</option><option value=\"2\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_PAYMENT_REVIEW').'</option><option value=\"3\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_SUSPECTED_FRAUD').'</option><option value=\"4\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_DECLINED').'</option><option value=\"5\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_PROCESSING').'</option><option value=\"6\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_ON_HOLD').'</option><option value=\"7\">'.Yii::t('controllers/OrdersController','LABEL_STATUS_COMPLETED').'</option></select>";	
	tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.firstChild.onchange=function(){
		load_grid(obj);
	};		
}

'.$containerObj.'.layout.A.grid.obj.setInitWidthsP("10,10,20,20,15,15,10");
'.$containerObj.'.layout.A.grid.obj.setColAlign("left,left,left,left,right,center,center");
'.$containerObj.'.layout.A.grid.obj.setColSorting("server,server,na,na,server,na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false,false,false,false,false,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableMultiline(true);
'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
'.$containerObj.'.layout.A.obj.attachStatusBar().setText("<div id=\'recinfoArea\'></div>");
'.$containerObj.'.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerObj.'.layout.A.grid.obj.enablePaging(true, 100, 3, "recinfoArea");
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
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[0].name="id";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="date_order_start";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[1].name="date_order_end";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="bill_to";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="ship_to";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[4].getElementsByTagName("INPUT")[0].name="total";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[5].getElementsByTagName("SELECT")[0].name="status";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[6].getElementsByTagName("SELECT")[0].name="priority";

'.$containerObj.'.layout.A.grid.obj.enableSmartRendering(true);
// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_orders',array('id'=>$id)).'";
// load the initial grid
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	window.open("'.CController::createUrl('orders/').'?id_orders="+rId,"_blank");
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
	load_grid(this, ind, dir);
	
	return false;
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