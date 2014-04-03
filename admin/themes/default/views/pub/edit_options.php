<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
$columns = Html::getColumnsMaxLength(Tbl_Pub::tableName());	
echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	
$help_hint_path = '/marketing/pub/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
    <div class="row border_bottom">

            <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[active]',($model->active)?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',(!$model->active or empty($model->id))?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
            ?>
            </div>       

    </div>
     <div class="row border_bottom">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('size'=>30, 'id'=>$container.'_name'));
        ?>
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>                
	</div> 
    <?php /*<div class="row border_bottom">

            <strong><?php echo Yii::t('views/pub/edit_options','LABEL_WIDTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'width'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[width]',($model->width == 100 or !$model->width)?1:0,array('value'=>100,'id'=>$container.'_width_1')).'&nbsp;<label for="'.$container.'_width_1" style="display:inline; text-align: left;">100 px</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[width]',($model->width == 120)?1:0,array('value'=>120,'id'=>$container.'_width_2')).'&nbsp;<label for="'.$container.'_width_2" style="display:inline; text-align: left;">120 px</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[width]',($model->width == 160)?1:0,array('value'=>160,'id'=>$container.'_width_3')).'&nbsp;<label for="'.$container.'_width_3" style="display:inline; text-align: left;">160 px</label>'; 
            ?>
            </div>       

    </div>
     <div class="row border_bottom">

            <strong><?php echo Yii::t('views/pub/edit_options','LABEL_COLUMN');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-column'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[display_in_column]',(!$model->display_in_column or empty($model->id))?1:0,array('value'=>0,'id'=>$container.'_display_in_column_0')).'&nbsp;<label for="'.$container.'_display_in_column_0" style="display:inline; text-align: left;">'.Yii::t('views/pub/edit_options','LABEL_LEFT').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display_in_column]',($model->display_in_column)?1:0,array('value'=>1,'id'=>$container.'_display_in_column_1')).'&nbsp;<label for="'.$container.'_display_in_column_1" style="display:inline; text-align: left;">'.Yii::t('views/pub/edit_options','LABEL_RIGHT').'</label>'; 
            ?>
            </div>       

    </div>
    */?>
    <div class="row border_bottom">

            <strong><?php echo Yii::t('views/pub/edit_options','LABEL_DISPLAY_PAGE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-page'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[display_in_page]',(!$model->display_in_page or empty($model->id))?1:0,array('value'=>0,'id'=>$container.'_display_in_page_0')).'&nbsp;<label for="'.$container.'_display_in_page_0" style="display:inline; text-align: left;">'.Yii::t('views/pub/edit_options','LABEL_HOME_PAGE').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display_in_page]',($model->display_in_page)?1:0,array('value'=>1,'id'=>$container.'_display_in_page_1')).'&nbsp;<label for="'.$container.'_display_in_page_1" style="display:inline; text-align: left;">'.Yii::t('views/pub/edit_options','LABEL_EVERY_PAGE').'</label>'; 
            ?>
            </div>       

    </div>

    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_START_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'display_start_date',array('size'=>20, 'id'=>$container.'_display_start_date'));
        ?>
        <br /><span id="<?php echo $container; ?>_display_start_date_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/pub/edit_options','LABEL_END_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'display_end_date',array('size'=>20, 'id'=>$container.'_display_end_date'));
        ?>
        <br /><span id="<?php echo $container; ?>_display_start_date_errorMsg" class="error"></span>
        </div>                
	</div> 
</div>
</div>
<?php 
$script = '
$(function(){
		
	$("#'.$container.'_display_start_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	$("#'.$container.'_display_end_date").datetimepicker({
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