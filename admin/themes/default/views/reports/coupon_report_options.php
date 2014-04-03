<?php 
$help_hint_path = '/statistics/reports/coupon-report/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div>
    	<?php echo CHtml::htmlButton(Yii::t('global','LABEL_BTN_SELECT_REBATE_COUPON'),array('id'=>'id_button_select','class'=>'select_customer_button')); ?>        
        <div style="margin-top:10px;" id="coupon_code"></div>   
	</div>  
</div>
</div>
<?php
$script = '
wins_list = new Object();
$(function(){
	$("#id_button_select").click(function(){
			name = "'.Yii::t('global','LABEL_BTN_SELECT_REBATE_COUPON').'";

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
			wins_list.layout.A.grid.obj.setHeader("Type,'.Yii::t('global','LABEL_DISCOUNT_TYPE').','.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_CODE').'");
			wins_list.layout.A.grid.obj.attachHeader("#select_filter_custom_coupon,#select_filter_custom_coupon_type,#text_filter_custom,#text_filter_custom");
			
			// custom text filter input
			wins_list.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;

			// custom select filter input
			wins_list.layout.A.grid.obj._in_header_select_filter_custom_coupon=select_filter_custom_coupon;
			wins_list.layout.A.grid.obj._in_header_select_filter_custom_coupon_type=select_filter_custom_coupon_type;
			
			wins_list.layout.A.grid.obj.setInitWidthsP("25,25,25,25");
			wins_list.layout.A.grid.obj.enableResizing("false,false,false,false");
			wins_list.layout.A.grid.obj.setColAlign("left,left,left,left");
			wins_list.layout.A.grid.obj.setColSorting("na,na,na,na");
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
			wins_list.layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("SELECT")[0].name="type";
			wins_list.layout.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("SELECT")[0].name="discount_type";
			wins_list.layout.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="name";
			wins_list.layout.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="coupon_code";
			
			// we create a variable to store the default url used to get our grid data, so we can reuse it later
			wins_list.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_rebate_coupon_add').'";
			
			// load the initial grid
			load_grid(wins_list.layout.A.grid.obj);		
			
			wins_list.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
				ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
			}); 
			
			wins_list.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
				ajaxOverlay(grid_obj.entBox.id,0);
			});	
			
			wins_list.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){			
				$("#coupon_code").html("").append(this.cellById(rId,2).getValue());
				
				layout.B.load(rId);
				
				wins_list.obj.close();
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