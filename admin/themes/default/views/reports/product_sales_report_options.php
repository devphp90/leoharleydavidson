<?php 
$help_hint_path = '/statistics/reports/product-sales-report/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row" style="float:left; margin-right:10px;">
        <strong><?php echo Yii::t('global','LABEL_START_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-date'); ?><br />
        <div>
        <input type="text" size="15" name="start_date" id="start_date" value="" class="datetimepicker" />
        <br /><span id="start_date_errorMsg" class="error"></span>
        </div>         
	</div>  
    <div class="row" style="float:left; margin-right:10px;">
        <strong><?php echo Yii::t('global','LABEL_END_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-date'); ?><br />
        <div>
        <input type="text" size="15" name="end_date" id="end_date" value="" class="datetimepicker" />
        <br /><span id="end_date_errorMsg" class="error"></span>
        </div>         
	</div>    
    <div class="row" style="float:left; margin-right:10px; padding-top:5px;">
    	<br />
    	<input type="button" value="<?php echo Yii::t('global','LABEL_BTN_SEARCH'); ?>" class="select_customer_button" id="btn-search" />
    </div>
    <div style="clear:both;"></div>  
</div>
</div>
<?php
$script = '
$(function(){
	$(".datetimepicker").datetimepicker({ dateFormat: "yy-mm-dd", changeYear: true });
	
	$("#btn-search").on("click",function(){
		layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_product_sales_report').'?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val();
		load_grid(layout.B.grid.obj);
	});
});
';

echo Html::script($script); 
?>