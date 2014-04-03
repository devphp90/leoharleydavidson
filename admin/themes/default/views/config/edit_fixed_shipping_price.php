<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));

$help_hint_path = '/settings/shipping/fixed-shipping-price/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
     <div class="row">
        <strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'price',array('size'=>10, 'id'=>'price'));
        ?>
        <br /><span id="price_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_MAX_CART_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'max-cart-price'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'max_cart_price',array('size'=>10, 'id'=>'max_cart_price'));
        ?>
        <br /><span id="max_cart_price_errorMsg" class="error"></span>
        </div>                
	</div>  
</div>
</div>