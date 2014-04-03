<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_popup_id')); 
echo CHtml::activeHiddenField($model,'id_options',array('id'=>$container.'_id_options'));  
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_popup_<?php echo $container;?>">	
<div style="padding:10px;"><span id="<?php echo $container?>_country_code_errorMsg" class="error"></span>
        <div>
        <strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong><br />
        <div>
        <?php
        echo Html::generateCountryList($model_name.'[country_code]', $model->country_code, '', array('onchange'=>'js: '.$container.'_get_province_list(this.value);', 'id'=>$container.'_country_code'));
        ?>
        </div>                
    </div> 
    <div>
        <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong><br />
        <div id="<?php echo $container?>_list_ship_only_states">
        <?php
        echo Html::generateStateList($model_name.'[state_code]', $model->country_code, $model->state_code, '', array( 'prompt'=>Yii::t('global','LABEL_PROMPT'), 'id'=>$container.'_state_code','style'=>'min-width:80px;'));
        ?>
        </div>                
    </div>   
        
</div>     
</div>
<?php
$script = '
function '.$container.'_get_province_list(current_id){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list_ship_only_region_options').'",
		type: "POST",
		data: { "country":current_id,"container":"'.$container.'" },
		success: function(data) {					
			$("#'.$container.'_list_ship_only_states").html("").append(data);	
		}
	});		
}
';

echo Html::script($script); 
?>