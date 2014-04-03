<?php
// register css/script files and scripts
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_PRODUCT_SALES').'");
$("#how-to-link a").prop("title","/index.html?rapports_statistiques.html");

var layout = new Object();
layout.obj = templateLayout_B.attachLayout("2E");

layout.A = new Object();
layout.A.obj = layout.obj.cells("a");
layout.A.obj.hideHeader();
layout.A.obj.setHeight(75);

$.ajax({
	url: "'.CController::createUrl('product_sales_report_options').'",
	type: "POST",
	success: function(data){
		layout.A.obj.attachHTMLString(data);		
	}
});	


layout.B = new Object();
layout.B.obj = layout.obj.cells("b");
layout.B.obj.hideHeader();

layout.B.toolbar = new Object();
layout.B.toolbar.obj = layout.B.obj.attachToolbar();
layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);	
layout.B.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
layout.B.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
layout.B.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

layout.B.toolbar.obj.attachEvent("onClick", function(id){
	var obj = this;
	var title = "'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_PRODUCT_SALES').'";
	var start_date = $("#start_date").val();
	var end_date = $("#end_date").val();
	
	if (start_date.length || end_date.length) {
		title += " - ";
		if (start_date.length) title += "'.Yii::t('global','LABEL_START_DATE').' : "+start_date+" ";
		if (end_date.length) title += "'.Yii::t('global','LABEL_END_DATE').' : "+end_date; 
	}	
	wins2="";
	
	switch (id) {
		case "export_pdf":
			printGridPopup(layout.B.grid.obj,"pdf",null,title);
			break;	
		case "export_excel":
			printGridPopup(layout.B.grid.obj,"excel",null,title);
			break;		
		case "print":
			printGridPopup(layout.B.grid.obj,"printview",null,title);
			break;		
	}
});




layout.B.grid = new Object();
layout.B.grid.obj = layout.B.obj.attachGrid();
layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
layout.B.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').'",null,["text-align:left","text-align:left","text-align:left","text-align:center"]);
layout.B.grid.obj.attachHeader("#text_filter_custom,#text_filter_custom,#text_filter_custom,#text_filter_custom");

// custom select filter input
layout.B.grid.obj._in_header_text_filter_custom=text_filter_custom;

layout.B.grid.obj.setInitWidthsP("40,25,25,10");
layout.B.grid.obj.setColAlign("left,left,left,center");
layout.B.grid.obj.enableResizing("false,false,false,false,false,false,false");
layout.B.grid.obj.setColSorting("str,str,str,str,int");
layout.B.grid.obj.setSkin(dhx_skin);
layout.B.grid.obj.enableMultiline(true);
layout.B.grid.obj.enableRowsHover(true,dhx_rowhover);
//layout.B.grid.obj.attachFooter("<strong>Total</strong>,,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>");	
layout.B.grid.obj.init();

// set filter input names
layout.B.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[0].name="name";
layout.B.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="variant_name";
layout.B.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
layout.B.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="qty";

layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_product_sales_report').'";

// load the initial grid
//load_grid(layout.B.grid.obj);

layout.B.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
	load_grid(this, ind, dir);
	
	return false;
});

layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(layout.B.grid.obj.entBox.id+" .objbox",1);
}); 

layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(layout.B.grid.obj.entBox.id,0);
});

',CClientScript::POS_END);
?>