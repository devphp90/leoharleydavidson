<?php 
$model_name = get_class($model);
echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));
echo CHtml::activeHiddenField($model,'id_tpl_product_bundled_product_category',array('id'=>'id_tpl_product_bundled_product_category'));

$help_hint_path = '/catalog/bundled-products-groups-templates/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">
<div style="padding:10px;">		
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_INPUT_TYPE');?></strong><br />
        <div>
        <?php
		echo CHtml::activeDropDownList($model,'input_type',array(1=>Yii::t('global','LABEL_RADIO_BUTTON'),2=>Yii::t('global','LABEL_CHECKBOX')),array( 'id'=>'input_type'));
        ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'input-type'); ?>
        <br /><span id="input_type_errorMsg" class="error"></span>
        </div>           
	</div>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_REQUIRED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'required'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[required]',$model->required?1:0,array('value'=>1,'id'=>'required_1')).'&nbsp;<label for="required_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[required]',!$model->required?1:0,array('value'=>0,'id'=>'required_0')).'&nbsp;<label for="required_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div>    
</div>
</div>