<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/custom-form-fields/';

echo CHtml::activeHiddenField($model,'id',array('id'=>'id_custom_fields_option')); 
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('views/config/add_custom_form_fields_option_options','LABEL_ADD_TEXTFIELD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'add_textfield'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[add_extra]',$model->add_extra?1:0,array('value'=>1,'id'=>$container.'_add_extra_1')).'&nbsp;<label for="'.$container.'_add_extra_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[add_extra]',!$model->add_extra?1:0,array('value'=>0,'id'=>$container.'_add_extra_0')).'&nbsp;<label for="'.$container.'_add_extra_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>                
    </div>       
    <div class="row">
        <strong><?php echo Yii::t('views/config/add_custom_form_fields_option_options','LABEL_REQUIRED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'required'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[extra_required]',$model->extra_required?1:0,array('value'=>1,'id'=>$container.'_extra_required_1')).'&nbsp;<label for="'.$container.'_extra_required_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[extra_required]',!$model->extra_required?1:0,array('value'=>0,'id'=>$container.'_extra_required_0')).'&nbsp;<label for="'.$container.'_extra_required_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>                
    </div> 
    <?php
	// radio
	if ($type == 5) {
	?>
    <div class="row">
        <strong><?php echo Yii::t('views/config/add_custom_form_fields_option_options','LABEL_SELECTED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'selected'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[selected]',$model->selected?1:0,array('value'=>1,'id'=>$container.'_selected_1')).'&nbsp;<label for="'.$container.'_selected_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[selected]',!$model->selected?1:0,array('value'=>0,'id'=>$container.'_selected_0')).'&nbsp;<label for="'.$container.'_selected_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>                
    </div>     
    <?php
	}
	?>   
</div>
</div>