<?php
// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/newslettersubscription/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?gestion_des_inscriptions_a_lin.html");

// add a layout to the Main Template Layout B 
var layout = new Object();

layout.obj = templateLayout_B.attachLayout("1C");
layout.A = new Object();
layout.A.obj = layout.obj.cells("a");
layout.A.obj.hideHeader();

layout.A.grid = new Object();

layout.A.toolbar = new Object();

layout.A.toolbar.obj = layout.A.obj.attachToolbar();
layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);

layout.A.toolbar.load = function(current_id){
	var title = "'.Yii::t('views/newslettersubscription/index','LABEL_TITLE').'";
	var obj = layout.A.toolbar.obj;
  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
	obj.addSeparator("sep1", null);
	obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);		

	obj.attachEvent("onClick",function(id){
		switch (id) {							
			case "delete":
				var checked = layout.A.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/newslettersubscription/index','LABEL_ALERT_DELETE').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){						
								load_grid(layout.A.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/newslettersubscription/index','LABEL_ALERT_NO_CHECKED').'");		
				}
				break;	
				case "export_pdf":
					printGridPopup(layout.A.grid.obj,"pdf",[0],title);
					break;	
				case "export_excel":
					printGridPopup(layout.A.grid.obj,"excel",[0],title);
					break;		
				case "print":
					printGridPopup(layout.A.grid.obj,"printview",[0],title);
					break;			
				}
	});	
};	
layout.A.toolbar.load();

layout.A.grid.obj = layout.A.obj.attachGrid();
layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/newslettersubscription/index','LABEL_EMAIL').','.Yii::t('views/newslettersubscription/index','LABEL_DATE_CREATED').','.Yii::t('views/newslettersubscription/index','LABEL_LANGUAGE').'",null,["text-align:center"]);
layout.A.grid.obj.attachHeader("&nbsp;,#text_filter_custom,&nbsp;,#select_filter_custom");

// custom text filter input
layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

// custom select filter input
layout.A.grid.obj._in_header_select_filter_custom=select_filter_custom_language;

layout.A.grid.obj.setInitWidths("40,*,200,100");
layout.A.grid.obj.setColAlign("center,left,left,left");
layout.A.grid.obj.setColSorting("na,na,na,na");
layout.A.grid.obj.setSkin(dhx_skin);
layout.A.grid.obj.enableDragAndDrop(false);
layout.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
//layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
layout.A.obj.attachStatusBar().setText("<div id=\'recinfoArea\'></div>");
layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
layout.A.grid.obj.enablePaging(true, 100, 3, "recinfoArea");
layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
layout.A.grid.obj.i18n.paging={
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
layout.A.grid.obj.init();

// set filter input names
layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="email";
layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("SELECT")[0].name="language_code";

// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list').'";

// load the initial grid
load_grid(layout.A.grid.obj);

layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

',CClientScript::POS_END);
?>