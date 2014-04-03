<?php
$columns = Html::getColumnsMaxLength(Tbl_TplProductBundledProductCategory::tableName());

echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product'));

$help_hint_path = '/catalog/products/bundled-products/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' '.$columns['name'].')</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'template-name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('style' => 'width:99%;', 'maxlength'=>$columns['name'], 'id'=>$container.'_name'));
        ?>
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>   
    </div>    
</div>
</div>    