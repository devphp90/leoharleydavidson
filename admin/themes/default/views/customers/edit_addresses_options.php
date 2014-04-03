<?php 
$model_name = get_class($model);
$columns = Html::getColumnsMaxLength(Tbl_CustomerAddress::tableName());

$help_hint_path = '/customers/customers/addresses/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<?php echo CHtml::activeHiddenField($model,'id_customer',array('id'=>$container.'_id_customer')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	       
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_FIRST_NAME');?></strong><?php echo (isset($columns['firstname']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_firstname_maxlength">'.($columns['firstname']-strlen($model->firstname)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'first-name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'firstname',array('style' => 'width: 250px;','maxlength'=>$columns['firstname'], 'id'=>$container.'_firstname'));
        ?>
        <br /><span id="<?php echo $container; ?>_firstname_errorMsg" class="error"></span>
        </div>                
	</div>      
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_LAST_NAME');?></strong><?php echo (isset($columns['lastname']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_lastname_maxlength">'.($columns['lastname']-strlen($model->lastname)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'last-name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'lastname',array('style' => 'width: 250px;','maxlength'=>$columns['lastname'], 'id'=>$container.'_lastname'));
        ?>
        <br /><span id="<?php echo $container; ?>_lastname_errorMsg" class="error"></span>
        </div>                
	</div>     
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_COMPANY');?></strong><?php echo (isset($columns['company']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_company_maxlength">'.($columns['company']-strlen($model->company)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'company'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'company',array('style' => 'width: 250px;','maxlength'=>$columns['company'], 'id'=>$container.'_company'));
        ?>
        <br /><span id="<?php echo $container; ?>_company_errorMsg" class="error"></span>
        </div>                
	</div>         
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_ADDRESS');?></strong><?php echo (isset($columns['address']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_address_maxlength">'.($columns['address']-strlen($model->address)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'address'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'address',array('style' => 'width: 250px;','maxlength'=>$columns['address'], 'id'=>$container.'_address'));
        ?>
        <br /><span id="<?php echo $container; ?>_address_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_CITY');?></strong><?php echo (isset($columns['city']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_city_maxlength">'.($columns['city']-strlen($model->city)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'city'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'city',array('style' => 'width: 250px;','maxlength'=>$columns['city'], 'id'=>$container.'_city'));
        ?>
        <br /><span id="<?php echo $container; ?>_city_errorMsg" class="error"></span>
        </div>                
	</div>    
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'country'); ?><br />
        <div>
        <?php
        echo Html::generateCountryList($model_name.'[country_code]', $model->country_code, '', array('onchange'=>'js: get_province_list(this.value);','prompt'=>'--', 'id'=>$container.'_country_code'));
        ?>
        <br /><span id="<?php echo $container; ?>_country_code_errorMsg" class="error"></span>
        </div>                
    </div> 
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'province'); ?><br />
        <div id="<?php echo $container;?>_list_states">
        <?php
        echo Html::generateStateList($model_name.'[state_code]', $model->country_code, $model->state_code, '', array( 'style'=>'min-width:80px;','prompt'=>'--', 'id'=>$container.'_state_code'));
        ?>
        <br /><span id="<?php echo $container; ?>_state_code_errorMsg" class="error"></span>
        </div>                
    </div>     
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_ZIP');?></strong><?php echo (isset($columns['zip']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_zip_maxlength">'.($columns['zip']-strlen($model->zip)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'zip'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'zip',array('style' => 'width: 250px;','maxlength'=>$columns['zip'], 'id'=>$container.'_zip'));
        ?>
        <br /><span id="<?php echo $container; ?>_zip_errorMsg" class="error"></span>
        </div>                
	</div>  
    
    
    
    
    <hr />
    <div style="background-color:#EAEAEA; padding:5px;">   
    <div class="row">
        <div style="float:left"><strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_LATITUDE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'lat'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'lat',array('style' => 'width: 100px;','maxlength'=>$columns['lat'], 'id'=>$container.'_lat'));
        ?>
        <br /><span id="<?php echo $container; ?>_lat_errorMsg" class="error"></span>
        </div> 
        </div>
        <div style="float:left; margin-left:10px;">
             <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_LONGITUDE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'lng'); ?><br />
            <div>
            <?php
            echo CHtml::activeTextField($model,'lng',array('style' => 'width: 100px;','maxlength'=>$columns['lng'], 'id'=>$container.'_lng'));
            ?>
            <br /><span id="<?php echo $container; ?>_lng_errorMsg" class="error"></span>
            </div>                
        </div> 
        <div style="float:left; margin-left:20px; padding-top:17px;">
        <input type="button" value="Geocode" id="<?php echo $container; ?>_geocode" />
        </div>
        <div style="clear:both"></div>              
	</div>    
   	</div>
     <hr />
    
    
    
     
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_TELEPHONE');?></strong><?php echo (isset($columns['telephone']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_telephone_maxlength">'.($columns['telephone']-strlen($model->telephone)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'telephone'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'telephone',array('style' => 'width: 250px;','maxlength'=>$columns['telephone'], 'id'=>$container.'_telephone'));
        ?>
        <br /><span id="<?php echo $container; ?>_telephone_errorMsg" class="error"></span>
        </div>                
	</div>      
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_FAX');?></strong><?php echo (isset($columns['fax']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_fax_maxlength">'.($columns['fax']-strlen($model->fax)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'fax'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'fax',array('style' => 'width: 250px;','maxlength'=>$columns['fax'], 'id'=>$container.'_fax'));
        ?>
        <br /><span id="<?php echo $container; ?>_fax_errorMsg" class="error"></span>
        </div>                
	</div>
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_DEFAULT_BILLING_ADDRESS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'default-billing'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[default_billing]',($model->default_billing or !$model->id)?1:0,array('value'=>1,'id'=>$container.'_default_billing_1')).'&nbsp;<label for="'.$container.'_default_billing_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[default_billing]',(!$model->default_billing and $model->id)?1:0,array('value'=>0,'id'=>$container.'_default_billing_0')).'&nbsp;<label for="'.$container.'_default_billing_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div> 
    <div class="row">
        <strong><?php echo Yii::t('views/customers/edit_addresses_options','LABEL_DEFAUTL_SHIPPING_ADDRESS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'default-shipping'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[default_shipping]',($model->default_shipping or !$model->id)?1:0,array('value'=>1,'id'=>$container.'_default_shipping_1')).'&nbsp;<label for="'.$container.'_default_shipping_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[default_shipping]',(!$model->default_shipping and $model->id)?1:0,array('value'=>0,'id'=>$container.'_default_shipping_0')).'&nbsp;<label for="'.$container.'_default_shipping_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div>           
</div>
</div>
<?php
$script = '

$(function(){
	$("#'.$container.'_geocode").on("click",function(){
		var address = "";		
		if ($("#'.$container.'_address").val()) address += $("#'.$container.'_address").val()+" ";
		if ($("#'.$container.'_city").val()) address += $("#'.$container.'_city").val()+" ";
		if ($("#'.$container.'_state_code").val()) address += $("#'.$container.'_state_code").val()+" ";
		if ($("#'.$container.'_zip").val()) address += $("#'.$container.'_zip").val()+" ";		
			
		geocode(address, "'.$container.'_lat", "'.$container.'_lng");
	});	
});


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
}
';

echo Html::script($script); 
?>