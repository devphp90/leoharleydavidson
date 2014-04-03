<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerObj.'.wins_list = new Object();
'.$containerObj.'.wins = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.A.toolbar = new Object();
'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.obj.addButton("add", null, "'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$containerObj.'.layout.A.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_REMOVE_FROM_LIST').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$containerObj.'.layout.A.toolbar.obj.addSeparator("sep1", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.A.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/taxrules/edit_taxes','LABEL_TITLE_TAXES_UNDER').' "+tabs.obj.getLabel("'.$container.'")+" - '.Yii::t('views/taxrules/edit_taxes','LABEL_TITLE').'";

	switch (id) {
		case "add":
			'.$containerObj.'.layout.A.toolbar.add('.$id.');
			break;
		case "delete":			
			var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/taxrules/edit_taxes','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_tax',array('id'=>$id)).'",
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
							alert("'.Yii::t('global','LABEL_ALERT_REMOVE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/taxrules/edit_taxes','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "export_pdf":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup('.$containerObj.'.layout.A.grid.obj,"printview",[0],title);
			break;
	}
});


'.$containerObj.'.layout.A.toolbar.add = function(id_tax_rule){
	name = "'.Yii::t('views/taxrules/edit_taxes','LABEL_ADD_TAXES').'";
	

	'.$containerObj.'.wins_list.obj = '.$containerObj.'.dhxWins.createWindow("addTaxesWindow", 10, 10, 600, 380);
	'.$containerObj.'.wins_list.obj.setText(name);
	'.$containerObj.'.wins_list.obj.button("park").hide();
	'.$containerObj.'.wins_list.obj.keepInViewport(true);
	'.$containerObj.'.wins_list.obj.setModal(true);
				
	'.$containerObj.'.wins_list.toolbar = new Object();

	'.$containerObj.'.wins_list.toolbar.load = function(){
		var obj = '.$containerObj.'.wins_list.toolbar.obj;
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_INSERT_SELECTED').'","toolbar/save.gif","toolbar/save_dis.gif");
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$containerObj.'.wins_list.toolbar.save(id);
					break;
			}
		});	
	}	
	
	'.$containerObj.'.wins_list.toolbar.obj = '.$containerObj.'.wins_list.obj.attachToolbar();
	'.$containerObj.'.wins_list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins_list.toolbar.load();

	'.$containerObj.'.wins_list.layout = new Object();
	'.$containerObj.'.wins_list.layout.obj = '.$containerObj.'.wins_list.obj.attachLayout("1C");
	'.$containerObj.'.wins_list.layout.A = new Object();
	'.$containerObj.'.wins_list.layout.A.obj = '.$containerObj.'.wins_list.layout.obj.cells("a");
	
	'.$containerObj.'.wins_list.layout.A.obj.hideHeader();
	
	'.$containerObj.'.wins_list.layout.A.grid = new Object();
	
	'.$containerObj.'.wins_list.layout.A.grid.obj = '.$containerObj.'.wins_list.layout.A.obj.attachGrid();
	'.$containerObj.'.wins_list.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
	'.$containerObj.'.wins_list.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_CODE').','.Yii::t('global','LABEL_TAX_NUMBER').'");
	'.$containerObj.'.wins_list.layout.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom");
	
	// custom text filter input
	'.$containerObj.'.wins_list.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
	
	'.$containerObj.'.wins_list.layout.A.grid.obj.setInitWidths("40,*,100,100,100");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setColAlign("center,left,left,center,right");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setColSorting("na,na,na,na,na");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setSkin(dhx_skin);
	'.$containerObj.'.wins_list.layout.A.grid.obj.enableDragAndDrop(false);
	'.$containerObj.'.wins_list.layout.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
	//'.$containerObj.'.wins_list.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	//Paging
	'.$containerObj.'.wins_list.layout.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_win\'></div>");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	'.$containerObj.'.wins_list.layout.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_win");
	'.$containerObj.'.wins_list.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
	'.$containerObj.'.wins_list.layout.A.grid.obj.i18n.paging={
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
	'.$containerObj.'.wins_list.layout.A.grid.obj.init();
	
	// set filter input names
	'.$containerObj.'.wins_list.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	'.$containerObj.'.wins_list.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="code";
	'.$containerObj.'.wins_list.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="tax_number";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	'.$containerObj.'.wins_list.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_taxes_add').'?id_tax_rule="+id_tax_rule;
	
	// load the initial grid
	load_grid('.$containerObj.'.wins_list.layout.A.grid.obj);		
	
	// clean variables
	'.$containerObj.'.wins_list.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins_list = new Object();
		return true;
	});	
	
	'.$containerObj.'.wins_list.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	'.$containerObj.'.wins_list.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});		
	
	'.$containerObj.'.wins_list.toolbar.save = function(id){
		var obj = '.$containerObj.'.wins_list.toolbar.obj;
		var checked = '.$containerObj.'.wins_list.layout.A.grid.obj.getCheckedRows(0);
		if (checked) {											
				checked = checked.split(",");
				var ids=[];
				
				for (var i=0;i<checked.length;++i) {
					if (checked[i]) {
						ids.push("ids[]="+checked[i]);									
					}
				}						
				
				$.ajax({
					url: "'.CController::createUrl('add_taxes').'",
					type: "POST",
					data: ids.join("&")+"&id_tax_rule="+id_tax_rule,
					beforeSend: function(){		
						obj.disableItem(id);
					},
					complete: function(){
						'.$containerObj.'.wins_list.obj.close();
					},
					success: function(data){	
						load_grid('.$containerObj.'.layout.A.grid.obj);
						alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
					}
				});								
		} else {
			alert("'.Yii::t('views/taxrules/edit_taxes','LABEL_ALERT_NO_CHECKED').'");		
		}	
		
	};

}

'.$containerObj.'.layout.A.toolbar.modify = function(current_id){

	name = "'.Yii::t('global','LABEL_EDIT').' "+'.$containerObj.'.layout.A.grid.obj.cellById(current_id,1).getValue(); 
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("editTaxesWindow", 10, 10, 300, 130);
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
	
	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	'.$containerObj.'.wins.layout.A.obj.hideHeader();	
	

	$.ajax({
		url: "'.CController::createUrl('edit_tax_rate',array('container'=>$containerObj)).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			'.$containerObj.'.wins.layout.A.obj.attachHTMLString(data);		
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
			url: "'.CController::createUrl('save_tax_rate',array('container'=>$containerObj)).'",
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
							var id_tag_container = "tpl_product_variant_group_description['.Yii::app()->language.'][name]";
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


'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_CODE').','.Yii::t('global','LABEL_TAX_NUMBER').',%,'.Yii::t('views/taxrules/edit_taxes','LABEL_STACKED').'",null,["text-align:center",,,,"text-align:right","text-align:center"]);

'.$containerObj.'.layout.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#text_filter_custom,,");
	
// custom text filter input
'.$containerObj.'.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

'.$containerObj.'.layout.A.grid.obj.setInitWidths("40,*,*,*,100,100");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,left,right,center");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,true,true,true,true,true");
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

// set filter input names
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="code";
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="tax_number";

'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_taxes',array('id'=>$id)).'";
load_grid('.$containerObj.'.layout.A.grid.obj);

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
		url: "'.CController::createUrl('save_taxes_sort_order',array('id'=>$id)).'",
		type: "POST",
		data: ids.join("&")
	});	
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	if (cInd == 5) {
		$.ajax({
			url: "'.CController::createUrl('toggle_stacked').'",
			type: "POST",
			data: { "id":rId,"stacked":state }
		});			
	}
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.modify(rId);
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerObj.'.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});

// clear event for this tab and reset
$(window).off("resize.'.$containerObj.'");
$(window).on("resize.'.$containerObj.'",function(){
	setTimeout("'.$containerObj.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();

function isNumeric(elem){
	var numericExpression = /^[0-9\.]+$/;
	if(!elem.match(numericExpression)){
		return false;
	}
	
	return true;
}
';

echo Html::script($script); 
?>
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>