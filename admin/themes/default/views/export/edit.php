<?php
$containerJS = 'Tab'.$container;
$containerLayout = $containerJS.'_layout';
$language = Yii::app()->language;

$script = '
var '.$containerJS.' = new Object();
// variables used to check modification
'.$containerJS.'.og_form = [];

'.$containerJS.'.id_export_tpl = '.($model->id ? $model->id:0).';

'.$containerJS.'.dhxWins = new dhtmlXWindows();
'.$containerJS.'.dhxWins.enableAutoViewport(true);
//'.$containerJS.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerJS.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerJS.'.wins = new Object();

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "2E");

'.$containerJS.'.layout.A = new Object();
'.$containerJS.'.layout.A.obj = '.$containerJS.'.layout.obj.cells("a");
'.$containerJS.'.layout.A.obj.setHeight(200);
'.$containerJS.'.layout.A.obj.hideHeader();
'.$containerJS.'.layout.A.toolbar = new Object();
'.$containerJS.'.layout.A.toolbar.obj = '.$containerJS.'.layout.A.obj.attachToolbar();
'.$containerJS.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);

'.$containerJS.'.layout.A.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.A.toolbar.obj;
	
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
				'.$containerJS.'.layout.A.toolbar.save(id,false);
				break;
			case "save_close":
				'.$containerJS.'.layout.A.toolbar.save(id,true);
				break;
			case "delete":
				if (confirm("'.Yii::t('views/export/edit','LABEL_ALERT_DELETE').'")) {
					obj.disableItem(id);
					
					$.ajax({
						url: "'.CController::createUrl('delete').'",
						type: "POST",
						data: { "ids[]":current_id },
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);
						},						
						success: function(data){		
							'.$containerJS.'.load_og_form();
										
							tabs.close_tab(tabs.obj, "'.$container.'", true);
							load_grid(tabs.list.grid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});					
				}
				break;	
		}
	});	
};

'.$containerJS.'.layout.A.toolbar.save = function(id,close){
	var obj = '.$containerJS.'.layout.A.toolbar.obj;
	
	$.ajax({
		url: "'.CController::createUrl('save',array('container'=>$containerJS)).'",
		type: "POST",
		data: $("#'.$containerLayout.'").serialize(),
		dataType: "json",
		beforeSend: function(){			
			// clear all errors					
			$("#'.$containerLayout.' span.error").html("");
			$("#'.$containerLayout.' *").removeClass("error");
		
			obj.disableItem(id);			
		},
		complete: function(jqXHR, textStatus){
			if (typeof obj.enableItem == "function") obj.enableItem(id);
			
			if (close && !jQuery.parseJSON(jqXHR.responseText).errors) tabs.close_tab(tabs.obj, "'.$container.'", true);	
		},
		success: function(data){						
			// Remove class error to the background of the main div
			$(".div_'.$containerJS.'").removeClass("error_background");
			if (data) {
				if (data.errors) {
					$.each(data.errors, function(key, value){
						var id_tax_container = "'.$containerJS.'_"+key;
						var id_tax_selector = "#"+id_tax_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
						
						if (!$(id_tax_selector).hasClass("error")) { 
							$(id_tax_selector).addClass("error");
							
							if (value) {		
								value = String(value);
								var id_errormsg_container = id_tax_container+"_errorMsg";
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
					$(".div_'.$containerJS.'").addClass("error_background");																															
				} else {					

					load_grid(tabs.list.grid.obj);
					
					if (!'.$containerJS.'.id_export_tpl) {								
						'.$containerJS.'.id_export_tpl=data.id;
						'.$containerJS.'.layout.A.toolbar.load('.$containerJS.'.id_export_tpl);	
						$("#'.$containerJS.'_id").val(data.id);															
						
						// when we create a new product and save, store its id and container value in array of opened tabs
						tabs.totalOpened[data.id] = "'.$container.'";									
					}
					
					alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
					'.$containerJS.'.load_og_form();
					
					if(!close){
						var id_tax_container = "'.$containerJS.'_name";
						var id_tax_selector = "#"+id_tax_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
					
						// if name changed, rename
						if (tabs.obj.getLabel("'.$container.'") != $(id_tax_selector).val()) {
							tabs.obj.setLabel("'.$container.'",$(id_tax_selector).val());	
						}	
						'.$containerJS.'.enable_grid_toolbar();
						'.$containerJS.'.layout.B.grid.load();
					}	
				}
			} else {
				alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
			}
		}
	});					
};

'.$containerJS.'.layout.A.toolbar.load('.$containerJS.'.id_export_tpl);

$.ajax({
	url: "'.CController::createUrl('edit_options',array('container'=>$containerJS,'id'=>$model->id)).'",
	type: "POST",
	success: function(data){
		'.$containerJS.'.layout.A.obj.attachHTMLString(data);	
		'.$containerJS.'.load_og_form();	
	}
});

'.$containerJS.'.layout.B = new Object();
'.$containerJS.'.layout.B.obj = '.$containerJS.'.layout.obj.cells("b");
'.$containerJS.'.layout.B.obj.hideHeader();
'.$containerJS.'.layout.B.toolbar = new Object();
'.$containerJS.'.layout.B.toolbar.obj = '.$containerJS.'.layout.B.obj.attachToolbar();
'.$containerJS.'.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);

'.$containerJS.'.layout.B.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.B.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/export/edit','LABEL_BTN_ADD_COLUMN').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	 		
	obj.addSeparator("sep1", null);
	obj.addButton("export", null, "'.Yii::t('views/export/edit','LABEL_BTN_EXPORT').'", "toolbar/excel.png", null);

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				'.$containerJS.'.layout.B.toolbar.add();
				break;							
			case "delete":
				var checked = '.$containerJS.'.layout.B.grid.obj.getCheckedRows(0);
				if (checked) {											
					if (confirm("'.Yii::t('views/export/edit','LABEL_ALERT_DELETE_COLUMN').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_column').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){								
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){	
								'.$containerJS.'.layout.B.grid.load();
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/export/edit','LABEL_ALERT_NO_CHECKED_COLUMN').'");		
				}			
				break;
			case "export":
				'.$containerJS.'.layout.B.toolbar.export();			
				break;
		}
	});	
}

'.$containerJS.'.layout.B.toolbar.add = function(){
	if (!'.$containerJS.'.id_export_tpl) { alert("'.Yii::t('views/export/index','LABEL_ALERT_TEMPLATE_NOT_SAVED').'"); return false; }
	
	name = "'.Yii::t('views/export/edit','LABEL_BTN_ADD_COLUMN').'";
	
	'.$containerJS.'.wins.obj = '.$containerJS.'.dhxWins.createWindow("'.$containerJS.'_addColumnWindow", 10, 10, 630, 320);
	'.$containerJS.'.wins.obj.setText(name);
	'.$containerJS.'.wins.obj.button("park").hide();
	'.$containerJS.'.wins.obj.keepInViewport(true);
	'.$containerJS.'.wins.obj.setModal(true);
	//'.$containerJS.'.wins.obj.maximize();	
				
	'.$containerJS.'.wins.toolbar = new Object();
	
	'.$containerJS.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerJS.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerJS.'.wins.toolbar.save(id);
					break;
			}
		});	
	}	
	
	'.$containerJS.'.wins.toolbar.obj = '.$containerJS.'.wins.obj.attachToolbar();
	'.$containerJS.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerJS.'.wins.toolbar.load();
	
	'.$containerJS.'.wins.toolbar.save = function(id,close){
		var obj = '.$containerJS.'.wins.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_column').'",
			type: "POST",
			data: $("#div_'.$containerJS.'_column_options *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#div_'.$containerJS.'_column_options span.error").html("");
				$("#div_'.$containerJS.'_column_options *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$containerJS.'.wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$("#div_'.$containerJS.'_column_options").removeClass("error_background");
				if (data) {
					if (data.errors) {
						$.each(data.errors, function(key, value){
							var id_tag_container = "'.$containerJS.'_"+key;
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
						$("#div_'.$containerJS.'_column_options").addClass("error_background");																														
					} else {											
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						'.$containerJS.'.wins.obj.close();
						'.$containerJS.'.layout.B.grid.load();
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}	
	
	// clean variables
	'.$containerJS.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerJS.'.wins = new Object();						
		
		return true;
	});				
	
	//_add_column_container
	$.ajax({
		url: "'.CController::createUrl('edit_column_options',array('container'=>$containerJS)).'&id="+'.$containerJS.'.id_export_tpl,
		success: function(data){
			'.$containerJS.'.wins.obj.attachHTMLString(data);	
		}
	});	
}

'.$containerJS.'.layout.B.toolbar.export = function(){
	// check if we have columns
	if (!'.$containerJS.'.layout.B.grid.obj.getRowsNum()) {
		alert("'.Yii::t('views/export/edit','LABEL_ALERT_NO_COLUMN').'");
		return false;	
	}
	
	'.$containerJS.'.wins.obj = '.$containerJS.'.dhxWins.createWindow("'.$containerJS.'_exportWindow", 10, 10, 650, 440);
	
	'.$containerJS.'.wins.obj.setText("'.Yii::t('views/export/edit','LABEL_BTN_EXPORT').'");
	'.$containerJS.'.wins.obj.button("park").hide();
	'.$containerJS.'.wins.obj.keepInViewport(true);
	'.$containerJS.'.wins.obj.setModal(true);				

	$.ajax({
		url: "'.CController::createUrl('edit_export_files_options',array('container'=>$containerJS)).'&id="+'.$containerJS.'.id_export_tpl,
		success: function(data){
			'.$containerJS.'.wins.obj.attachHTMLString(data);		
		}
	});	
	
	'.$containerJS.'.wins.toolbar = new Object();
	
	'.$containerJS.'.wins.toolbar.load = function(){
		var obj = '.$containerJS.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("export",null,"'.Yii::t('views/export/edit','LABEL_BTN_EXPORT').'", "toolbar/excel.png", null);
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "export":		
					window.open("'.CController::createUrl('export').'?"+$("#div_'.$containerJS.'_export_files_options_container *").serialize());				
					/*
					$.ajax({
						url: "'.CController::createUrl('export').'",
						type: "POST",
						data: $("#div_'.$containerJS.'_export_files_options_container *").serialize(),
						dataType: "json",
						beforeSend: function(){			
							// clear all errors					
							$("#div_'.$containerJS.'_export_files_options_container span.error").html("");
							$("#div_'.$containerJS.'_export_files_options_container *").removeClass("error");
						
							obj.disableItem(id);			
						},
						complete: function(jqXHR, textStatus){
							if (typeof obj.enableItem == "function") obj.enableItem(id);
							
							if (close && !jQuery.parseJSON(jqXHR.responseText).errors) tabs.close_tab(tabs.obj, "'.$container.'", true);	
						},
						success: function(data){						
							// Remove class error to the background of the main div
							$("#div_'.$containerJS.'_export_files_options_container").removeClass("error_background");
							if (data) {
								if (data.errors) {
									$.each(data.errors, function(key, value){
										var id_tax_container = "'.$containerJS.'_"+key;
										var id_tax_selector = "#"+id_tax_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
										
										if (!$(id_tax_selector).hasClass("error")) { 
											$(id_tax_selector).addClass("error");
											
											if (value) {		
												value = String(value);
												var id_errormsg_container = id_tax_container+"_errorMsg";
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
									$("#div_'.$containerJS.'_export_files_options_container").addClass("error_background");																															
								} else {					
									
								}
							} else {
								alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
							}
						}
					});		*/					
					break;
			}
		});
	}	
	
	'.$containerJS.'.wins.toolbar.obj = '.$containerJS.'.wins.obj.attachToolbar();
	'.$containerJS.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerJS.'.wins.toolbar.load();

	// clean variables
	'.$containerJS.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerJS.'.wins = new Object();															
		return true;
	});		
}
	
'.$containerJS.'.layout.B.toolbar.load();
'.$containerJS.'.layout.B.grid = new Object();
'.$containerJS.'.layout.B.grid.obj = '.$containerJS.'.layout.B.obj.attachGrid();
'.$containerJS.'.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerJS.'.layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').'",null,["text-align:center"]);

'.$containerJS.'.layout.B.grid.obj.setInitWidths("40,*");
'.$containerJS.'.layout.B.grid.obj.setColAlign("center,left");
'.$containerJS.'.layout.B.grid.obj.setColSorting("na,na");
'.$containerJS.'.layout.B.grid.obj.setSkin(dhx_skin);
'.$containerJS.'.layout.B.grid.obj.enableDragAndDrop(true);
'.$containerJS.'.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

'.$containerJS.'.layout.B.grid.obj.init();	

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerJS.'.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_columns').'";

'.$containerJS.'.layout.B.grid.load = function(callback){
	var obj = '.$containerJS.'.layout.B.grid.obj;
	
	obj.clearAll();
	if ('.$containerJS.'.id_export_tpl) obj.loadXML(obj.xmlOrigFileUrl+"?id="+'.$containerJS.'.id_export_tpl,callback);	
};

'.$containerJS.'.layout.B.grid.obj.attachEvent("onDrop", function(sId,tId,dId,sObj,tObj,sCol,tCol){
	var rows=this.getAllRowIds().split(",");
	var ids=[];

	for (var i=0;i<rows.length;++i) {
		if (rows[i]) {
			ids.push("ids[]="+rows[i]);									
		}
	}	
	
	$.ajax({
		url: "'.CController::createUrl('save_column_sort_order').'?id="+'.$containerJS.'.id_export_tpl,
		type: "POST",
		data: ids.join("&"),
		success: function(data){
			if (data) {
				alert(data);							
				'.$containerJS.'.layout.B.grid.load();
			}
		}
	});	
});

'.$containerJS.'.layout.B.grid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

'.$containerJS.'.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerJS.'.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

'.$containerJS.'.layout.B.grid.load();

// clear event for this tab and reset
$(window).off("resize.'.$containerJS.'");
$(window).on("resize.'.$containerJS.'",function(){
	setTimeout("'.$containerJS.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.highlight_tab_errors = function(tabObj,cssStyle){
	var errors=0;
	
	if (!cssStyle) { cssStyle = "color:#FF0000;"; }

	$.each(tabObj._tabs, function(key, value) {									
		if ($("*",$("#'.$containerLayout.' [tab_id=\'"+key+"\']")).hasClass("error")) {
			tabObj.setCustomStyle(key,null,null,cssStyle);
			
			errors++;
		} else {
			tabObj.setCustomStyle(key,null,null,null);
		}
	});
	
	if (errors) {
		tabs.obj.setCustomStyle("'.$container.'",null,null,cssStyle);
	} else {
		tabs.obj.setCustomStyle("'.$container.'",null,null,null);
	}
};

// load original form values
'.$containerJS.'.load_og_form = function()
{
	// layout a
	$("[name]",'.$containerJS.'.layout.A.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) '.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			'.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		}	
	});
};

// check if any modifications has been made
'.$containerJS.'.has_modifications = function()
{
	// check for modifications
	var str_array=[];
		
	// layout a
	$("[name]",'.$containerJS.'.layout.A.obj.vs.def.dhxcont).each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) str_array[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			str_array[$(this).attr("name")] = $(this).val();	
		}	
	});	
	
	return (count(array_diff_assoc('.$containerJS.'.og_form,str_array)) || count(array_diff_assoc(str_array,'.$containerJS.'.og_form)) ? 1:0);
};

//Function to enable and disable grid with toolbar who have a parent grid
'.$containerJS.'.enable_grid_toolbar = function(){	
	var colNum = '.$containerJS.'.layout.B.grid.obj.getColumnsNum();
	var state = '.$containerJS.'.id_export_tpl ? 0:1;
	
	for(x=0;x<colNum;x++){
		'.$containerJS.'.layout.B.grid.obj.setColumnHidden(x,state);
	}	
	
	'.$containerJS.'.layout.B.toolbar.obj.forEachItem(function(itemId){
		if(!state){
			'.$containerJS.'.layout.B.toolbar.obj.enableItem(itemId);
		}else{
			'.$containerJS.'.layout.B.toolbar.obj.disableItem(itemId);
		}
	});
}

'.$containerJS.'.enable_grid_toolbar();
';

echo Html::script($script);
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>
<div id="<?php echo $containerJS.'_export_grid'; ?>" style="width:100%;"></div>
<div id="<?php echo $containerJS.'_upload_files_button'; ?>" style="display:none;"></div>
<div id="<?php echo $containerJS.'_upload_files_queue'; ?>" style="display:none;"></div>