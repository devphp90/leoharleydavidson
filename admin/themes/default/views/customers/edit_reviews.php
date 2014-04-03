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
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.A.toolbar = new Object();

'.$containerObj.'.layout.A.toolbar.load = function(current_id){
	var obj = '.$containerObj.'.layout.A.toolbar.obj;
 
	obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");		

	obj.attachEvent("onClick",function(id){
		switch (id) {							
			case "delete":
				var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
				
				if (checked) {											
					if (confirm("'.Yii::t('views/products/edit_reviews','LABEL_ALERT_DELETE_REVIEWS').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}						
						
						$.ajax({
							url: "'.CController::createUrl('delete_review',array('id'=>$id)).'",
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
					alert("'.Yii::t('views/products/edit_reviews','LABEL_ALERT_NO_CHECKED').'");		
				}
				break;			
		}
	});	
};

'.$containerObj.'.layout.A.toolbar.modify = function(current_id){
	name = "'.Yii::t('global','LABEL_EDIT').' -  "+'.$containerObj.'.layout.A.grid.obj.cellById(current_id,2).getValue() + " Review";
	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("modifyReviewWindow", 10, 10, 600, 380);
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
					if (confirm("'.Yii::t('views/products/edit_reviews','LABEL_ALERT_DELETE_REVIEWS').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_review').'",
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
	
	'.$containerObj.'.wins.layout = new Object();
	'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("1C");
	'.$containerObj.'.wins.layout.A = new Object();
	'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");
	'.$containerObj.'.wins.layout.A.obj.hideHeader();			
	
	$.ajax({
		url: "'.CController::createUrl('edit_reviews_options',array('container'=>$containerObj)).'",
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
	
	'.$containerObj.'.wins.toolbar.save = function(id,close){
		var obj = '.$containerObj.'.wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_review').'",
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
						load_grid('.$containerObj.'.layout.A.grid.obj);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						'.$containerObj.'.layout.A.grid.obj.selectRowById(data.id);
					
						
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
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/products/edit_reviews','LABEL_DATE_CREATED').','.Yii::t('views/products/edit_reviews','LABEL_PRODUCT').','.Yii::t('views/products/edit_reviews','LABEL_REVIEW').','.Yii::t('views/products/edit_reviews','LABEL_RATING').'", null, ["text-align:center",,,,]);
'.$containerObj.'.layout.A.grid.obj.attachHeader(",,#text_filter_custom,,,");
	
// custom text filter input
'.$containerObj.'.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

'.$containerObj.'.layout.A.grid.obj.setInitWidthsP("4,15,20,51,10");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,left,left");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,server,server,na,server");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false,true,true,false");
'.$containerObj.'.layout.A.grid.obj.enableMultiline(true);
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

// set filter input names
'.$containerObj.'.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="name";

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_review',array('id'=>$id)).'";

// load the initial grid
load_grid('.$containerObj.'.layout.A.grid.obj);

'.$containerObj.'.layout.A.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
		load_grid(this, ind, dir);
		
		return false;
	});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	if (cInd == 5) {
		$.ajax({
			url: "'.CController::createUrl('toggle_approved').'",
			type: "POST",
			data: { "id":rId,"active":state }
		});		
	}
});

'.$containerObj.'.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$containerObj.'.layout.A.toolbar.modify(rId,this.cellById(rId,1).getValue());
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
';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>