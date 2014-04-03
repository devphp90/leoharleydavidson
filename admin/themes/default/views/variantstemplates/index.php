<?php
Html::include_uploadify();


// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/variantstemplates/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?gestion_des_gabarits_de_produi.html");

// add a layout to the Main Template Layout B 
var layout = new Object();

layout.obj = templateLayout_B.attachLayout("3W");
layout.A = new Object();
layout.B = new Object();
layout.C = new Object();
layout.obj.setAutoSize("a,b,c");
layout.A.obj = layout.obj.cells("a");
layout.B.obj = layout.obj.cells("b");
layout.C.obj = layout.obj.cells("c");

layout.A.obj.hideHeader();
layout.B.obj.hideHeader();
layout.C.obj.hideHeader();

layout.A.grid = new Object();
layout.B.grid = new Object();
layout.C.grid = new Object();

layout.A.toolbar = new Object();
layout.B.toolbar = new Object();
layout.C.toolbar = new Object();

layout.A.toolbar.obj = layout.A.obj.attachToolbar();
layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);

layout.A.toolbar.load = function(){
	var obj = layout.A.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/variantstemplates/index','LABEL_BTN_ADD_TEMPLATE').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				layout.A.toolbar.add();											
				break;							
			case "delete":
				var checked = layout.A.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_DELETE').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_template').'",
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
								layout.C.grid.load();
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_NO_CHECKED').'");		
				}
				break;			
		}
	});	
};

layout.A.toolbar.add = function(current_id, name){
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/variantstemplates/index','LABEL_BTN_ADD_TEMPLATE').'";	
	var wins = new Object();
	wins.obj = dhxWins.createWindow("addTemplateWindow", 0, 0, 300, 150);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.button("minmax1").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	wins.obj.denyResize();
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
					if (confirm("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_template').'",
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
								layout.C.grid.load();
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
	wins.layout.obj = wins.obj.attachLayout("1C");
	wins.layout.A = wins.layout.obj.cells("a");
	wins.layout.A.hideHeader();

	$.ajax({
		url: "'.CController::createUrl('add_category_template').'",
		data: { "id":current_id },
		type: "POST",					
		success: function(data){
			wins.layout.A.attachHTMLString(data);		
		}
	});		
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		var wins = new Object();
		return true;
	});			
			
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_category_template').'",
			type: "POST",
			data: $("#"+wins.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+wins.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+wins.layout.obj.cont.obj.id+" *").removeClass("error");

				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
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
						layout.C.grid.load();
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "name";
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

layout.A.grid.obj = layout.A.obj.attachGrid();
layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').'",null,["text-align:center"]);
layout.A.grid.obj.attachHeader(",#text_filter_custom");
				
// custom text filter input
layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

layout.A.grid.obj.setInitWidths("40,*");
layout.A.grid.obj.setColAlign("center,left");
layout.A.grid.obj.setColSorting("na,na");
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

// set filter input names
layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";

// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_option_category').'";

// load the initial grid
load_grid(layout.A.grid.obj);

layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

layout.A.grid.obj.attachEvent("onSelectStateChanged", function(id,ind){
	layout.B.grid.load(id, enable_grid_toolbar(layout.B.grid.obj,layout.B.toolbar.obj, layout.A.grid.obj));
	layout.C.grid.load(0);
	enable_grid_toolbar(layout.C.grid.obj,layout.C.toolbar.obj, layout.B.grid.obj);
});

layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

layout.B.toolbar.obj = layout.B.obj.attachToolbar();
layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);

layout.B.toolbar.load = function(current_id){
	var obj = layout.B.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/variantstemplates/index','LABEL_BTN_ADD_GROUP').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	 		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				layout.B.toolbar.add(0, layout.A.grid.obj.getSelectedRowId());
				break;							
			case "delete":
				var checked = layout.B.grid.obj.getCheckedRows(0);
				if (checked) {											
					if (confirm("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_REMOVE_GROUP').'")) {
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
								layout.B.grid.load(layout.A.grid.obj.getSelectedRowId());
								layout.C.grid.load();
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_NO_CHECKED_GROUP').'");		
				}			
				break;
		}
	});	
}

layout.B.toolbar.add = function(current_id, id_tpl_product_variant_category){
	if (!id_tpl_product_variant_category) { alert("'.Yii::t('global','LABEL_ALERT_TEMPLATE_NO_SELECTED').'"); return false; }
	
	name = current_id ? "'.Yii::t('global','LABEL_EDIT').' "+layout.B.grid.obj.cellById(current_id,1).getValue() : "'.Yii::t('views/variantstemplates/index','LABEL_ADD_GROUP_TO').'  "+layout.A.grid.obj.cellById(id_tpl_product_variant_category,1).getValue();
	var wins = new Object();
	wins.obj = dhxWins.createWindow("addGroupWindow", 0, 0, 600, 320);
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
					if (confirm("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_DELETE_GROUP').'")) {
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
								layout.B.grid.load(layout.A.grid.obj.getSelectedRowId());
								layout.C.grid.load();
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
	wins.layout.A.obj.setWidth(350);
	wins.layout.A.obj.fixSize(false,false);		
	
	wins.layout.B = new Object();
	wins.layout.B.obj = wins.layout.obj.cells("b");
	wins.layout.B.obj.hideHeader();
	
	wins.layout.A.tabs = new Object();
	wins.layout.A.tabs.obj = wins.layout.A.obj.attachTabbar();
	wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
	wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_option_group_description').'?id="+current_id);
	
	$.ajax({
		url: "'.CController::createUrl('edit_variants_group').'?id_tpl_product_variant_category="+id_tpl_product_variant_category,
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			wins.layout.B.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		wins = new Object();
		
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
						layout.B.grid.load(layout.A.grid.obj.getSelectedRowId());
						layout.C.grid.load(data.id)
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "tpl_product_variant_group_description['.Yii::app()->language.'][name]";
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");		
							$("#id").val(data.id);
							wins.toolbar.load(data.id);
							wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
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

layout.B.grid.obj = layout.B.obj.attachGrid();
layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_INPUT_TYPE').',Input Type ID",null,["text-align:center"]);

layout.B.grid.obj.setInitWidths("40,*,120,0");
layout.B.grid.obj.setColAlign("center,left,left");
layout.B.grid.obj.setColSorting("na,na,na");
layout.B.grid.obj.setSkin(dhx_skin);
layout.B.grid.obj.enableDragAndDrop(false);
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
layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_option_groups').'";

layout.B.grid.load = function(template_id,callback){
	var obj = layout.B.grid.obj;
	
	obj.clearAll();
	if (template_id) obj.loadXML(obj.xmlOrigFileUrl+"?id="+template_id,callback);	
};

layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	layout.B.toolbar.add(rId,layout.A.grid.obj.getSelectedRowId());
});

layout.B.grid.obj.attachEvent("onSelectStateChanged", function(id,ind){
	layout.C.grid.load(id, enable_grid_toolbar(layout.C.grid.obj,layout.C.toolbar.obj, layout.B.grid.obj));
});

layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

layout.C.toolbar.obj = layout.C.obj.attachToolbar();
layout.C.toolbar.obj.setIconsPath(dhx_globalImgPath);

layout.C.toolbar.load = function(current_id){
	var obj = layout.C.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/variantstemplates/index','LABEL_BTN_ADD_OPTION').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	 		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				layout.C.toolbar.add(0, layout.B.grid.obj.getSelectedRowId());
				break;							
			case "delete":
				var checked = layout.C.grid.obj.getCheckedRows(0);
				if (checked) {											
					if (confirm("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_REMOVE_OPTION').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_option_group_option').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){	
								layout.C.grid.load(layout.B.grid.obj.getSelectedRowId());
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_NO_CHECKED_OPTION').'");		
				}			
				break;
		}
	});	
}

layout.C.toolbar.add = function(current_id, id_tpl_product_variant_group){
	if (!id_tpl_product_variant_group) { alert("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_NO_SELECTED_GROUP').'"); return false; }
	
	name = current_id ? "'.Yii::t('global','LABEL_EDIT').' "+layout.C.grid.obj.cellById(current_id,1).getValue() : "'.Yii::t('views/variantstemplates/index','LABEL_ADD_OPTION_TO').' "+layout.B.grid.obj.cellById(id_tpl_product_variant_group,1).getValue();
	var wins = new Object();
	wins.obj = dhxWins.createWindow("addOptionWindow", 0, 0, 540, 320);
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
					wins.toolbar.save(id, 0, current_id);
					break;
				case "save_close":
					wins.toolbar.save(id,1, current_id);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/variantstemplates/index','LABEL_ALERT_DELETE_OPTION').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_option_group_option').'",
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
								layout.C.grid.load(layout.B.grid.obj.getSelectedRowId());
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
	
	if(layout.B.grid.obj.cellById(layout.B.grid.obj.getSelectedRowId(),3).getValue()==2){
		wins.layout = new Object();
		wins.layout.obj = wins.obj.attachLayout("2U");
		
		wins.layout.A = new Object();
		wins.layout.A.obj = wins.layout.obj.cells("a");
		wins.layout.A.obj.hideHeader();
		//wins.layout.A.obj.setHeight(125);
		wins.layout.A.obj.fixSize(false,false);	
		
		wins.layout.A.tabs = new Object();
		wins.layout.A.tabs.obj = wins.layout.A.obj.attachTabbar();
		wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
		wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_option_group_option_description').'?id="+current_id);
			
		wins.layout.B = new Object();
		wins.layout.B.obj = wins.layout.obj.cells("b");
		wins.layout.B.obj.hideHeader();	
		$.ajax({
			url: "'.CController::createUrl('edit_variants_group_option').'",
			type: "POST",
			data: { "id":current_id,"id_tpl_product_variant_group":id_tpl_product_variant_group },
			success: function(data){
				wins.layout.B.obj.attachHTMLString(data);		
			}
		});	
	}else{
		wins.layout = new Object();
		wins.layout.obj = wins.obj.attachLayout("1C");
		
		wins.layout.A = new Object();
		wins.layout.A.obj = wins.layout.obj.cells("a");
		wins.layout.A.obj.hideHeader();
		//wins.layout.A.obj.setHeight(125);
		wins.layout.A.obj.fixSize(false,false);	
		
		wins.layout.A.tabs = new Object();
		wins.layout.A.tabs.obj = wins.layout.A.obj.attachTabbar();
		wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
		wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_option_group_option_description').'?id="+current_id);		
	}
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		// To correct a Bug in SWF Uploadify
		var swfuploadify = window["uploadify_button"];
		if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();
		
		wins = new Object();
		layout.C.grid.load(layout.B.grid.obj.getSelectedRowId());
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
	
	wins.toolbar.save = function(id, close, current_id){
		var obj = wins.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_option_option').'",
			type: "POST",
			data: $("#"+wins.layout.obj.cont.obj.id+" *").serialize() + "&VariantOptionTemplateForm[id_tpl_product_variant_group]=" + id_tpl_product_variant_group + "&VariantOptionTemplateForm[id]=" + current_id,
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
						
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "tpl_product_variant_group_option_description['.Yii::app()->language.'][name]";
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");		
							$("#id").val(data.id);
							wins.toolbar.load(data.id);
							wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
						}
						
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}
	
layout.C.toolbar.load();

layout.C.grid.obj = layout.C.obj.attachGrid();
layout.C.grid.obj.setImagePath(dhx_globalImgPath);	
layout.C.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').'",null,["text-align:center"]);

layout.C.grid.obj.setInitWidths("40,*");
layout.C.grid.obj.setColAlign("center,left");
layout.C.grid.obj.setColSorting("na,na");
layout.C.grid.obj.setSkin(dhx_skin);
layout.C.grid.obj.enableDragAndDrop(false);
layout.C.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
layout.C.obj.attachStatusBar().setText("<div id=\'recinfoArea_c\'></div>");
layout.C.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
layout.C.grid.obj.enablePaging(true, 100, 3, "recinfoArea_c");
layout.C.grid.obj.setPagingSkin("toolbar", dhx_skin);
layout.C.grid.obj.i18n.paging={
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
layout.C.grid.obj.init();	

// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.C.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_option_group_options').'";

layout.C.grid.load = function(template_id,callback){
	var obj = layout.C.grid.obj;
	
	obj.clearAll();
	if (template_id) obj.loadXML(obj.xmlOrigFileUrl+"?id="+template_id,callback);	
};

layout.C.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	layout.C.toolbar.add(rId,layout.B.grid.obj.getSelectedRowId());
});

layout.C.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

layout.C.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(false);
dhxWins.attachViewportTo(layout.obj.cont.obj.id);
//dhxWins.attachViewportTo(layout.A.grid.obj.entBox.id);
dhxWins.setImagePath(dhx_globalImgPath);


enable_grid_toolbar(layout.B.grid.obj,layout.B.toolbar.obj, layout.A.grid.obj);
enable_grid_toolbar(layout.C.grid.obj,layout.C.toolbar.obj, layout.B.grid.obj);

',CClientScript::POS_END);
?>