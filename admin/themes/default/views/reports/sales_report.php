<?php
// register css/script files and scripts
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_SALES').'");
$("#how-to-link a").prop("title","/index.html?rapports_statistiques.html");

var layout = new Object();
layout.obj = templateLayout_B.attachLayout("1C");

layout.A = new Object();
layout.A.obj = layout.obj.cells("a");
layout.A.obj.hideHeader();

layout.A.toolbar = new Object();
layout.A.toolbar.obj = layout.A.obj.attachToolbar();
layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

layout.A.toolbar.obj.attachEvent("onClick", function(id){
	var obj = this;
	var title = "'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_SALES').'";
	wins2="";
	
	switch (id) {
		case "export_pdf":
			printGridPopup(layout.A.grid.obj,"pdf",null,title);
			break;	
		case "export_excel":
			printGridPopup(layout.A.grid.obj,"excel",null,title);
			break;		
		case "print":
			printGridPopup(layout.A.grid.obj,"printview",null,title);
			break;		
	}
});




layout.A.grid = new Object();
layout.A.grid.obj = layout.A.obj.attachGrid();
layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
layout.A.grid.obj.setHeader("'.Yii::t('views/reports/sales_report','LABEL_ORDER_DATE').','.Yii::t('views/orders/edit_info_options','LABEL_ORDER_DATE_PAYMENT').','.Yii::t('views/reports/sales_report','LABEL_INVOICE_NO').','.Yii::t('views/reports/sales_report','LABEL_SUBTOTAL').','.Yii::t('views/reports/sales_report','LABEL_SHIPPING').','.Yii::t('views/reports/sales_report','LABEL_COST').','.Yii::t('views/reports/sales_report','LABEL_TAXES').','.Yii::t('views/reports/sales_report','LABEL_DISCOUNTS').','.Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD').'",null);
layout.A.grid.obj.attachHeader("#datetime_filter_custom,#date_filter_custom,#text_filter_custom,#text_filter_custom,#text_filter_custom,#text_filter_custom,#text_filter_custom,#text_filter_custom,#select_filter_custom_payment_method");

// custom select filter input
layout.A.grid.obj._in_header_datetime_filter_custom=datetime_filter_custom;
layout.A.grid.obj._in_header_date_filter_custom=date_filter_custom;
layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

layout.A.grid.obj._in_header_select_filter_custom_payment_method=function(tag,index,data){
	var obj = this;
	
	tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"0\">'.Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CREDIT_CARD').'</option><option value=\"1\">'.Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_INTERACT').'</option><option value=\"2\">'.Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CHEQUE').'</option><option value=\"4\">PayPal</option><option value=\"5\">'.Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CASH').'</option></select>";	
	tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.firstChild.onchange=function(){
		load_grid(obj);
	};		
}

layout.A.grid.obj.setInitWidthsP("15,15,10,10,10,10,10,10,10");
layout.A.grid.obj.setColAlign("left,left,left,right,right,right,right,right,center");
layout.A.grid.obj.enableResizing("false,false,false,false,false,false,false,false,false");
layout.A.grid.obj.setColSorting("str,str,str,int,int,int,int,int");
layout.A.grid.obj.setSkin(dhx_skin);
layout.A.grid.obj.enableMultiline(true);
layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
layout.A.grid.obj.setNumberFormat("0.00",2);
layout.A.grid.obj.setNumberFormat("0.00",3);
layout.A.grid.obj.setNumberFormat("0.00",4);
layout.A.grid.obj.setNumberFormat("0.00",5);
layout.A.grid.obj.setNumberFormat("0.00",6);
layout.A.grid.obj.attachFooter("<strong>Total</strong>,,,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>,");	
layout.A.grid.obj.init();

// set filter input names
layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[0].name="date_order_start";
layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[1].name="date_order_end";
layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="date_order_payment_start";
layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[1].name="date_order_payment_end";
layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="invoice_no";
layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="subtotal";
layout.A.grid.obj.hdr.rows[2].cells[4].getElementsByTagName("INPUT")[0].name="shipping";
layout.A.grid.obj.hdr.rows[2].cells[5].getElementsByTagName("INPUT")[0].name="cost";
layout.A.grid.obj.hdr.rows[2].cells[6].getElementsByTagName("INPUT")[0].name="taxes";
layout.A.grid.obj.hdr.rows[2].cells[7].getElementsByTagName("INPUT")[0].name="discounts";
layout.A.grid.obj.hdr.rows[2].cells[8].getElementsByTagName("SELECT")[0].name="payment_method";

layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_sales_report').'";

// load the initial grid
load_grid(layout.A.grid.obj);

layout.A.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
	load_grid(this, ind, dir);
	
	return false;
});

layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	window.open("'.CController::createUrl('orders/').'?id_orders="+rId,"_blank");
});


layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(layout.A.grid.obj.entBox.id+" .objbox",1);
}); 

layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(layout.A.grid.obj.entBox.id,0);
});

',CClientScript::POS_END);
?>