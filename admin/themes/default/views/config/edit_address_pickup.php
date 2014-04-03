<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));  
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_popup_<?php echo $container;?>">	
	
    <div style="padding-left:10px; padding-top:10px;">
    	<span id="address_errorMsg" class="error"></span>
        <div><strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_ADDRESS');?></strong></div>
        <div>
        <?php
        echo CHtml::activeTextField($model,'address',array('size'=>30,'maxlength'=>255, 'id'=>'adresse'));
        ?>
        </div>                
    </div> 
    
    <div style="padding-left:10px;">
    	<span id="city_errorMsg" class="error"></span>
        <div><strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_CITY');?></strong></div>
        <div>
        <?php
        echo CHtml::activeTextField($model,'city',array('size'=>30,'maxlength'=>255, 'id'=>'city'));
        ?>
        </div>                
    </div> 

    <div style="padding-left:10px;">
    	<span id="_country_code_errorMsg" class="error"></span>
        <div><strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong></div>
        <div>
        <?php
        echo Html::generateCountryList($model_name.'[country_code]', $model->country_code, '', array('onchange'=>'js: get_province_list(this.value);', 'id'=>'_country_code'));
        ?>
        </div>                
    </div> 
    <div style="padding-left:10px;">
        <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong><br />
        <div id="list_states">
        <?php
        echo Html::generateStateList($model_name.'[state_code]', $model->country_code, $model->state_code, '', array( 'prompt'=>Yii::t('global','LABEL_PROMPT'), 'id'=>'state_code','style'=>'min-width:80px;'));
        ?>
        </div>                
    </div>  
    
    <div style="padding-left:10px;">
    	<span id="zip_errorMsg" class="error"></span>
        <div><strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_ZIP');?></strong></div>
        <div>
        <?php
        echo CHtml::activeTextField($model,'zip',array('size'=>10,'maxlength'=>10, 'id'=>'zip'));
        ?>
        </div>                
    </div>  
        
</div>     
</div>
<?php
$script = '
function get_province_list(current_id){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list_address_pickup').'",
		type: "POST",
		data: { "country":current_id },
		success: function(data) {					
			$("#list_states").html("").append(data);	
		}
	});		
}
';

echo Html::script($script); 
?>