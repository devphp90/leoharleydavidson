<?php
$containerJS = 'Tab'.$container;
$containerLayout = $containerJS.'_layout';
$language = Yii::app()->language;

$script = '
var '.$containerJS.' = new Object();
// variables used to check modification
'.$containerJS.'.og_form = [];

'.$containerJS.'.id_import_tpl = '.($model->id ? $model->id:0).';

'.$containerJS.'.dhxWins = new dhtmlXWindows();
'.$containerJS.'.dhxWins.enableAutoViewport(true);
//'.$containerJS.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerJS.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerJS.'.wins = new Object();

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "3T");

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
				if (confirm("'.Yii::t('views/import/edit','LABEL_ALERT_DELETE').'")) {
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
					
					if (!'.$containerJS.'.id_import_tpl) {								
						'.$containerJS.'.id_import_tpl=data.id;
						'.$containerJS.'.layout.A.toolbar.load('.$containerJS.'.id_import_tpl);	
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

'.$containerJS.'.layout.A.toolbar.load('.$containerJS.'.id_import_tpl);

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
'.$containerJS.'.layout.B.obj.setWidth(500);
'.$containerJS.'.layout.B.toolbar = new Object();
'.$containerJS.'.layout.B.toolbar.obj = '.$containerJS.'.layout.B.obj.attachToolbar();
'.$containerJS.'.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);

'.$containerJS.'.layout.B.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.B.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/import/edit','LABEL_BTN_ADD_COLUMN').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	 		
	obj.addSeparator("sep1", null);
	obj.addButton("export_template", null, "'.Yii::t('views/import/edit','LABEL_BTN_EXPORT_TEMPLATE_EXCEL').'", "toolbar/excel.png", null);

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				'.$containerJS.'.layout.B.toolbar.add();
				break;							
			case "delete":
				var checked = '.$containerJS.'.layout.B.grid.obj.getCheckedRows(0);
				if (checked) {											
					if (confirm("'.Yii::t('views/import/edit','LABEL_ALERT_DELETE_COLUMN').'")) {
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
					alert("'.Yii::t('views/import/edit','LABEL_ALERT_NO_CHECKED_COLUMN').'");		
				}			
				break;
			case "export_template":
				var title = tabs.list.grid.obj.cellById('.$containerJS.'.id_import_tpl,1).getValue();
			
				var header = [];
				var ids = '.$containerJS.'.layout.B.grid.obj.getAllRowIds();
				ids = ids.split(",");
				
				for (var i=0;i<ids.length;++i) {
					header.push("columns[]="+'.$containerJS.'.layout.B.grid.obj.cellById(ids[i],1).getValue());
				}
				
				window.open("'.CController::createUrl('export_template').'?title="+title+"&"+header.join("&"),"_blank");												
				break;
		}
	});	
}

'.$containerJS.'.layout.B.toolbar.add = function(){
	if (!'.$containerJS.'.id_import_tpl) { alert("'.Yii::t('views/import/index','LABEL_ALERT_TEMPLATE_NOT_SAVED').'"); return false; }
	
	name = "'.Yii::t('views/import/edit','LABEL_BTN_ADD_COLUMN').'";
	
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
		url: "'.CController::createUrl('edit_column_options',array('container'=>$containerJS)).'&id="+'.$containerJS.'.id_import_tpl,
		success: function(data){
			'.$containerJS.'.wins.obj.attachHTMLString(data);	
		}
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
	if ('.$containerJS.'.id_import_tpl) obj.loadXML(obj.xmlOrigFileUrl+"?id="+'.$containerJS.'.id_import_tpl,callback);	
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
		url: "'.CController::createUrl('save_column_sort_order').'?id="+'.$containerJS.'.id_import_tpl,
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

'.$containerJS.'.layout.C = new Object();
'.$containerJS.'.layout.C.obj = '.$containerJS.'.layout.obj.cells("c");
'.$containerJS.'.layout.C.obj.hideHeader();
'.$containerJS.'.layout.C.toolbar = new Object();
'.$containerJS.'.layout.C.toolbar.obj = '.$containerJS.'.layout.C.obj.attachToolbar();
'.$containerJS.'.layout.C.toolbar.obj.setIconsPath(dhx_globalImgPath);

'.$containerJS.'.layout.C.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.C.toolbar.obj;
	
	obj.addButton("upload",null,"'.Yii::t('global','LABEL_BTN_UPLOAD').'","toolbar/upload.png","toolbar/upload_dis.png");  	
	obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "upload":		
				'.$containerJS.'.wins.obj = '.$containerJS.'.dhxWins.createWindow("'.$containerJS.'_uploadWindow", 10, 10, 400, 440);
				
				'.$containerJS.'.wins.obj.setText("'.Yii::t('views/import/edit','LABEL_BTN_UPLOAD_FILES').'");
				'.$containerJS.'.wins.obj.button("park").hide();
				'.$containerJS.'.wins.obj.keepInViewport(true);
				'.$containerJS.'.wins.obj.setModal(true);				
				

				$.ajax({
					url: "'.CController::createUrl('edit_upload_files_options',array('container'=>$containerJS)).'&id="+'.$containerJS.'.id_import_tpl,
					success: function(data){
						'.$containerJS.'.wins.obj.attachHTMLString(data);		
					}
				});	
				
				'.$containerJS.'.wins.toolbar = new Object();
				
				'.$containerJS.'.wins.toolbar.load = function(){
					var obj = '.$containerJS.'.wins.toolbar.obj;
					
					obj.clearAll();
					obj.detachAllEvents();
					
					obj.addButton("upload",null,"'.Yii::t('global','LABEL_BTN_UPLOAD').'","toolbar/upload.png","toolbar/upload_dis.png");  
				
					obj.attachEvent("onClick",function(id){
						switch (id) {
							case "upload":						
								if ($("#'.$containerJS.'_queue div").hasClass("uploadify-queue-item")) {				
									$("#'.$containerJS.'_button").uploadify("upload","*");
								} else {
									alert("'.Yii::t('views/import/edit','LABEL_ALERT_NO_FILES_SELECTED').'");	
								}
								break;
						}
					});
				}	
				
				'.$containerJS.'.wins.toolbar.obj = '.$containerJS.'.wins.obj.attachToolbar();
				'.$containerJS.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerJS.'.wins.toolbar.load();

				// clean variables
				'.$containerJS.'.wins.obj.attachEvent("onClose",function(win){
					if ($("#'.$containerJS.'_queue div").hasClass("uploadify-queue-item") && !confirm("'.Yii::t('views/import/edit','LABEL_CONFIRM_CLOSE_FILES_IN_QUEUE').'")) return false;
					
					var swfuploadify = window["uploadify_'.$containerJS.'_button"];
					if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();
								
					'.$containerJS.'.wins = new Object();															
					return true;
				});				
				break;			
			case "delete":
				var checked = '.$containerJS.'.layout.C.grid.obj.getCheckedRows(0);
				if (checked) {											
					if (confirm("'.Yii::t('views/import/edit','LABEL_ALERT_DELETE_FILE').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_file').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){								
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){	
								'.$containerJS.'.layout.C.grid.load();
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/import/edit','LABEL_ALERT_NO_CHECKED_FILE').'");		
				}		
				break;				
		}
	});	
}

'.$containerJS.'.layout.C.toolbar.load();
'.$containerJS.'.layout.C.grid = new Object();
'.$containerJS.'.layout.C.grid.obj = '.$containerJS.'.layout.C.obj.attachGrid();
'.$containerJS.'.layout.C.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerJS.'.layout.C.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_FILE').','.Yii::t('views/import/edit','LABEL_TYPE').','.Yii::t('views/import/edit','LABEL_FILESIZE').','.Yii::t('views/import/edit','LABEL_DATE_UPLOADED').','.Yii::t('views/import/edit','LABEL_DATE_IMPORTED').'",null,["text-align:center",,,"text-align:right"]);

'.$containerJS.'.layout.C.grid.obj.setInitWidths("40,*,210,70,120,120");
'.$containerJS.'.layout.C.grid.obj.setColAlign("center,left,left,right,left,left");
'.$containerJS.'.layout.C.grid.obj.setColSorting("na,na,na,na,na,na");
'.$containerJS.'.layout.C.grid.obj.setSkin(dhx_skin);
'.$containerJS.'.layout.C.grid.obj.enableDragAndDrop(false);
'.$containerJS.'.layout.C.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

'.$containerJS.'.layout.C.grid.obj.init();	

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerJS.'.layout.C.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_imported_files').'";

'.$containerJS.'.layout.C.grid.load = function(callback){
	var obj = '.$containerJS.'.layout.C.grid.obj;
	
	obj.clearAll();
	if ('.$containerJS.'.id_import_tpl) obj.loadXML(obj.xmlOrigFileUrl+"?id="+'.$containerJS.'.id_import_tpl,callback);	
};

'.$containerJS.'.layout.C.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerJS.'.layout.C.grid.dblclick(rId,cInd);			
});

'.$containerJS.'.layout.C.grid.dblclick = function(rId,cInd){
	'.$containerJS.'.wins.obj = '.$containerJS.'.dhxWins.createWindow("'.$containerJS.'_importFileWindow", 10, 10, 630, 460);
	'.$containerJS.'.wins.obj.setText('.$containerJS.'.layout.C.grid.obj.cellById(rId,1).getValue());
	'.$containerJS.'.wins.obj.button("park").hide();
	'.$containerJS.'.wins.obj.keepInViewport(true);
	'.$containerJS.'.wins.obj.setModal(true);
	
	
	'.$containerJS.'.wins.ajaxRequest = "";
				
	'.$containerJS.'.wins.toolbar = new Object();
	
	'.$containerJS.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerJS.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("import",null,"'.Yii::t('views/import/edit_import_files_options','LABEL_BTN_IMPORT').'","toolbar/import.png","toolbar/import_dis.png");
		obj.addButton("download",null,"'.Yii::t('views/import/edit_import_files_options','LABEL_BTN_DOWNLOAD').'","toolbar/download.png","toolbar/download_dis.png");
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "import":				
					if (confirm("'.Yii::t('views/import/edit','LABEL_CONFIRM_IMPORT').'")) {		
						'.$containerJS.'.wins.ajaxRequest = $.ajax({
							url: "'.CController::createUrl('import_file').'",
							type: "POST",
							data: $("#div_'.$containerJS.'_import_files_options_container *").serialize(),
							beforeSend: function(){		
								var errors = 0;	
								// check for errors
								if ($("#'.$containerJS.'_columns_separated_with").val().length == 0) {
									errors = 1;
									$("#'.$containerJS.'_columns_separated_with").not(".error").addClass("error");							
								}
								
								if ($("#'.$containerJS.'_columns_enclosed_with").val().length == 0) {
									errors = 1;
									$("#'.$containerJS.'_columns_enclosed_with").not(".error").addClass("error");							
								}							
							
								if ($("#'.$containerJS.'_columns_escaped_with").val().length == 0) {
									errors = 1;
									$("#'.$containerJS.'_columns_escaped_with").not(".error").addClass("error");							
								}							
								
								if (errors) return false;	
												
								// clear all errors					
								$("#div_'.$containerJS.'_import_files_options_container span.error").html("");
								$("#div_'.$containerJS.'_import_files_options_container *").removeClass("error");
							
								obj.disableItem(id);	
								
								ajaxOverlay("div_'.$containerJS.'_import_files_options_container",1);		
							},
							complete: function(jqXHR, textStatus){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								
								ajaxOverlay("div_'.$containerJS.'_import_files_options_container",0);	
								
								'.$containerJS.'.wins.ajaxRequest = "";	
							},
							success: function(data){							
								'.$containerJS.'.wins.load(current_id);
								'.$containerJS.'.layout.C.grid.load();
							}
						});
					}
					break;
				case "download":
					window.open("'.CController::createUrl('download_file').'?id="+rId+"&"+(new Date().getTime()),"_blank");												
					break;
			}
		});	
	}	
	
	'.$containerJS.'.wins.toolbar.obj = '.$containerJS.'.wins.obj.attachToolbar();
	'.$containerJS.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerJS.'.wins.toolbar.load(rId);		
	
	// clean variables
	'.$containerJS.'.wins.obj.attachEvent("onClose",function(win){
		if (!'.$containerJS.'.wins.ajaxRequest) {
			'.$containerJS.'.wins = new Object();						
		
			return true;
		} else {
			alert("'.Yii::t('views/import/edit','LABEL_ALERT_IMPORT_IN_PROGRESS').'");
			return false;	
		}
	});				
	
	'.$containerJS.'.wins.load = function(current_id){
		//_add_column_container
		$.ajax({
			url: "'.CController::createUrl('edit_import_files_options',array('container'=>$containerJS)).'&id="+current_id,
			dataType: "json",
			success: function(data){
				if (data) {
					if (!data.incomplete) {
						'.$containerJS.'.wins.toolbar.obj.disableItem("import");	
						'.$containerJS.'.wins.obj.attachHTMLString(data);	
					} else {
						'.$containerJS.'.wins.obj.attachHTMLString(data.output);	
						
						'.$containerJS.'.wins.grid = new Object();
						'.$containerJS.'.wins.grid.obj = new dhtmlXGridObject("'.$containerJS.'_preview");
						'.$containerJS.'.wins.grid.obj.setImagePath(dhx_globalImgPath);	
						'.$containerJS.'.wins.grid.obj.setHeader("'.Yii::t('views/import/edit','LABEL_COLUMN').','.Yii::t('views/import/edit','LABEL_ROW_1').','.Yii::t('views/import/edit','LABEL_ROW_2').','.Yii::t('views/import/edit','LABEL_ROW_3').'",null,[]);
						
						'.$containerJS.'.wins.grid.obj.setInitWidthsP("34,22,22,22");
						'.$containerJS.'.wins.grid.obj.setColAlign("left,left,left,left");
						'.$containerJS.'.wins.grid.obj.setColSorting("na,na,na,na");
						'.$containerJS.'.wins.grid.obj.setSkin(dhx_skin);
						'.$containerJS.'.wins.grid.obj.enableDragAndDrop(false);
						'.$containerJS.'.wins.grid.obj.enableRowsHover(true,dhx_rowhover);
						'.$containerJS.'.wins.grid.obj.enableMultiline(true);
						
						'.$containerJS.'.wins.grid.obj.init();	
						
						// we create a variable to store the default url used to get our grid data, so we can reuse it later
						'.$containerJS.'.wins.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_preview_column').'";
						
						'.$containerJS.'.wins.grid.load = function(callback){
							var obj = '.$containerJS.'.wins.grid.obj;
							
							obj.clearAll();				
							
							obj.loadXML(obj.xmlOrigFileUrl+"?"+$("#div_'.$containerJS.'_import_files_options_container *").serialize(),callback);	
						};			
						
						'.$containerJS.'.wins.grid.obj.attachEvent("onXLS", function(grid_obj){
							ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
						}); 
						
						'.$containerJS.'.wins.grid.obj.attachEvent("onXLE", function(grid_obj,count){
							ajaxOverlay(grid_obj.entBox.id,0);
						});			
						
						'.$containerJS.'.wins.grid.load();
					} 
				}
			}
		});			
	}
	
	'.$containerJS.'.wins.load(rId);	
};

'.$containerJS.'.layout.C.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerJS.'.layout.C.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

'.$containerJS.'.layout.C.grid.load();

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
	var state = '.$containerJS.'.id_import_tpl ? 0:1;
	
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
	
	var colNum = '.$containerJS.'.layout.C.grid.obj.getColumnsNum();
	var state = '.$containerJS.'.id_import_tpl ? 0:1;
	
	for(x=0;x<colNum;x++){
		'.$containerJS.'.layout.C.grid.obj.setColumnHidden(x,state);
	}	
	
	'.$containerJS.'.layout.C.toolbar.obj.forEachItem(function(itemId){
		if(!state){
			'.$containerJS.'.layout.C.toolbar.obj.enableItem(itemId);
		}else{
			'.$containerJS.'.layout.C.toolbar.obj.disableItem(itemId);
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