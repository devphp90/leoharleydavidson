<?php
$containerJS = 'Tab'.$container;
$containerLayout = $containerJS.'_layout';
$language = Yii::app()->language;

$script = '
var '.$containerJS.' = new Object();
// variables used to check modification
'.$containerJS.'.og_form = [];

'.$containerJS.'.id_tax_group = '.($model->id ? $model->id:0).';

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "3T");
'.$containerJS.'.layout.toolbar = new Object();

'.$containerJS.'.dhxWins = new dhtmlXWindows();
'.$containerJS.'.dhxWins.enableAutoViewport(false);
'.$containerJS.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerJS.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerJS.'.wins_list = new Object();

'.$containerJS.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
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
				'.$containerJS.'.layout.toolbar.save(id);
				break;
			case "save_close":
				'.$containerJS.'.layout.toolbar.save(id,true);
				break;
			case "delete":
				if (confirm("'.Yii::t('views/taxgroups/edit','LABEL_ALERT_DELETE').'")) {
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

'.$containerJS.'.layout.toolbar.save = function(id,close){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
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
					var id_tax_container = "'.$containerJS.'_name";
					var id_tax_selector = "#"+id_tax_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
				
					// if name changed, rename
					if (tabs.obj.getLabel("'.$container.'") != $(id_tax_selector).val()) {
						tabs.obj.setLabel("'.$container.'",$(id_tax_selector).val());	
					}
										
					//Enable de Toolbar
					'.$containerJS.'.layout.B.toolbar.obj.forEachItem(function(itemId){
						'.$containerJS.'.layout.B.toolbar.obj.enableItem(itemId);
					});	
					
					if (!'.$containerJS.'.id_tax_group) {								
						'.$containerJS.'.id_tax_group=data.id;
						'.$containerJS.'.layout.toolbar.load('.$containerJS.'.id_tax_group);																
						
						// when we create a new product and save, store its id and container value in array of opened tabs
						tabs.totalOpened[data.id] = "'.$container.'";									
					}
					
					load_grid(tabs.list.grid.obj);
					
					alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
					
					'.$containerJS.'.load_og_form();
				}
			} else {
				alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
			}
		}
	});					
};

'.$containerJS.'.layout.toolbar.obj = '.$containerJS.'.layout.obj.attachToolbar();
'.$containerJS.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.toolbar.load('.$model->id.');

'.$containerJS.'.layout.A = new Object();
'.$containerJS.'.layout.A.obj = '.$containerJS.'.layout.obj.cells("a");
'.$containerJS.'.layout.A.obj.setHeight(100);
'.$containerJS.'.layout.A.obj.fixSize(false, true);
'.$containerJS.'.layout.A.obj.hideHeader();

'.$containerJS.'.layout.A.tabs = new Object();
'.$containerJS.'.layout.A.tabs.obj = '.$containerJS.'.layout.A.obj.attachTabbar();
'.$containerJS.'.layout.A.tabs.obj.setImagePath(dhx_globalImgPath);	
'.$containerJS.'.layout.A.tabs.obj.clearAll();

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
//'.$containerJS.'.layout.B.obj.hideHeader();
'.$containerJS.'.layout.B.obj.showHeader();
'.$containerJS.'.layout.B.obj.setText("'.Yii::t('views/taxgroups/edit','LABEL_TITLE_PRODUCT').'");
'.$containerJS.'.layout.B.obj.hideArrow();

'.$containerJS.'.layout.B.toolbar = new Object();
'.$containerJS.'.layout.B.toolbar.obj = '.$containerJS.'.layout.B.obj.attachToolbar();
'.$containerJS.'.layout.B.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.B.toolbar.obj.addButton("add",null,"'.Yii::t('views/taxgroups/edit','LABEL_BTN_ADD_PRODUCT').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$containerJS.'.layout.B.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_REMOVE_FROM_LIST').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$containerJS.'.layout.B.toolbar.obj.addSeparator("sep1", null);
'.$containerJS.'.layout.B.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerJS.'.layout.B.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerJS.'.layout.B.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerJS.'.layout.B.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/taxgroups/edit','LABEL_TITLE_PRODUCT_UNDER').' "+tabs.obj.getLabel("'.$container.'")+" - '.Yii::t('global','LABEL_TAX_GROUP').'";

	switch (id) {
		case "add":
			'.$containerJS.'.layout.B.toolbar.add('.$containerJS.'.id_tax_group);
			break;
		case "delete":			
			var checked = '.$containerJS.'.layout.B.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/taxgroups/edit','LABEL_ALERT_REMOVE_PRODUCT').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_product').'?id_tax_group="+'.$containerJS.'.id_tax_group,
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid('.$containerJS.'.layout.B.grid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/taxgroups/edit','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "export_pdf":
			printGridPopup('.$containerJS.'.layout.B.grid.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup('.$containerJS.'.layout.B.grid.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup('.$containerJS.'.layout.B.grid.obj,"printview",[0],title);
			break;					
	}
});


'.$containerJS.'.layout.B.toolbar.obj.forEachItem(function(itemId){
	if('.$containerJS.'.id_tax_group){
		'.$containerJS.'.layout.B.toolbar.obj.enableItem(itemId);
	}else{
		'.$containerJS.'.layout.B.toolbar.obj.disableItem(itemId);
	}
});	



'.$containerJS.'.layout.B.toolbar.add = function(id_tax_group){
	name = "'.Yii::t('views/taxgroups/edit','LABEL_BTN_ADD_PRODUCT').'";

	'.$containerJS.'.wins_list.obj = '.$containerJS.'.dhxWins.createWindow("addProductWindow", 10, 10, 700, 440);
	'.$containerJS.'.wins_list.obj.setText(name);
	'.$containerJS.'.wins_list.obj.button("park").hide();
	'.$containerJS.'.wins_list.obj.keepInViewport(true);
	'.$containerJS.'.wins_list.obj.setModal(true);
	//'.$containerJS.'.wins_list.obj.center();
	
	'.$containerJS.'.wins_list.toolbar = new Object();

	'.$containerJS.'.wins_list.toolbar.load = function(id_tax_group){
		var obj = '.$containerJS.'.wins_list.toolbar.obj;
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED').'","toolbar/save.gif","toolbar/save_dis.gif");
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerJS.'.wins_list.toolbar.save(id);
					break;
			}
		});	
	}	
	
	'.$containerJS.'.wins_list.toolbar.obj = '.$containerJS.'.wins_list.obj.attachToolbar();
	'.$containerJS.'.wins_list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerJS.'.wins_list.toolbar.load(id_tax_group);

	'.$containerJS.'.wins_list.layout = new Object();
	'.$containerJS.'.wins_list.layout.obj = '.$containerJS.'.wins_list.obj.attachLayout("1C");
	'.$containerJS.'.wins_list.layout.B = new Object();
	'.$containerJS.'.wins_list.layout.B.obj = '.$containerJS.'.wins_list.layout.obj.cells("a");
	
	'.$containerJS.'.wins_list.layout.B.obj.hideHeader();
	
	'.$containerJS.'.wins_list.layout.B.grid = new Object();
	
	'.$containerJS.'.wins_list.layout.B.grid.obj = '.$containerJS.'.wins_list.layout.B.obj.attachGrid();
	'.$containerJS.'.wins_list.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
	'.$containerJS.'.wins_list.layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_TAX_GROUP').'",null,["text-align:center;"]);
	'.$containerJS.'.wins_list.layout.B.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
	// custom text filter input
	'.$containerJS.'.wins_list.layout.B.grid.obj._in_header_text_filter_custom=text_filter_custom;
	
	'.$containerJS.'.wins_list.layout.B.grid.obj.setInitWidths("40,*,150,150");
	'.$containerJS.'.wins_list.layout.B.grid.obj.setColAlign("center,left,left,left");
	'.$containerJS.'.wins_list.layout.B.grid.obj.setColSorting("na,na,na,na");
	'.$containerJS.'.wins_list.layout.B.grid.obj.setSkin(dhx_skin);
	'.$containerJS.'.wins_list.layout.B.grid.obj.enableDragAndDrop(false);
	'.$containerJS.'.wins_list.layout.B.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
	//'.$containerJS.'.wins_list.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	//Paging
	'.$containerJS.'.wins_list.layout.B.obj.attachStatusBar().setText("<div id=\''.$containerJS.'_recinfoArea_win_b\'></div>");
	'.$containerJS.'.wins_list.layout.B.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	'.$containerJS.'.wins_list.layout.B.grid.obj.enablePaging(true, 100, 3, "'.$containerJS.'_recinfoArea_win_b");
	'.$containerJS.'.wins_list.layout.B.grid.obj.setPagingSkin("toolbar", dhx_skin);
	'.$containerJS.'.wins_list.layout.B.grid.obj.i18n.paging={
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
	'.$containerJS.'.wins_list.layout.B.grid.obj.init();
	
	// set filter input names
	'.$containerJS.'.wins_list.layout.B.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	'.$containerJS.'.wins_list.layout.B.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
	'.$containerJS.'.wins_list.layout.B.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="tax_group";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	'.$containerJS.'.wins_list.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_product_add').'?id_tax_group="+id_tax_group;
	
	// load the initial grid
	load_grid('.$containerJS.'.wins_list.layout.B.grid.obj);		
	
	'.$containerJS.'.wins_list.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	'.$containerJS.'.wins_list.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});	
	
	// clean variables
	'.$containerJS.'.wins_list.obj.attachEvent("onClose",function(win){
		'.$containerJS.'.wins_list = new Object();
		return true;
	});			
	
	'.$containerJS.'.wins_list.toolbar.save = function(id){
		var obj = '.$containerJS.'.wins_list.toolbar.obj;
		var checked = '.$containerJS.'.wins_list.layout.B.grid.obj.getCheckedRows(0);
		if (checked) {											
			checked = checked.split(",");
			var ids=[];
			
			for (var i=0;i<checked.length;++i) {
				if (checked[i]) {
					ids.push("ids[]="+checked[i]);									
				}
			}						
			$.ajax({
				url: "'.CController::createUrl('add_product').'",
				type: "POST",
				data: ids.join("&")+"&id_tax_group="+id_tax_group,
				beforeSend: function(){		
					obj.disableItem(id);
				},
				complete: function(){
					'.$containerJS.'.wins_list.obj.close();
				},
				success: function(data){	
					//Refresh grid XML URL with id_tax_group
					'.$containerJS.'.layout.B.grid.obj.clearAll();
					'.$containerJS.'.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_products').'?id_tax_group="+data;
					'.$containerJS.'.layout.B.grid.obj.loadXML('.$containerJS.'.layout.B.grid.obj.xmlOrigFileUrl);
					
					alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
				}
			});							
		} else {
			alert("'.Yii::t('views/taxgroups/edit','LABEL_ALERT_NO_CHECKED').'");		
		}	
		
	};		
	

}


'.$containerJS.'.layout.B.grid = new Object();
'.$containerJS.'.layout.B.grid.obj = '.$containerJS.'.layout.B.obj.attachGrid();
'.$containerJS.'.layout.B.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerJS.'.layout.B.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').'",null,["text-align:center;"]);
'.$containerJS.'.layout.B.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom");
	
// custom text filter input
'.$containerJS.'.layout.B.grid.obj._in_header_text_filter_custom=text_filter_custom;

'.$containerJS.'.layout.B.grid.obj.setInitWidths("40,*,*,50");
'.$containerJS.'.layout.B.grid.obj.setColAlign("center,left,left");
'.$containerJS.'.layout.B.grid.obj.setColSorting("na,na,na,na");
'.$containerJS.'.layout.B.grid.obj.enableResizing("false,false,false");
'.$containerJS.'.layout.B.grid.obj.setSkin(dhx_skin);
'.$containerJS.'.layout.B.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
//'.$containerJS.'.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover);

//Paging
'.$containerJS.'.layout.B.obj.attachStatusBar().setText("<div id=\''.$containerJS.'_recinfoArea_b\'></div>");
'.$containerJS.'.layout.B.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerJS.'.layout.B.grid.obj.enablePaging(true, 100, 3, "'.$containerJS.'_recinfoArea_b");
'.$containerJS.'.layout.B.grid.obj.setPagingSkin("toolbar", dhx_skin);
'.$containerJS.'.layout.B.grid.obj.i18n.paging={
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
'.$containerJS.'.layout.B.grid.obj.init();

// set filter input names
'.$containerJS.'.layout.B.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
'.$containerJS.'.layout.B.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";

'.$containerJS.'.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_products').'?id_tax_group="+'.$containerJS.'.id_tax_group;
'.$containerJS.'.layout.B.grid.obj.loadXML('.$containerJS.'.layout.B.grid.obj.xmlOrigFileUrl);

'.$containerJS.'.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerJS.'.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});


'.$containerJS.'.layout.C = new Object();
'.$containerJS.'.layout.C.obj = '.$containerJS.'.layout.obj.cells("c");
//'.$containerJS.'.layout.C.obj.hideHeader();
'.$containerJS.'.layout.C.obj.showHeader();
'.$containerJS.'.layout.C.obj.hideArrow();
'.$containerJS.'.layout.C.obj.setText("'.Yii::t('views/taxgroups/edit','LABEL_TITLE_OPTION').'");

'.$containerJS.'.layout.C.toolbar = new Object();
'.$containerJS.'.layout.C.toolbar.obj = '.$containerJS.'.layout.C.obj.attachToolbar();
'.$containerJS.'.layout.C.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.C.toolbar.obj.addButton("add",null,"'.Yii::t('views/taxgroups/edit','LABEL_BTN_ADD_OPTION').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$containerJS.'.layout.C.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_REMOVE_FROM_LIST').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$containerJS.'.layout.C.toolbar.obj.addSeparator("sep1", null);
'.$containerJS.'.layout.C.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerJS.'.layout.C.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerJS.'.layout.C.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerJS.'.layout.C.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/taxgroups/edit','LABEL_TITLE_OPTION_UNDER').' "+tabs.obj.getLabel("'.$container.'")+" - '.Yii::t('global','LABEL_TAX_GROUP').'";

	switch (id) {
		case "add":
			'.$containerJS.'.layout.C.toolbar.add('.$containerJS.'.id_tax_group);
			break;
		case "delete":			
			var checked = '.$containerJS.'.layout.C.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/taxgroups/edit','LABEL_ALERT_REMOVE_OPTION').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_option').'?id_tax_group="+'.$containerJS.'.id_tax_group,
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid('.$containerJS.'.layout.C.grid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/taxgroups/edit','LABEL_ALERT_NO_CHECKED_OPTION').'");	
			}
			break;
		case "export_pdf":
			printGridPopup('.$containerJS.'.layout.C.grid.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup('.$containerJS.'.layout.C.grid.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup('.$containerJS.'.layout.C.grid.obj,"printview",[0],title);
			break;					
	}
});


'.$containerJS.'.layout.C.toolbar.obj.forEachItem(function(itemId){
	if('.$containerJS.'.id_tax_group){
		'.$containerJS.'.layout.C.toolbar.obj.enableItem(itemId);
	}else{
		'.$containerJS.'.layout.C.toolbar.obj.disableItem(itemId);
	}
});	



'.$containerJS.'.layout.C.toolbar.add = function(id_tax_group){
	name = "'.Yii::t('views/taxgroups/edit','LABEL_BTN_ADD_OPTION').'";

	'.$containerJS.'.wins_list.obj = '.$containerJS.'.dhxWins.createWindow("addOptionWindow", 10, 10, 700, 440);
	'.$containerJS.'.wins_list.obj.setText(name);
	'.$containerJS.'.wins_list.obj.button("park").hide();
	'.$containerJS.'.wins_list.obj.keepInViewport(true);
	'.$containerJS.'.wins_list.obj.setModal(true);
	//'.$containerJS.'.wins_list.obj.center();
	
	'.$containerJS.'.wins_list.toolbar = new Object();

	'.$containerJS.'.wins_list.toolbar.load = function(id_tax_group){
		var obj = '.$containerJS.'.wins_list.toolbar.obj;
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED').'","toolbar/save.gif","toolbar/save_dis.gif");
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerJS.'.wins_list.toolbar.save(id);
					break;
			}
		});	
	}	
	
	'.$containerJS.'.wins_list.toolbar.obj = '.$containerJS.'.wins_list.obj.attachToolbar();
	'.$containerJS.'.wins_list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerJS.'.wins_list.toolbar.load(id_tax_group);

	'.$containerJS.'.wins_list.layout = new Object();
	'.$containerJS.'.wins_list.layout.obj = '.$containerJS.'.wins_list.obj.attachLayout("1C");
	'.$containerJS.'.wins_list.layout.C = new Object();
	'.$containerJS.'.wins_list.layout.C.obj = '.$containerJS.'.wins_list.layout.obj.cells("a");
	
	'.$containerJS.'.wins_list.layout.C.obj.hideHeader();
	
	'.$containerJS.'.wins_list.layout.C.grid = new Object();
	
	'.$containerJS.'.wins_list.layout.C.grid.obj = '.$containerJS.'.wins_list.layout.C.obj.attachGrid();
	'.$containerJS.'.wins_list.layout.C.grid.obj.setImagePath(dhx_globalImgPath);	
	'.$containerJS.'.wins_list.layout.C.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/taxgroups/edit','LABEL_GROUP_OPTION').','.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_TAX_GROUP').'",null,["text-align:center;"]);
	'.$containerJS.'.wins_list.layout.C.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
	// custom text filter input
	'.$containerJS.'.wins_list.layout.C.grid.obj._in_header_text_filter_custom=text_filter_custom;
	
	'.$containerJS.'.wins_list.layout.C.grid.obj.setInitWidths("40,150,*,150,150");
	'.$containerJS.'.wins_list.layout.C.grid.obj.setColAlign("center,left,left,left,left");
	'.$containerJS.'.wins_list.layout.C.grid.obj.setColSorting("na,na,na,na,na");
	'.$containerJS.'.wins_list.layout.C.grid.obj.setSkin(dhx_skin);
	'.$containerJS.'.wins_list.layout.C.grid.obj.enableDragAndDrop(false);
	'.$containerJS.'.wins_list.layout.C.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
	//'.$containerJS.'.wins_list.layout.C.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	//Paging
	'.$containerJS.'.wins_list.layout.C.obj.attachStatusBar().setText("<div id=\''.$containerJS.'_recinfoArea_win_b\'></div>");
	'.$containerJS.'.wins_list.layout.C.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	'.$containerJS.'.wins_list.layout.C.grid.obj.enablePaging(true, 100, 3, "'.$containerJS.'_recinfoArea_win_b");
	'.$containerJS.'.wins_list.layout.C.grid.obj.setPagingSkin("toolbar", dhx_skin);
	'.$containerJS.'.wins_list.layout.C.grid.obj.i18n.paging={
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
	'.$containerJS.'.wins_list.layout.C.grid.obj.init();
	
	// set filter input names
	'.$containerJS.'.wins_list.layout.C.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="options_group";
	'.$containerJS.'.wins_list.layout.C.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="name";
	'.$containerJS.'.wins_list.layout.C.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="sku";
	'.$containerJS.'.wins_list.layout.C.grid.obj.hdr.rows[2].cells[4].getElementsByTagName("INPUT")[0].name="tax_group";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	'.$containerJS.'.wins_list.layout.C.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_options_add').'?id_tax_group="+id_tax_group;
	
	// load the initial grid
	load_grid('.$containerJS.'.wins_list.layout.C.grid.obj);		
	
	'.$containerJS.'.wins_list.layout.C.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	'.$containerJS.'.wins_list.layout.C.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});	
	
	// clean variables
	'.$containerJS.'.wins_list.obj.attachEvent("onClose",function(win){
		'.$containerJS.'.wins_list = new Object();
		return true;
	});			
	
	'.$containerJS.'.wins_list.toolbar.save = function(id){
		var obj = '.$containerJS.'.wins_list.toolbar.obj;
		var checked = '.$containerJS.'.wins_list.layout.C.grid.obj.getCheckedRows(0);
		if (checked) {											
			checked = checked.split(",");
			var ids=[];
			
			for (var i=0;i<checked.length;++i) {
				if (checked[i]) {
					ids.push("ids[]="+checked[i]);									
				}
			}						
			$.ajax({
				url: "'.CController::createUrl('add_options').'",
				type: "POST",
				data: ids.join("&")+"&id_tax_group="+id_tax_group,
				beforeSend: function(){		
					obj.disableItem(id);
				},
				complete: function(){
					'.$containerJS.'.wins_list.obj.close();
				},
				success: function(data){	
					//Refresh grid XML URL with id_tax_group
					'.$containerJS.'.layout.C.grid.obj.clearAll();
					'.$containerJS.'.layout.C.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_options').'?id_tax_group="+data;
					'.$containerJS.'.layout.C.grid.obj.loadXML('.$containerJS.'.layout.C.grid.obj.xmlOrigFileUrl);
					
					alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
				}
			});							
		} else {
			alert("'.Yii::t('views/taxgroups/edit','LABEL_ALERT_NO_CHECKED').'");		
		}	
		
	};		
	

}


'.$containerJS.'.layout.C.grid = new Object();
'.$containerJS.'.layout.C.grid.obj = '.$containerJS.'.layout.C.obj.attachGrid();
'.$containerJS.'.layout.C.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerJS.'.layout.C.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/taxgroups/edit','LABEL_GROUP_OPTION').','.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').'",null,["text-align:center;"]);
'.$containerJS.'.layout.C.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
// custom text filter input
'.$containerJS.'.layout.C.grid.obj._in_header_text_filter_custom=text_filter_custom;

'.$containerJS.'.layout.C.grid.obj.setInitWidths("40,*,*,*");
'.$containerJS.'.layout.C.grid.obj.setColAlign("center,left,left,left");
'.$containerJS.'.layout.C.grid.obj.setColSorting("na,na,na,na");
'.$containerJS.'.layout.C.grid.obj.enableResizing("false,false,false,false");
'.$containerJS.'.layout.C.grid.obj.setSkin(dhx_skin);
'.$containerJS.'.layout.C.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
//'.$containerJS.'.layout.C.grid.obj.enableRowsHover(true,dhx_rowhover);

//Paging
'.$containerJS.'.layout.C.obj.attachStatusBar().setText("<div id=\''.$containerJS.'_recinfoArea_c\'></div>");
'.$containerJS.'.layout.C.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$containerJS.'.layout.C.grid.obj.enablePaging(true, 100, 3, "'.$containerJS.'_recinfoArea_c");
'.$containerJS.'.layout.C.grid.obj.setPagingSkin("toolbar", dhx_skin);
'.$containerJS.'.layout.C.grid.obj.i18n.paging={
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
'.$containerJS.'.layout.C.grid.obj.init();

// set filter input names
'.$containerJS.'.layout.C.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="options_group";
'.$containerJS.'.layout.C.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="name";
'.$containerJS.'.layout.C.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="sku";

'.$containerJS.'.layout.C.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_options').'?id_tax_group="+'.$containerJS.'.id_tax_group;
'.$containerJS.'.layout.C.grid.obj.loadXML('.$containerJS.'.layout.C.grid.obj.xmlOrigFileUrl);

'.$containerJS.'.layout.C.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerJS.'.layout.C.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

// clear event for this tab and reset
$(window).off("resize.'.$containerJS.'");
$(window).on("resize.'.$containerJS.'",function(){
	setTimeout("'.$containerJS.'.layout.obj.setSizes()",500);
});

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
';

echo Html::script($script);
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>