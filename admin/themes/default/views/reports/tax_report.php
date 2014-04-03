<?php
// register css/script files and scripts
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_TAX').'");
$("#how-to-link a").prop("title","/index.html?rapports_statistiques.html");

var layout = new Object();
layout.obj = templateLayout_B.attachLayout("2E");

layout.A = new Object();
layout.A.obj = layout.obj.cells("a");
layout.A.obj.hideHeader();
layout.A.obj.setHeight(75);

$.ajax({
	url: "'.CController::createUrl('tax_report_options').'",
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
	var title = "'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_TAX').'";
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
layout.B.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_TAX_NUMBER').','.Yii::t('global','LABEL_TOTAL').'",null,[,,"text-align:right"]);

layout.B.grid.obj.setInitWidthsP("65,15,20");
layout.B.grid.obj.setColAlign("left,left,right");
layout.B.grid.obj.enableResizing("false,false,false");
//layout.B.grid.obj.setColSorting("str,str,int,int,int,int,int");
layout.B.grid.obj.setSkin(dhx_skin);
layout.B.grid.obj.enableMultiline(true);
layout.B.grid.obj.enableRowsHover(true,dhx_rowhover);
layout.B.grid.obj.init();

layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_tax_report').'";

// load the initial grid
load_grid(layout.B.grid.obj);

layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(layout.B.grid.obj.entBox.id+" .objbox",1);
}); 

layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(layout.B.grid.obj.entBox.id,0);
});

',CClientScript::POS_END);
?>