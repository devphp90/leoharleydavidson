<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
$columns = Html::getColumnsMaxLength(Tbl_Banner::tableName());	
echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));
$app = Yii::app();

$include_path = $app->params['includes_js_path'];

$help_hint_path = '/settings/general/banners/';	
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
<h1><?php echo Yii::t('global','LABEL_TITLE_PARAMETERS');?></h1>
    <div class="row">
    	
        <div style="float:left;">
            <strong><?php echo Yii::t('global','LABEL_ENABLED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[active]',($model->active or empty($model->id))?1:0,array('value'=>1,'id'=>'active_1')).'&nbsp;<label for="active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',(!$model->active and !empty($model->id))?1:0,array('value'=>0,'id'=>'active_0')).'&nbsp;<label for="active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
            ?>
            </div>       
        </div>    
        <div style="clear:both;"></div>
        <span id="sku_errorMsg" class="error"></span>
    </div>
     <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('size'=>30, 'id'=>'name'));
        ?>
        <br /><span id="name_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_START_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'display_start_date',array('size'=>20, 'id'=>'display_start_date'));
        ?>
        <br /><span id="display_start_date_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_END_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'display_end_date',array('size'=>20, 'id'=>'display_end_date'));
        ?>
        <br /><span id="display_start_date_errorMsg" class="error"></span>
        </div>                
	</div> 
</div>
</div>
<?php 
$script = '
$(function(){
		
	$("#display_start_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	$("#display_end_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	
});
';

echo Html::script($script); 
?>