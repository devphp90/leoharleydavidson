<?php 
$model_name = get_class($model);
$columns = Html::getColumnsMaxLength(Tbl_Tax::tableName());

$help_hint_path = '/settings/taxes/taxes/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding-top:10px; padding-left:10px;">	
	<div>
        <div class="row" style="float:left; padding-right:10px;">
            <strong><?php echo Yii::t('global','LABEL_CODE');?></strong><?php echo isset($columns['code']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_code_maxlength">'.($columns['code']-strlen($model->code)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'code'); ?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'code',array('size'=>10,'maxlength'=>$columns['code'], 'id'=>$container.'_code'));
            ?>          
            <br /><span id="<?php echo $container; ?>_code_errorMsg" class="error"></span>    
            </div>
        </div>        
        <div class="row" style="float:left; padding-right:10px;">
            <strong><?php echo Yii::t('global','LABEL_TAX_NUMBER');?></strong><?php echo isset($columns['tax_number']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tax_number_maxlength">'.($columns['tax_number']-strlen($model->tax_number)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'tax-number'); ?>
            <div>
            <?php echo CHtml::activeTextField($model,'tax_number',array('style'=>'width:250px;','maxlength'=>50,'id'=>$container.'_tax_number')); ?>         
            <br /><span id="<?php echo $container; ?>_tax_number_errorMsg" class="error"></span>       
            </div>                
        </div> 
        <div style="clear:both;"></div>
	</div>        
</div>
</div>