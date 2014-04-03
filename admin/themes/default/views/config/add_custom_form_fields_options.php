<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/custom-form-fields/';

echo CHtml::activeHiddenField($model,'id',array('id'=>'id_custom_fields')); 
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
	<h1><?php echo Yii::t('global','LABEL_TITLE_PARAMETERS');?></h1>
    <div class="row">
        <strong><?php echo Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'type'); ?><br />
        <div>
        <?php
			$options=array(
				-1 => '--',
				// Single Checkbox
				0 => Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE_0'),
				// Multiple Checkboxes
				1 => Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE_1'),
				// Dropdown
				2 => Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE_2'),
				// Radio
				5 => Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE_5'),				
				// Textfield
				3 => Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE_3'),
				// Textarea
				4 => Yii::t('views/config/add_custom_form_fields_options','LABEL_TYPE_4'),
			);
			
			echo CHtml::activeDropDownList($model,'type',$options,array('id'=>$container.'_select_type'));        
        ?>
        <br /><span id="<?php echo $container; ?>_type_errorMsg" class="error"></span>
        </div>
    </div>
    
    <div class="row">
        <strong><?php echo Yii::t('views/config/add_custom_form_fields_options','LABEL_REQUIRED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'required'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[required]',$model->required?1:0,array('value'=>1,'id'=>$container.'_required_1')).'&nbsp;<label for="'.$container.'_required_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[required]',!$model->required?1:0,array('value'=>0,'id'=>$container.'_required_0')).'&nbsp;<label for="'.$container.'_required_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>                
    </div>    
</div>
</div>