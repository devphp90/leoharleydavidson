<?php 
$model_name = get_class($model);
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding-top:10px; padding-left:10px;">	
    <div>
        <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>'active_1')).'&nbsp;<label for="active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>'active_0')).'&nbsp;<label for="active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
        ?>
        </div>        
    </div>    
</div>
</div>