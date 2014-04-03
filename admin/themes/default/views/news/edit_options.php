<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
$columns = Html::getColumnsMaxLength(Tbl_Pub::tableName());	
echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	
$help_hint_path = '/marketing/news/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
    <div class="row">

            <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[active]',($model->active)?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',(!$model->active or empty($model->id))?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
            ?>
            </div>       

    </div>
 
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'date_news',array('size'=>10, 'id'=>$container.'_date_news'));
        ?>
        <br /><span id="<?php echo $container; ?>_date_news_errorMsg" class="error"></span>
        </div>                
	</div>  
</div>
</div>
<?php 
$script = '
$(function(){
		
	$("#'.$container.'_date_news").datepicker({
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