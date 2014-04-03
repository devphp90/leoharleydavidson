<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product'));

$help_hint_path = '/catalog/products/variants/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_INPUT_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'input-type'); ?><br />
        <div>
        <?php
        echo CHtml::activeDropDownList($model,'input_type',array(0=>Yii::t('global','LABEL_DROP_DOWN_LIST'),1=>Yii::t('global','LABEL_RADIO_BUTTON'),2=>Yii::t('global','LABEL_SWATCH_PALETTE')),array( 'id'=>$container.'_input_type'));
        ?>
        <br /><span id="<?php echo $container; ?>_input_type_errorMsg" class="error"></span>
        </div>           
    </div>        
</div>
</div>