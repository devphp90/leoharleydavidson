<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/users/user-info/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
     <div style="padding-bottom:5px;">
        <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
        ?>
        </div>        
    </div>
     
     
    <div>
        <strong><?php echo Yii::t('views/users/edit_login_info_options','LABEL_USERNAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'username'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'username',array('style' => 'width: 300px;','maxlength'=>30, 'id'=>$container.'_username'));
        ?>
        <br /><span id="<?php echo $container; ?>_username_errorMsg" class="error"></span>
        </div>                
	</div>        
    <div>
        <strong><?php echo(!empty($model->username)?Yii::t('views/users/edit_login_info_options','LABEL_CHANGE_PASSWORD'):Yii::t('views/users/edit_login_info_options','LABEL_PASSWORD'));?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'password'); ?><br />
        <div>
        <?php
        echo CHtml::activePasswordField($model,'password',array('style' => 'width: 300px;', 'id'=>$container.'_password'));
        ?>
        <br /><span id="<?php echo $container; ?>_password_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div>
        <strong><?php echo Yii::t('views/users/edit_login_info_options','LABEL_CONFIRM_PASSWORD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'confirm-password'); ?><br />
        <div>
        <?php
        echo CHtml::activePasswordField($model,'confirm_password',array('style' => 'width: 300px;', 'id'=>$container.'_confirm_password'));
        ?><br /><span id="<?php echo $container; ?>_password_errorMsg" class="error"></span>
        </div>                
	</div>  
    
    
    
                        
</div>
</div>