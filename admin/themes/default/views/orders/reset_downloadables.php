<?php 
// get list of product groups
//$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$app = Yii::app();

$help_hint_path = '/sales/orders/information/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
 		<strong><?php echo Yii::t('views/orders/reset_downloadables','LABEL_DOWNLOADABLE_VIDEOS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'downloadable-videos'); ?><br />
        <div id="<?php echo $container.'_downloadable_videos_grid'; ?>" style="width:100%; height:250px;"></div>       
    </div>    
    
    <div class="row">
 		<strong><?php echo Yii::t('views/orders/reset_downloadables','LABEL_DOWNLOADABLE_FILES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'downloadable-files'); ?><br />
        <div id="<?php echo $container.'_downloadable_files_grid'; ?>" style="width:100%; height:250px;"></div>       
    </div>        
</div>
</div>
<?php
$script = '
$(function(){

});

'.$container.'.wins.grid = new Object();
'.$container.'.wins.grid.obj = new dhtmlXGridObject("'.$container.'_downloadable_videos_grid");
'.$container.'.wins.grid.obj.setImagePath(dhx_globalImgPath);	
'.$container.'.wins.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('views/orders/reset_downloadables','LABEL_NO_DAYS_EXPIRE').','.Yii::t('views/orders/reset_downloadables','LABEL_NO_DOWNLOADS').','.Yii::t('views/orders/reset_downloadables','LABEL_CURRENT_NO_DOWNLOADS').',",null,[,"text-align:center;","text-align:center;","text-align:center;","text-align:center;"]);

'.$container.'.wins.grid.obj.setInitWidthsP("40,15,15,15,15");
'.$container.'.wins.grid.obj.setColAlign("left,left,left,left,center");
'.$container.'.wins.grid.obj.setColSorting("na,na,na,na,na");
'.$container.'.wins.grid.obj.setSkin(dhx_skin);
'.$container.'.wins.grid.obj.enableDragAndDrop(false);
'.$container.'.wins.grid.obj.enableRowsHover(true,dhx_rowhover);
'.$container.'.wins.grid.obj.enableMultiline(true);

'.$container.'.wins.grid.obj.init();	

// we create a variable to store the default url used to get our grid data, so we can reuse it later
'.$container.'.wins.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_orders_downloadable_videos',array('id'=>$id)).'";

'.$containerJS.'.wins.grid.obj.attachEvent("onXLS", function(grid_obj){
	ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
}); 

'.$containerJS.'.wins.grid.obj.attachEvent("onXLE", function(grid_obj,count){
	ajaxOverlay(grid_obj.entBox.id,0);
});			

load_grid('.$container.'.wins.grid.obj);
';

echo Html::script($script); 
?>