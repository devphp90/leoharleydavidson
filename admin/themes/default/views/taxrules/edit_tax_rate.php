<?php 
$model_name = get_class($model);

// get list of product groups

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));  

$help_hint_path = '/settings/taxes/tax-rules/taxes/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	

        <div class="row" style="float:left; padding-right:5px;">
            <?php echo Yii::t('views/taxrules/edit_tax_rate','LABEL_PERCENT_RATE', array("{field}" => CHtml::activeTextField($model,'rate',array('size'=>5, 'maxlength'=>10, 'id'=>$container.'_rate','onkeyup'=>'rewrite_number($(this).attr("id"));'))));?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'rate'); ?>
            <div><span id="<?php echo $container; ?>_rate_errorMsg" class="error"></span>
            </div>                
        </div>

</div>     
</div>