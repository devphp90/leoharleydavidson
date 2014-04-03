<?php
// register css/script files and scripts
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/rebatecoupon/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?gestion_rabais_et_coupons.html");

// add tabs to our main content 
var tabs = new Object();
tabs.totalCount=0;
tabs.totalOpened=[];

tabs.obj = templateLayout_B.attachTabbar();
tabs.obj.setImagePath(dhx_globalImgPath);
tabs.obj.addTab("list","'.Yii::t('global','LABEL_TAB_LIST').'","70px");
tabs.obj.addTab("new","<img src=\'"+dhx_globalImgPath+"toolbar/new.gif\' />","30px");
tabs.obj.enableTabCloseButton(true);
tabs.obj.setTabActive("list");

tabs.load_tab = function (id, name){	
	var obj = tabs.obj;

	id=id?id:"";
	
	if (tabs.totalCount >= 5) { alert("'.Yii::t('global','LABEL_ALERT_TAB_LIMIT_OPENED').'"); return false; }
	
	// tab id 
	// new 
	if (!id) { 	
		var tab_id = "new"+new Date().getTime();
		var name = "'.Yii::t('global','LABEL_NEW').'";
	// edit
	} else {
		var tab_id = "edit"+id;
		
		// check if tab is opened by id		
		if (tabs.totalOpened[id]) {
			// set that tab active
			obj.setTabActive(tabs.totalOpened[id]); 
			return false;
		} else {
			// store id and container into array of opened tabs
			tabs.totalOpened[id] = tab_id;	
		}
	}
	
	obj.addTab(tab_id,name,"*");		
	obj.setHrefMode("ajax-html");	
	obj.setContentHref(tab_id,"'.CController::createUrl('edit').'?container="+tab_id+"&id="+id);
	obj.setTabActive(tab_id);
	
	tabs.totalCount++;
}

tabs.close_tab = function(tabObj, id, select_list) {
	if (typeof(window["Tab"+id+"_obj"].has_modifications) == "function" && window["Tab"+id+"_obj"].has_modifications() && !confirm("'.Yii::t('global','LABEL_CONFIRM_CONTINUE_WITHOUT_SAVING').'")) return false;	
	
	// unset variable object 
	window["Tab"+id] = null;
	window["Tab"+id+"_obj"] = null;	
	
	$(window).off("resize.Tab"+id+"_obj");
	
	// remove tab
	tabObj.removeTab(id,false);
	tabs.totalCount--;			
	
	// when closing a tab always go back to the list
	if (select_list) {
		tabObj.setTabActive("list");	
	}
	
	// loop through array of opened tabs and remove it from the list.
	for (tab in tabs.totalOpened) {
		if (tabs.totalOpened[tab] == id) {
			tabs.totalOpened[tab] = 0;
		}		
	}
}

tabs.obj.attachEvent("onTabClose", function(id){
	tabs.close_tab(tabs.obj, id, true);
	
	//any custom code	
	return false;
// when selecting a tab	
});

tabs.obj.attachEvent("onSelect",function(id,last_id){
	switch (id) {
		// when clicking on new
		case "new":			
			tabs.load_tab();
			return false;
			break;	
	}
	
	return true;
});	

// add a context menu that we will use for our tabs
tabs.contextmenu = new Object();
tabs.contextmenu.obj = new dhtmlXMenuObject();
tabs.contextmenu.obj.setIconsPath(dhx_globalImgPath);
tabs.contextmenu.obj.renderAsContextMenu();

// bind contextmenu event to tabs (right click)
$(tabs.obj._tabZone).bind("contextmenu",function(e) {	
	var obj = tabs.obj;

	// get current tab id
	var tab_id = $(e.target).parents("div[tab_id]").attr("tab_id");
	
	// activate when we right click on new tabs or an existing editing tab
	if (tab_id && tab_id != "list" && tab_id != "new") {	
		var objMenu = tabs.contextmenu.obj;
			
		objMenu.clearAll();
		objMenu.detachAllEvents();
		objMenu.addNewChild(objMenu.topId, 0, "close", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE').'", false, null);
		objMenu.addNewChild(objMenu.topId, 1, "close_other", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE_OTHER').'", false, null);
		objMenu.addNewChild(objMenu.topId, 2, "close_all", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE_ALL').'", false, null);		
		objMenu.showContextMenu(e.pageX, e.pageY);
		
		objMenu.attachEvent("onClick", function(id, zoneId, casState){
        	switch (id) {
				case "close":
					tabs.close_tab(obj, tab_id, true);
					break;
				case "close_other":
					$.each(obj._tabs, function(key, value) {
						if (key != "list" && key != "new" && key != tab_id) { 
							tabs.close_tab(obj, key, false);
						} 
					});
					
					if (obj.getActiveTab() != tab_id) { 
						obj.setTabActive(tab_id);
					}
					break;
				case "close_all":
					$.each(obj._tabs, function(key, value) {
						if (key != "list" && key != "new") {
							tabs.close_tab(obj, key, true);
						}
					});						
					break;	
			}
        });
	}
	
	return false;
});

// first tab called "list", we add a toolbar
tabs.list = new Object();
tabs.list.obj = tabs.obj.cells("list");

tabs.list.toolbar = new Object();
tabs.list.toolbar.obj = tabs.list.obj.attachToolbar();
tabs.list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
tabs.list.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
tabs.list.toolbar.obj.addSeparator("sep1", null);
tabs.list.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
tabs.list.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
tabs.list.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

tabs.list.toolbar.obj.attachEvent("onClick", function(id){
	var obj = this;
	var title = "'.Yii::t('views/rebatecoupon/index','LABEL_TAB_LIST').'";
	
	switch (id) {
		case "delete":			
			var checked = tabs.list.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/rebatecoupon/index','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					var opened=0;
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							if (!tabs.totalOpened[checked[i]]) {
								ids.push("ids[]="+checked[i]);									
							} else {
								opened++;	
							}
						}
					}
					
					if (!opened) {					
						$.ajax({
							url: "'.CController::createUrl('delete').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								obj.enableItem(id);	
							},							
							success: function(data){													
								load_grid(tabs.list.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});						
					} else {
						alert("'.Yii::t('views/rebatecoupon/index','LABEL_ALERT_DELETE_CLOSE').'");	
					}
				}
			} else {
				alert("'.Yii::t('views/rebatecoupon/index','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "export_pdf":
			printGridPopup(tabs.list.grid.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup(tabs.list.grid.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup(tabs.list.grid.obj,"printview",[0],title);
			break;		
	}
});

// first tab called "list", we add a grid
tabs.list.grid = new Object();
tabs.list.grid.obj = tabs.list.obj.attachGrid();
tabs.list.grid.obj.setImagePath(dhx_globalImgPath);
tabs.list.grid.obj.setHeader("#master_checkbox,Type,'.Yii::t('global','LABEL_DISCOUNT_TYPE').','.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_CODE').','.Yii::t('global','LABEL_START_DATE').','.Yii::t('global','LABEL_END_DATE').','.Yii::t('global','LABEL_DISCOUNT').','.Yii::t('global','LABEL_ENABLED').'",null,["text-align:center",,,,,,,"text-align:right","text-align:center"]);
tabs.list.grid.obj.attachHeader("&nbsp;,#select_filter_custom_coupon,#select_filter_custom_coupon_type,#text_filter_custom,#text_filter_custom,#datetime_filter_custom,#datetime_filter_custom,#text_filter_custom,#select_filter_custom_enableddisabled");

// custom text filter input
tabs.list.grid.obj._in_header_text_filter_custom=text_filter_custom;

// custom text filter input
tabs.list.grid.obj._in_header_datetime_filter_custom=datetime_filter_custom;

// custom select filter input
tabs.list.grid.obj._in_header_select_filter_custom_enableddisabled=select_filter_custom_enableddisabled;
tabs.list.grid.obj._in_header_select_filter_custom_coupon=select_filter_custom_coupon;
tabs.list.grid.obj._in_header_select_filter_custom_coupon_type=select_filter_custom_coupon_type;

tabs.list.grid.obj.setInitWidthsP("5,10,20,20,15,10,10,5,5");
tabs.list.grid.obj.setColAlign("center,left,left,left,left,left,left,right,center");
tabs.list.grid.obj.enableResizing("false,false,false,false,false,false,false,false,false");
tabs.list.grid.obj.setColSorting("na,server,server,server,server,server,server,server,server");
tabs.list.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
tabs.list.obj.attachStatusBar().setText("<div id=\'recinfoArea\'></div>");
tabs.list.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
tabs.list.grid.obj.enablePaging(true, 100, 3, "recinfoArea");
tabs.list.grid.obj.setPagingSkin("toolbar", dhx_skin);
tabs.list.grid.obj.i18n.paging={
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
tabs.list.grid.obj.init();

// set filter input names
tabs.list.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("SELECT")[0].name="type";
tabs.list.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("SELECT")[0].name="discount_type";
tabs.list.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="name";
tabs.list.grid.obj.hdr.rows[2].cells[4].getElementsByTagName("INPUT")[0].name="coupon_code";
tabs.list.grid.obj.hdr.rows[2].cells[5].getElementsByTagName("INPUT")[0].name="start_date_start";
tabs.list.grid.obj.hdr.rows[2].cells[5].getElementsByTagName("INPUT")[1].name="start_date_end";
tabs.list.grid.obj.hdr.rows[2].cells[6].getElementsByTagName("INPUT")[0].name="end_date_start";
tabs.list.grid.obj.hdr.rows[2].cells[6].getElementsByTagName("INPUT")[1].name="end_date_end";
tabs.list.grid.obj.hdr.rows[2].cells[7].getElementsByTagName("INPUT")[0].name="discount";
tabs.list.grid.obj.hdr.rows[2].cells[8].getElementsByTagName("SELECT")[0].name="active";

tabs.list.grid.obj.setSkin(dhx_skin);
tabs.list.grid.obj.enableSmartRendering(true);
tabs.list.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list').'";	
load_grid(tabs.list.grid.obj);

tabs.list.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	tabs.load_tab(rId,this.cellById(rId,3).getValue());
});

tabs.list.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
	load_grid(this, ind, dir);
	
	return false;
});

tabs.list.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	if (cInd == 8) {
		$.ajax({
			url: "'.CController::createUrl('toggle_active').'",
			type: "POST",
			data: { "id":rId,"active":state }
		});	
		if(state){
			tabs.list.grid.obj.setRowTextStyle(rId, "color: #000000");	
		}else{
			tabs.list.grid.obj.setRowTextStyle(rId, "color: #B5B5B5;");		
		}		
	}
});

tabs.list.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

tabs.list.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});
',CClientScript::POS_END);
?>