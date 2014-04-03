<?php
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/options/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?gestion_des_options_relies_aux.html");

// add a layout to the Main Template Layout B 
var layout = new Object();

layout.obj = templateLayout_B.attachLayout("2U");

layout.A = new Object();
layout.B = new Object();
layout.obj.setAutoSize("a,b");

layout.A.obj = layout.obj.cells("a");
layout.B.obj = layout.obj.cells("b");

layout.A.obj.hideHeader();
layout.B.obj.hideHeader();

layout.A.grid = new Object();
layout.B.grid = new Object();

layout.A.toolbar = new Object();
layout.B.toolbar = new Object();

layout.A.toolbar.obj = layout.A.obj.attachToolbar();
layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);

layout.A.toolbar.load = function(current_id){
	var obj = layout.A.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/options/index','LABEL_BTN_ADD_GROUP').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	 		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				layout.A.toolbar.add(0);
				break;							
			case "delete":
				var checked = layout.A.grid.obj.getCheckedRows(0);
				if (checked) {											
					if (confirm("'.Yii::t('views/options/index','LABEL_ALERT_DELETE_2').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_option_group').'",
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
								layout.B.grid.load();
								enable_grid_toolbar(layout.B.grid.obj,layout.B.toolbar.obj, layout.A.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/options/index','LABEL_ALERT_NO_CHECKED_2').'");		
				}			
				break;
		}
	});	
}

layout.A.toolbar.add = function(current_id){
	
	name = current_id ? "'.Yii::t('global','LABEL_EDIT').' "+layout.A.grid.obj.cellById(current_id,1).getValue() : "'.Yii::t('views/options/index','LABEL_BTN_ADD_GROUP').'";
	var wins = new Object();
	wins.obj = dhxWins.createWindow("addGroupWindow", 0, 0, 700, 300);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	wins.obj.center();		
	var pos = wins.obj.getPosition();
	wins.obj.setPosition(pos[0],10);
				
	wins.toolbar = new Object();
	
	wins.toolbar.load = function(current_id){
		var obj = wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins.toolbar.save(id);
					break;
				case "save_close":
					wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/options/index','LABEL_ALERT_DELETE_2').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_option_group').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								wins.obj.close();
							},
							success: function(data){
								load_grid(layout.A.grid.obj);
								layout.B.grid.load();
								enable_grid_toolbar(layout.B.grid.obj,layout.B.toolbar.obj, layout.A.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	wins.toolbar.obj = wins.obj.attachToolbar();
	wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins.toolbar.load(current_id);
	
	wins.layout = new Object();
	wins.layout.obj = wins.obj.attachLayout("2U");
	wins.layout.A = new Object();
	wins.layout.A.obj = wins.layout.obj.cells("a");
	wins.layout.A.obj.hideHeader();	
	wins.layout.A.obj.setWidth(300);
	wins.layout.A.obj.fixSize(false,false);	
	
	wins.layout.B = new Object();
	wins.layout.B.obj = wins.layout.obj.cells("b");
	wins.layout.B.obj.hideHeader();
	
	wins.layout.A.tabs = new Object();
	wins.layout.A.tabs.obj = wins.layout.A.obj.attachTabbar();
	wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
	wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_group_description').'?id="+current_id);
	
	$.ajax({
		url: "'.CController::createUrl('edit_options_group').'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			wins.layout.B.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		var wins = new Object();
		
		return true;
	});			
	
	wins.highlight_tab_errors = function(tabObj,cssStyle){
		if (!cssStyle) { cssStyle = "color:#FF0000;"; }
	
		$.each(tabObj._tabs, function(key, value) {									
			if ($("*",$("#"+wins.layout.obj.cont.obj.id+" [tab_id=\'"+key+"\']")).hasClass("error")) {
				tabObj.setCustomStyle(key,null,null,cssStyle);
			} else {
				tabObj.setCustomStyle(key,null,null,null);
			}
		});
	};		
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_option_group').'",
			type: "POST",
			data: $("#"+wins.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+wins.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+wins.layout.obj.cont.obj.id+" *").removeClass("error");
					
				wins.highlight_tab_errors(wins.layout.A.tabs.obj);			
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				wins.highlight_tab_errors(wins.layout.A.tabs.obj);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_'.$containerObj.'").removeClass("error_background");
				if (data) {
					
					if (data.errors) {
						$.each(data.errors, function(key, value){
							var id_tag_container = key;
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							
							if (!$(id_tag_selector).hasClass("error")) { 
								$(id_tag_selector).addClass("error");
								
								if (value) {		
									value = String(value);
									var id_errormsg_container = id_tag_container+"_errorMsg";
									var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
									
									if (!$(id_errormsg_selector).hasClass("error")) { 
										$(id_errormsg_selector).addClass("error");
									}
									
									if ($(id_errormsg_selector).length) { 
										$(id_errormsg_selector).html(value); 
									}
								}						
							}
						});
						// Apply class error to the background of the main div
						$(".div_'.$containerObj.'").addClass("error_background");																															
					} else {					
						load_grid(layout.A.grid.obj);
						layout.B.grid.load(data.id);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "tbl_options_group_description['.Yii::app()->language.'][name]";
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
							$("#id").val(data.id);
							wins.toolbar.load(data.id);
							wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
						}
						layout.A.grid.obj.selectRowById(data.id);
						
						
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}
	
layout.A.toolbar.load();


layout.B.toolbar.obj = layout.B.obj.attachToolbar();
layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);

layout.B.toolbar.load = function(current_id){
	var obj = layout.B.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/options/index','LABEL_BTN_ADD_OPTION').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	 		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				layout.B.toolbar.add();
				break;							
			case "delete":
				var checked = layout.B.grid.obj.getCheckedRows(0);
				if (checked) {											
					if (confirm("'.Yii::t('views/options/index','LABEL_ALERT_DELETE').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						$.ajax({
							url: "'.CController::createUrl('delete_options').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){	
								layout.B.grid.load(layout.A.grid.obj.getSelectedRowId());
								alert("'.Yii::t('global','LABEL_ALERT_REMOVE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/options/index','LABEL_ALERT_NO_CHECKED').'");		
				}			
				break;
		}
	});	
}
var wins_options = new Object();

layout.B.toolbar.add = function(current_id){
	name = current_id ? "'.Yii::t('global','LABEL_EDIT').' "+layout.B.grid.obj.cellById(current_id,1).getValue() : "'.Yii::t('global','LABEL_NEW').'";
	
	wins_options.obj = dhxWins.createWindow("modifyTagWindow", 0, 0, 920, 420);
	wins_options.obj.setText(name);
	wins_options.obj.button("park").hide();
	wins_options.obj.keepInViewport(true);
	wins_options.obj.setModal(true);
	wins_options.obj.center();		
	var pos = wins_options.obj.getPosition();
	wins_options.obj.setPosition(pos[0],10);
				
	wins_options.toolbar = new Object();
	
	wins_options.toolbar.load = function(current_id){
		var obj = wins_options.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins_options.toolbar.save(id);
					break;
				case "save_close":
					wins_options.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/options/index','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_options').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								wins_options.obj.close();
							},
							success: function(data){
								layout.B.grid.load(layout.A.grid.obj.getSelectedRowId());
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	wins_options.toolbar.obj = wins_options.obj.attachToolbar();
	wins_options.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins_options.toolbar.load(current_id);
	
	wins_options.layout = new Object();
	wins_options.layout.obj = wins_options.obj.attachLayout("2U");
	wins_options.layout.A = new Object();
	wins_options.layout.A.obj = wins_options.layout.obj.cells("a");
	wins_options.layout.A.obj.hideHeader();	
	wins_options.layout.A.obj.setWidth(265);
	wins_options.layout.A.obj.fixSize(false,false);		
	
	wins_options.layout.B = new Object();
	wins_options.layout.B.obj = wins_options.layout.obj.cells("b");
	wins_options.layout.B.obj.hideHeader();
	wins_options.layout.A.tabs = new Object();
	wins_options.layout.A.tabs.obj = wins_options.layout.A.obj.attachTabbar();
	wins_options.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
	wins_options.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_options_description').'?id="+current_id);

	$.ajax({
		url: "'.CController::createUrl('edit_options').'?id="+current_id+"&id_options_group="+layout.A.grid.obj.getSelectedRowId(),
		type: "POST",
		success: function(data){
			wins_options.layout.B.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	wins_options.obj.attachEvent("onClose",function(win){
		var wins_options = new Object();
		
		return true;
	});			
	
	wins_options.highlight_tab_errors = function(tabObj,cssStyle){
		if (!cssStyle) { cssStyle = "color:#FF0000;"; }
	
		$.each(tabObj._tabs, function(key, value) {									
			if ($("*",$("#"+wins_options.layout.obj.cont.obj.id+" [tab_id=\'"+key+"\']")).hasClass("error")) {
				tabObj.setCustomStyle(key,null,null,cssStyle);
			} else {
				tabObj.setCustomStyle(key,null,null,null);
			}
		});
	};		
	
	wins_options.toolbar.save = function(id,close){
		var obj = wins_options.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save').'",
			type: "POST",
			data: $("#"+wins_options.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+wins_options.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+wins_options.layout.obj.cont.obj.id+" *").removeClass("error");
					
				wins_options.highlight_tab_errors(wins_options.layout.A.tabs.obj);			
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				wins_options.highlight_tab_errors(wins_options.layout.A.tabs.obj);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins_options.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_'.$containerObj.'").removeClass("error_background");
				if (data) {
					
					if (data.errors) {
						$.each(data.errors, function(key, value){
							var id_tag_container = key;
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							
							if (!$(id_tag_selector).hasClass("error")) { 
								$(id_tag_selector).addClass("error");
								
								if (value) {		
									value = String(value);
									var id_errormsg_container = id_tag_container+"_errorMsg";
									var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
									
									if (!$(id_errormsg_selector).hasClass("error")) { 
										$(id_errormsg_selector).addClass("error");
									}
									
									if ($(id_errormsg_selector).length) { 
										$(id_errormsg_selector).html(value); 
									}
								}						
							}
						});
						// Apply class error to the background of the main div
						$(".div_'.$containerObj.'").addClass("error_background");																															
					} else {
						layout.B.grid.load(layout.A.grid.obj.getSelectedRowId());
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "options_description['.Yii::app()->language.'][name]";
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");		
							$("#id").val(data.id);

							wins_options.toolbar.load(data.id);
							wins_options.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
						}
						layout.B.grid.obj.selectRowById(data.id);
						
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}
	
layout.B.toolbar.load();

layout.A.grid.obj = layout.A.obj.attachGrid();
layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_INPUT_TYPE').','.Yii::t('global','LABEL_NAME').'",null,["text-align:center"]);

layout.A.grid.obj.setInitWidths("40,*,120");
layout.A.grid.obj.setColAlign("center,left,left");
layout.A.grid.obj.setColSorting("na,na,na");
layout.A.grid.obj.setSkin(dhx_skin);
layout.A.grid.obj.enableDragAndDrop(false);
layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

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

// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_options_groups').'";

// load the initial grid
load_grid(layout.A.grid.obj);

layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

layout.A.grid.obj.attachEvent("onSelectStateChanged", function(id,ind){
	layout.B.grid.load(id, enable_grid_toolbar(layout.B.grid.obj,layout.B.toolbar.obj, layout.A.grid.obj));
});

layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

layout.B.grid.obj = layout.B.obj.attachGrid();
layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').','.Yii::t('global','LABEL_PRICE').','.Yii::t('global','LABEL_ENABLED').'",null,["text-align:center",,,"text-align:center","text-align:right","text-align:center"]);

layout.B.grid.obj.setInitWidths("40,*,100,50,100,100");
layout.B.grid.obj.setColAlign("center,left,left,center,right,center");
layout.B.grid.obj.setColSorting("na,na,na,na,na,na");

layout.B.grid.obj.setSkin(dhx_skin);
layout.B.grid.obj.enableDragAndDrop(true);
layout.B.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
layout.B.obj.attachStatusBar().setText("<div id=\'recinfoArea_b\'></div>");
layout.B.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
layout.B.grid.obj.enablePaging(true, 100, 3, "recinfoArea_b");
layout.B.grid.obj.setPagingSkin("toolbar", dhx_skin);
layout.B.grid.obj.i18n.paging={
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

layout.B.grid.obj.init();	

// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_options').'";

layout.B.grid.load = function(template_id, callback){
	var obj = layout.B.grid.obj;
	
	obj.clearAll();
	if (template_id) obj.loadXML(obj.xmlOrigFileUrl+"?id="+template_id,callback);	
};

layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	layout.B.toolbar.add(rId);
});

layout.B.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	var obj = this;
	
	var rows=this.getAllRowIds().split(",");
	var ids=[];

	for (var i=0;i<rows.length;++i) {
		if (rows[i]) {
			ids.push("ids[]="+rows[i]);									
		}
	}
	$.ajax({
		url: "'.CController::createUrl('save_options_sort_order').'?id="+layout.A.grid.obj.getSelectedRowId(),
		type: "POST",
		data: ids.join("&")
	});	
});

layout.B.grid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

layout.B.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	if (cInd == 5) {
		$.ajax({
			url: "'.CController::createUrl('toggle_active').'",
			type: "POST",
			data: { "id":rId,"active":state }
		});
		if(state){
			layout.B.grid.obj.setRowTextStyle(rId, "color: #000000");	
		}else{
			layout.B.grid.obj.setRowTextStyle(rId, "color: #B5B5B5;");		
		}			
	}
});


layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(false);
dhxWins.attachViewportTo(layout.obj.cont.obj.id);
dhxWins.setImagePath(dhx_globalImgPath);

enable_grid_toolbar(layout.B.grid.obj,layout.B.toolbar.obj, layout.A.grid.obj);

',CClientScript::POS_END);
?>