<?php 
$model_name = get_class($model);

?>
<form id="add_product" style="width:100%; height:100%; overflow:auto;">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('views/products/add_product','LABEL_PRODUCT_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint('/catalog/products/product-type'); ?>      
    </div>   
    <div class="row" style="margin-bottom:10px;">
    <?php 
    echo CHtml::radioButton('product_type',!$model->product_type?1:0,array('value'=>0,'id'=>$container.'_product_type_0')).'&nbsp;<label for="'.$container.'_product_type_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PRODUCT_TYPE_PRODUCT').'</label>'; 
    ?>
    <br /><em><?php echo Yii::t('views/products/add_product','LABEL_PRODUCT_TYPE_PRODUCT_DESCRIPTION');?></em>
    </div>      
    <div class="row" style="margin-bottom:10px;">
    <?php 
    echo CHtml::radioButton('product_type',$model->product_type==1?1:0,array('value'=>1,'id'=>$container.'_product_type_1')).'&nbsp;<label for="'.$container.'_product_type_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PRODUCT_TYPE_COMBO').'</label>'; 
    ?>
    <br /><em><?php echo Yii::t('views/products/add_product','LABEL_PRODUCT_TYPE_COMBO_DESCRIPTION');?></em>
    </div>      
    <div class="row" style="margin-bottom:10px;">
    <?php 
    echo CHtml::radioButton('product_type',$model->product_type==2?1:0,array('value'=>2,'id'=>$container.'_product_type_2')).'&nbsp;<label for="'.$container.'_product_type_2" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PRODUCT_TYPE_BUNDLED').'</label>'; 
    ?>
    <br /><em><?php echo Yii::t('views/products/add_product','LABEL_PRODUCT_TYPE_BUNDLED_DESCRIPTION');?></em>
    </div>         
</div>
</form>