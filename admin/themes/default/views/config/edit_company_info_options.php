<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/company-info/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
    	<table border="0" cellpadding="2" cellspacing="2" width="100%">
        	<tr>
            	<td valign="top" width="10%">
                    <strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_COMPANY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'company'); ?>
				</td>
                <td valign="top">
                     <div>
					<?php
                    echo CHtml::activeTextField($model,'settings[company_company]',array('style' => 'width: 250px;', 'id'=>'settings[company_company]'));
                    ?>
                    <br /><span id="settings[company_company]_errorMsg" class="error"></span>
                    </div>                   
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
        		<strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_ADDRESS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'address'); ?>
                    
				</td>
                <td valign="top">
                      <div>
					<?php
                    echo CHtml::activeTextField($model,'settings[company_address]',array('style' => 'width: 250px;', 'id'=>'address'));
                    ?>
                    <br /><span id="settings[company_address]_errorMsg" class="error"></span>
                    </div>                    
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
                     <strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_CITY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'city'); ?>
             
				</td>
                <td valign="top">
                    <div>
        <?php
        echo CHtml::activeTextField($model,'settings[company_city]',array('style' => 'width: 250px;', 'id'=>'city'));
        ?>
        <br /><span id="settings[company_city]_errorMsg" class="error"></span>
        </div>                   
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
                    <strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'country'); ?>
         
				</td>
                <td valign="top">
                      <div>
        <?php
        echo Html::generateCountryList($model_name.'[settings][company_country_code]', $model->settings[company_country_code], '', array('onchange'=>'js: get_province_list(this.value);','prompt'=>'--', 'id'=>'settings[company_country_code]'));
        ?>
        <br /><span id="settings[company_country_code]_errorMsg" class="error"></span>
        </div>                 
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
                    <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'state'); ?>
         
				</td>
                <td valign="top">
                          <div id="list_states">
        <?php
        echo Html::generateStateList($model_name.'[settings][company_state_code]', $model->settings[company_country_code], $model->settings[company_state_code], '', array( 'style'=>'min-width:80px;','prompt'=>'--', 'id'=>'state_code'));
        ?>
        <br /><span id="settings[company_state_code]_errorMsg" class="error"></span>
        </div>              
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
                    <strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_ZIP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'zip'); ?>
        
				</td>
                <td valign="top">
                     <div>
        <?php
        echo CHtml::activeTextField($model,'settings[company_zip]',array('style' => 'width: 250px;', 'id'=>'zip'));
        ?>
        <br /><span id="settings[company_zip]_errorMsg" class="error"></span>
        </div>                  
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
                    <strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_TELEPHONE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'telephone'); ?>
        
				</td>
                <td valign="top">
                      <div style="float:left; margin-right:10px">
        <?php
        echo CHtml::activeTextField($model,'settings[company_telephone]',array('style' => 'width: 250px;', 'id'=>'settings[company_telephone]'));
        ?>
        <br /><span id="settings[company_telephone]_errorMsg" class="error"></span>
        </div>
        <div style="float:left; margin-right:10px">
         <strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_TELEPHONE_TOP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'telephone_top'); ?>
        </div>
        <div style="float:left;">
         <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_telephone]',$model->settings['display_telephone']?1:0,array('value'=>1,'id'=>'display_telephone_1')).'&nbsp;<label for="display_telephone_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_telephone]',!$model->settings['display_telephone']?1:0,array('value'=>0,'id'=>'display_telephone_0')).'&nbsp;<label for="display_telephone_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>';
                    ?>
        </div>  
        <div style="clear:both"></div>              
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
                    <strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_FAX');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'fax'); ?>
      
				</td>
                <td valign="top">
                         <div>
        <?php
        echo CHtml::activeTextField($model,'settings[company_fax]',array('style' => 'width: 250px;', 'id'=>'settings[company_fax]'));
        ?>
        <br /><span id="settings[company_fax]_errorMsg" class="error"></span>
        </div>                
                </td>
			</tr>
            <tr>
            	<td valign="top" width="10%">
                     <strong><?php echo Yii::t('views/config/edit_company_info_options','LABEL_EMAIL');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'email'); ?><br />
       
				</td>
                <td valign="top">
                        <div>
        <?php
        echo CHtml::activeTextField($model,'settings[company_email]',array('style' => 'width: 250px;', 'id'=>'settings[company_email]'));
        ?>
        <br /><span id="settings[company_email]_errorMsg" class="error"></span>
        </div>               
                </td>
			</tr>
            <tr>
            <td colspan="2">
            <hr />
            </td>
            </tr>    
			<tr>
            	<td colspan="2">
                	<div style="float:left"><strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_LATITUDE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'lat'); ?></div> <div style="float:left; margin-left:10px;">
					<?php
                    echo CHtml::activeTextField($model,'settings[store_locations_default_lat]',array('style' => 'width: 100px;','maxlength'=>$columns['lat'], 'id'=>'lat'));
                    ?>
                    <br /><span id="lat_errorMsg" class="error"></span>
				</div>   
                   <div style="float:left; margin-left:20px;"> <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_LONGITUDE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'lng'); ?></div>
                   <div style="float:left; margin-left:10px;">
                   <?php
                    echo CHtml::activeTextField($model,'settings[store_locations_default_lng]',array('style' => 'width: 100px;','maxlength'=>$columns['lng'], 'id'=>'lng'));
                    ?>
                    <br /><span id="lng_errorMsg" class="error"></span>
                   </div>
                   <div style="float:left; margin-left:30px;">
                   <input type="button" value="Geocode" id="geocode" />
                   </div>
                    
                    
                    
                </td>
            </tr>                                                                                           
		</table>                                                
    </div>     
</div>
</div>
<?php
$script = '
$(function(){
	$("#geocode").on("click",function(){
		var address = "";		
		if ($("#address").val()) address += $("#address").val()+" ";
		if ($("#city").val()) address += $("#city").val()+" ";
		if ($("#state_code").val()) address += $("#state_code").val()+" ";
		if ($("#zip").val()) address += $("#zip").val()+" ";		
			
		geocode(address, "lat", "lng");
	});	
});

function get_province_list(current_id){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list_company').'",
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