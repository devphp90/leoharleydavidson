<?php
Html::include_ckeditor();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/cmspages/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?pages_cms.html");

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

tabs.load_tab = function (tabObj, id, name){	
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
			tabObj.setTabActive(tabs.totalOpened[id]); 
			return false;
		} else {
			// store id and container into array of opened tabs
			tabs.totalOpened[id] = tab_id;	
		}
	}
	
	tabObj.addTab(tab_id,name,"*");		
	tabObj.setHrefMode("ajax-html");	
	tabObj.setContentHref(tab_id,"'.CController::createUrl('edit').'?container="+tab_id+"&id="+id);
	tabObj.setTabActive(tab_id);
	
	tabs.totalCount++;
}

tabs.close_tab = function(tabObj, id, select_list) {
	if (typeof(window["Tab"+id].has_modifications) == "function" && window["Tab"+id].has_modifications() && !confirm("'.Yii::t('global','LABEL_CONFIRM_CONTINUE_WITHOUT_SAVING').'")) return false;	
	
	// unset variable object 
	window["Tab"+id] = null;
	
	$(window).off("resize.Tab"+id);
	
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
	tabs.close_tab(this, id, true);
	//any custom code	
	return false;
});

// when selecting a tab	
tabs.obj.attachEvent("onSelect",function(id,last_id){
	switch (id) {
		// when clicking on new
		case "new":			
			tabs.load_tab(this);
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
	// get current tab id
	var tab_id = $(e.target).parents("div[tab_id]").attr("tab_id");
	
	// activate when we right click on new tabs or an existing editing tab
	if (tab_id && tab_id != "list" && tab_id != "'.Yii::t('global','LABEL_NEW').'") {			
		tabs.contextmenu.obj.clearAll();
		tabs.contextmenu.obj.detachAllEvents();
		tabs.contextmenu.obj.addNewChild(tabs.contextmenu.obj.topId, 0, "close", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE').'", false, null);
		tabs.contextmenu.obj.addNewChild(tabs.contextmenu.obj.topId, 1, "close_other", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE_OTHER').'", false, null);
		tabs.contextmenu.obj.addNewChild(tabs.contextmenu.obj.topId, 2, "close_all", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE_ALL').'", false, null);		
		tabs.contextmenu.obj.showContextMenu(e.pageX, e.pageY);
		
		tabs.contextmenu.obj.attachEvent("onClick", function(id, zoneId, casState){
        	switch (id) {
				case "close":
					tabs.close_tab(tabs.obj, tab_id, true);
					break;
				case "close_other":
					$.each(tabs.obj._tabs, function(key, value) {
						if (key != "list" && key != "new" && key != tab_id) { 
							tabs.close_tab(tabs.obj, key, false);
						} 
					});
					
					if (tabs.obj.getActiveTab() != tab_id) { 
						tabs.obj.setTabActive(tab_id);
					}
					break;
				case "close_all":
					$.each(tabs.obj._tabs, function(key, value) {
						if (key != "list" && key != "new") {
							tabs.close_tab(tabs.obj, key, true);
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
	var title = "'.Yii::t('views/cmspages/index','LABEL_TITLE_CMSPAGE').'";
	
	switch (id) {
		case "delete":			
			var checked = tabs.list.treegrid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/cmspages/index','LABEL_ALERT_DELETE').'")) {
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
								load_grid(tabs.list.treegrid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
								//alert(data);
							}
						});						
					} else {
						alert("'.Yii::t('views/cmspages/index','LABEL_ALERT_DELETE_CLOSE').'");	
					}
				}
			} else {
				alert("'.Yii::t('views/cmspages/index','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;		
		case "print":
			printGridPopup(tabs.list.treegrid.obj,"printview",[0],title);
			break;			
	}
});

// first tab called "list", we add a grid
tabs.list.treegrid = new Object();
tabs.list.treegrid.obj = tabs.list.obj.attachGrid();
tabs.list.treegrid.obj.selMultiRows = true;
tabs.list.treegrid.obj.setImagePath(dhx_globalImgPath);	
tabs.list.treegrid.obj.setHeader("#master_checkbox,'.Yii::t('controllers/CmspagesController','LABEL_TITLE').','.Yii::t('global','LABEL_ALIAS').','.Yii::t('views/cmspages/index','LABEL_DISPLAY_MENU_WICH').','.Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU').','.Yii::t('global','LABEL_ENABLED').'",null,["text-align:center",,,"text-align:left","text-align:center","text-align:center"]);

tabs.list.treegrid.obj.setInitWidthsP("5,30,30,17,9,9");
tabs.list.treegrid.obj.enableResizing("false,true,true,true,false,false");
tabs.list.treegrid.obj.setColAlign("center,left,left,left,center,center");
tabs.list.treegrid.obj.setColTypes("ch,tree,ro,ro,ch,ch");
tabs.list.treegrid.obj.setColSorting("na,na,na,na,na,na");
tabs.list.treegrid.obj.enableDragAndDrop(true);
tabs.list.treegrid.obj.setDragBehavior("complex");
tabs.list.treegrid.obj.enableTreeGridLines(true);
tabs.list.treegrid.obj.enableMultiselect(false);
tabs.list.treegrid.obj.init();
tabs.list.treegrid.obj.setSkin(dhx_skin);
tabs.list.treegrid.obj.enableRowsHover(true,dhx_rowhover_pointer);

tabs.list.treegrid.obj.enableSmartRendering(true);
// we create a variable to store the default url used to get our grid data, so we can reuse it later
tabs.list.treegrid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list').'";
// load the initial grid
load_grid(tabs.list.treegrid.obj);

tabs.list.treegrid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	tabs.load_tab(tabs.obj,rId,this.cellById(rId,1).getValue());
});

tabs.list.treegrid.obj.attachEvent("onCheck", function(rId,cInd,state){
	var obj = this;
	
	switch (cInd) {
		// checkbox
		case 4:
			$.ajax({
			url: "'.CController::createUrl('toggle_display').'",
			type: "POST",
			data: { "id":rId,"display":state },							
			success: function(data){													
				load_grid(tabs.list.treegrid.obj);
			}
			});			
			break;		
		// status
		case 5:			
			$.ajax({
			url: "'.CController::createUrl('toggle_active').'",
			type: "POST",
			data: { "id":rId,"active":state }
			});	
			if(state){
				tabs.list.treegrid.obj.setRowTextStyle(rId, "color: #000000");	
			}else{
				tabs.list.treegrid.obj.setRowTextStyle(rId, "color: #B5B5B5;");		
			}	
			break;
		}
});

tabs.list.treegrid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	var obj = this;
	for (var i=0; i<this._dragged.length; i++) {
		
		var id = this._dragged[i].idd;
		var id_parent = this.getParentId(id);
		var index=this._h2.get[dId].index;			
		$.ajax({
			url: "'.CController::createUrl('save_sort_order').'",
			type: "POST",
			data: { "id":id,"id_parent":id_parent,"index":index },
			success: function(data){
				if (data) {
					alert(data);
					load_grid(obj);
				}
			}
		});
	}
});

tabs.list.treegrid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

tabs.list.treegrid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

tabs.list.treegrid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});
',CClientScript::POS_END);
?>