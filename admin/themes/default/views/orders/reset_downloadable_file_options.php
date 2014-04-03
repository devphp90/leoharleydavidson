<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$help_hint_path = '/sales/orders/information/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('views/orders/reset_downloadable_file_options','LABEL_NO_DAYS_EXPIRE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'no-days-expire'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'no_days_expire',array('size'=>5, 'id'=>$container.'_no_days_expire','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_no_days_expire_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div class="row">
        <strong><?php echo Yii::t('views/orders/reset_downloadable_file_options','LABEL_NO_DOWNLOADS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'no-downloads'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'no_downloads',array('size'=>5, 'id'=>$container.'_no_downloads','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_no_downloads_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div class="row">
        <strong><?php echo Yii::t('views/orders/reset_downloadable_file_options','LABEL_CURRENT_NO_DOWNLOADS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'no-downloads'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'current_no_downloads',array('size'=>5, 'id'=>$container.'_no_downloads','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_current_no_downloads_errorMsg" class="error"></span>
        </div>                
	</div>            
</div>
</div>
<?php

$script = '
$(function(){

});

';

echo Html::script($script); 
?>