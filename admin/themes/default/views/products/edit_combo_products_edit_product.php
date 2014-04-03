<?php 
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$help_hint_path = '/catalog/products/combo-products/';
?>
<form style="width:100%; height:100%; overflow:auto;" id="<?php echo $container.'_form'; ?>">	
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="padding:10px;">	 	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_QTY');?>:&nbsp;</strong><?php echo CHtml::activeTextField($model,'qty',array('size'=>5,'id'=>$container.'_qty')); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'qty'); ?><br /><span id="<?php echo $container; ?>_qty_errorMsg" class="error"></span>
	</div>     
</div>
</form>