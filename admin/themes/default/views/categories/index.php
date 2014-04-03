<?php
// register css/script files and scripts
Html::include_timepicker();
Html::include_ckeditor();

// Client Script
$cs=Yii::app()->clientScript;  

// Verify if id to go directly into the product id
if(isset($_GET['task'])){
	$task = $_GET['task'];
}else{
	$task = '';
}

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/categories/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?gestion_des_categories.html");

// add tabs to our main content 
var tabs = new Object();
tabs.totalCount=0;
tabs.totalOpened=[];

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
	if (typeof(window["Tab"+id+"_obj"])=="object"){
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
}


tabs.obj = templateLayout_B.attachTabbar();
tabs.obj.setImagePath(dhx_globalImgPath);
tabs.obj.addTab("list","'.Yii::t('global','LABEL_TAB_LIST').'","70px");
tabs.obj.addTab("new","<img src=\'"+dhx_globalImgPath+"toolbar/new.gif\' />","30px");
tabs.obj.enableTabCloseButton(true);
tabs.obj.setTabActive("list");

tabs.obj.attachEvent("onTabClose", function(id){
	tabs.close_tab(this, id, true);
	
	//any custom code	
	return false;
// when selecting a tab	
});

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
					//if (confirm("'.Yii::t('global','LABEL_ALERT_TAB_CLOSE').'")) {
						tabs.close_tab(obj, tab_id, true);
					//}
					break;
				case "close_other":
					//if (confirm("'.Yii::t('global','LABEL_ALERT_TAB_CLOSE_OTHER').'")) {				
						$.each(obj._tabs, function(key, value) {
							if (key != "list" && key != "new" && key != tab_id) { 
								tabs.close_tab(obj, key, false);
							} 
						});
						
						if (obj.getActiveTab() != tab_id) { 
							obj.setTabActive(tab_id);
						}
					//}				
					break;
				case "close_all":
					//if (confirm("'.Yii::t('global','LABEL_ALERT_TAB_CLOSE_ALL').'")) {
						$.each(obj._tabs, function(key, value) {
							if (key != "list" && key != "new") {
								tabs.close_tab(obj, key, true);
							}
						});						
					//}						
					break;	
			}
        });
	}
	
	return false;
});

// add a context menu that we will use for our tabs
tabs.contextmenu = new Object();
tabs.contextmenu.obj = new dhtmlXMenuObject();
tabs.contextmenu.obj.setIconsPath(dhx_globalImgPath);
tabs.contextmenu.obj.renderAsContextMenu();

// first tab called "list", we add a toolbar
tabs.list = new Object();
tabs.list.obj = tabs.obj.cells("list");

tabs.list.toolbar = new Object();
tabs.list.toolbar.obj = tabs.list.obj.attachToolbar();
tabs.list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
tabs.list.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
tabs.list.toolbar.obj.addSeparator("sep1", null);
tabs.list.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

tabs.list.toolbar.obj.attachEvent("onClick", function(id){
	var obj = this;
	var title = "'.Yii::t('views/categories/index','LABEL_TAB_LIST').'";
	
	switch (id) {
		case "delete":			
			var checked = tabs.list.treegrid.obj.getCheckedRows(0);
			
			if (checked.length) {
				if (confirm("'.Yii::t('views/categories/index','LABEL_ALERT_DELETE').'")) {
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
							}
						});						
					} else {
						alert("'.Yii::t('views/categories/index','LABEL_ALERT_DELETE_CLOSE').'");	
					}
				}
			} else {
				alert("'.Yii::t('views/categories/index','LABEL_ALERT_NO_CHECKED').'");	
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
tabs.list.treegrid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('views/categories/index','LABEL_LIST_GRID_DISPLAY_FROM').','.Yii::t('views/categories/index','LABEL_LIST_GRID_DISPLAY_TO').','.Yii::t('views/categories/index','LABEL_LIST_GRID_FEATURE').','.Yii::t('global','LABEL_ENABLED').'",null,["text-align:center;",,,,"text-align:center;","text-align:center"]);
tabs.list.treegrid.obj.setInitWidthsP("4,48,17,17,7,7");
tabs.list.treegrid.obj.enableResizing("false,true,true,true,false,false");
tabs.list.treegrid.obj.setColAlign("center,left,left,left,center,center");
tabs.list.treegrid.obj.setColTypes("ch,tree,ro,ro,ch,ch");
tabs.list.treegrid.obj.setColSorting("na,na,na,na,na,na");
tabs.list.treegrid.obj.enableDragAndDrop(true);
tabs.list.treegrid.obj.setDragBehavior("complex");
tabs.list.treegrid.obj.enableTreeGridLines(true);
tabs.list.treegrid.obj.enableMultiselect(false);
tabs.list.treegrid.obj.enableRowsHover(true,dhx_rowhover_pointer);
tabs.list.treegrid.obj.init();
tabs.list.treegrid.obj.setSkin(dhx_skin);
tabs.list.treegrid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list').'";	
load_grid(tabs.list.treegrid.obj);

tabs.list.treegrid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	tabs.load_tab(tabs.obj,rId,this.cellById(rId,1).getValue());
});

tabs.list.treegrid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	var obj = this;
	
	for (var i=0; i<this._dragged.length; i++) {
		var id = this._dragged[i].idd;
		var id_parent = this.getParentId(id);
		var index=this._h2.get[dId] ? this._h2.get[dId].index:0;
		
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

tabs.list.treegrid.obj.attachEvent("onCheck", function(rId,cInd,state){
	var obj = this;
	
	switch (cInd) {
		// checkbox
		case 0:
			do_check(rId,state);	
			break;		
		// featured
		case 4:			
			$.ajax({
				url: "'.CController::createUrl('toggle_featured').'",
				type: "POST",
				data: { "id":rId,"state":state }
			});		
			break;
		// status
		case 5:
			$.ajax({
				url: "'.CController::createUrl('toggle_active').'",
				type: "POST",
				data: { "id":rId,"state":state }
			});	
			if(state){
				tabs.list.treegrid.obj.setRowTextStyle(rId, "color: #000000");	
			}else{
				tabs.list.treegrid.obj.setRowTextStyle(rId, "color: #B5B5B5;");		
			}			
			break;
	}
});

function do_check(rId,state){
	var obj = tabs.list.treegrid.obj;
	
	var id_parent = obj.getParentId(rId);		
	var childs = obj.getSubItems(rId);	
	if (childs) {
		childs = childs.split(",");
		
		for (var i=0;i<childs.length;i++) {
			if (childs[i]) obj.cells(childs[i],0).setChecked(state);
			
			if (obj.getSubItems(childs[i])) do_check(childs[i],state);
		}
	} else if (id_parent && obj.cells(id_parent,0).isChecked()) { 
		
		obj.cells(id_parent,0).setChecked(false);
	}	
}

tabs.list.treegrid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

'.($task=='new'?'tabs.load_tab(tabs.obj,"new"+new Date().getTime(),"'.Yii::t('global','LABEL_NEW').'");':'').'


',CClientScript::POS_END);
?>