<?php 
$app = Yii::app();

$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$image_base_url = $app->params['product_images_base_url'].'suggest/';

switch ($app->params['images_orientation']) {
    case 'portrait':
        $dataview_item_width = $app->params['portrait_thumb_width'];
        $dataview_item_height = $app->params['portrait_thumb_height'];
        break;
    case 'landscape':
        $dataview_item_width = $app->params['landscape_thumb_width'];
        $dataview_item_height = $app->params['landscape_thumb_height'];
        break;
}

$script = '


'.$containerObj.' = new Object();

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerObj.'.wins = new Object();
'.$containerObj.'.wins_image = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "3U");

/************************************************************
*															*
*															*
*						LAYOUT A							*
*															*
*															*
************************************************************/

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();
'.$containerObj.'.layout.A.obj.setHeight(180);

'.$containerObj.'.layout.A.toolbar = new Object();

'.$containerObj.'.layout.A.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.A.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/products/edit_variants','LABEL_BTN_ADD_GROUP').'","toolbar/add.gif","toolbar/add_dis.gif");  	
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
					$.ajax({
						url: "'.CController::createUrl('count_variants',array('id'=>$id)).'",
						type: "POST",
						data: { "ids[]":current_id },
						success: function(data){														
							if(data=="orders") {
								alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_CANNOT_DELETE_ONCE_ORDERS').'");	
							} else {
								if (confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_DELETE_GROUP').'")) {
									checked = checked.split(",");
									var ids=[];
									
									for (var i=0;i<checked.length;++i) {
										if (checked[i]) {
											ids.push("ids[]="+checked[i]);									
										}
									}						
									$.ajax({
										url: "'.CController::createUrl('delete_variant_group').'",
										type: "POST",
										data: ids.join("&")+"&pass=0&id_product='.$id.'",
										dataType: "json",							
										success: function(data){													
											if(data.in_other_product==1 && confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_COMBO_BUNDLED').'") || data.in_other_product!=1){
												$.ajax({
													url: "'.CController::createUrl('delete_variant_group').'",
													type: "POST",
													data: ids.join("&")+"&pass=1&id_product='.$id.'",
													beforeSend: function(){		
														obj.disableItem(id);
													},
													complete: function(){
														if (typeof obj.enableItem == "function") obj.enableItem(id);
													},							
													success: function(data){													
														load_grid('.$containerObj.'.layout.A.grid.obj);
														'.$containerObj.'.layout.B.grid.load();
														load_grid('.$containerObj.'.layout.C.grid.obj);
														load_grid(tabs.list.grid.obj);
														alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
													}
												});	
											}
										}
									});				
								}
							}  
						}
					});				
				} else {
					alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_NO_CHECKED_GROUP').'");		
				}
				break;
			case "template_load":
				$.ajax({
					url: "'.CController::createUrl('count_variants',array('id'=>$id)).'",
					type: "POST",
					data: { "ids[]":current_id },
					success: function(data){														
						if(data=="orders") {
							alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_CANNOT_LOAD_TEMPLATE_ONCE_ORDERS').'");	
						} else {
							'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_loadTemplateWindow", 10, 10, 700, 420);
							'.$containerObj.'.wins.obj.setText("'.Yii::t('views/products/edit_variants','LABEL_TITLE_LOAD_FROM_TEMPLATE').'");
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
											var id_tpl_product_variant_category = '.$containerObj.'.wins.layout.A.grid.obj.getSelectedRowId();
											
											if (!id_tpl_product_variant_category) { alert("'.Yii::t('global','LABEL_ALERT_TEMPLATE_NO_SELECTED').'"); return false; }
										
											if (confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_APPLY_TEMPLATE').'")){								
												$.ajax({
													url: "'.CController::createUrl('apply_variant_template',array('id'=>$id)).'",
													type: "POST",
													data: { "id_tpl_product_variant_category":id_tpl_product_variant_category },
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
														alert("'.Yii::t('global','LABEL_ALERT_TEMPLATE_SUCCESS').'");
														'.$containerObj.'.wins.obj.close();
																		
														load_grid('.$containerObj.'.layout.A.grid.obj);
														'.$containerObj.'.layout.B.grid.load();
														load_grid('.$containerObj.'.layout.C.grid.obj);											
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
							'.$containerObj.'.wins.layout.A.obj.setWidth(300);
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
							'.$containerObj.'.wins.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
							'.$containerObj.'.wins.layout.A.grid.obj.init();
							
							// set filter input names
							'.$containerObj.'.wins.layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[0].name="name";
							
							// we create a variable to store the default url used to get our grid data, so we can reuse it later
							'.$containerObj.'.wins.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_load_variant_group_template').'";
							
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
								if (current_id) obj.loadXML(obj.xmlOrigFileUrl+"?id="+current_id,function(){
									obj.groupBy(1);
								});	
							};				
							
							'.$containerObj.'.wins.layout.B.grid.obj = '.$containerObj.'.wins.layout.B.obj.attachGrid();
							'.$containerObj.'.wins.layout.B.grid.obj.setImagePath(dhx_globalImgPath);
							'.$containerObj.'.wins.layout.B.grid.obj.setHeader("'.Yii::t('views/products/edit_variants','LABEL_OPTION').','.Yii::t('views/products/edit_variants','LABEL_GROUP').'");
							'.$containerObj.'.wins.layout.B.grid.obj.setInitWidths("*,*");
							'.$containerObj.'.wins.layout.B.grid.obj.setColAlign("left,left");
							'.$containerObj.'.wins.layout.B.grid.obj.setColSorting("na,na");
							'.$containerObj.'.wins.layout.B.grid.obj.setColumnsVisibility("false,true");
							'.$containerObj.'.wins.layout.B.grid.obj.setSkin(dhx_skin);
							'.$containerObj.'.wins.layout.B.grid.obj.init();
							'.$containerObj.'.wins.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_load_variant_template').'";			
			
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
						}
					}
				});
				break;
			case "template_save":
				$.ajax({
					url: "'.CController::createUrl('count_variant_groups',array('container'=>$containerObj,'id'=>$id)).'",
					type: "POST",					
					success: function(data){	
						if (data > 0) {						
							'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_saveTemplateWindow", 10, 10, 400, 180);
							'.$containerObj.'.wins.obj.setText("'.Yii::t('views/products/edit_variants','LABEL_TITLE_SAVE_TEMPLATE').'");
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
							
								obj.attachEvent("onClick",function(id){
									switch (id) {
										case "save":	
											$.ajax({
												url: "'.CController::createUrl('save_variant_template').'",
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
								url: "'.CController::createUrl('add_variant_template',array('container'=>$containerObj,'id'=>$id)).'",
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
							alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_NO_CREATED_GROUP').'");	
						}
					}
				});							
				break;
		}
	});	
};

'.$containerObj.'.layout.A.toolbar.add = function(current_id, name){
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/products/edit_variants','LABEL_BTN_ADD_GROUP').'";	
	
	$.ajax({
		url: "'.CController::createUrl('count_variants',array('id'=>$id)).'",
		type: "POST",
		data: { "ids[]":current_id },
		success: function(data){														
			if (data == 0 || current_id) {
				'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addGroupWindow", 10, 10, 500, 300);
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
								if (confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_DELETE_GROUP').'")) {
									$.ajax({
										url: "'.CController::createUrl('delete_variant_group').'",
										type: "POST",
										data: { "ids[]":current_id+"&id_product='.$id.'" },
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
											load_grid('.$containerObj.'.layout.C.grid.obj);
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
						url: "'.CController::createUrl('save_variant_group').'",
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
										var id_tag_container = "'.$containerObj.'_product_variant_group_description['.Yii::app()->language.'][name]";
										var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
										$("#'.$containerObj.'_id").val(data.id);
										'.$containerObj.'.wins.toolbar.load(data.id);
										'.$containerObj.'.wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
									}
									'.$containerObj.'.layout.A.grid.obj.selectRowById(data.id);
								}
							} else {
								alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
							}
						}
					});	
				}			
				
				'.$containerObj.'.wins.layout = new Object();
				'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("2U");
				
				'.$containerObj.'.wins.layout.A = new Object();
				'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
				'.$containerObj.'.wins.layout.A.obj.hideHeader();
				'.$containerObj.'.wins.layout.A.obj.setWidth(300);
				'.$containerObj.'.wins.layout.A.obj.fixSize(false,false);
														
				'.$containerObj.'.wins.layout.A.tabs = new Object();
				'.$containerObj.'.wins.layout.A.tabs.obj = '.$containerObj.'.wins.layout.A.obj.attachTabbar();
				'.$containerObj.'.wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
				'.$containerObj.'.wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_variant_group_description',array('container'=>$containerObj)).'&id="+current_id);
				
				'.$containerObj.'.wins.layout.B = new Object();
				'.$containerObj.'.wins.layout.B.obj = '.$containerObj.'.wins.layout.obj.cells("b");
				'.$containerObj.'.wins.layout.B.obj.hideHeader();	
				
				$.ajax({
					url: "'.CController::createUrl('edit_variants_group',array('container'=>$containerObj,'id_product'=>$id)).'",
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
			} else if(data=="orders") {
				alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_CANNOT_ADD_ONCE_ORDERS').'");	
			} else {
				alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_CANNOT_ADD_ONCE_GENERATED').'");	
			}
		}
	});			
}

'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.load();

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_INPUT_TYPE').',",null,["text-align:center"]);

'.$containerObj.'.layout.A.grid.obj.setInitWidthsP("8,60,32,0");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,left");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.setColumnsVisibility("false,false,false,true");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,true,true,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableDragAndDrop(true);
'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
'.$containerObj.'.layout.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea\'></div>");
'.$containerObj.'.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerObj.'.layout.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea");
'.$containerObj.'.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
'.$containerObj.'.layout.A.grid.obj.i18n.paging={
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
'.$containerObj.'.layout.A.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_variant_groups',array('id'=>$id)).'";
// load the initial grid
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onSelectStateChanged", function(id,ind){
	'.$containerObj.'.layout.B.grid.load(id, enable_grid_toolbar('.$containerObj.'.layout.B.grid.obj,'.$containerObj.'.layout.B.toolbar.obj, '.$containerObj.'.layout.A.grid.obj));
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	$.ajax({
		url: "'.CController::createUrl('count_variants',array('id'=>$id)).'",
		type: "POST",
		success: function(data){														
			if (data == 0) {	
				var obj = '.$containerObj.'.layout.A.grid.obj;
				
				var rows=obj.getAllRowIds().split(",");
				var ids=[];
			
				for (var i=0;i<rows.length;++i) {
					if (rows[i]) {
						ids.push("ids[]="+rows[i]);									
					}
				}
				
				$.ajax({
					url: "'.CController::createUrl('save_variant_group_sort_order',array('id'=>$id)).'",
					type: "POST",
					data: ids.join("&"),
					success: function(data){
						if (data) {
							alert(data);							
							load_grid(obj);
						} else{
							load_grid('.$containerObj.'.layout.C.grid.obj);		
						}
					}
				});	
			} else {
				alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_CANNOT_CHANGE_ORDER_ONCE_GENERATED').'");
				load_grid('.$containerObj.'.layout.A.grid.obj);		
				load_grid('.$containerObj.'.layout.B.grid.obj);		
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


/************************************************************
*															*
*															*
*						LAYOUT B							*
*															*
*															*
************************************************************/


'.$containerObj.'.layout.B = new Object();
'.$containerObj.'.layout.B.obj = '.$containerObj.'.layout.obj.cells("b");
'.$containerObj.'.layout.B.obj.hideHeader();

'.$containerObj.'.layout.B.toolbar = new Object();

'.$containerObj.'.layout.B.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.B.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/products/edit_variants','LABEL_BTN_ADD_OPTION').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
			

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				'.$containerObj.'.layout.B.toolbar.add('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
				break;							
			case "delete":
				var checked = '.$containerObj.'.layout.B.grid.obj.getCheckedRows(0);
				
				if (checked) {
					if (confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_DELETE_OPTION').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);							
							}
						}
						$.ajax({
							url: "'.CController::createUrl('count_orders_variants',array('id'=>$id)).'",
							type: "POST",
							data: ids.join("&"),
							success: function(data){														
								if(data=="orders") {
									alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_CANNOT_DELETE_OPTION_ONCE_ORDERS').' ("+data+")");
								} else {

									$.ajax({
										url: "'.CController::createUrl('delete_variant_group_option').'",
										type: "POST",
										data: ids.join("&")+"&pass=0",
										dataType: "json",							
										success: function(data){													
											if(data.in_other_product==1 && confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_COMBO_BUNDLED').'") || data.in_other_product!=1){
												$.ajax({
													url: "'.CController::createUrl('delete_variant_group_option').'",
													type: "POST",
													data: ids.join("&")+"&pass=1",
													beforeSend: function(){		
														obj.disableItem(id);
													},
													complete: function(){
														if (typeof obj.enableItem == "function") obj.enableItem(id);
													},							
													success: function(data){													
														'.$containerObj.'.layout.B.grid.load('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
														load_grid('.$containerObj.'.layout.C.grid.obj);
														load_grid(tabs.list.grid.obj);
														alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
													}
												});	
											}
										}
									});	
								}
							}
						});	
						
						
						
											
						
					}
				} else {
					alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_NO_CHECKED_OPTION').'");		
				}			
				break;
		}
	});	
}

'.$containerObj.'.layout.B.toolbar.add = function(id_product_variant_group, current_id, name, input_type){
	if (!id_product_variant_group) { alert("'.Yii::t('views/products/edit_variants','LABEL_BTN_ADD_OPTION').'"); return false; }
	
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/products/edit_variants','LABEL_BTN_ADD_OPTION').'";
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addOptionWindow", 10, 10, 630, 320);
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
					if (confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_DELETE_OPTION').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_variant_group_option').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){						
								'.$containerObj.'.wins.obj.close();
								load_grid('.$containerObj.'.layout.B.grid.obj);
								load_grid('.$containerObj.'.layout.C.grid.obj);
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
			url: "'.CController::createUrl('save_variant_group_option').'",
			type: "POST",
			data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize()+"&id_product_variant_group="+id_product_variant_group,
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
						var id_tag_container = "'.$containerObj.'_product_variant_group_option_description['.Yii::app()->language.'][name]";
						var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");						
											
						'.$containerObj.'.layout.B.grid.load('.$containerObj.'.layout.A.grid.obj.getSelectedRowId());
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						$("#'.$containerObj.'_id").val(data.id);
						$("#'.$containerObj.'_filename").val(data.filename);
						$("#'.$containerObj.'_old_filename").val(data.old_filename);
						'.$containerObj.'.wins.toolbar.load(data.id);
						
						'.$containerObj.'.wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());	
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}	
	
	
	if('.$containerObj.'.layout.A.grid.obj.cellById('.$containerObj.'.layout.A.grid.obj.getSelectedRowId(),3).getValue()==2){
	
		'.$containerObj.'.wins.layout = new Object();
		'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("2U");
		
		'.$containerObj.'.wins.layout.A = new Object();
		'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
		'.$containerObj.'.wins.layout.A.obj.hideHeader();
		'.$containerObj.'.wins.layout.A.obj.setWidth(250);
		'.$containerObj.'.wins.layout.A.obj.fixSize(false,false);	
		
		'.$containerObj.'.wins.layout.A.tabs = new Object();
		'.$containerObj.'.wins.layout.A.tabs.obj = '.$containerObj.'.wins.layout.A.obj.attachTabbar();
		'.$containerObj.'.wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
		'.$containerObj.'.wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_variant_group_option_description',array('container'=>$containerObj)).'&id="+current_id);
			
		'.$containerObj.'.wins.layout.B = new Object();
		'.$containerObj.'.wins.layout.B.obj = '.$containerObj.'.wins.layout.obj.cells("b");
		'.$containerObj.'.wins.layout.B.obj.hideHeader();	
		
		$.ajax({
			url: "'.CController::createUrl('edit_variants_group_option',array('container'=>$containerObj)).'",
			type: "POST",
			data: { "id":current_id,"id_product_variant_group":id_product_variant_group },
			success: function(data){
				'.$containerObj.'.wins.layout.B.obj.attachHTMLString(data);		
			}
		});	
	
	}else{
		'.$containerObj.'.wins.layout = new Object();
		'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
		
		'.$containerObj.'.wins.layout.A = new Object();
		'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
		'.$containerObj.'.wins.layout.A.obj.hideHeader();
		'.$containerObj.'.wins.layout.A.obj.setWidth(125);
		'.$containerObj.'.wins.layout.A.obj.fixSize(false,false);	
		
		'.$containerObj.'.wins.layout.A.tabs = new Object();
		'.$containerObj.'.wins.layout.A.tabs.obj = '.$containerObj.'.wins.layout.A.obj.attachTabbar();
		'.$containerObj.'.wins.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
		'.$containerObj.'.wins.layout.A.tabs.obj.loadXML("'.CController::createUrl('xml_list_variant_group_option_description',array('container'=>$containerObj)).'&id="+current_id);
		
	}
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		// To correct a Bug in SWF Uploadify
		var swfuploadify = window["uploadify_'.$containerObj.'_button"];
		if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();		
		
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
}

'.$containerObj.'.layout.B.toolbar.obj = '.$containerObj.'.layout.B.obj.attachToolbar();
'.$containerObj.'.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.B.toolbar.load();

'.$containerObj.'.layout.B.grid = new Object();
'.$containerObj.'.layout.B.grid.obj = '.$containerObj.'.layout.B.obj.attachGrid();
'.$containerObj.'.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').'",null,["text-align:center"]);

'.$containerObj.'.layout.B.grid.obj.setInitWidths("40,*");
'.$containerObj.'.layout.B.grid.obj.setColAlign("center,left");
'.$containerObj.'.layout.B.grid.obj.setColSorting("na,na");
'.$containerObj.'.layout.B.grid.obj.enableResizing("false,false");
'.$containerObj.'.layout.B.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.B.grid.obj.enableDragAndDrop(true);
'.$containerObj.'.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
'.$containerObj.'.layout.B.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_b\'></div>");
'.$containerObj.'.layout.B.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerObj.'.layout.B.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_b");
'.$containerObj.'.layout.B.grid.obj.setPagingSkin("toolbar", dhx_skin);
'.$containerObj.'.layout.B.grid.obj.i18n.paging={
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
'.$containerObj.'.layout.B.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_variant_group_options').'";

'.$containerObj.'.layout.B.grid.load = function(current_id,callback){
	var obj = '.$containerObj.'.layout.B.grid.obj;
	
	obj.clearAll();
	if (current_id) obj.loadXML('.$containerObj.'.layout.B.grid.obj.xmlOrigFileUrl+"?id="+current_id,callback);	
};

'.$containerObj.'.layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.B.toolbar.add('.$containerObj.'.layout.A.grid.obj.getSelectedRowId(),rId,this.cellById(rId,1).getValue());
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
		url: "'.CController::createUrl('save_variant_group_option_sort_order').'?id="+'.$containerObj.'.layout.A.grid.obj.getSelectedRowId(),
		type: "POST",
		data: ids.join("&"),
		success: function(data){
			load_grid('.$containerObj.'.layout.C.grid.obj);
		}
	});			
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

/************************************************************
*															*
*															*
*						LAYOUT C							*
*															*
*															*
************************************************************/


'.$containerObj.'.layout.C = new Object();
'.$containerObj.'.layout.C.obj = '.$containerObj.'.layout.obj.cells("c");
'.$containerObj.'.layout.C.obj.hideHeader();

'.$containerObj.'.layout.C.toolbar = new Object();

'.$containerObj.'.layout.C.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.C.toolbar.obj;
	var title = "'.Yii::t('views/products/edit_variants','LABEL_VARIANTS_UNDER').' "+tabs.obj.getLabel("'.$container.'")+" - '.Yii::t('views/products/edit_variants','LABEL_PRODUCTS').'";
	
	obj.addButton("generate",null,"'.Yii::t('views/products/edit_variants','LABEL_BTN_GENERATE').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('views/products/edit_variants','LABEL_BTN_DELETE_ALL_VARIANTS').'", "toolbar/delete.png", "toolbar/delete-dis.png");	
	obj.addSeparator("sep1", null);
	obj.addButton("images",null,"'.Yii::t('views/products/edit_variants','LABEL_BTN_IMAGES').'","toolbar/upload.png","toolbar/upload_dis.png"); 
	obj.addSeparator("sep2", null);
	obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);		

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "generate":		
				'.$containerObj.'.layout.C.toolbar.generate(id);	
				break;		
			case "images":
				'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_VariantImagesWindow", 10, 10, 500, 420);
				'.$containerObj.'.wins.obj.setText("Images");
				'.$containerObj.'.wins.obj.button("park").hide();
				'.$containerObj.'.wins.obj.keepInViewport(true);
				'.$containerObj.'.wins.obj.setModal(true);
				
				'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
					'.$containerObj.'.wins = new Object();
					
					return true;
				});											
				
				'.$containerObj.'.wins.grid = new Object();
				'.$containerObj.'.wins.grid.obj = '.$containerObj.'.wins.obj.attachGrid();					
				'.$containerObj.'.wins.grid.obj.setImagePath(dhx_globalImgPath);
				'.$containerObj.'.wins.grid.obj.setHeader("'.Yii::t('global','LABEL_VARIANT').','.Yii::t('views/products/edit_variants','LABEL_BTN_IMAGES').','.Yii::t('views/products/edit_variants','LABEL_DISPLAY_LISTING').'",null,[,"text-align:center;","text-align:center;"]);
				'.$containerObj.'.wins.grid.obj.setInitWidths("*,80,*");
				'.$containerObj.'.wins.grid.obj.setColAlign("left,center,center");
				'.$containerObj.'.wins.grid.obj.setColSorting("na,na,na");
				'.$containerObj.'.wins.grid.obj.enableResizing("false,false,false");
				'.$containerObj.'.wins.grid.obj.setSkin(dhx_skin);
				'.$containerObj.'.wins.grid.obj.enableMultiline(true);
				'.$containerObj.'.wins.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);				
				'.$containerObj.'.wins.grid.obj.init();		
				
				// we create a variable to store the default url used to get our grid data, so we can reuse it later
				'.$containerObj.'.wins.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_product_image_variants',array('id'=>$id)).'";
				// load the initial grid
				load_grid('.$containerObj.'.wins.grid.obj);
				
				'.$containerObj.'.wins.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
					'.$containerObj.'.wins.upload_images(rId,this.cellById(rId,0).getValue());
				});
				
				'.$containerObj.'.wins.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
					if (cInd == 2) {
						$.ajax({
							url: "'.CController::createUrl('toggle_variant_image_displayed',array('id_product'=>$id)).'",
							type: "POST",
							data: { "id":rId,"displayed_in_listing":state }
						});	
					}
				});				
				
				'.$containerObj.'.wins.grid.obj.attachEvent("onXLS", function(grid_obj){
					ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
				}); 
				
				'.$containerObj.'.wins.grid.obj.attachEvent("onXLE", function(grid_obj,count){
					ajaxOverlay(grid_obj.entBox.id,0);
				});
				
				'.$containerObj.'.wins.upload_images = function(current_id, variant){
					'.$containerObj.'.wins_image.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_uploadVariantImagesWindow", 20, 20, 700, 400);
					'.$containerObj.'.wins_image.obj.setText("'.Yii::t('global','LABEL_BTN_UPLOAD_IMAGE').'"+" - "+variant);
					'.$containerObj.'.wins_image.obj.button("park").hide();
					'.$containerObj.'.wins_image.obj.keepInViewport(true);
					
					'.$containerObj.'.wins.obj.setModal(false);	
					'.$containerObj.'.wins_image.obj.setModal(true);
					
					'.$containerObj.'.wins_image.tabs = new Object();
					'.$containerObj.'.wins_image.tabs.obj = '.$containerObj.'.wins_image.obj.attachTabbar();
					'.$containerObj.'.wins_image.tabs.obj.setImagePath(dhx_globalImgPath);
					'.$containerObj.'.wins_image.tabs.obj.addTab("images","'.Yii::t('views/products/edit_variants','LABEL_BTN_IMAGES').'","*");
					'.$containerObj.'.wins_image.tabs.obj.addTab("errors","'.Yii::t('views/products/edit_variants','LABEL_ERRORS').'","*");
					'.$containerObj.'.wins_image.tabs.obj.setTabActive("images");
					
					'.$containerObj.'.wins_image.tabs.images = new Object();
					'.$containerObj.'.wins_image.tabs.images.obj = '.$containerObj.'.wins_image.tabs.obj.cells("images");
					
					'.$containerObj.'.wins_image.tabs.images.layout = new Object();					
					'.$containerObj.'.wins_image.tabs.images.layout.obj = '.$containerObj.'.wins_image.tabs.images.obj.attachLayout("2U");						
					
					// upload images
					'.$containerObj.'.wins_image.tabs.images.layout.A = new Object();
					'.$containerObj.'.wins_image.tabs.images.layout.A.obj = '.$containerObj.'.wins_image.tabs.images.layout.obj.cells("a");
					'.$containerObj.'.wins_image.tabs.images.layout.A.obj.hideHeader();			
					'.$containerObj.'.wins_image.tabs.images.layout.A.obj.setWidth(200);																																
					
					'.$containerObj.'.wins_image.tabs.images.layout.A.toolbar = new Object();
					'.$containerObj.'.wins_image.tabs.images.layout.A.toolbar.obj = '.$containerObj.'.wins_image.tabs.images.layout.A.obj.attachToolbar();
					'.$containerObj.'.wins_image.tabs.images.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
					'.$containerObj.'.wins_image.tabs.images.layout.A.toolbar.obj.addButton("upload",null,"'.Yii::t('global','LABEL_BTN_UPLOAD').'","toolbar/upload.png","toolbar/upload_dis.png"); 
					
					$.ajax({
						url: "'.CController::createUrl('edit_variants_image_upload',array('container'=>$containerObj)).'",
						type: "POST",
						data: { "id":current_id },
						success: function(data){
							'.$containerObj.'.wins_image.tabs.images.layout.A.obj.attachHTMLString(data);		
						}
					});						
					
					'.$containerObj.'.wins_image.tabs.images.layout.A.toolbar.obj.attachEvent("onClick",function(id){
						var obj = this;
						
						switch (id) {
							case "upload":
								if ($("#'.$containerObj.'_queue div").hasClass("uploadify-queue-item")) {				
									$("#'.$containerObj.'_button").uploadify("upload","*");
								} else {
									alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_NO_IMAGE_SELECTED').'");	
								}								
								break;
						}
					});
												

					'.$containerObj.'.wins_image.tabs.images.layout.B = new Object();
					'.$containerObj.'.wins_image.tabs.images.layout.B.obj = '.$containerObj.'.wins_image.tabs.images.layout.obj.cells("b");
					'.$containerObj.'.wins_image.tabs.images.layout.B.obj.hideHeader();																														
					
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar = new Object();
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj = '.$containerObj.'.wins_image.tabs.images.layout.B.obj.attachToolbar();
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);	
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj.addButton("set_cover",null,"'.Yii::t('global','LABEL_BTN_SET_COVER_IMAGE').'","toolbar/green_check.png","toolbar/green_check_dis.png");
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj.addSeparator("sep01", null);
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj.addButton("select_all",null,"'.Yii::t('global','LABEL_BTN_SELECT_ALL_IMAGE').'",null,null);
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj.addButton("unselect_all",null,"'.Yii::t('global','LABEL_BTN_UNSELECT_ALL_IMAGE').'",null,null);
					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	

					'.$containerObj.'.wins_image.tabs.images.layout.B.toolbar.obj.attachEvent("onClick",function(id){
						var obj = this;
						
						switch (id) {
							case "set_cover":
								var rows='.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.getSelected({ as_array: true });
								
								if (rows.length) {
									var id_product_image;
									for (var i=0;i<1;++i) {
										if (rows[i]) {
											id_product_image = rows[i];
										}
									}							
									
									$.ajax({
										url: "'.CController::createUrl('set_cover_image_variant').'",
										type: "POST",
										data: { "id_product_image":id_product_image,"id":current_id },
										beforeSend: function(){		
											obj.disableItem(id);
										},
										complete: function(){
											if (typeof obj.enableItem == "function") obj.enableItem(id);	
										},								
										success: function(data){
											'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.load();	
										}
									});				
								} else {
									alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_NO_IMAGE_SELECTED').'");	
								}
								break;				
							case "select_all":
								'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.selectAll();
								break;
							case "unselect_all":
								'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.unselectAll();
								break;				
							case "delete":
								var rows='.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.getSelected({ as_array: true });
								
								if (rows.length) {
									if (confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_DELETE_IMAGE').'")) {
										var ids=[];
									
										for (var i=0;i<rows.length;++i) {
											if (rows[i]) {
												ids.push("ids[]="+rows[i]);									
											}
										}
										
										$.ajax({
											url: "'.CController::createUrl('delete_image_variant').'",
											type: "POST",
											data: ids.join("&")+"&id="+current_id,
											beforeSend: function(){		
												obj.disableItem(id);
											},
											complete: function(){
												if (typeof obj.enableItem == "function") obj.enableItem(id);	
											},							
											success: function(data){													
												'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.load();
												load_grid('.$containerObj.'.wins.grid.obj);
												alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
											}
										});						
									}
								} else {
									alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_NO_IMAGE_SELECTED').'");	
								}				
								break;								
						}
					});								
							
					'.$containerObj.'.wins_image.tabs.images.layout.B.dataview = new Object();							
					'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj = '.$containerObj.'.wins_image.tabs.images.layout.B.obj.attachDataView({
						type: {
							template: function(obj){	
								var tooltip = "'.Yii::t('views/products/edit_variants','LABEL_DOUBLE_CLICK_CROP').'";
							
								if (obj.force_crop == "1") {				
									tooltip = tooltip+" '.Yii::t('views/products/edit_variants','LABEL_NEED_TO_CROP').'";
								} 
								
								return \'<div style="width:'.($dataview_item_width).'px; height:100%; padding:10px; text-align: center; " \'+((!obj.$selected && obj.force_crop == "1") ? \'class="force_crop"\':"")+\'><img border="0" src="'.$image_base_url.'\'+obj.filename+\'" width="\'+obj.width_current+\'" height="\'+obj.height_current+\'" ondragstart="javascript:return false;" title="\'+tooltip+\'" style="border: 1px solid #303030;" />\'+((obj.cover == "1") ? \'<div style="margin-top:5px">'.CHtml::image(Html::themeImageUrl('green_check.png'),'',array('width'=>20,'height'=>20,'border'=>0)).'</div>\':"")+\'</div>\';
							},		
							height: '.($dataview_item_height+40).',
							width: '.($dataview_item_width+20).',
							padding: 0
						},	
						select:"multiselect",
						drag:true,
						auto_scroll: true
					});
					
					'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.load = function(){
						'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.clearAll();
						'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.load("'.CController::createUrl('xml_list_variant_images').'?id="+current_id);
					}					
							
					'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.load();							
					
					'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.attachEvent("onItemDblClick", function (id, ev, html){	
						'.$containerObj.'.wins2 = new Object();
						'.$containerObj.'.wins2.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_cropWindow", 40, 40, 500, 350);
						'.$containerObj.'.wins2.obj.setText("Crop image");
						'.$containerObj.'.wins2.obj.button("park").hide();
						'.$containerObj.'.wins2.obj.keepInViewport(true);
						'.$containerObj.'.wins_image.obj.setModal(false);
						'.$containerObj.'.wins2.obj.setModal(true);
						
						'.$containerObj.'.wins2.toolbar = new Object();
						'.$containerObj.'.wins2.toolbar.obj = '.$containerObj.'.wins2.obj.attachToolbar();
						'.$containerObj.'.wins2.toolbar.obj.setIconsPath(dhx_globalImgPath);	
						'.$containerObj.'.wins2.toolbar.obj.addButton("crop_save",null,"'.Yii::t('global','LABEL_BTN_CROP_IMAGE_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
						'.$containerObj.'.wins2.toolbar.obj.addSeparator("sep01", null);
						'.$containerObj.'.wins2.toolbar.obj.addButton("rotate_left",null,"'.Yii::t('global','LABEL_BTN_ROTATE_LEFT_IMAGE').'","toolbar/rotate-left.png","toolbar/rotate-left-dis.png"); 
						'.$containerObj.'.wins2.toolbar.obj.addButton("rotate_right",null,"'.Yii::t('global','LABEL_BTN_ROTATE_RIGHT_IMAGE').'","toolbar/rotate-right.png","toolbar/rotate-right-dis.png"); 							
					
						'.$containerObj.'.wins2.crop_image = function(id, rotate){								
							$.ajax({
								url: "'.CController::createUrl('edit_variants_image_crop',array('container'=>$containerObj)).'",
								type: "POST",
								data: { "id":id, "id_product_image_variant":current_id, "rotate":rotate },
								success: function(data){
									'.$containerObj.'.wins2.obj.attachHTMLString(data);		
								}
							});				
						}	
						
						'.$containerObj.'.wins2.crop_image(id);					
						
						'.$containerObj.'.wins2.toolbar.obj.attachEvent("onClick",function(id){
							var obj = this;
							
							switch (id) {
								case "crop_save":	
									// check if we have a selection
									if ($("#'.$containerObj.'_w").val() > 0) {									
										// ajax request				
										$.ajax({
											url: "'.CController::createUrl('crop_and_save_variant').'",
											type: "POST",
											data: $("#'.$containerObj.'_form *").serialize(),
											beforeSend: function(){		
												obj.disableItem(id);
											},
											complete: function(){
												if (typeof obj.enableItem == "function") obj.enableItem(id);	
											},							
											success: function(data) {
												if (data == "true") { 				
													'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.load();					
													'.$containerObj.'.wins2.obj.close();
												} else {
													alert(data);
												}
											}
										});

									} else {
										alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_MUST_MAKE_SELECTION').'");	
									}
									break;
								case "rotate_left":
									var current_id = parseInt($("#'.$containerObj.'_crop_image_id").val());
									var rotate = parseInt($("#'.$containerObj.'_rotate").val());
								
									'.$containerObj.'.wins2.crop_image(current_id,rotate-90);
									break;
								case "rotate_right":
									var current_id = parseInt($("#'.$containerObj.'_crop_image_id").val());
									var rotate = parseInt($("#'.$containerObj.'_rotate").val());
								
									'.$containerObj.'.wins2.crop_image(current_id,rotate+90);			
									break;									
							}
						});	
						
						// clean variables
						'.$containerObj.'.wins2.obj.attachEvent("onClose",function(win){
							var obj = this;
							obj.setModal(false);

							'.$containerObj.'.wins_image.obj.setModal(true);
							'.$containerObj.'.wins2 = new Object();
							
							return true;
						});	
							
							
						//any custom logic here
						return true;
					});
					
					'.$containerObj.'.wins_image.tabs.images.layout.B.dataview.obj.attachEvent("onAfterDrop", function (context,e){
						//any custom logic here
						var obj = this;
						var iTotal = obj.dataCount();
						var ids=[];	
						
						for (var i=0; i<iTotal; i++) {
							ids.push("ids[]="+obj.idByIndex(i));						
						}
						
						$.ajax({
							url: "'.CController::createUrl('save_image_sort_order_variant').'",
							type: "POST",
							data: ids.join("&")+"&id="+current_id
						});									
					});							
										
					'.$containerObj.'.wins_image.tabs.errors = new Object();
					'.$containerObj.'.wins_image.tabs.errors.obj = '.$containerObj.'.wins_image.tabs.obj.cells("errors");
					
					'.$containerObj.'.wins_image.tabs.errors.toolbar = new Object();
					'.$containerObj.'.wins_image.tabs.errors.toolbar.obj = '.$containerObj.'.wins_image.tabs.errors.obj.attachToolbar();
					'.$containerObj.'.wins_image.tabs.errors.toolbar.obj.setIconsPath(dhx_globalImgPath);	
					'.$containerObj.'.wins_image.tabs.errors.toolbar.obj.addButton("clear",null,"'.Yii::t('global','LABEL_BTN_CLEAR_ALL').'","toolbar/delete.png","toolbar/delete_dis.png"); 
					
					'.$containerObj.'.wins_image.tabs.errors.toolbar.obj.attachEvent("onClick",function(id){
						switch (id) {
							case "clear":						
								'.$containerObj.'.wins_image.tabs.obj.setCustomStyle("errors",null,null,null);
								'.$containerObj.'.wins_image.tabs.errors.grid.obj.clearAll();
								break;
						}
	
					});										
					
					'.$containerObj.'.wins_image.tabs.errors.grid = new Object();
					'.$containerObj.'.wins_image.tabs.errors.grid.obj = '.$containerObj.'.wins_image.tabs.errors.obj.attachGrid();
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.setImagePath(dhx_globalImgPath);
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.setHeader("Image,Error");
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.setInitWidths("*,*");
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.setColAlign("left,left");
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.setColSorting("na,na");
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.enableResizing("false,false");
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.setSkin(dhx_skin);
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.enableMultiline(true);
					'.$containerObj.'.wins_image.tabs.errors.grid.obj.init();																			
	
					// clean variables
					'.$containerObj.'.wins_image.obj.attachEvent("onClose",function(win){
						if ($("#'.$containerObj.'_queue div").hasClass("uploadify-queue-item") && !confirm("'.Yii::t('views/products/edit_images','LABEL_CONFIRM_CLOSE_IMAGES_IN_QUEUE').'")) return false;
						
						var obj = this;
						obj.setModal(false);

						'.$containerObj.'.wins.obj.setModal(true);						
						
						var swfuploadify = window["uploadify_'.$containerObj.'_button"];
						if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();
									
						'.$containerObj.'.wins_image = new Object();															
						return true;
					});						
				}																									
				break;									
			case "export_pdf":
				printGridPopup('.$containerObj.'.layout.C.grid.obj,"pdf",[0],title);
				break;	
			case "export_excel":
				printGridPopup('.$containerObj.'.layout.C.grid.obj,"excel",[0],title);
				break;		
			case "print":
				printGridPopup('.$containerObj.'.layout.C.grid.obj,"printview",[0],title);
				break;		
			case "delete":
				$.ajax({
					url: "'.CController::createUrl('count_variants',array('id'=>$id)).'",
					type: "POST",
					data: { "ids[]":current_id },
					success: function(data){														
						if (data == "orders") {
							alert("'.Yii::t('views/products/edit_variants','LABEL_ALERT_CANNOT_VARIANT_ONCE_ORDERS').'");
							
						}else{
							if (confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_DELETE_ALL_VARIANTS').'")) {
								$.ajax({
									url: "'.CController::createUrl('delete_variants', array('id'=>$id)).'",
									type: "POST",
									data: {"pass":"0"},
									dataType: "json",							
									success: function(data){													
										if(data.in_other_product==1 && confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_COMBO_BUNDLED').'") || data.in_other_product!=1){
											$.ajax({
												url: "'.CController::createUrl('delete_variants', array('id'=>$id)).'",
												type: "POST",
												data: {"pass":"1"},
												beforeSend: function(){		
													obj.disableItem(id);
												},
												complete: function(){
													if (typeof obj.enableItem == "function") obj.enableItem(id);
												},							
												success: function(data){													
													load_grid('.$containerObj.'.layout.C.grid.obj);
													load_grid(tabs.list.grid.obj);
													alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
												}
											});	
										}
									}
								});					
							}
						}
					}
				});
				break;						
		}
	});	
}


'.$containerObj.'.layout.C.toolbar.generate = function(id){
	var obj = '.$containerObj.'.layout.C.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('generate_variants', array('id'=>$id)).'",
			type: "POST",
			data: {},
			dataType: "json",
			beforeSend: function(){			
				obj.disableItem(id);
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
			},
			success: function(data){																					
				load_grid('.$containerObj.'.layout.C.grid.obj);		
			}
		});	
}

'.$containerObj.'.layout.C.toolbar.add = function(current_id, name){
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/products/edit_variants','LABEL_BTN_ADD_VARIANT').'";
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addVariantWindow", 10, 10, 450, 420);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);	
				
	'.$containerObj.'.wins.toolbar = new Object();
	
	'.$containerObj.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 		
	
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
	'.$containerObj.'.wins.toolbar.load(current_id);
	
	'.$containerObj.'.wins.toolbar.save = function(id,close){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_variant').'",
			type: "POST",
			data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize()+"&id_product="+'.$containerJS.'.id_product+"&pass=0",
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");

				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				//if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$containerObj.'.wins.obj.close();
			},
			success: function(data){												
				
				if(data.in_other_product==1 && confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_ONE_VARIANT_COMBO_BUNDLED').'") || data.in_other_product!=1){
					$.ajax({
						url: "'.CController::createUrl('save_variant').'",
						type: "POST",
						data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize()+"&id_product="+'.$containerJS.'.id_product+"&pass=1",
						dataType: "json",
						beforeSend: function(){			
							// clear all errors					
							$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
							$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");
			
							obj.disableItem(id);			
						},
						complete: function(jqXHR, textStatus){
							if (typeof obj.enableItem == "function") obj.enableItem(id);
							
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
									alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
					
									$("#'.$containerObj.'_id").val(data.id);
									if (!close) '.$containerObj.'.wins.toolbar.load(data.id);
									
									//In case combo or bundled has been disabled
									load_grid(tabs.list.grid.obj);									
								}
							} else {
								alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
							}
						}
					});
						
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
		url: "'.CController::createUrl('edit_variants_options',array('container'=>$containerObj,'id_product'=>$id)).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		load_grid('.$containerObj.'.layout.C.grid.obj);
		return true;
	});	
}

'.$containerObj.'.layout.C.toolbar.obj = '.$containerObj.'.layout.C.obj.attachToolbar();
'.$containerObj.'.layout.C.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.C.toolbar.load();

'.$containerObj.'.layout.C.grid = new Object();
'.$containerObj.'.layout.C.grid.obj = '.$containerObj.'.layout.C.obj.attachGrid();
'.$containerObj.'.layout.C.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.C.grid.obj.setHeader("'.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').','.Yii::t('views/products/edit_variants','LABEL_PRICE_PERCENT').','.Yii::t('global','LABEL_PRICE').','.Yii::t('global','LABEL_ENABLED').'",null,[,,"text-align:center","text-align:right","text-align:right","text-align:center"]);

'.$containerObj.'.layout.C.grid.obj.setInitWidthsP("30,30,7,13,13,7");
'.$containerObj.'.layout.C.grid.obj.setColAlign("left,left,center,right,right,center");
'.$containerObj.'.layout.C.grid.obj.setColSorting("na,na,na,na,na,na");
'.$containerObj.'.layout.C.grid.obj.enableResizing("true,true,true,true,true,false");
'.$containerObj.'.layout.C.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.C.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
'.$containerObj.'.layout.C.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_c\'></div>");
'.$containerObj.'.layout.C.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerObj.'.layout.C.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_c");
'.$containerObj.'.layout.C.grid.obj.setPagingSkin("toolbar", dhx_skin);
'.$containerObj.'.layout.C.grid.obj.i18n.paging={
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
'.$containerObj.'.layout.C.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.C.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_variants',array('id'=>$id)).'";
// load the initial grid
load_grid('.$containerObj.'.layout.C.grid.obj);

'.$containerObj.'.layout.C.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.C.toolbar.add(rId,this.cellById(rId,1).getValue());
});

'.$containerObj.'.layout.C.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	if (cInd == 5) {
		if(state){
			$.ajax({
				url: "'.CController::createUrl('toggle_active_variant',array('id_product'=>$id)).'",
				type: "POST",
				data: { "id":rId,"active":state,"pass":"1" },
				dataType: "json",							
				success: function(data){
					'.$containerObj.'.layout.C.grid.obj.setRowTextStyle(rId, "color: #000000");															
				}
			});
				
		}else{
			$.ajax({
					url: "'.CController::createUrl('toggle_active_variant',array('id_product'=>$id)).'",
					type: "POST",
					data: { "id":rId,"active":state,"pass":"0"},
					dataType: "json",							
					success: function(data){																			
						if(data.in_other_product==1 && confirm("'.Yii::t('views/products/edit_variants','LABEL_ALERT_ONE_VARIANT_COMBO_BUNDLED').'") || data.in_other_product!=1){
							$.ajax({
								url: "'.CController::createUrl('toggle_active_variant',array('id_product'=>$id)).'",
								type: "POST",
								data: { "id":rId,"active":state,"pass":"1" },
								dataType: "json",							
								success: function(data2){
									if (data.in_other_product==1) load_grid(tabs.list.grid.obj);			
								}
							});	
						}else{
							if (data.in_other_product==1) {
								state = (state==false?true:false);
								'.$containerObj.'.layout.C.grid.obj.cells(rId,cInd).setValue(state);								
							}
						}	
						if(state){
							'.$containerObj.'.layout.C.grid.obj.setRowTextStyle(rId, "color: #000000");	
						}else{
							'.$containerObj.'.layout.C.grid.obj.setRowTextStyle(rId, "color: #B5B5B5;");		
						}
					}
				});
		}			
	}
});

'.$containerObj.'.layout.C.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.C.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});


'.$containerJS.'.layout.A.dataview.ajaxComplete();

enable_grid_toolbar('.$containerObj.'.layout.B.grid.obj,'.$containerObj.'.layout.B.toolbar.obj, '.$containerObj.'.layout.A.grid.obj);
';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>