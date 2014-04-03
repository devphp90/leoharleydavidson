<?php 
$model_name = get_class($model);
$columns = Html::getColumnsMaxLength(Tbl_Customer::tableName());

$help_hint_path = '/customers/customers/information/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
        ?>
        </div>        
    </div>    
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_CUSTOMER_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'customer-type'); ?><br />
        <div>
        <?php 			
        echo Html::generateCustomerTypeList($model_name.'[id_customer_type]', $model->id_customer_type, array('class'=>'buttonsize','prompt'=>'--', 'id'=>$container.'_id_customer_type'));
		?>
        <br /><span id="<?php echo $container; ?>_id_customer_type_errorMsg" class="error"></span>
        </div>
    </div>      
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_FIRST_NAME');?></strong><?php echo (isset($columns['firstname']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_firstname_maxlength">'.($columns['firstname']-strlen($model->firstname)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'first-name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'firstname',array('style' => 'width: 250px;','maxlength'=>$columns['firstname'], 'id'=>$container.'_firstname'));
        ?>
        <br /><span id="<?php echo $container; ?>_firstname_errorMsg" class="error"></span>
        </div>                
	</div>      
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_LAST_NAME');?></strong><?php echo (isset($columns['lastname']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_lastname_maxlength">'.($columns['lastname']-strlen($model->lastname)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'last-name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'lastname',array('style' => 'width: 250px;','maxlength'=>$columns['lastname'], 'id'=>$container.'_lastname'));
        ?>
        <br /><span id="<?php echo $container; ?>_lastname_errorMsg" class="error"></span>
        </div>                
	</div>     
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_EMAIL');?></strong><?php echo (isset($columns['email']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_email_maxlength">'.($columns['email']-strlen($model->email)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'email'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'email',array('style' => 'width: 250px;','maxlength'=>$columns['email'], 'id'=>$container.'_email'));
        ?>
        <br /><span id="<?php echo $container; ?>_email_errorMsg" class="error"></span>
        </div>                
	</div>     
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_DATE_BIRTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'dob'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'dob',array('size' => 10, 'id'=>$container.'_dob'));
        ?>
        <br /><span id="<?php echo $container; ?>_dob_errorMsg" class="error"></span>
        </div>                
	</div>   
    <div>
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_GENDER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'gender'); ?><br />
        <div>
         <?php 
        echo CHtml::radioButton($model_name.'[gender]',!$model->gender?1:0,array('value'=>0,'id'=>$container.'_gender_0')).'&nbsp;<label for="'.$container.'_gender_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_MALE').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[gender]',$model->gender?1:0,array('value'=>1,'id'=>$container.'_gender_1')).'&nbsp;<label for="'.$container.'_gender_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_FEMALE').'</label>'; 
        ?>
        </div>                
	</div> 
     <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_CUSTOMER_LANGUAGE_CORRESPONDENCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'language-of-correspondence'); ?><br />
        <div>
        <?php 			
        echo Html::generateLanguageList($model_name.'[language_code]', $model->language_code, array('class'=>'buttonsize','prompt'=>'--', 'id'=>$container.'_language_code'));
		?>
        <br /><span id="<?php echo $container; ?>_language_code_errorMsg" class="error"></span>
        </div>
    </div>         
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_TAX_VAT_NUMBER');?></strong><?php echo (isset($columns['tax_number']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tax_number_maxlength">'.($columns['tax_number']-strlen($model->tax_number)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'tax-vat-number'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'tax_number',array('style' => 'width: 250px;','maxlength'=>$columns['tax_number'], 'id'=>$container.'_tax_number'));
        ?>
        <br /><span id="<?php echo $container; ?>_tax_number_errorMsg" class="error"></span>
        </div>                
	</div>    
    <div class="row">
        <strong><?php echo(!empty($model->id)?Yii::t('views/customers/edit_info_options','LABEL_CHANGE_PASSWORD'):Yii::t('views/customers/edit_info_options','LABEL_PASSWORD'));?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'change-password'); ?><br />
        <div>
        <?php
        echo CHtml::activePasswordField($model,'password',array('style' => 'width: 250px;','maxlength'=>$columns['password'], 'id'=>$container.'_password'));
        ?>
        <br /><span id="<?php echo $container; ?>_password_errorMsg" class="error"></span>
        </div>                
    </div>          
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_info_options','LABEL_CONFIRM_PASSWORD');?></strong><?php echo (isset($columns['cpassword']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_cpassword_maxlength">'.($columns['cpassword']-strlen($model->cpassword)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'confirm-password'); ?><br />
        <div>
        <?php
        echo CHtml::activePasswordField($model,'cpassword',array('style' => 'width: 250px;','maxlength'=>$columns['password'], 'id'=>$container.'_cpassword'));
        ?>
        <br /><span id="<?php echo $container; ?>_cpassword_errorMsg" class="error"></span>
        </div>                
    </div>           
</div>
</div>
<?php
$script = '
$(function(){
	$("#'.$container.'_dob").datepicker({ dateFormat: "yy-mm-dd", changeYear: true, yearRange: "1930:'.date('Y').'" });	
});
';

echo Html::script($script); 
?>