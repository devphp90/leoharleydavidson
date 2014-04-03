<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/taxes/tax-groups/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div>
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('style' => 'width: 250px;','maxlength'=>50, 'id'=>$container.'_name'));
        ?>
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>                
	</div>         
</div>
</div>