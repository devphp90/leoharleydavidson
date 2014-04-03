<?php 
$script = '
edit = new Object();

edit.wins = new Object();
edit.wins_list = new Object();

edit.layout = new Object();
edit.layout.obj = layout.B.obj.attachLayout("1C");


dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(false);
dhxWins.attachViewportTo(edit.layout.obj.cont.obj.id);
dhxWins.setImagePath(dhx_globalImgPath);	

edit.layout.A = new Object();
edit.layout.A.obj = edit.layout.obj.cells("a");
edit.layout.A.obj.hideHeader();

edit.layout.A.toolbar = new Object();

edit.layout.A.toolbar.load = function(){
	var obj = edit.layout.A.toolbar.obj;
	
	obj.addButton("add",null,"'.Yii::t('views/config/edit_store_locations','BTN_ADD_STORE_LOCATION').'","toolbar/add.gif","toolbar/add_dis.gif"); 
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");	
	obj.addSeparator("sep01", null);
	obj.addButton("preview",null,"'.Yii::t('global','LABEL_PREVIEW').'","toolbar/preview.png","toolbar/preview_dis.png");

	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "add":		
				edit.layout.A.toolbar.add();
				break;							
			case "delete":
				var checked = edit.layout.A.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/config/edit_store_locations','LABEL_ALERT_DELETE').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_store_location').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
							},
							success: function(data){
								load_grid(edit.layout.A.grid.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
				} else {
					alert("'.Yii::t('views/config/edit_store_locations','LABEL_ALERT_NO_CHECKED').'");		
				}
				break;
			case "preview":
				window.open("'.CController::createUrl('preview_store_locations').'","_blank");
				break;
		}
	});	
};


edit.layout.A.toolbar.add = function(current_id){
	name = current_id ? "'.Yii::t('global','LABEL_EDIT').' "+edit.layout.A.grid.obj.cellById(current_id,1).getValue() : "'.Yii::t('global','LABEL_NEW').'";
	var wins_options = new Object();
	wins_options.obj = dhxWins.createWindow("modifyStoreWindow", 0, 0, 600, 460);
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
					if (confirm("'.Yii::t('views/config/edit_store_locations','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_store_location').'",
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
								load_grid(edit.layout.A.grid.obj);
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
	wins_options.layout.obj = wins_options.obj.attachLayout("1C");
	wins_options.layout.A = new Object();
	wins_options.layout.A.obj = wins_options.layout.obj.cells("a");
	wins_options.layout.A.obj.hideHeader();	
	//wins_options.layout.A.obj.setWidth(550);
	wins_options.layout.A.obj.fixSize(false,false);		
	
	$.ajax({
		url: "'.CController::createUrl('edit_store_locations_options').'?id="+current_id,
		type: "POST",
		success: function(data){
			wins_options.layout.A.obj.attachHTMLString(data);	
		}
	});				
	
	// clean variables
	wins_options.obj.attachEvent("onClose",function(win){
		// To correct a Bug in SWF Uploadify
		var swfuploadify = window["uploadify_image_upload_button"];
		if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();
		
		var wins_options = new Object();
		
		return true;
	});			

	
	wins_options.toolbar.save = function(id,close){			
		var obj = wins_options.toolbar.obj;
		
		$.ajax({
			url: "'.CController::createUrl('save_store_location').'",
			type: "POST",
			data: $("#"+wins_options.layout.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){				
				// clear all errors					
				$("#"+wins_options.layout.obj.cont.obj.id+" span.error").html("");
				$("#"+wins_options.layout.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);

				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins_options.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_").removeClass("error_background");
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
						
						load_grid(edit.layout.A.grid.obj);
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var id_tag_container = "name";
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");		
							$("#id").val(data.id);

							wins_options.toolbar.load(data.id);
							
							wins_options.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector).val());
						
							var swfuploadify = window["uploadify_image_upload_button"];
							if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();
						}
					
						
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

edit.layout.A.toolbar.obj = edit.layout.A.obj.attachToolbar();
edit.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
edit.layout.A.toolbar.load();

edit.layout.A.grid = new Object();
edit.layout.A.grid.obj = edit.layout.A.obj.attachGrid();
edit.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
edit.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('views/config/edit_store_locations_options','LABEL_ADDRESS').','.Yii::t('views/config/edit_store_locations_options','LABEL_HIDE_ADDRESS').','.Yii::t('global','LABEL_ENABLED').'",null,["text-align:center",,,"text-align:center","text-align:center"]);

edit.layout.A.grid.obj.setInitWidthsP("4,33,33,15,15");
edit.layout.A.grid.obj.setColAlign("center,left,left,center,center");
edit.layout.A.grid.obj.setColSorting("na,server,na,na,na");
edit.layout.A.grid.obj.enableResizing("false,true,true,false,false");
edit.layout.A.grid.obj.enableMultiline(true);
edit.layout.A.grid.obj.setSkin(dhx_skin);
edit.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
edit.layout.A.obj.attachStatusBar().setText("<div id=\'edit_recinfoArea\'></div>");
edit.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
edit.layout.A.grid.obj.enablePaging(true, 100, 3, "edit_recinfoArea");
edit.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
edit.layout.A.grid.obj.i18n.paging={
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
edit.layout.A.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
edit.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_store_locations').'";

// load the initial grid
load_grid(edit.layout.A.grid.obj);

edit.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	edit.layout.A.toolbar.add(rId,this.cellById(rId,1).getValue());
});

edit.layout.A.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
	load_grid(this, ind, dir);
	
	return false;
});

edit.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

edit.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

edit.layout.A.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	var obj = this;
	
	switch (cInd) {
		// status
		case 3:
			$.ajax({
				url: "'.CController::createUrl('toggle_hide_address_store_location').'",
				type: "POST",
				data: { "id":rId,"state":state }
			});	
					
			break;
		case 4:
			$.ajax({
				url: "'.CController::createUrl('toggle_active_store_location').'",
				type: "POST",
				data: { "id":rId,"state":state }
			});	
			if(state){
				edit.layout.A.grid.obj.setRowTextStyle(rId, "color: #000000");	
			}else{
				edit.layout.A.grid.obj.setRowTextStyle(rId, "color: #B5B5B5;");		
			}			
			break;
	}
});


layout.A.dataview.ajaxComplete();

';

echo Html::script($script); 
?>