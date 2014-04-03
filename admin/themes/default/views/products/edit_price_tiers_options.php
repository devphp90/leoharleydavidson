<?php 
$model_name = get_class($model);

// get list of product groups
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); 
echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product')); 

$help_hint_path = '/catalog/products/bulk-pricing/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">
<div style="padding:10px;">	
<span id="<?php echo $container; ?>_exist_errorMsg" class="error"></span>
    <div>
    <div style="float:left">
        <strong><?php echo Yii::t('views/products/edit_price_tiers','LABEL_CUSTOMER_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'customer-type'); ?><br />
        <div>
        <?php
		echo Html::generateCustomerTypeList($model_name.'[id_customer_type]', $model->id_customer_type, array( 'id'=>$container.'_id_customer_type','prompt'=>'All'));
        ?>
        <br /><span id="<?php echo $container; ?>_id_customer_type_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div style="float:left; margin-left:10px; width:100px">
        <strong><?php echo Yii::t('views/products/edit_price_tiers','LABEL_QYT_TO_BUY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'qty-to-buy'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'qty',array('size' => 5, 'id'=>$container.'_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_qty_errorMsg" class="error"></span>
        </div>   
	</div>        
	<div style="float:left; margin-left:10px;">
        <strong><?php echo Yii::t('views/products/edit_price_tiers','LABEL_PRICE_UNIT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price-per-unit'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'price',array('size' => 10, 'id'=>$container.'_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_price_errorMsg" class="error"></span>
        </div>   
	</div> 
    </div>       
</div>
</div>