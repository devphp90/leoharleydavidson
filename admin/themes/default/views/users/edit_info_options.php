<?php 
$model_name = get_class($model);
$columns = Html::getColumnsMaxLength(Tbl_User::tableName());

$help_hint_path = '/settings/users/personal-info/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
	<?php
    if (sizeof($linked_store = Tbl_LinkedStore::model()->findAll())) {
		echo '<div class="row">
		<strong>'.Yii::t('views/users/edit_info_options','LABEL_STORE').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'store').'<br />
		';
		
		foreach ($linked_store as $store) {
			echo '<div style="margin-top:5px;">'.CHtml::activeCheckBox($model,'linked_store['.$store->id.']',array('id'=>$container.'_select_store_'.$store->id,'onclick'=>isset($model->linked_store[$store->id]) ? 'javascript:this.checked=true;':'')).'&nbsp;<label for="'.$container.'_select_store_'.$store->id.'" style="display:inline; font-weight: normal; font-size:14px;">'.$store->domain.'</label></div>';
		}
		
		echo '<hr /></div>';
	}
    ?>
   
    <div class="row">
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_GENDER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'gender'); ?><br />
        <div style="padding-top:3px;">
         <?php 
        echo CHtml::radioButton($model_name.'[gender]',$model->gender?1:0,array('value'=>1,'id'=>$container.'_gender_1')).'&nbsp;<label for="'.$container.'_gender_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_MALE').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[gender]',!$model->gender?1:0,array('value'=>0,'id'=>$container.'_gender_0')).'&nbsp;<label for="'.$container.'_gender_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_FEMALE').'</label>'; 
        ?>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_FIRST_NAME');?></strong><?php echo isset($columns['firstname']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_firstname_maxlength">'.($columns['firstname']-strlen($model->firstname)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'first-name'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'firstname',array('style' => 'width: 300px;', 'maxlength'=>$columns['firstname'], 'id'=>$container.'_firstname'));
        ?>
        <br /><span id="<?php echo $container; ?>_firstname_errorMsg" class="error"></span>
        </div>                
	</div>     
    <div class="row">
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_LAST_NAME');?></strong><?php echo isset($columns['lastname']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_lastname_maxlength">'.($columns['lastname']-strlen($model->lastname)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'last-name'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'lastname',array('style' => 'width: 300px;', 'maxlength'=>$columns['lastname'], 'id'=>$container.'_lastname'));
        ?>
        <br /><span id="<?php echo $container; ?>_lastname_errorMsg" class="error"></span>
        </div>                
	</div>        

    <div class="row">
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_ADDRESS');?></strong><?php echo isset($columns['address']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_address_maxlength">'.($columns['address']-strlen($model->address)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'address'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'address',array('style' => 'width: 300px;', 'maxlength'=>$columns['address'], 'id'=>$container.'_address'));
        ?>
        </div>                
	</div>  
    <div>
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_CITY');?></strong><?php echo isset($columns['city']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_city_maxlength">'.($columns['city']-strlen($model->city)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'city'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'city',array('style' => 'width: 300px;', 'maxlength'=>$columns['city'], 'id'=>$container.'_city'));
        ?>
        <br /><span id="<?php echo $container; ?>_city_errorMsg" class="error"></span>
        </div>                
	</div>  
     <div>
        <strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'country'); ?><br />
        <div>
        <?php
        echo Html::generateCountryList($model_name.'[country]', $model->country, '', array('style' => 'width: 300px;','onchange'=>'js: get_province_list(this.value,"' . $container . '_list_states");'));
        ?>
        </div>                
	</div> 
    <div>
        <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'state'); ?><br />
        <div id="<?php echo $container;?>_list_states">
        <?php
        echo Html::generateStateList($model_name.'[state]', $model->country, $model->state, Yii::app()->language, array( 'style'=>'width: 300px;'));
        ?>
        </div>                
	</div>  
     <div>
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_ZIP_POSTAL_CODE');?></strong><?php echo isset($columns['zip']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_zip_maxlength">'.($columns['zip']-strlen($model->zip)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'zip'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'zip',array('style' => 'width: 100px;', 'maxlength'=>$columns['zip'], 'id'=>$container.'_zip'));
        ?>
        </div>                
	</div>  
    <div>
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_HOME_PHONE');?></strong><?php echo isset($columns['phone_home']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_phone_home_maxlength">'.($columns['phone_home']-strlen($model->phone_home)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'home-phone'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'phone_home',array('style' => 'width: 100px;', 'maxlength'=>$columns['phone_home'], 'id'=>$container.'_phone_home'));
        ?>
        </div>                
	</div>  
    <div>
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_CELL_PHONE');?></strong><?php echo isset($columns['phone_cell']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_phone_cell_maxlength">'.($columns['phone_cell']-strlen($model->phone_cell)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'cell-phone'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'phone_cell',array('style' => 'width: 100px;', 'maxlength'=>$columns['phone_cell'], 'id'=>$container.'_phone_cell'));
        ?>
        </div>                
	</div>  
    <div>
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_EMAIL');?></strong><?php echo isset($columns['email']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_email_maxlength">'.($columns['email']-strlen($model->email)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'email'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'email',array('style' => 'width: 300px;', 'maxlength'=>$columns['email'], 'id'=>$container.'_email'));
        ?>
        <br /><span id="<?php echo $container; ?>_email_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div>
        <strong><?php echo Yii::t('views/users/edit_info_options','LABEL_LANGUAGE_CORRESPONDENCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'language-of-correspondence'); ?><br />
        <div>
        <?php
        echo Html::generateLanguageList($model_name.'[default_language_code]', $model->default_language_code, array('id'=>$container.'_default_language_code'));
        ?>
        </div>                
	</div>   
    
    
                        
</div>
</div>