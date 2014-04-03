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
//'.$containerObj.'.layout.A.obj.setHeight(180);

'.$containerObj.'.layout.A.toolbar = new Object();

'.$containerObj.'.layout.A.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.A.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_BTN_ADD_VIDEO').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");		

	obj.attachEvent("onClick",function(id){		
		switch (id) {
			case "add":		
				'.$containerObj.'.layout.A.toolbar.add();											
				break;							
			case "delete":
				var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_DELETE_VIDEO').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}				
								
						$.ajax({
							url: "'.CController::createUrl('delete_downloadable_video',array('id_product'=>$id)).'",
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
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});	
					}		
				} else {
					alert("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_NO_CHECKED_VIDEO').'");		
				}
				break;
		}
	});	
};

'.$containerObj.'.layout.A.toolbar.add = function(current_id, name){
	current_id = current_id ? current_id:0;
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_BTN_ADD_VIDEO').'";	
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addVideoWindow", 10, 10, 600, 450);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	'.$containerObj.'.wins.obj.denyResize();
	//'.$containerObj.'.wins.obj.maximize();	
				
	'.$containerObj.'.wins.toolbar = new Object();
	
	'.$containerObj.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		//if (current_id) {
			//obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		//}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins.toolbar.save(id);
					break;
				case "save_close":
					'.$containerObj.'.wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_DELETE_VIDEO').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_downloadable_video',array('id_product'=>$id)).'",
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
			url: "'.CController::createUrl('save_downloadable_video').'",
			type: "POST",
			data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");
					
				'.$containerObj.'.wins.highlight_tab_errors('.$containerObj.'.wins.tabbar.obj);			
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				'.$containerObj.'.wins.highlight_tab_errors('.$containerObj.'.wins.tabbar.obj);

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
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "'.$containerObj.'_description['.Yii::app()->language.'][name]";
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
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
	
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	'.$containerObj.'.wins.layout.A.obj.hideHeader();
	'.$containerObj.'.wins.layout.A.obj.fixSize(false,false);
	
	$.ajax({
		url: "'.CController::createUrl('edit_downloadable_videos_options',array('container'=>$containerObj,'id_product'=>$id)).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		
		var swfuploadify = window["uploadify_'.$containerObj.'_button_video"];
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

'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.load();

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').'",null,["text-align:center"]);

'.$containerObj.'.layout.A.grid.obj.setInitWidthsP("5,95");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na");
'.$containerObj.'.layout.A.grid.obj.enableDragAndDrop(true);
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);

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
'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_downloadable_videos',array('id'=>$id)).'";
// load the initial grid
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

'.$containerObj.'.layout.A.grid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

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
		url: "'.CController::createUrl('save_videos_sort_order',array('id'=>$id)).'",
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
	
	obj.addButton("add",null,"'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_BTN_ADD_FILE').'","toolbar/add.gif","toolbar/add_dis.gif");  	
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
			

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				'.$containerObj.'.layout.B.toolbar.add();
				break;							
			case "delete":
				var checked = '.$containerObj.'.layout.B.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_DELETE_FILE').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}				
								
						$.ajax({
							url: "'.CController::createUrl('delete_downloadable_file',array('id_product'=>$id)).'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},							
							success: function(data){													
								load_grid('.$containerObj.'.layout.B.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});	
					}		
				} else {
					alert("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_NO_CHECKED_FILE').'");		
				}
				break;
		}
	});	
}

'.$containerObj.'.layout.B.toolbar.add = function(current_id, name, certificate){
	current_id = current_id ? current_id:0;
	
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_BTN_ADD_FILE').'";	
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addFileWindow", 10, 10, 630, 450);
	'.$containerObj.'.wins.obj.setText(name);
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	//'.$containerObj.'.wins.obj.maximize();	
	'.$containerObj.'.wins.obj.denyResize();
				
	'.$containerObj.'.wins.toolbar = new Object();
	
	'.$containerObj.'.wins.toolbar.load = function(current_id){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		if(certificate=="1"){
			obj.addSeparator("sep1", null);
			obj.addButton("list_certificate",null,"'.Yii::t('global','LABEL_BTN_CERTIFICATE').'","toolbar/certificate.png","toolbar/certificate_dis.png");
			obj.addButton("preview",null,"'.Yii::t('global','LABEL_PREVIEW').'","toolbar/preview.png","toolbar/preview_dis.png");
		}
		
		
		//if (current_id) {
			//obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		//}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins.toolbar.save(id);
					break;
				case "save_close":
					'.$containerObj.'.wins.toolbar.save(id,1);
					break;
				case "list_certificate":
					'.$containerObj.'.wins.toolbar.list_certificate(current_id);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_DELETE_FILE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_downloadable_file',array('id_product'=>$id)).'",
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
								load_grid('.$containerObj.'.layout.B.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
				case "preview":
					window.open("'.CController::createUrl('preview_course').'?id="+current_id);
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
			url: "'.CController::createUrl('save_downloadable_file').'",
			type: "POST",
			data: $("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$containerObj.'.wins.layout.obj.cont.obj.id+" *").removeClass("error");
					
				'.$containerObj.'.wins.highlight_tab_errors('.$containerObj.'.wins.tabbar.obj);			
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				'.$containerObj.'.wins.highlight_tab_errors('.$containerObj.'.wins.tabbar.obj);

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

						load_grid('.$containerObj.'.layout.B.grid.obj);		
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							// Verify to reload the window to show the Certificate button
							if(data.type == "ADL SCORM 1.2" && data.config_certificate==1 && $("#'.$containerObj.'_id").val() == 0){
								var id_tag_container = "'.$containerObj.'_name";
								var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
								var text_title = $(id_tag_selector).val();	
								'.$containerObj.'.wins.obj.close();
								
								'.$containerObj.'.layout.B.toolbar.add(data.id,text_title,"1");
								return false;
							}else{
								var id_tag_container = "'.$containerObj.'_name";
								var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
								$("#'.$containerObj.'_id").val(data.id);
								'.$containerObj.'.wins.toolbar.load(data.id);
								'.$containerObj.'.wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());
							}

									
						}
						'.$containerObj.'.layout.B.grid.obj.selectRowById(data.id);
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
	'.$containerObj.'.wins.layout.A.obj.fixSize(false,false);
	
	$.ajax({
		url: "'.CController::createUrl('edit_downloadable_files_options',array('container'=>$containerObj,'id_product'=>$id)).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	'.$containerObj.'.wins.toolbar.list_certificate = function(current_id){
		
		current_id = current_id ? current_id:0;
		
		'.$containerObj.'.wins_certificate = new Object();
		
		name = "'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_TITLE_SCORM_CERTIFICATE').'";	
		
		'.$containerObj.'.wins_certificate.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addCertificateWindow", 20, 20, 650, 450);
		'.$containerObj.'.wins_certificate.obj.setText(name);
		'.$containerObj.'.wins_certificate.obj.button("park").hide();
		'.$containerObj.'.wins_certificate.obj.keepInViewport(true);
		'.$containerObj.'.wins.obj.setModal(false);
		'.$containerObj.'.wins_certificate.obj.setModal(true);
		//'.$containerObj.'.wins_certificate.obj.maximize();	
		'.$containerObj.'.wins_certificate.obj.denyResize();
					
		'.$containerObj.'.wins_certificate.toolbar = new Object();
		
		'.$containerObj.'.wins_certificate.toolbar.load = function(current_id){
			var obj = '.$containerObj.'.wins_certificate.toolbar.obj;
			
			obj.clearAll();
			obj.detachAllEvents();
			
			obj.addButton("add_certificate", null, "'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_BTN_ADD_SCORM_CERTIFICATE').'", "toolbar/add.gif","toolbar/add_dis.gif");
			obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
			obj.addSeparator("sep1", null);
			
		
			obj.attachEvent("onClick",function(id){
				switch (id) {
					case "add_certificate":		
						'.$containerObj.'.wins_certificate.toolbar.add_certificate();											
						break;							
					case "delete":
						var checked = '.$containerObj.'.wins_certificate.grid.obj.getCheckedRows(0);
						
						if (checked) {											
							if (confirm("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_DELETE_SCORM_CERTIFICATE').'")) {
								checked = checked.split(",");
								var ids=[];
								
								for (var i=0;i<checked.length;++i) {
									if (checked[i]) {
										ids.push("ids[]="+checked[i]);									
									}
								}				
										
								$.ajax({
									url: "'.CController::createUrl('delete_scorm_certificate_product',array()).'",
									type: "POST",
									data: ids.join("&"),
									beforeSend: function(){		
										obj.disableItem(id);
									},
									complete: function(){
										if (typeof obj.enableItem == "function") obj.enableItem(id);
									},							
									success: function(data){													
										'.$containerObj.'.wins_certificate.grid.load($("#'.$containerObj.'_id").val());	
										alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
									}
								});	
							}		
						} else {
							alert("'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_ALERT_NO_CHECKED_SCORM_CERTIFICATE').'");		
						}
						break;
					
				}
			});	
		}	
		
		'.$containerObj.'.wins_certificate.toolbar.obj = '.$containerObj.'.wins_certificate.obj.attachToolbar();
		'.$containerObj.'.wins_certificate.toolbar.obj.setIconsPath(dhx_globalImgPath);	
		'.$containerObj.'.wins_certificate.toolbar.load(current_id);
		
		
		
		'.$containerObj.'.wins_certificate.grid = new Object()								
		'.$containerObj.'.wins_certificate.grid.obj = '.$containerObj.'.wins_certificate.obj.attachGrid();
		'.$containerObj.'.wins_certificate.grid.obj.setImagePath(dhx_globalImgPath);
		'.$containerObj.'.wins_certificate.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/products/edit_scorm_certificate_options','LABEL_CERTIFICATE').','.Yii::t('views/products/edit_scorm_certificate_options','LABEL_CONDITION').'",null,[",text-align:left","text-align:left"]);
		'.$containerObj.'.wins_certificate.grid.obj.setInitWidths("40,200,*");
		'.$containerObj.'.wins_certificate.grid.obj.setColAlign("center,left,left");
		'.$containerObj.'.wins_certificate.grid.obj.enableResizing("false,true,true");
		'.$containerObj.'.wins_certificate.grid.obj.setColSorting("na,str,str");
		'.$containerObj.'.wins_certificate.grid.obj.enableMultiline(true);	
		'.$containerObj.'.wins_certificate.grid.obj.setSkin(dhx_skin);
		'.$containerObj.'.wins_certificate.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
		'.$containerObj.'.wins_certificate.grid.obj.init();
		
		// we create a variable to store the default url used to get our grid data, so we can reuse it later
		'.$containerObj.'.wins_certificate.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_scorm_certificate').'";
		
		'.$containerObj.'.wins_certificate.grid.load = function(current_id){

			var obj = '.$containerObj.'.wins_certificate.grid.obj;
			
			obj.clearAll();
			obj.loadXML('.$containerObj.'.wins_certificate.grid.obj.xmlOrigFileUrl+"?id_product_downloadable_files="+current_id);	
		};	
		
		'.$containerObj.'.wins_certificate.grid.load(current_id);
		
			
		'.$containerObj.'.wins_certificate.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
			'.$containerObj.'.wins_certificate.toolbar.add_certificate(rId,this.cellById(rId,1).getValue());
		});
				
		
		// clean variables
		'.$containerObj.'.wins_certificate.obj.attachEvent("onClose",function(win){
			'.$containerObj.'.wins_certificate.obj.setModal(false);
			'.$containerObj.'.wins_certificate = new Object();
			'.$containerObj.'.wins.obj.setModal(true);
			return true;
		});	
		
		
		
		
		
		
		
		'.$containerObj.'.wins_certificate.toolbar.add_certificate = function(current_id, name){
				
				current_id = current_id ? current_id:0;
				
				// Insert a row in table scorm_certificate_product with current id_product_downloadable_files because we need a id_scorm_certificate_product
				if(current_id == 0){
					$.ajax({
						url: "'.CController::createUrl('create_scorm_certificate_product').'",
						data: {"id_product_downloadable_files" : $("#'.$containerObj.'_id").val()},
						type: "POST",
						success: function(data){						
							if (data) {
								'.$containerObj.'.wins_certificate.grid.load($("#'.$containerObj.'_id").val());	
								'.$containerObj.'.wins_certificate.toolbar.add_certificate(data);
							} else {
								alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
								
							}
						}
					});	
					return false;	
				}
	
				'.$containerObj.'.wins_certificate_option = new Object();
				
				name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('views/products/edit_downloadable_videos_files','LABEL_BTN_ADD_SCORM_CERTIFICATE').'";	
				
				'.$containerObj.'.wins_certificate_option.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_addCertificateOption", 10, 10, 600, 450);
				'.$containerObj.'.wins_certificate_option.obj.setText(name);
				'.$containerObj.'.wins_certificate_option.obj.button("park").hide();
				'.$containerObj.'.wins_certificate_option.obj.keepInViewport(true);
				'.$containerObj.'.wins_certificate.obj.setModal(false);
				'.$containerObj.'.wins_certificate_option.obj.setModal(true);
				'.$containerObj.'.wins_certificate_option.obj.denyResize();
				//'.$containerObj.'.wins_certificate_option.obj.maximize();	
							
				'.$containerObj.'.wins_certificate_option.toolbar = new Object();
				
				'.$containerObj.'.wins_certificate_option.toolbar.load = function(current_id){
					var obj = '.$containerObj.'.wins_certificate_option.toolbar.obj;
					
					obj.clearAll();
					obj.detachAllEvents();
					
					obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
					obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
				
					obj.attachEvent("onClick",function(id){
						switch (id) {
							case "save":	
								'.$containerObj.'.wins_certificate_option.toolbar.save(id);
								break;
							case "save_close":
								'.$containerObj.'.wins_certificate_option.toolbar.save(id,1);
								break;
						}
					});	
				}	
				
				'.$containerObj.'.wins_certificate_option.toolbar.obj = '.$containerObj.'.wins_certificate_option.obj.attachToolbar();
				'.$containerObj.'.wins_certificate_option.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerObj.'.wins_certificate_option.toolbar.load(current_id);

				'.$containerObj.'.wins_certificate_option.layout = new Object();
				'.$containerObj.'.wins_certificate_option.layout.obj = '.$containerObj.'.wins_certificate_option.obj.attachLayout("1C");
				
				'.$containerObj.'.wins_certificate_option.layout.A = new Object();
				'.$containerObj.'.wins_certificate_option.layout.A.obj = '.$containerObj.'.wins_certificate_option.layout.obj.cells("a");
				'.$containerObj.'.wins_certificate_option.layout.A.obj.hideHeader();
				'.$containerObj.'.wins_certificate_option.layout.A.obj.fixSize(false,false);

				$.ajax({
					url: "'.CController::createUrl('edit_scorm_certificate_options',array('container'=>$containerObj,'id_product'=>$id)).'",
					type: "POST",
					data: { "id":current_id, "id_product_downloadable_files":$("#'.$containerObj.'_id").val() },
					success: function(data){
						'.$containerObj.'.wins_certificate_option.layout.A.obj.attachHTMLString(data);		
					}
				});				
				
				// clean variables
				'.$containerObj.'.wins_certificate_option.obj.attachEvent("onClose",function(win){
					'.$containerObj.'.wins_certificate_option.obj.setModal(false);
					'.$containerObj.'.wins_certificate_option = new Object();
					'.$containerObj.'.wins_certificate.obj.setModal(true);
					return true;
				});	
				
				'.$containerObj.'.wins_certificate_option.toolbar.save = function(id,close){
					var obj = '.$containerObj.'.wins_certificate_option.toolbar.obj;
					$.ajax({
						url: "'.CController::createUrl('save_scorm_certificate_product').'",
						type: "POST",
						data: $("#"+'.$containerObj.'.wins_certificate_option.layout.obj.cont.obj.id+" *").serialize(),
						dataType: "json",
						beforeSend: function(){	
							obj.disableItem(id);			
						},
						complete: function(jqXHR, textStatus){
							if (typeof obj.enableItem == "function") obj.enableItem(id);
			
							if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$containerObj.'.wins_certificate_option.obj.close();
						},
						success: function(data){						
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
								} else {
			
									'.$containerObj.'.wins_certificate.grid.load($("#'.$containerObj.'_id").val());	
									
									alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
									
									// Verify if popup window is close
									if(!close){
										var id_tag_container = "'.$containerObj.'_id_scorm_certificate";
										var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
										$("#'.$containerObj.'_id_scorm_certificate_product").val(data.id);
										'.$containerObj.'.wins_certificate_option.toolbar.load(data.id);
										
										'.$containerObj.'.wins_certificate_option.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector+" option[value="+$(id_tag_selector).val()+"]").text());	
									}
									'.$containerObj.'.wins_certificate.grid.obj.selectRowById(data.id);
								}
							} else {
								alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
							}
						}
					});	
				}					
			}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
						
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
			
}

'.$containerObj.'.layout.B.toolbar.obj = '.$containerObj.'.layout.B.obj.attachToolbar();
'.$containerObj.'.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.B.toolbar.load();

'.$containerObj.'.layout.B.grid = new Object();
'.$containerObj.'.layout.B.grid.obj = '.$containerObj.'.layout.B.obj.attachGrid();
'.$containerObj.'.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
'.$containerObj.'.layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_FILE').','.Yii::t('global','LABEL_TYPE').'",null,["text-align:center"]);

'.$containerObj.'.layout.B.grid.obj.setInitWidthsP("5,50,30,15");
'.$containerObj.'.layout.B.grid.obj.setColAlign("center,left,left,left");
'.$containerObj.'.layout.B.grid.obj.setColSorting("na,na,na,na");
'.$containerObj.'.layout.B.grid.obj.enableDragAndDrop(true);
'.$containerObj.'.layout.B.grid.obj.enableResizing("false,false,false,false");
'.$containerObj.'.layout.B.grid.obj.setSkin(dhx_skin);
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
'.$containerObj.'.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_downloadable_files',array('id'=>$id)).'";
// load the initial grid
load_grid('.$containerObj.'.layout.B.grid.obj);

'.$containerObj.'.layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	// Verify if this file is Scorm and if the config for scorm certificate is active to show the button <Certificate>.
	$.ajax({
		url: "'.CController::createUrl('verify_scorm',array('id_product'=>$id)).'",
		type: "POST",
		data: { "id":rId },
		success: function(data){

			if(data=="1"){
				'.$containerObj.'.layout.B.toolbar.add(rId,'.$containerObj.'.layout.B.grid.obj.cellById(rId,1).getValue(),"1");	
			}else{
				'.$containerObj.'.layout.B.toolbar.add(rId,'.$containerObj.'.layout.B.grid.obj.cellById(rId,1).getValue(),"0");	
			}
			
		}
	});
	
	
});

'.$containerObj.'.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

'.$containerObj.'.layout.B.grid.obj.rowToDragElement=function(id){
	//any custom logic here
	var text="";

	for (var i=0; i<this._dragged.length; i++) {
		text += this.cells(this._dragged[i].idd,1).getValue() + "<br/>";
	}
	
	return text;
}

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
		url: "'.CController::createUrl('save_files_sort_order',array('id'=>$id)).'",
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




'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>