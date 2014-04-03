<?php 
$model_name = get_class($model);
$script = '
';

//echo Html::script($script); 
$help_hint_path = '/catalog/product-variants-templates/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));
echo CHtml::activeHiddenField($model,'id_tpl_product_variant_category',array('id'=>'id_tpl_product_variant_category'));
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">		
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_INPUT_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'input-type'); ?><br />
        <div>
        <?php
        echo CHtml::activeDropDownList($model,'input_type',array(0=>Yii::t('global','LABEL_DROP_DOWN_LIST'),1=>Yii::t('global','LABEL_RADIO_BUTTON'),2=>Yii::t('global','LABEL_SWATCH_PALETTE')),array( 'id'=>$container.'_input_type'));
        ?>
        <br /><span id="input_type_errorMsg" class="error"></span>
        </div>           
	</div>
</div>
</div>