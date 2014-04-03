<?php
// register css/script files and scripts
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_REBATE_COUPONS').'");
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
layout.A.obj.setHeight(80);

$.ajax({
	url: "'.CController::createUrl('coupon_report_options').'",
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
	var title = layout.A.search_coupon_code.obj.getActualValue();
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
layout.B.grid.obj.setHeader("'.Yii::t('views/orders/index','LABEL_INVOICE_NO').','.Yii::t('views/orders/index','LABEL_ORDER_DATE').','.Yii::t('global','LABEL_NAME').','.Yii::t('views/users/index','LABEL_EMAIL').','.Yii::t('views/orders/index','LABEL_GRAND_TOTAL').','.Yii::t('views/reports/coupon_report','LABEL_DISCOUNT_TOTAL').'",null,[,,,,"text-align:right","text-align:right"]);
layout.B.grid.obj.attachHeader("#numeric_filter,#datetime_filter_custom,#numeric_filter,#text_filter,#text_filter,#numeric_filter");

// custom select filter input
layout.B.grid.obj._in_header_datetime_filter_custom=function(tag,index,data){   // the name contains "_in_header_"+shortcut_name
	var obj = this;

	tag.innerHTML="<input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text;margin-bottom:2px;\" class=\"hdr_custom_filters\" datetimepicker=\"datetimepicker\" /><br /><input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text\" class=\"hdr_custom_filters\" datetimepicker=\"datetimepicker\" />";	
	
	tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.firstChild.onchange=function(){		
		// date start
		obj.filterBy(1,function(data){		
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
	
	tag.lastChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.lastChild.onchange=function(){
		// date start
		obj.filterBy(1,function(data){		
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

layout.B.grid.obj.setInitWidthsP("10,20,25,15,15,15");
layout.B.grid.obj.setColAlign("left,left,left,left,right,right");
layout.B.grid.obj.enableResizing("false,false,false,false,false,false");
layout.B.grid.obj.setColSorting("str,str,int,str,int,int");
layout.B.grid.obj.setSkin(dhx_skin);
layout.B.grid.obj.enableMultiline(true);
layout.B.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
layout.B.grid.obj.setNumberFormat("0.00",4);
layout.B.grid.obj.setNumberFormat("0.00",5);
layout.B.grid.obj.attachFooter("<strong>Total</strong>,,,,<strong>{#stat_total}</strong>,<strong>{#stat_total}</strong>");	
layout.B.grid.obj.init();

// set filter input names
layout.B.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="date_order_start";
layout.B.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[1].name="date_order_end";

layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_coupon_report').'";

layout.B.load = function(id)
{
	var obj = layout.B.grid.obj;
	
	obj.clearAll();
	obj.loadXML(layout.B.grid.obj.xmlOrigFileUrl+"?coupon_code="+id,function(){
		obj.callEvent("onGridReconstructed",[]);
		
		if (!obj.getAllRowIds().length) { 
			alert("'.Yii::t('global','LABEL_GRID_PAGING_NO_RECORDS_FOUND').'"); 
			return false;
		}				
	});				
}

layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	window.open("'.CController::createUrl('orders/').'?id_orders="+rId,"_blank");
});

$(function(){

});
',CClientScript::POS_END);
?>