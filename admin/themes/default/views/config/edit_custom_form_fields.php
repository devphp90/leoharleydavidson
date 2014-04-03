<?php 
$script = '
edit = new Object();

edit.dhxWins = new dhtmlXWindows();
edit.dhxWins.enableAutoViewport(false);
edit.dhxWins.attachViewportTo("custom_form_fields_layout");
edit.dhxWins.setImagePath(dhx_globalImgPath);	

edit.wins = new Object();
edit.wins2 = new Object();

edit.layout = new Object();
edit.layout.obj = new dhtmlXLayoutObject("custom_form_fields_layout", "1C");

edit.layout.A = new Object();
edit.layout.A.obj = edit.layout.obj.cells("a");
edit.layout.A.obj.hideHeader();

$.ajax({
	url: "'.CController::createUrl('edit_custom_form_fields_options').'",
	type: "POST",
	beforeSend: function(){
		layout.A.dataview.ajaxRequests++;
	},	
	success: function(data){
		layout.A.dataview.ajaxComplete();
		edit.layout.A.obj.attachHTMLString(data);		
	}
});

layout.A.dataview.ajaxComplete();

// on change get custom form fields list
$("#custom_form_fields_layout").on("change","#_select_form",function(){
	var form_type = $(this).val();
	
	// unload previous grid
	if (edit.grid && edit.grid.obj) edit.grid.obj.destructor(); 
	
	if (form_type.length) { 
		edit.toolbar = new Object();
		edit.toolbar.obj = new dhtmlXToolbarObject("_custom_fields_toolbar");
		edit.toolbar.obj.setIconsPath(dhx_globalImgPath);	
		edit.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
		edit.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
		
		edit.toolbar.obj.attachEvent("onClick", function(id){	
			var obj = this;
			var title = "'.Yii::t('views/config/edit_custom_form_fields','LABEL_TITLE_CUSTOM_FORM_FIELDS').'";
		
			switch (id) {
				case "add":					
					edit.toolbar.add();
					break;
				case "delete":			
					var checked = edit.grid.obj.getCheckedRows(0);
					
					if (checked) {
						if (confirm("'.Yii::t('views/config/edit_custom_form_fields','LABEL_ALERT_DELETE').'")) {
							checked = checked.split(",");
							var ids=[];
							
							for (var i=0;i<checked.length;++i) {
								if (checked[i]) {
									ids.push("ids[]="+checked[i]);									
								}
							}
							
							$.ajax({
								url: "'.CController::createUrl('delete_custom_fields').'",
								type: "POST",
								data: ids.join("&"),
								beforeSend: function(){		
									obj.disableItem(id);
								},
								complete: function(){
									if (typeof obj.enableItem == "function") obj.enableItem(id);	
								},							
								success: function(data){													
									edit.grid.load();
									alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
								}
							});						
						}
					} else {
						alert("'.Yii::t('views/config/edit_custom_form_fields','LABEL_ALERT_NO_CHECKED').'");	
					}
					break;	
			}
		});
		edit.toolbar.add = function(current_id,title){
			title = title ? title:"";
			
			edit.wins.obj = edit.dhxWins.createWindow("editCustomFormFieldsWindow", 10, 10, 600, 450);
			edit.wins.obj.setText(title);
			edit.wins.obj.button("park").hide();
			edit.wins.obj.keepInViewport(true);
			edit.wins.obj.setModal(true);
			//edit.wins.obj.center();
						
			edit.wins.toolbar = new Object();
			
			edit.wins.toolbar.load = function(current_id){
				var obj = edit.wins.toolbar.obj;
				
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
							edit.wins.toolbar.save(id);
							break;
						case "save_close":
							edit.wins.toolbar.save(id,1);
							break;
						case "delete":
							if (confirm("'.Yii::t('views/config/edit_custom_form_fields','LABEL_ALERT_DELETE').'")) {
								$.ajax({
									url: "'.CController::createUrl('delete_custom_fields').'",
									type: "POST",
									data: { "ids[]":current_id },
									beforeSend: function(){		
										obj.disableItem(id);
									},
									complete: function(){
										if (typeof obj.enableItem == "function") obj.enableItem(id);
										edit.wins.obj.close();
									},
									success: function(data){
										edit.grid.load();
										alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
									}
								});					
							}			
							break;
					}
				});	
			}	
			
			edit.wins.toolbar.obj = edit.wins.obj.attachToolbar();
			edit.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
			edit.wins.toolbar.load(current_id);
			
			edit.wins.layout = new Object();
			edit.wins.layout.obj = edit.wins.obj.attachLayout("3L");
			edit.wins.layout.A = new Object();
			edit.wins.layout.A.obj = edit.wins.layout.obj.cells("a");
			edit.wins.layout.A.obj.hideHeader();	
			
			edit.wins.tabbar = new Object();
			edit.wins.tabbar.obj = edit.wins.layout.A.obj.attachTabbar();
			edit.wins.tabbar.obj.setImagePath(dhx_globalImgPath);	
			edit.wins.tabbar.obj.clearAll();
			edit.wins.tabbar.obj.loadXML("'.CController::createUrl('xml_list_custom_fields_description').'?id="+current_id, function(){
			});
	
			
			
			edit.wins.layout.B = new Object();
			edit.wins.layout.B.obj = edit.wins.layout.obj.cells("b");
			edit.wins.layout.B.obj.hideHeader();		
			edit.wins.layout.B.obj.setHeight(160);					
			
			$.ajax({
				url: "'.CController::createUrl('add_custom_form_fields_options').'",
				type: "POST",
				data: { "id":current_id, "form":form_type },
				success: function(data){
					edit.wins.layout.B.obj.attachHTMLString(data);		
					
					$("#_select_type").trigger("change");
				}
			});		
			
			edit.wins.layout.C = new Object();
			edit.wins.layout.C.obj = edit.wins.layout.obj.cells("c");
			edit.wins.layout.C.obj.hideHeader();	
			edit.wins.layout.C.obj.fixSize(false,true);		
					
			
			// clean variables
			edit.wins.obj.attachEvent("onClose",function(win){
				edit.wins = new Object();
				
				return true;
			});		
			
			edit.wins.highlight_tab_errors = function(tabObj,cssStyle){
				if (!cssStyle) { cssStyle = "color:#FF0000;"; }
			
				$.each(tabObj._tabs, function(key, value) {									
					if ($("*",$("#"+edit.wins.layout.obj.cont.obj.id+" [tab_id=\'"+key+"\']")).hasClass("error")) {
						tabObj.setCustomStyle(key,null,null,cssStyle);
					} else {
						tabObj.setCustomStyle(key,null,null,null);
					}
				});
			};														
			
			edit.wins.toolbar.save = function(id,close){
				var obj = edit.wins.toolbar.obj;
				
				$.ajax({
					url: "'.CController::createUrl('save_custom_fields').'",
					type: "POST",
					data: $("#"+edit.wins.layout.obj.cont.obj.id+" *").serialize()+"&form="+form_type,
					dataType: "json",
					beforeSend: function(){			
						// clear all errors					
						$("#"+edit.wins.layout.obj.cont.obj.id+" span.error").html("");
						$("#"+edit.wins.layout.obj.cont.obj.id+" *").removeClass("error");
						
						edit.wins.highlight_tab_errors(edit.wins.tabbar.obj);			
					
						obj.disableItem(id);			
					},
					complete: function(jqXHR, textStatus){
						if (typeof obj.enableItem == "function") obj.enableItem(id);
						
						edit.wins.highlight_tab_errors(edit.wins.tabbar.obj);			
						
						if (close && !jQuery.parseJSON(jqXHR.responseText).errors) edit.wins.obj.close();
					},
					success: function(data){						
						// Remove class error to the background of the main div
						if (data) {
							if (data.errors) {
								
								$.each(data.errors, function(key, value){
									var id_tag_container = "_"+key;
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
							} else {					
								
								alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
								
								// Verify if popup window is close
								if(!close){
									var id_tag_container = "'.$containerObj.'_custom_fields_description['.Yii::app()->language.'][name]";
									var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
										
									$("#id_custom_fields").val(data.id);
									edit.wins.toolbar.load(data.id);
									edit.wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());
									
									var type = $("#_select_type").val();
									
									if (type == 1 || type == 2 || type == 5) {
										edit.wins.layout.C.toolbar.obj.enableItem("add");
										edit.wins.layout.C.toolbar.obj.enableItem("delete");	
									}									
								}
								edit.grid.load();
							}
						} else {
							alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
						}
					}
				});	
			}
		}		
	
		edit.grid = new Object();
		edit.grid.obj = new dhtmlXGridObject("_custom_fields_grid");	
		edit.grid.obj.setImagePath(dhx_globalImgPath);
		edit.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('views/config/edit_custom_form_fields','LABEL_TYPE').','.Yii::t('views/config/edit_custom_form_fields','LABEL_REQUIRED').'",null,["text-align:center;",,,"text-align:center;"]);
		//edit.grid.obj.setInitWidthsP("4,50,30,16");
		edit.grid.obj.setInitWidths("40,*,100,80");
		edit.grid.obj.enableResizing("false,false,false,false");
		edit.grid.obj.setColAlign("center,left,left,center");
		edit.grid.obj.setColSorting("na,na,na,na");
		edit.grid.obj.enableDragAndDrop(true);
		edit.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
		edit.grid.obj.init();
		edit.grid.obj.setSkin(dhx_skin);
		edit.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_custom_fields').'";	
		
		edit.grid.load = function(callback){
			var obj = edit.grid.obj;
			
			obj.clearAll();
			obj.loadXML(edit.grid.obj.xmlOrigFileUrl+"?form="+form_type,callback);	
		}
		
		edit.grid.load();
		
		edit.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
			edit.toolbar.add(rId,this.cellById(rId,1).getValue());
		});
		
		edit.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
			var obj = this;
			
			var rows=this.getAllRowIds().split(",");
			var ids=[];
		
			for (var i=0;i<rows.length;++i) {
				if (rows[i]) {
					ids.push("ids[]="+rows[i]);									
				}
			}
			
			$.ajax({
				url: "'.CController::createUrl('save_custom_fields_sort_order').'",
				type: "POST",
				data: ids.join("&")
			});	
		});
			
		
		edit.grid.obj.rowToDragElement=function(id){
			//any custom logic here
			var text="";
		
			for (var i=0; i<this._dragged.length; i++) {
				text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
			}
			
			return text;
		}
		
	}
});

// on change type
$("#custom_form_fields_layout").on("change","#_select_type",function(){
	var type = $(this).val();
	edit.wins.layout.C.obj.detachToolbar();
	edit.wins.layout.C.obj.detachObject();

	switch (type) {
		// checkbox or dropdown
		case "1":
		case "2":		
		case "5":
			edit.wins.layout.C.toolbar = new Object();		
			edit.wins.layout.C.toolbar.obj = edit.wins.layout.C.obj.attachToolbar();
			edit.wins.layout.C.toolbar.obj.setIconsPath(dhx_globalImgPath);	
			edit.wins.layout.C.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
			edit.wins.layout.C.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");			
			
			if ($("#id_custom_fields").val() == 0){
				edit.wins.layout.C.toolbar.obj.disableItem("add");
				edit.wins.layout.C.toolbar.obj.disableItem("delete");
			}
			
			edit.wins.layout.C.toolbar.obj.attachEvent("onClick", function(id){	
				var obj = this;
				var title = "'.Yii::t('views/config/edit_custom_form_fields','LABEL_TITLE_CUSTOM_FORM_FIELDS').'";
			
				switch (id) {
					case "add":					
						edit.wins.layout.C.toolbar.add();
						break;
					case "delete":			
						var checked = edit.wins.layout.C.grid.obj.getCheckedRows(0);
						
						if (checked) {
							if (confirm("'.Yii::t('views/config/edit_custom_form_fields','LABEL_ALERT_DELETE_OPTION').'")) {
								checked = checked.split(",");
								var ids=[];
								
								for (var i=0;i<checked.length;++i) {
									if (checked[i]) {
										ids.push("ids[]="+checked[i]);									
									}
								}
								
								$.ajax({
									url: "'.CController::createUrl('delete_custom_fields_option').'",
									type: "POST",
									data: ids.join("&"),
									beforeSend: function(){		
										obj.disableItem(id);
									},
									complete: function(){
										if (typeof obj.enableItem == "function") obj.enableItem(id);	
									},							
									success: function(data){													
										edit.wins.layout.C.grid.load();
										alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
									}
								});						
							}
						} else {
							alert("'.Yii::t('views/config/edit_custom_form_fields','LABEL_ALERT_NO_CHECKED').'");	
						}
						break;	
				}
			});
			
			edit.wins.layout.C.toolbar.add = function(current_id, title){
				title = title ? title:"";
				edit.wins2.obj = edit.dhxWins.createWindow("editCustomFormFieldsOptionWindow", 10, 10, 450, 400);
				edit.wins2.obj.setText(title);
				edit.wins2.obj.button("park").hide();
				edit.wins2.obj.keepInViewport(true);
				edit.wins.obj.setModal(false);
				edit.wins2.obj.setModal(true);
				
				// clean variables
				edit.wins2.obj.attachEvent("onClose",function(win){					
					edit.wins2.obj.setModal(false);
					edit.wins.obj.setModal(true);
					edit.wins2 = new Object();
					
					return true;
				});			
				
				edit.wins2.highlight_tab_errors = function(tabObj,cssStyle){
					if (!cssStyle) { cssStyle = "color:#FF0000;"; }
				
					$.each(tabObj._tabs, function(key, value) {									
						if ($("*",$("#"+edit.wins2.layout.obj.cont.obj.id+" [tab_id=\'"+key+"\']")).hasClass("error")) {
							tabObj.setCustomStyle(key,null,null,cssStyle);
						} else {
							tabObj.setCustomStyle(key,null,null,null);
						}
					});
				};							
	
				edit.wins2.toolbar = new Object();
				
				edit.wins2.toolbar.load = function(current_id){
					var obj = edit.wins2.toolbar.obj;
					
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
								edit.wins2.toolbar.save(id);
								break;
							case "save_close":
								edit.wins2.toolbar.save(id,1);
								break;
							case "delete":
								if (confirm("'.Yii::t('views/config/edit_custom_form_fields','LABEL_ALERT_DELETE_OPTION').'")) {
									$.ajax({
										url: "'.CController::createUrl('delete_custom_fields_option').'",
										type: "POST",
										data: { "ids[]":current_id },
										beforeSend: function(){		
											obj.disableItem(id);
										},
										complete: function(){
											if (typeof obj.enableItem == "function") obj.enableItem(id);
											edit.wins2.obj.close();
										},
										success: function(data){
											edit.wins.layout.C.grid.load();
											alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
										}
									});					
								}			
								break;
						}
					});	
				}	
				
				edit.wins2.toolbar.obj = edit.wins2.obj.attachToolbar();
				edit.wins2.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				edit.wins2.toolbar.load(current_id);
				
				edit.wins2.layout = new Object();
				edit.wins2.layout.obj = edit.wins2.obj.attachLayout("2E");
				edit.wins2.layout.A = new Object();
				edit.wins2.layout.A.obj = edit.wins2.layout.obj.cells("a");
				edit.wins2.layout.A.obj.hideHeader();	
				
				edit.wins2.tabbar = new Object();
				edit.wins2.tabbar.obj = edit.wins2.layout.A.obj.attachTabbar();
				edit.wins2.tabbar.obj.setImagePath(dhx_globalImgPath);	
				edit.wins2.tabbar.obj.clearAll();
				edit.wins2.tabbar.obj.loadXML("'.CController::createUrl('xml_list_custom_fields_option_description').'?id="+current_id, function(){
				});
		
				
				
				edit.wins2.layout.B = new Object();
				edit.wins2.layout.B.obj = edit.wins2.layout.obj.cells("b");
				edit.wins2.layout.B.obj.hideHeader();							
				
				$.ajax({
					url: "'.CController::createUrl('add_custom_form_fields_option_options').'",
					type: "POST",
					data: { "id":current_id, type:type },
					success: function(data){
						edit.wins2.layout.B.obj.attachHTMLString(data);		
					}
				});																						
				
				edit.wins2.toolbar.save = function(id,close){
					var obj = edit.wins.toolbar.obj;
					$.ajax({
						url: "'.CController::createUrl('save_custom_fields_option').'",
						type: "POST",
						data: $("#"+edit.wins2.layout.obj.cont.obj.id+" *").serialize()+"&id_custom_fields="+$("#id_custom_fields").val(),
						dataType: "json",
						beforeSend: function(){			
							// clear all errors					
							$("#"+edit.wins2.layout.obj.cont.obj.id+" span.error").html("");
							$("#"+edit.wins2.layout.obj.cont.obj.id+" *").removeClass("error");
							
							edit.wins2.highlight_tab_errors(edit.wins2.tabbar.obj);
						
							obj.disableItem(id);			
						},
						complete: function(jqXHR, textStatus){
							if (typeof obj.enableItem == "function") obj.enableItem(id);
							
							edit.wins2.highlight_tab_errors(edit.wins2.tabbar.obj);
							
							if (close && !jQuery.parseJSON(jqXHR.responseText).errors) edit.wins2.obj.close();
						},
						success: function(data){						
							if (data) {
								if (data.errors) {
									
									$.each(data.errors, function(key, value){
										var id_tag_container = "_"+key;
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
								} else {					
									
									alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
									
									// Verify if popup window is close
									if(!close){
										var id_tag_container = "'.$containerObj.'_custom_fields_option_description['.Yii::app()->language.'][name]";
										var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");											
										$("#id_custom_fields_option").val(data.id);
										edit.wins2.toolbar.load(data.id);
										edit.wins2.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());
									}
									edit.wins.layout.C.grid.load();
								}
							} else {
								alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
							}
						}
					});	
				}
			}						
			
			edit.wins.layout.C.grid = new Object();
			edit.wins.layout.C.grid.obj = edit.wins.layout.C.obj.attachGrid();
			edit.wins.layout.C.grid.obj.setImagePath(dhx_globalImgPath);
			edit.wins.layout.C.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').'",null,["text-align:center;"]);
			edit.wins.layout.C.grid.obj.setInitWidths("40,*");			
			edit.wins.layout.C.grid.obj.enableResizing("false,false");
			edit.wins.layout.C.grid.obj.setColAlign("center,left");
			edit.wins.layout.C.grid.obj.setColSorting("na,na");
			edit.wins.layout.C.grid.obj.enableDragAndDrop(true);
			edit.wins.layout.C.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
			edit.wins.layout.C.grid.obj.init();
			edit.wins.layout.C.grid.obj.setSkin(dhx_skin);
			edit.wins.layout.C.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_custom_fields_options').'";	
			
			edit.wins.layout.C.grid.load = function(callback){
				var obj = edit.wins.layout.C.grid.obj;
				var id = $("#id_custom_fields").val();
				
				obj.clearAll();
				if (id != 0) obj.loadXML(edit.wins.layout.C.grid.obj.xmlOrigFileUrl+"?id="+id,callback);	
			}
			
			edit.wins.layout.C.grid.load();
			
			edit.wins.layout.C.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
				edit.wins.layout.C.toolbar.add(rId,this.cellById(rId,1).getValue());
			});
			
			edit.wins.layout.C.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
				var obj = this;
				
				var rows=this.getAllRowIds().split(",");
				var ids=[];
			
				for (var i=0;i<rows.length;++i) {
					if (rows[i]) {
						ids.push("ids[]="+rows[i]);									
					}
				}
				
				$.ajax({
					url: "'.CController::createUrl('save_custom_fields_option_sort_order').'",
					type: "POST",
					data: ids.join("&")
				});	
			});
				
			
			edit.wins.layout.C.grid.obj.rowToDragElement=function(id){
				//any custom logic here
				var text="";
			
				for (var i=0; i<this._dragged.length; i++) {
					text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
				}
				
				return text;
			}			
			break;			
	}
});
';

echo Html::script($script); 
?>
<form id="custom_form_fields_layout" style="width:100%; height:100%; padding:0; margin:0;"></form>