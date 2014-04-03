<?php
// register css/script files and scripts
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/reports/customer_report','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?rapports_statistiques.html");

var custom_field_exist = "'.$count.'";

var dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(false);
dhxWins.attachViewportTo(templateLayout_B);
dhxWins.setImagePath(dhx_globalImgPath);

var wins2 = new Object();

var layout = new Object();


if(custom_field_exist>0){
	layout.obj = templateLayout_B.attachLayout("2E");
	layout.A = new Object();
	layout.A.obj = layout.obj.cells("a");
	layout.A.obj.hideHeader();
	layout.A.obj.setHeight(75);
	
	$.ajax({
		url: "'.CController::createUrl('customer_report_options').'",
		type: "POST",
		success: function(data){
			layout.A.obj.attachHTMLString(data);		
		}
	});	
	layout.B = new Object();
	layout.B.obj = layout.obj.cells("b");
}else{
	layout.obj = templateLayout_B.attachLayout("1C");
	layout.B = new Object();
	layout.B.obj = layout.obj.cells("a");	
}


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
layout.B.grid.obj.setHeader("'.Yii::t('views/customers/index','LABEL_LIST_GRID_NAME').','.Yii::t('views/customers/index','LABEL_LIST_GRID_EMAIL').','.Yii::t('views/customers/index','LABEL_LIST_GRID_CUSTOMER_TYPE').','.Yii::t('views/customers/index','LABEL_LIST_GRID_TELEPHONE').','.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').','.Yii::t('views/customers/index','LABEL_LIST_GRID_ZIP').','.Yii::t('views/customers/index','LABEL_LIST_GRID_CUSTOMER_SINCE').'");
layout.B.grid.obj.attachHeader("#text_filter,#text_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter,#datetime_filter_custom");

// custom select filter input
layout.B.grid.obj._in_header_datetime_filter_custom=function(tag,index,data){   // the name contains "_in_header_"+shortcut_name
	var obj = this;

	tag.innerHTML="<input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text;margin-bottom:2px;\" class=\"hdr_custom_filters\" datetimepicker=\"datetimepicker\" /><br /><input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text\" class=\"hdr_custom_filters\" datetimepicker=\"datetimepicker\" />";	
	
	tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.firstChild.onchange=function(){		
		// date start
		obj.filterBy(7,function(data){		
			var datetime = $(tag.firstChild).val();
			if (datetime.length && datetime.search(/([0-9]{4})-([0-9]{2})-([0-9]{2})\s([0-9]{2}):([0-9]{2})(:[0-9]{2})?/) != -1) {
				datetime = datetime.split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_start = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1)).getTime();
			
				var datetime = data.toString().split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_row = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1),(time[2]*1)).getTime();
	
				return timestamp_row >= timestamp_start;
			} else return true;
		});	
		
		// date end
		obj.filterBy(7,function(data){		
			var datetime = $(tag.lastChild).val();
			if (datetime.length && datetime.search(/([0-9]{4})-([0-9]{2})-([0-9]{2})\s([0-9]{2}):([0-9]{2})(:[0-9]{2})?/) != -1) {
				datetime = datetime.split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_end = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1)).getTime();
			
				var datetime = data.toString().split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_row = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1),(time[2]*1)).getTime();
	
				return timestamp_row <= timestamp_end;
			} else return true;
		},true);			
	}
	
	tag.lastChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.lastChild.onchange=function(){
		// date start
		obj.filterBy(7,function(data){		
			var datetime = $(tag.firstChild).val();
			if (datetime.length && datetime.search(/([0-9]{4})-([0-9]{2})-([0-9]{2})\s([0-9]{2}):([0-9]{2})(:[0-9]{2})?/) != -1) {
				datetime = datetime.split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_start = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1)).getTime();
			
				var datetime = data.toString().split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_row = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1),(time[2]*1)).getTime();
	
				return timestamp_row >= timestamp_start;
			} else return true;
		});	
		
		// date end
		obj.filterBy(1,function(data){		
			var datetime = $(tag.lastChild).val();
			if (datetime.length && datetime.search(/([0-9]{4})-([0-9]{2})-([0-9]{2})\s([0-9]{2}):([0-9]{2})(:[0-9]{2})?/) != -1) {
				datetime = datetime.split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_end = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1)).getTime();
			
				var datetime = data.toString().split(" ");
				var date = datetime[0].split("-");
				var time = datetime[1].split(":");					
				var timestamp_row = new Date(date[0],(date[1]*1),(date[2]*1),(time[0]*1),(time[1]*1),(time[2]*1)).getTime();
	
				return timestamp_row <= timestamp_end;
			} else return true;
		},true);	
	}	
}			


layout.B.grid.obj.setInitWidthsP("*,*,*,*,*,*,*,*");
layout.B.grid.obj.setColAlign("left,left,left,left,left,left,left,left");
layout.B.grid.obj.enableResizing("false,false,false,false,false,false,false,false");
layout.B.grid.obj.setColSorting("str,str,str,str,str,str,int,str");
layout.B.grid.obj.setSkin(dhx_skin);
layout.B.grid.obj.enableMultiline(true);
layout.B.grid.obj.enableRowsHover(true,dhx_rowhover);
layout.B.grid.obj.init();

// set filter input names
layout.B.grid.obj.hdr.rows[2].cells[7].getElementsByTagName("INPUT")[0].name="date_order_start";
layout.B.grid.obj.hdr.rows[2].cells[7].getElementsByTagName("INPUT")[1].name="date_order_end";

layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_customer_report').'";

layout.B.load = function(callback)
{
	var obj = layout.B.grid.obj;	
	var custom_fields=[];
	
	$("#custom_fields input").each(function(){
		var id_custom_field = $(this).prop("name");
		id_custom_field = id_custom_field.replace("custom_fields[","");
		id_custom_field = id_custom_field.replace("][]","");
		
		custom_fields.push("custom_fields["+id_custom_field+"][]="+$(this).val());
	});
	
	obj.clearAll();
	obj.loadXML(layout.B.grid.obj.xmlOrigFileUrl+"?"+custom_fields.join("&"),callback);		
}

layout.B.load();
',CClientScript::POS_END);
?>