<?php
// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_SCORM_PARTICIPANTS_COURSE').'");
$("#how-to-link a").prop("title","/index.html?rapports_statistiques.html");

var dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(false);
dhxWins.attachViewportTo(templateLayout_B);
dhxWins.setImagePath(dhx_globalImgPath);

var wins2 = new Object();

var layout = new Object();
layout.obj = templateLayout_B.attachLayout("2E");

layout.A = new Object();
layout.A.obj = layout.obj.cells("a");
layout.A.obj.hideHeader();
layout.A.obj.setHeight(70);

$.ajax({
	url: "'.CController::createUrl('scorm_report_options').'",
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
	var title = "";
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
layout.B.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('views/users/index','LABEL_EMAIL').','.Yii::t('views/reports/scorm_report','LABEL_ATTEMPT').','.Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS').','.Yii::t('views/reports/scorm_report','LABEL_SCORE').'",null,[,,"text-align:center","text-align:center","text-align:center"]);
layout.B.grid.obj.attachHeader("#text_filter,#text_filter,#numeric_filter,#select_filter,#numeric_filter");
layout.B.grid.obj.setInitWidthsP("30,20,15,20,15");
layout.B.grid.obj.setColAlign("left,left,center,center,center");
layout.B.grid.obj.enableResizing("false,false,false,false,false");
layout.B.grid.obj.setColSorting("str,str,int,str,int");
layout.B.grid.obj.setSkin(dhx_skin);
layout.B.grid.obj.enableMultiline(true);
layout.B.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
layout.B.grid.obj.init();
layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_scorm_report').'";

layout.B.load = function(id,callback)
{
	var obj = layout.B.grid.obj;
	
	obj.clearAll();
	obj.loadXML(layout.B.grid.obj.xmlOrigFileUrl+"?id="+id,callback);			
}

layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	var title = this.cellById(rId,0).getValue()+" - "+this.cellById(rId,1).getValue();
	
	wins2 = new Object();
	wins2.obj = dhxWins.createWindow("loadWindow", 10, 10, 600, 400);
	wins2.obj.setText(title);
	wins2.obj.button("park").hide();
	wins2.obj.keepInViewport(true);
	wins2.obj.setModal(true);

	wins2.toolbar = new Object();
	wins2.toolbar.obj = wins2.obj.attachToolbar();
	wins2.toolbar.obj.setIconsPath(dhx_globalImgPath);		
	wins2.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	wins2.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	wins2.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);	
	
	wins2.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "export_pdf":
				printGridPopup(wins2.grid.obj,"pdf",null,title);
				break;	
			case "export_excel":
				printGridPopup(wins2.grid.obj,"excel",null,title);
				break;		
			case "print":
				printGridPopup(wins2.grid.obj,"printview",null,title);
				break;		
		}
	});	
				
	wins2.grid = new Object()								
	wins2.grid.obj = wins2.obj.attachGrid();
	wins2.grid.obj.setImagePath(dhx_globalImgPath);
	wins2.grid.obj.setHeader("'.Yii::t('global','LABEL_DATE').','.Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS').','.Yii::t('views/reports/scorm_report','LABEL_SCORE').'",null,[,"text-align:center","text-align:center"]);
	wins2.grid.obj.setInitWidthsP("50,25,25");
	wins2.grid.obj.setColAlign("left,center,center");
	wins2.grid.obj.setColSorting("str,str,int");	
	wins2.grid.obj.setSkin(dhx_skin);
	wins2.grid.obj.enableRowsHover(true,dhx_rowhover);
	wins2.grid.obj.init();
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	wins2.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_customer_course_attempt').'";
	
	wins2.grid.load = function(current_id){
		var obj = wins2.grid.obj;
		var id_course = $("#id_course").val();
		
		obj.clearAll();
		obj.loadXML(wins2.grid.obj.xmlOrigFileUrl+"?id="+current_id+"&id_course="+id_course);	
	};	
	
	wins2.grid.load(rId);
});

function printGridPopup(obj,method,omit,title,desc){
	if (!obj.getAllRowIds().length) { 
		alert("'.Yii::t('components/Html','LABEL_ALERT_NO_RECORD').'"); 
		return false;
	}
	
	title = title ? title:"";
	desc = desc ? desc:"";		
	
	var winsPrint = new Object();	
	
	winsPrint.obj = dhxWins.createWindow("printWindow", 0, 0, 650, 280);
	winsPrint.obj.setText("'.Yii::t('components/Html','LABEL_TITLE_WINDOW').'");
	winsPrint.obj.button("minmax1").hide();
	winsPrint.obj.button("park").hide();
	winsPrint.obj.keepInViewport(true);
	if (wins2 && wins2.obj) wins2.obj.setModal(false);
	
	winsPrint.obj.setModal(true);
	winsPrint.obj.center();
	
	winsPrint.obj.attachEvent("onClose",function(){
		if (wins2 && wins2.obj) {
			this.setModal(false);
			wins2.obj.setModal(true);
		}
		
		return true;
	});
	
	winsPrint.toolbar = new Object();
	winsPrint.toolbar.obj = winsPrint.obj.attachToolbar();
	winsPrint.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	
	switch (method) {
		case "pdf":		
			winsPrint.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'","toolbar/pdf.png", null);
			break;
		case "excel":
			winsPrint.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'","toolbar/excel.png", null);
			break;
		case "printview":
			winsPrint.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_PRINT').'","toolbar/print.png", null);	
			break;
	}			
	
	winsPrint.toolbar.obj.attachEvent("onClick",function(id){
		switch (id) {
			case "export":
				if (winsPrint.grid.obj.getCheckedRows(0).length) { 								
					if (omit && omit.length) {
						for (col_id in omit) {
							obj.setColumnHidden(omit[col_id],true);
						}
					}
					
					var rows = winsPrint.grid.obj.getAllRowIds();
					
					if (rows) {
						title = $("#"+winsPrint.layout.obj.cont.obj.id+" input[name=\'print_title\']").length ? $("#"+winsPrint.layout.obj.cont.obj.id+" input[name=\'print_title\']").val():"";
						desc = $("#"+winsPrint.layout.obj.cont.obj.id+" textarea[name=\'print_desc\']").length ? $("#"+winsPrint.layout.obj.cont.obj.id+" textarea[name=\'print_desc\']").val():"";
						
						rows = rows.split(",");					
						
						for (var i=0;i<rows.length;++i) {
							var row_id = rows[i];
							var cell = winsPrint.grid.obj.cellById(row_id,0);
						
							if (!cell.isChecked()) {
								obj.setColumnHidden(row_id,true);
							}
						}					
					
						switch (method) {
							case "pdf":		
								obj.toPDF(export_pdf_url(),"gray",null,null,null,title,desc);
								break;
							case "excel":
								obj.toExcel(export_excel_url(),"gray",null,null,null,title,desc);
								break;
							case "printview":
								var html_output="";
								
								if (title.length) {
									html_output += "<h1>"+title+"</h1>";	
								}
								
								if (desc.length) {
									html_output += "<div><em><pre style=\'padding:0;margin:0;\'>"+desc+"</pre></em></div><br />";	
								}
								
								obj.printView(html_output);
								break;
						}														
						
						for (var i=0;i<rows.length;++i) {
							var row_id = rows[i];
							var cell = winsPrint.grid.obj.cellById(row_id,0);
							
							if (!cell.isChecked()) {
								obj.setColumnHidden(row_id,false);
							}
						}								
					}
					
					if (omit && omit.length) {
						for (col_id in omit) {
							obj.setColumnHidden(omit[col_id],false);
						}
					}		
					
					winsPrint.obj.close();		
				} else {
					alert("'.Yii::t('components/Html','LABEL_ALERT_EXPORT_PDF_EXCEL_PRINT').'");	
				}
				break;	
		}
	});				
	
	winsPrint.layout = new Object();
	winsPrint.layout.obj = winsPrint.obj.attachLayout("2U");
	
	winsPrint.layout.A = new Object();
	winsPrint.layout.A.obj = winsPrint.layout.obj.cells("a");
	winsPrint.layout.A.obj.setWidth(350);
	winsPrint.layout.A.obj.hideHeader();
	winsPrint.layout.A.obj.attachHTMLString(\'<div style="width:100%; height:100%; overflow:auto;"><div style="padding:10px;"><div><strong>'.Yii::t('components/Html','LABEL_TITLE').'</strong><br /><input type="text" name="print_title" value="\'+title+\'" style="width: 100%;" /></div><div><strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong><br /><textarea name="print_desc" rows="4" style="width: 100%;">\'+desc+\'</textarea></div></div></div>\');		
	
	winsPrint.layout.B = new Object();
	winsPrint.layout.B.obj = winsPrint.layout.obj.cells("b");
	winsPrint.layout.B.obj.hideHeader();		
	
	winsPrint.grid = new Object(); 
	winsPrint.grid.obj = winsPrint.layout.B.obj.attachGrid();
	winsPrint.grid.obj.setImagePath(dhx_globalImgPath);
	winsPrint.grid.obj.setHeader("#master_checkbox,'.Yii::t('components/Html','LABEL_COLUMN').'",null,["text-align:center"]);
	winsPrint.grid.obj.setInitWidthsP("15,85");
	winsPrint.grid.obj.setColAlign("center,left");
	winsPrint.grid.obj.setColTypes("ch,ro");
	winsPrint.grid.obj.setColSorting("na,na");
	winsPrint.grid.obj.enableResizing("false,false");
	winsPrint.grid.obj.enableRowsHover(true,dhx_rowhover);
	winsPrint.grid.obj.init();
	
	var columnCount = obj.getColumnsNum();
	for (var i=0;i<columnCount;i++){
		if ((!omit || omit && omit.length && $.inArray(i, omit) == -1) && obj.isColumnHidden(i) == false) {
			var columnName = obj.getColumnLabel(i);		
			
			winsPrint.grid.obj.addRow(i,[1,columnName]);			
		}
	}								
}	   
',CClientScript::POS_END);
?>