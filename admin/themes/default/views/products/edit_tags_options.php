<?php 
$model_name = get_class($model);

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product'));
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
	<div style="padding-top:10px; padding-left:10px;">
        <strong><?php echo Yii::t('global','LABEL_ENABLED');?></strong><br />
        <div>
         <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        <br /><span id="<?php echo $container; ?>_input_type_errorMsg" class="error"></span>
        </div>           
	</div>
</div>
