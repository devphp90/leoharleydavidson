<?php 
$app = Yii::app();

$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerObj.'.wins = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "2E");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();
//'.$containerObj.'.layout.A.obj.setHeight(250);
'.$containerObj.'.layout.A.obj.fixSize(true,false);

'.$containerObj.'.layout.A.toolbar = new Object();

'.$containerObj.'.layout.A.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.A.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/products/edit_bundled_products','LABEL_BTN_ADD_GROUP').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	
	
	obj.addSeparator("sep01", null);
	
	var opts = [["template_load", "obj", "'.Yii::t('global','LABEL_BTN_LOAD_TEMPLATE').'", "toolbar/load.png"],
				//  ["template_sep01", "sep", "", ""],
				  ["template_save", "obj", "'.Yii::t('global','LABEL_BTN_SAVE_AS_TEMPLATE').'", "toolbar/save.gif"]
				 ]; 

	
	obj.addButtonSelect("template", null, "'.Yii::t('global','LABEL_BTN_TEMPLATE').'", opts, null, null);		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				'.$containerObj.'.layout.A.toolbar.add();											
				break;							
			case "delete":
				var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_DELETE').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_bundled_products_group',array('id'=>$id)).'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){						
								load_grid('.$containerObj.'.layout.A.grid.obj);
								'.$containerObj.'.layout.B.grid.load();
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_NO_CHECKED').'");		
				}
				break;
			case "template_load":
				'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_loadTemplateWindow", 10, 10, 700, 380);
				'.$containerObj.'.wins.obj.setText("'.Yii::t('views/products/edit_bundled_products','LABEL_TITLE_LOAD_TEMPLATE').'");
				'.$containerObj.'.wins.obj.button("park").hide();
				'.$containerObj.'.wins.obj.keepInViewport(true);
				'.$containerObj.'.wins.obj.setModal(true);
				//'.$containerObj.'.wins.obj.maximize();	
							
				'.$containerObj.'.wins.toolbar = new Object();
				
				'.$containerObj.'.wins.toolbar.load = function(current_id){
					var obj = '.$containerObj.'.wins.toolbar.obj;
					
					obj.clearAll();
					obj.detachAllEvents();
					
					obj.addButton("apply",null,"'.Yii::t('global','LABEL_BTN_APPLY_TEMPLATE').'","toolbar/green_check.png","toolbar/green_check_dis.png");
				
					obj.attachEvent("onClick",function(id){
						switch (id) {
							case "apply":
								var id_tpl_product_bundled_product_category = '.$containerObj.'.wins.layout.A.grid.obj.getSelectedRowId();
								
								if (!id_tpl_product_bundled_product_category) { alert("'.Yii::t('global','LABEL_ALERT_TEMPLATE_NO_SELECTED').'"); return false; }
							
								if (confirm("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_APPLY_TEMPLATE').'")){								
									$.ajax({
										url: "'.CController::createUrl('apply_bundled_products_template',array('id'=>$id)).'",
										type: "POST",
										data: { "id_tpl_product_bundled_product_category":id_tpl_product_bundled_product_category },
										beforeSend: function(){			
											obj.disableItem(id);			
										},
										complete: function(jqXHR, textStatus){
											if (typeof obj.enableItem == "function") obj.enableItem(id);										
										},
										success: function(data){		
											alert("'.Yii::t('global','LABEL_ALERT_TEMPLATE_SUCCESS').'");
											'.$containerObj.'.wins.obj.close();
															
											load_grid('.$containerObj.'.layout.A.grid.obj);
											'.$containerObj.'.layout.B.grid.load();										
										}
									});		
								}
								break;
						}
					});	
				}	
				
				'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
				'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerObj.'.wins.toolbar.load(current_id);
							
				'.$containerObj.'.wins.layout = new Object();
				'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("2U");				
				
				'.$containerObj.'.wins.layout.A = new Object();					
				'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
				'.$containerObj.'.wins.layout.A.obj.setWidth(200);
				'.$containerObj.'.wins.layout.A.obj.hideHeader();					
				
				'.$containerObj.'.wins.layout.A.grid = new Object()							
				
				'.$containerObj.'.wins.layout.A.grid.obj = '.$containerObj.'.wins.layout.A.obj.attachGrid();
				'.$containerObj.'.wins.layout.A.grid.obj.setImagePath(dhx_globalImgPath);
				'.$containerObj.'.wins.layout.A.grid.obj.setHeader("'.Yii::t('global','LABEL_BTN_TEMPLATE').'");
				'.$containerObj.'.wins.layout.A.grid.obj.attachHeader("#text_filter_custom");
				
				// custom text filter input
				'.$containerObj.'.wins.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

				'.$containerObj.'.wins.layout.A.grid.obj.setInitWidths("*");
				'.$containerObj.'.wins.layout.A.grid.obj.setColAlign("left");
				'.$containerObj.'.wins.layout.A.grid.obj.setColSorting("na");
				'.$containerObj.'.wins.layout.A.grid.obj.setSkin(dhx_skin);
				'.$containerObj.'.wins.layout.A.grid.obj.init();
				
				// set filter input names
				'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[0].name="name";
				
				// we create a variable to store the default url used to get our grid data, so we can reuse it later
				'.$containerObj.'.wins.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_bundled_products_templates').'";
				
				'.$containerObj.'.wins.layout.A.grid.load = function(current_id){
					var obj = '.$containerObj.'.wins.layout.A.grid.obj;
					
					obj.clearAll();
					if (current_id) obj.loadXML('.$containerObj.'.wins.layout.A.grid.obj.xmlOrigFileUrl+"?id="+current_id);	
				};
				
				// load the initial grid
				load_grid('.$containerObj.'.wins.layout.A.grid.obj);
				
				'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onSelectStateChanged", function(id,ind){
					'.$containerObj.'.wins.layout.B.grid.load(id);
				});						
				
				'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
					ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
				}); 
				
				'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
					ajaxOverlay(grid_obj.entBox.id,0);
				});				
				
				'.$containerObj.'.wins.layout.B = new Object();				
				'.$containerObj.'.wins.layout.B.obj = '.$containerObj.'.wins.layout.obj.cells("b");
				'.$containerObj.'.wins.layout.B.obj.hideHeader();										
				
				'.$containerObj.'.wins.layout.B.grid = new Object();
				
				'.$containerObj.'.wins.layout.B.grid.load = function(current_id){
					var obj = '.$containerObj.'.wins.layout.B.grid.obj;
					
					obj.clearAll();
					if (current_id) obj.loadXML(obj.xmlOrigFileUrl+"?id="+current_id);	
				};				
				
				'.$containerObj.'.wins.layout.B.grid.obj = '.$containerObj.'.wins.layout.B.obj.attachGrid();
				'.$containerObj.'.wins.layout.B.grid.obj.setImagePath(dhx_globalImgPath);
				'.$containerObj.'.wins.layout.B.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').'");
				'.$containerObj.'.wins.layout.B.grid.obj.setInitWidths("*");
				'.$containerObj.'.wins.layout.B.grid.obj.setColAlign("left");
				'.$containerObj.'.wins.layout.B.grid.obj.setColSorting("na");
				
				'.$containerObj.'.wins.layout.B.grid.obj.setSkin(dhx_skin);
				'.$containerObj.'.wins.layout.B.grid.obj.init();					
				'.$containerObj.'.wins.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_bundled_products_group_template').'";			
				'.$containerObj.'.wins.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
					ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
				}); 
				
				'.$containerObj.'.wins.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
					ajaxOverlay(grid_obj.entBox.id,0);
				});						
				
				// clean variables
				'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
					'.$containerObj.'.wins = new Object();
					
					return true;
				});					
				break;
			case "template_save":
				$.ajax({
					url: "'.CController::createUrl('count_bundled_products_groups',array('container'=>$containerObj,'id'=>$id)).'",
					type: "POST",					
					success: function(data){	
						if (data > 0) {					
							'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_saveTemplateWindow", 10, 10, 400, 180);
							'.$containerObj.'.wins.obj.setText("'.Yii::t('views/products/edit_bundled_products','LABEL_TITLE_SAVE_TEMPLATE').'");
							'.$containerObj.'.wins.obj.button("park").hide();
							'.$containerObj.'.wins.obj.keepInViewport(true);
							'.$containerObj.'.wins.obj.setModal(true);
										
							'.$containerObj.'.wins.toolbar = new Object();
							
							'.$containerObj.'.wins.toolbar.load = function(current_id){
								var obj = '.$containerObj.'.wins.toolbar.obj;
								
								obj.clearAll();
								obj.detachAllEvents();
								
								obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
							
								obj.attachEvent("onClick",function(id){
									switch (id) {
										case "save":	
											$.ajax({
												url: "'.CController::createUrl('save_bundled_products_template').'",
												type: "POST",
												data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize(),
												dataType: "json",
												beforeSend: function(){			
													// clear all errors					
													$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
													$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");										
													obj.disableItem(id);			
												},
												complete: function(jqXHR, textStatus){
													if (typeof obj.enableItem == "function") obj.enableItem(id);										
												},
												success: function(data){						
													// Remove class error to the background of the main div
													$(".div_'.$containerObj.'").removeClass("error_background");
													if (data) {
														if (data.errors) {												
															$.each(data.errors, function(key, value){
																var id_tag_container = "'.$containerObj.'_"+key;
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
															alert("'.Yii::t('global','LABEL_ALERT_TEMPLATE_SAVE_SUCCESS').'");
															'.$containerObj.'.wins.obj.close();
														}
													} else {
														alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
													}
												}
											});									
											break;
									}
								});	
							}	
							
							'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
							'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
							'.$containerObj.'.wins.toolbar.load(current_id);
						
							
							'.$containerObj.'.wins.layout = new Object();
							'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");				
							
							'.$containerObj.'.wins.layout.A = new Object();							
							'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");							
							'.$containerObj.'.wins.layout.A.obj.hideHeader();						
							
							$.ajax({
								url: "'.CController::createUrl('add_bundled_products_template',array('container'=>$containerObj,'id'=>$id)).'",
								type: "POST",					
								success: function(data){
									'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
								}
							});				
							
							// clean variables
							'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
								'.$containerObj.'.wins = new Object();
								
								return true;
							});		
						} else {
							alert("'.Yii::t('global','LABEL_ALERT_NO_CREATED').'");	
						}
					}
				});											
				break;				
		}
	});	
};

'.$containerObj.'.layout.A.toolbar.add = function(current_id, name){
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name: "'.Yii::t('views/products/edit_bundled_products','LABEL_TITLE').'";	
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addGroupWindow", 10, 10, 600, 200);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	//'.$containerObj.'.wins.obj.maximize();	
				
	'.$containerObj.'.wins.toolbar = new Object();
	
	'.$containerObj.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
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
					'.$containerObj.'.wins.toolbar.save(id);
					break;
				case "save_close":
					'.$containerObj.'.wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_option_group').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								'.$containerObj.'.wins.obj.close();
							},
							success: function(data){														
								load_grid('.$containerObj.'.layout.A.grid.obj);
								'.$containerObj.'.layout.B.grid.load();
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
	'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins.toolbar.load(current_id);
	
	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("2U");
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	'.$containerObj.'.wins.layout.A.obj.hideHeader();	
	'.$containerObj.'.wins.layout.A.obj.setWidth(350);
	'.$containerObj.'.wins.layout.A.obj.fixSize(false,false);	
	
	'.$containerObj.'.wins.layout.B = new Object();
	'.$containerObj.'.wins.layout.B.obj = '.$containerObj.'.wins.layout.obj.cells("b");
	'.$containerObj.'.wins.layout.B.obj.hideHeader();
	
	'.$containerObj.'.wins.layout.A.tabs = new Object();
	'.$containerObj.'.wins.layout.A.tabs.obj = '.$containerObj.'.wins.layout.A.obj.attachTabbar();
	'.$containerObj.'.wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
	'.$containerObj.'.wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_bundled_products_group_description',array('container'=>$containerObj)).'&id="+current_id);
	
	$.ajax({
		url: "'.CController::createUrl('edit_bundled_products_group',array('container'=>$containerObj,'id_product'=>$id)).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			'.$containerObj.'.wins.layout.B.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		
		return true;
	});			
	
	'.$containerObj.'.wins.highlight_tab_errors = function(tabObj,cssStyle){
		if (!cssStyle) { cssStyle = "color:#FF0000;"; }
	
		$.each(tabObj._tabs, function(key, value) {									
			if ($("*",$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" [tab_id=\'"+key+"\']")).hasClass("error")) {
				tabObj.setCustomStyle(key,null,null,cssStyle);
			} else {
				tabObj.setCustomStyle(key,null,null,null);
			}
		});
	};		
	
	'.$containerObj.'.wins.toolbar.save = function(id,close){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_bundled_products_group',array('id'=>$id)).'",
			type: "POST",
			data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");
					
				'.$containerObj.'.wins.highlight_tab_errors('.$containerObj.'.wins.layout.A.tabs.obj);			
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				'.$containerObj.'.wins.highlight_tab_errors('.$containerObj.'.wins.layout.A.tabs.obj);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$containerObj.'.wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_'.$containerObj.'").removeClass("error_background");
				if (data) {
					if (data.errors) {
						$.each(data.errors, function(key, value){
							var id_tag_container = "'.$containerObj.'_"+key;
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
					
						load_grid('.$containerObj.'.layout.A.grid.obj);
						'.$containerObj.'.layout.B.grid.load(data.id);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "'.$containerObj.'_bundled_products_group_description['.Yii::app()->language.'][name]";
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");						
							
							$("#'.$containerObj.'_id").val(data.id);
							'.$containerObj.'.wins.toolbar.load(data.id);
							
							'.$containerObj.'.wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
						}else{
							'.$containerObj.'.layout.A.grid.obj.selectRowById(data.id);
						}
						
								
							
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.load();

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_INPUT_TYPE').', '.Yii::t('global','LABEL_REQUIRED').'",null,["text-align:center",,,"text-align:center"]);

'.$containerObj.'.layout.A.grid.obj.setInitWidths("40,*,130,80");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,center");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableDragAndDrop(true);
'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
'.$containerObj.'.layout.A.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_bundled_products_groups',array('id'=>$id)).'";

// load the initial grid
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onSelectStateChanged", function(id,ind){
	'.$containerObj.'.layout.B.grid.load(id, enable_grid_toolbar('.$containerObj.'.layout.B.grid.obj,'.$containerObj.'.layout.B.toolbar.obj, '.$containerObj.'.layout.A.grid.obj));
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	var obj = this;
	
	var rows=this.getAllRowIds().split(",");
	var ids=[];

	for (var i=0;i<rows.length;++i) {
		if (rows[i]) {
			ids.push("ids[]="+rows[i]);									
		}
	}
	
	$.ajax({
		url: "'.CController::createUrl('save_bundled_products_groups_sort_order',array('id'=>$id)).'",
		type: "POST",
		data: ids.join("&"),
		success: function(data){
			if (data) {
				alert(data);							
				obj.load();
			} 
		}
	});			
});

'.$containerObj.'.layout.A.grid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

'.$containerObj.'.layout.B = new Object();
'.$containerObj.'.layout.B.obj = '.$containerObj.'.layout.obj.cells("b");
'.$containerObj.'.layout.B.obj.hideHeader();

'.$containerObj.'.layout.B.toolbar = new Object();

'.$containerObj.'.layout.B.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.B.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/products/edit_bundled_products','LABEL_BTN_ADD_PRODUCT').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_REMOVE_FROM_LIST').'", "toolbar/delete.png", "toolbar/delete-dis.png");	 		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				'.$containerObj.'.layout.B.toolbar.add('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());	
				break;
			case "delete":
				var checked = '.$containerObj.'.layout.B.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_DELETE_PRODUCTS').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_bundled_products_group_product',array('id'=>$id)).'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){						
								'.$containerObj.'.layout.B.grid.load('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_NO_CHECKED_PRODUCT').'");		
				}			
				break;
		}
	});	
}

'.$containerObj.'.layout.B.toolbar.add = function(id_product_bundled_product_group){
	if (!id_product_bundled_product_group) { alert("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_NO_SELECTED').'"); return false; }
	
	name = "'.Yii::t('views/products/edit_bundled_products','LABEL_BTN_ADD_PRODUCT_TO').' "+'.$containerObj.'.layout.A.grid.obj.cellById(id_product_bundled_product_group,1).getValue();
	

	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("addOptionWindow", 10, 10, 600, 380);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	//'.$containerObj.'.wins.obj.center();	
				
	'.$containerObj.'.wins.toolbar = new Object();

	'.$containerObj.'.wins.toolbar.load = function(id_product_bundled_product_group){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addSeparator("sep01", null);
		obj.addText("default_qty_text", null, "'.Yii::t('views/products/edit_bundled_products','LABEL_DEFAULT_QTY').'");
		obj.addInput("default_qty", null, 1, 30);
		
		obj.objPull[obj.idPrefix+"default_qty"].obj.firstChild.style.textAlign = "center";		
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins.toolbar.save(id);
					break;
				case "save_close":	
					'.$containerObj.'.wins.toolbar.save(id,1);
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
	'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins.toolbar.load(id_product_bundled_product_group);
	
	'.$containerObj.'.wins.toolbar.save = function(id, close){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		var checked = '.$containerObj.'.wins.layout.A.grid.obj.getCheckedRows(0);
		if (checked) {											
			//if (confirm("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_ADD_SELECTED_PRODUCT_TO').' " + layout.B.grid.obj.cellById(id_product_bundled_product_group,1).getValue() + "?")) {
				checked = checked.split(",");
				var ids=[];
				
				for (var i=0;i<checked.length;++i) {
					if (checked[i]) {
						ids.push("ids[]="+checked[i]);									
					}
				}						
				
				$.ajax({
					url: "'.CController::createUrl('add_bundled_products_group_product',array('id'=>$id)).'?id_product_bundled_product_group="+id_product_bundled_product_group,
					type: "POST",
					data: ids.join("&")+"&default_qty="+obj.getValue("default_qty"),
					beforeSend: function(){		
						obj.disableItem(id);
					},
					complete: function(){
						if (typeof obj.enableItem == "function") obj.enableItem(id);
						if (close) '.$containerObj.'.wins.obj.close();
					},
					success: function(data){	
						if (!close)load_grid('.$containerObj.'.wins.layout.A.grid.obj);
						'.$containerObj.'.layout.B.grid.load('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
						alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
					}
				});					
			//}			
		} else {
			alert("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_NO_CHECKED_PRODUCT').'");		
		}			
	}

	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	
	'.$containerObj.'.wins.layout.A.obj.hideHeader();
	
	'.$containerObj.'.wins.layout.A.grid = new Object();
	
	'.$containerObj.'.wins.layout.A.grid.obj = '.$containerObj.'.wins.layout.A.obj.attachGrid();
	'.$containerObj.'.wins.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
	'.$containerObj.'.wins.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center",,,,"text-align:right"]);
	'.$containerObj.'.wins.layout.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
	// custom text filter input
	'.$containerObj.'.wins.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
	
	'.$containerObj.'.wins.layout.A.grid.obj.setInitWidths("40,*,*,*,80,100");
	'.$containerObj.'.wins.layout.A.grid.obj.setColAlign("center,left,left,left,right");
	'.$containerObj.'.wins.layout.A.grid.obj.setColSorting("na,na,na,na,na");
	'.$containerObj.'.wins.layout.A.grid.obj.setSkin(dhx_skin);
	'.$containerObj.'.wins.layout.A.grid.obj.enableDragAndDrop(false);
	'.$containerObj.'.wins.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	//Paging
	'.$containerObj.'.wins.layout.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_win\'></div>");
	'.$containerObj.'.wins.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	'.$containerObj.'.wins.layout.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_win");
	'.$containerObj.'.wins.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
	'.$containerObj.'.wins.layout.A.grid.obj.i18n.paging={
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
	
	'.$containerObj.'.wins.layout.A.grid.obj.init();
	
	// set filter input names
	'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="variant";
	'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="sku";
	'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[4].getElementsByTagName("INPUT")[0].name="price";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	'.$containerObj.'.wins.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_bundled_products_add',array('id'=>$id)).'?id_product_bundled_product_group="+id_product_bundled_product_group;
	
	// load the initial grid
	load_grid('.$containerObj.'.wins.layout.A.grid.obj);		
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		return true;
	});			
	
	'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
		'.$containerObj.'.layout.B.toolbar.modify(rId,'.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
	});
	
	'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});		
}



'.$containerObj.'.layout.B.toolbar.modify = function(current_id,id_product_bundled_product_group){
	name = "'.Yii::t('global','LABEL_EDIT').' "+'.$containerObj.'.layout.B.grid.obj.cellById(current_id,1).getValue()+" "+'.$containerObj.'.layout.B.grid.obj.cellById(current_id,2).getValue();

	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("modifyTagWindow", 10, 10, 420,300);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	//'.$containerObj.'.wins.obj.center();
				
	'.$containerObj.'.wins.toolbar = new Object();
	
	'.$containerObj.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
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
					'.$containerObj.'.wins.toolbar.save(id);
					break;
				case "save_close":
					'.$containerObj.'.wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/products/edit_bundled_products','LABEL_ALERT_DELETE_PRODUCTS').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_bundled_products_group_product',array('id'=>$id)).'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								'.$containerObj.'.wins.obj.close();
							},
							success: function(data){
								'.$containerObj.'.layout.B.grid.load('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());

								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
	'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins.toolbar.load(current_id);
	
	'.$containerObj.'.wins.toolbar.save = function(id,close){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_bundled_products_group_product',array('id'=>$id)).'",
			type: "POST",
			data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");
		
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) {
					'.$containerObj.'.wins.obj.close();
					if(current_id==0) '.$containerObj.'.wins.obj.close();
				}
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_'.$containerObj.'").removeClass("error_background");
				if (data) {
					
					if (data.errors) {
						$.each(data.errors, function(key, value){
							var id_tag_container = "'.$containerObj.'_"+key;
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
						'.$containerObj.'.layout.B.grid.load('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}	
	
	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	'.$containerObj.'.wins.layout.A.obj.hideHeader();	

	$.ajax({
		url: "'.CController::createUrl('edit_bundled_products_group_products',array('container'=>$containerObj,'id'=>$id)).'&id_product_bundled_product_group_product="+current_id+"&id_product_bundled_product_group="+id_product_bundled_product_group,
		type: "POST",
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);	
		}
	});				
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		
		return true;
	});			
}

'.$containerObj.'.layout.B.toolbar.obj = '.$containerObj.'.layout.B.obj.attachToolbar();
'.$containerObj.'.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.B.toolbar.load();

'.$containerObj.'.layout.B.grid = new Object();
'.$containerObj.'.layout.B.grid.obj = '.$containerObj.'.layout.B.obj.attachGrid();
'.$containerObj.'.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').','.Yii::t('global','LABEL_PRICE').','.Yii::t('global','LABEL_DEFAULT').'",null,["text-align:center;",,,,"text-align:center","text-align:right","text-align:center"]);

'.$containerObj.'.layout.B.grid.obj.setInitWidths("40,*,*,*,80,100,100");
'.$containerObj.'.layout.B.grid.obj.setColAlign("center,left,left,left,center,right,center");
'.$containerObj.'.layout.B.grid.obj.setColSorting("na,na,na,na,na,na,na");
'.$containerObj.'.layout.B.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.B.grid.obj.enableDragAndDrop(true);
'.$containerObj.'.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
'.$containerObj.'.layout.B.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_bundled_products_group_products',array('id'=>$id)).'";

'.$containerObj.'.layout.B.grid.load = function(current_id,callback){
	var obj = '.$containerObj.'.layout.B.grid.obj;
	
	obj.clearAll();
	if (current_id) obj.loadXML(obj.xmlOrigFileUrl+"?id_product_bundled_product_group="+current_id,callback);	
};

'.$containerObj.'.layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.B.toolbar.modify(rId,'.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
});

'.$containerObj.'.layout.B.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	var obj = this;

	var rows=this.getAllRowIds().split(",");
	var ids=[];

	for (var i=0;i<rows.length;++i) {
		if (rows[i]) {
			ids.push("ids[]="+rows[i]);									
		}
	}
	$.ajax({
		url: "'.CController::createUrl('save_bundled_products_group_products_sort_order').'?id="+'.$containerObj.'.layout.A.grid.obj.getSelectedRowId(),
		type: "POST",
		data: ids.join("&")
	});	
});

'.$containerObj.'.layout.B.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	if (cInd == 6) {
		$.ajax({
			url: "'.CController::createUrl('toggle_default_bundled_products_group_product').'",
			type: "POST",
			data: { "id":rId }
		});		
	}
});

'.$containerObj.'.layout.B.grid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

'.$containerObj.'.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

$(window).resize(function(){
	setTimeout("'.$containerObj.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();

enable_grid_toolbar('.$containerObj.'.layout.B.grid.obj,'.$containerObj.'.layout.B.toolbar.obj, '.$containerObj.'.layout.A.grid.obj);


';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>