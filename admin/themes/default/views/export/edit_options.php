<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
$columns = Html::getColumnsMaxLength(Tbl_ExportTpl::tableName());	
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	
$help_hint_path = '/settings/import-export/export/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">
<div style="padding:10px; position:relative;">	
	<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('size'=>30, 'id'=>$container.'_name','maxlength'=>$columns['name']));
        ?>
		<br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>       
    </div>
    <div class="row">
        <strong><?php echo Yii::t('views/export/edit_options','LABEL_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'type'); ?><br />
        <div>
        <?php
			$options=array(
				// products
				0 => Yii::t('views/export/edit_options','LABEL_TYPE_0'),
			);
			
			echo CHtml::activeDropDownList($model,'type',$options,array('id'=>$container.'_select_type'));        
        ?>
        <br /><span id="<?php echo $container; ?>_type_errorMsg" class="error"></span>
        </div>                
    </div> 
</div>
</div>
<?php 
$script = '
$(function(){		

});
';

//echo Html::script($script); 
?>