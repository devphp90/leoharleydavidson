<?php 
$subscription_contest = Tbl_SubscriptionContest::model()->findByPk($id);

$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();

'.$containerObj.'.contest = '.$subscription_contest->contest.';

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerObj.'.wins_list = new Object();

'.$containerObj.'.layout.A.toolbar = new Object();
'.$containerObj.'.layout.A.toolbar.obj = '.$containerObj.'.layout.A.obj.attachToolbar();
'.$containerObj.'.layout.A.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.A.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_REMOVE_FROM_LIST').'", "toolbar/delete.png", "toolbar/delete-dis.png");
if ('.$containerObj.'.contest == 1) {
'.$containerObj.'.layout.A.toolbar.obj.addSeparator("sep1", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("winner", null, "'.Yii::t('views/subscriptioncontest/edit_persons','LABEL_BTN_WINNER').'", "toolbar/winner.png", "toolbar/winner-dis.png");
}
'.$containerObj.'.layout.A.toolbar.obj.addSeparator("sep2", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$containerObj.'.layout.A.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$containerObj.'.layout.A.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "Products under: "+tabs.obj.getLabel("'.$container.'");

	switch (id) {
		case "delete":			
			var checked = '.$containerObj.'.layout.A.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/subscriptioncontest/edit_persons','LABEL_ALERT_REMOVE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_person',array('id'=>$id)).'",
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
				alert("'.Yii::t('views/subscriptioncontest/edit_persons','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "winner":
			if (confirm("'.Yii::t('views/subscriptioncontest/edit_persons','LABEL_ALERT_WINNER').'")) {
				$.ajax({
					url: "'.CController::createUrl('winner_person').'",
					type: "POST",
					data: { "id":'.$id.' },
					beforeSend: function(){		
						obj.disableItem(id);
					},
					complete: function(){
						if (typeof obj.enableItem == "function") obj.enableItem(id);	
					},							
					success: function(data){													
						load_grid('.$containerObj.'.layout.A.grid.obj);
					}
				});	
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

'.$containerObj.'.layout.A.grid = new Object();
'.$containerObj.'.layout.A.grid.obj = '.$containerObj.'.layout.A.obj.attachGrid();
'.$containerObj.'.layout.A.grid.obj.setImagePath(dhx_globalImgPath);		
'.$containerObj.'.layout.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('views/subscriptioncontest/edit_persons','LABEL_LIST_GRID_NAME').','.Yii::t('views/subscriptioncontest/edit_persons','LABEL_LIST_GRID_EMAIL').','.Yii::t('views/subscriptioncontest/edit_persons','LABEL_LIST_GRID_TELEPHONE').','.Yii::t('views/subscriptioncontest/edit_persons','LABEL_LIST_GRID_ADDRESS').','.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').','.Yii::t('views/subscriptioncontest/edit_persons','LABEL_LIST_GRID_ZIP').','.Yii::t('views/subscriptioncontest/edit_persons','LABEL_LIST_GRID_CUSTOMER_SINCE').'",null,["text-align:center",,,,,,,,]);

	

'.$containerObj.'.layout.A.grid.obj.setInitWidthsP("5,15,20,8,20,8,8,8,8");
'.$containerObj.'.layout.A.grid.obj.setColAlign("center,left,left,left,left,left,left,left,left");
'.$containerObj.'.layout.A.grid.obj.setColSorting("na,na,na,na,na,na,na,na,na");
'.$containerObj.'.layout.A.grid.obj.enableResizing("false,false,false,false,false,false,false,false,false");
'.$containerObj.'.layout.A.grid.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.grid.obj.enableDragAndDrop(false);
'.$containerObj.'.layout.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
//'.$containerObj.'.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover);

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



'.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_person',array('id'=>$id)).'";
'.$containerObj.'.layout.A.grid.obj.loadXML('.$containerObj.'.layout.A.grid.obj.xmlOrigFileUrl);


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
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>