<?php 
$help_hint_path = '/statistics/reports/scorm-participant-report/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    
        <?php echo CHtml::htmlButton(Yii::t('global','LABEL_BTN_SELECT_PARTICIPANT'),array('id'=>'id_button_select','class'=>'select_customer_button')); ?>        
        <div style="margin-top:10px;" id="customer_name"></div>
</div>
</div>

<?php
$script = '
wins_list = new Object();
$(function(){
	$("#id_button_select").click(function(){
			name = "'.Yii::t('global','LABEL_BTN_SELECT_CUSTORMER').'";

			wins_list.obj = dhxWins.createWindow("addCustomerWindow", 10, 10, 600, 440);
			wins_list.obj.setText(name);
			wins_list.obj.button("park").hide();
			wins_list.obj.keepInViewport(true);
			wins_list.obj.setModal(true);
			//wins_list.obj.center();	
						
			
		
			wins_list.layout = new Object();
			wins_list.layout.obj = wins_list.obj.attachLayout("1C");
			wins_list.layout.A = new Object();
			wins_list.layout.A.obj = wins_list.layout.obj.cells("a");
			
			wins_list.layout.A.obj.hideHeader();
			
			wins_list.layout.A.grid = new Object();
			
			wins_list.layout.A.grid.obj = wins_list.layout.A.obj.attachGrid();
			wins_list.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
			wins_list.layout.A.grid.obj.setHeader("'.Yii::t('views/giftcertificates/edit','LABEL_LIST_GRID_NAME').','.Yii::t('views/giftcertificates/edit','LABEL_LIST_GRID_EMAIL').','.Yii::t('views/giftcertificates/edit','LABEL_LIST_GRID_PHONE').'");
			wins_list.layout.A.grid.obj.attachHeader("#text_filter_custom,,");
			
			// custom text filter input
			wins_list.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
			
			wins_list.layout.A.grid.obj.setInitWidths("*,200,150");
			wins_list.layout.A.grid.obj.enableResizing("false,false,false");
			wins_list.layout.A.grid.obj.setColAlign("left,left,left");
			wins_list.layout.A.grid.obj.setColSorting("na,na,na");
			wins_list.layout.A.grid.obj.setSkin(dhx_skin);
			wins_list.layout.A.grid.obj.enableDragAndDrop(false);
			wins_list.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
			
			
			
			//Paging
			wins_list.layout.A.obj.attachStatusBar().setText("<div id=\'recinfoArea\'></div>");
			wins_list.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
			wins_list.layout.A.grid.obj.enablePaging(true, 100, 3, "recinfoArea");
			wins_list.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
			wins_list.layout.A.grid.obj.i18n.paging={
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
			wins_list.layout.A.grid.obj.init();
			
			
			// set filter input names
			wins_list.layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[0].name="customer_name";
			
			// we create a variable to store the default url used to get our grid data, so we can reuse it later
			wins_list.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('giftcertificates/xml_list_customer_add').'";
			
			// load the initial grid
			load_grid(wins_list.layout.A.grid.obj);		
			
			wins_list.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
				ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
			}); 
			
			wins_list.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
				ajaxOverlay(grid_obj.entBox.id,0);
			});	
			
			wins_list.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
				$("#customer_name").html("").append(\'<div><strong>\'+this.cellById(rId,0).getValue()+\'</strong></div><div>'.Yii::t('global','LABEL_CONTACT_US_E').'&nbsp;<a href="mailto:\'+this.cellById(rId,1).getValue()+\'">\'+this.cellById(rId,1).getValue()+\'</a></div><div>'.Yii::t('global','LABEL_CONTACT_US_T').'&nbsp;\'+this.cellById(rId,2).getValue()+\'</div>\');
				layout.B.load(rId);
				wins_list.obj.close();
				
				
				/*
				$.ajax({
					url: "'.CController::createUrl('giftcertificates/add_customer').'",
					type: "POST",
					data: { "id":rId },
					dataType: "json",
					complete: function(){
						wins_list.obj.close();
					},
					success: function(data){	
						if (data.errors) {
							alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");
						}else{
							$("#customer_name").html("").append(data.info);
							layout.B.load(data.id);
						}
					}
				});*/
			});
			
			// clean variables
			wins_list.obj.attachEvent("onClose",function(win){
				wins_list = new Object();
				return true;
			});			
	});
			
	
		
}); ';

echo Html::script($script); 
?>