<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_popup_id')); 
echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product')); 
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_popup_<?php echo $container;?>">	
<div style="padding:10px;"><span id="<?php echo $container?>_country_code_errorMsg" class="error"></span>
        <div>
        <strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong><br />
        <div>
        <?php
        echo Html::generateCountryList($model_name.'[country_code]', $model->country_code, '', array('onchange'=>'js: '.$container.'_get_province_list(this.value);','prompt'=>Yii::t('global','LABEL_PROMPT'), 'id'=>$container.'_country_code'));
        ?>
        </div>                
    </div> 
    <div>
        <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong><br />
        <div id="<?php echo $container?>_list_states">
        <?php
        echo Html::generateStateList($model_name.'[state_code]', $model->country_code, $model->state_code, '', array( 'prompt'=>Yii::t('global','LABEL_PROMPT'), 'id'=>$container.'_state_code','style'=>'min-width:80px;'));
        ?>
        </div>                
    </div>
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_SHIPPING_PRICE');?></strong><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'price',array('size'=>10, 'id'=>$container.'_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_price_errorMsg" class="error"></span>
        </div>                
	</div>    
        
</div>     
</div>
<?php
$script = '
function '.$container.'_get_province_list(current_id){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list').'",
		type: "POST",
		data: { "country":current_id,"container":"'.$container.'" },
		success: function(data) {				
			$("#'.$container.'_list_states").html("").append(data);	
		}
	});		
}
';

echo Html::script($script); 
?>