<?php 
$model_name = get_class($model);

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));

$help_hint_path = '/customers/customers/reviews/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
	<div style="padding-top:10px; padding-left:10px;">
        <div class="row">
            <strong><?php echo Yii::t('global','LABEL_TITLE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'title'); ?><br />
            <div>
            <?php 
           echo CHtml::activeTextField($model,'title',array('style' => 'width: 250px;','maxlength'=>30, 'id'=>$container.'_title')); 
            ?>
            <br /><span id="<?php echo $container; ?>_title_errorMsg" class="error"></span>
            </div>           
        </div>
        <div class="row">
            <strong><?php echo Yii::t('views/products/edit_reviews','LABEL_REVIEW');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'review'); ?><br />
            <div>
            <?php 
            echo CHtml::activeTextArea($model,'review',array('id'=>$container.'_review','style'=>'width:500px;height:125px;'));
            ?>
            <br /><span id="<?php echo $container; ?>_title_errorMsg" class="error"></span>
            </div>           
        </div>
        <div class="row">
            <strong><?php echo Yii::t('views/products/edit_reviews','LABEL_ANONYMOUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'anonymous'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[anonymous]',$model->anonymous?1:0,array('value'=>1,'id'=>$container.'_anonymous_1')).'&nbsp;<label for="'.$container.'_anonymous_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[anonymous]',!$model->anonymous?1:0,array('value'=>0,'id'=>$container.'_anonymous_0')).'&nbsp;<label for="'.$container.'_anonymous_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
            ?>
            <br /><span id="<?php echo $container; ?>_approved_errorMsg" class="error"></span>
            </div>           
        </div>
    </div>
</div>
