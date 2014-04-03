<?php 
$model_name = get_class($model);
$columns = Html::getColumnsMaxLength(Tbl_TaxRule::tableName());

$help_hint_path = '/settings/taxes/tax-rules/information/';
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
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_name_maxlength">'.($columns['name']-strlen($model->name)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('style' => 'width: 250px;','maxlength'=>$columns['name'], 'id'=>$container.'_name'));
        ?>
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>                
	</div>        
    <div>
        <strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'country'); ?><br />
        <div>
        <?php
        echo Html::generateCountryList($model_name.'[country_code]', $model->country_code, '', array('onchange'=>'js: get_province_list(this.value);','prompt'=>Yii::t('views/taxrules/edit_info_options','LABEL_ALL_COUNTRY_EXCEPT'), 'id'=>$container.'_country_code'));
        ?>
        <br /><span id="<?php echo $container; ?>_country_code_errorMsg" class="error"></span>
        </div>                
    </div> 
    <div>
        <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'state'); ?><br />
        <div id="<?php echo $container;?>_list_states">
        <?php
        echo Html::generateStateList($model_name.'[state_code]', $model->country_code, $model->state_code, '', array( 'prompt'=>Yii::t('views/taxrules/edit_info_options','LABEL_ALL_STATE_EXCEPT'), 'id'=>$container.'_state_code','style'=>'min-width:80px;'));
        ?>
        <br /><span id="<?php echo $container; ?>_state_code_errorMsg" class="error"></span>
        </div>                
    </div>     
    <div id="<?php echo $container;?>_zip_from_to_display" style="display:<?php echo($model->country_code == 'US' ? 'block' : 'none');?>">
        <div class="row" style="float:left;padding-right:5px;">
            <strong><?php echo Yii::t('views/taxrules/edit_info_options','LABEL_ZIP_FROM');?></strong><?php echo isset($columns['zip_from']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_zip_from_maxlength">'.($columns['zip_from']-strlen($model->zip_from)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'zip-from'); ?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'zip_from',array('size'=>10,'maxlength'=>$columns['zip_from'],'id'=>$container.'_zip_from'));
            ?>
            <br /><span id="<?php echo $container; ?>_zip_from_errorMsg" class="error"></span>
            </div>                
        </div>              
        <div class="row" style="float:left;padding-right:5px;">
            <strong><?php echo Yii::t('views/taxrules/edit_info_options','LABEL_ZIP_TO');?></strong><?php echo isset($columns['zip_to']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_zip_to_maxlength">'.($columns['zip_to']-strlen($model->zip_to)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'zip-to'); ?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'zip_to',array('size'=>10,'maxlength'=>$columns['zip_to'], 'id'=>$container.'_zip_to'));
            ?>
            <br /><span id="<?php echo $container; ?>_zip_to_errorMsg" class="error"></span>
            </div>                
        </div>                  
        <div style="clear:both;"></div>
	</div>        
</div>
</div>
<?php
$script = '
function get_province_list(current_id){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list').'",
		type: "POST",
		data: { "country":current_id },
		success: function(data) {					
			$("#'.$container.'_list_states").html("").append(data);	
		}
	});
	
	
	if(current_id == "US"){
		$("#'.$container.'_zip_from_to_display").show();
	}else{
		$("#'.$container.'_zip_from_to_display").hide();
	}
}
';

echo Html::script($script); 
?>