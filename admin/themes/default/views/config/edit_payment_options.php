<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/payment/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
    	<table border="0" cellpadding="2" cellspacing="2" width="100%">
        	<tr>
		        <td valign="top" width="30%">
                    <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_ENABLE_PAYMENT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-payment'); ?>                    
            	</td>
            	<td valign="top">
				<?php 
                echo CHtml::radioButton($model_name.'[enable_payment]',$model->enable_payment?1:0,array('value'=>1,'id'=>'enable_payment_1')).'&nbsp;<label for="enable_payment_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[enable_payment]',!$model->enable_payment?1:0,array('value'=>0,'id'=>'enable_payment_0')).'&nbsp;<label for="enable_payment_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                ?>
                </td>
			</tr>                	
            <tr>
            	<td valign="top" colspan="2">
                	<em><?php echo Yii::t('views/config/edit_payment_options','LABEL_ENABLE_PAYMENT_DESCRIPTION');?></em>
                </td>
			</tr>                
        <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
          <td colspan="2"><hr /></td>
        </tr>
        <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
            <td valign="top">
                <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_PAYMENT_GATEWAY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'payment-gateway'); ?>
            </td>
            <td valign="top">
                <?php
                echo Html::generatePaymentGatewayList($model_name.'[payment_gateway][id]', $model->payment_gateway['id'], array('onchange'=>'payment_change(this.value)','prompt'=>'--', 'id'=>'name'));
                ?>
                <br /><span id="payment_gateway[id]_errorMsg" class="error"></span>
              
            </td>
        </tr>
        <tr class="enabled_disabled_payment enabled_disabled_payment_merchant_id" <?php echo !$model->enable_payment || $model->payment_gateway['id']==4 ? 'style="display:none;"':''; ?>>
            <td class="payment_display" valign="top" width="15%" <?php echo !$model->payment_gateway['id'] ? 'style="display:none;"':''; ?>>
                <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_MERCHANT_ID');?></strong>
            </td>
            <td class="payment_display" valign="top" <?php echo !$model->payment_gateway['id'] ? 'style="display:none;"':''; ?>>
                 <div>
                <?php
                echo CHtml::activeTextField($model,'payment_gateway[merchant_id]',array('style' => 'width: 250px;', 'id'=>'merchant_id'));
                ?>
                <br /><span id="payment_gateway[merchant_id]_errorMsg" class="error"></span>
                </div>                   
            </td>
        </tr>
        
        
        
        
        
        <tr class="enabled_disabled_payment display_user_id_pin" <?php echo (!$model->enable_payment || $model->payment_gateway['id']!=3 && $model->payment_gateway['id'] != 2 && $model->payment_gateway['id']!=4) ? 'style="display:none;"':''; ?>>
            <td class="payment_display" valign="top" width="15%" <?php echo !$model->payment_gateway['id'] ? 'style="display:none;"':''; ?>>
                <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_USER_ID');?></strong>
            </td>
            <td class="payment_display" valign="top" <?php echo !$model->payment_gateway['id'] ? 'style="display:none;"':''; ?>>
                 <div>
                <?php
                echo CHtml::activeTextField($model,'payment_gateway[user_id]',array('style' => 'width: 250px;', 'id'=>'user_id'));
                ?>
                <br /><span id="payment_gateway[user_id]_errorMsg" class="error"></span>
                </div>                   
            </td>
        </tr>
        <tr class="enabled_disabled_payment display_user_id_pin" <?php echo (!$model->enable_payment || $model->payment_gateway['id']!=3 && $model->payment_gateway['id'] != 2 && $model->payment_gateway['id']!=4) ? 'style="display:none;"':''; ?>>
            <td class="payment_display" valign="top" width="15%" <?php echo !$model->payment_gateway['id'] ? 'style="display:none;"':''; ?>>
                <strong id="label_pin_display"><?php echo ($model->payment_gateway['id']==3?Yii::t('views/config/edit_payment_options','LABEL_PIN'):($model->payment_gateway['id']==2 || $model->payment_gateway['id']==4?Yii::t('views/config/edit_payment_options','LABEL_PASSWORD'):''));?></strong>
            </td>
            <td class="payment_display" valign="top" <?php echo !$model->payment_gateway['id'] ? 'style="display:none;"':''; ?>>
                 <div>
                <?php
                echo CHtml::activeTextField($model,'payment_gateway[pin]',array('style' => 'width: 250px;', 'id'=>'pin'));
                ?>
                <br /><span id="payment_gateway[pin]_errorMsg" class="error"></span>
                </div>                   
            </td>
        </tr>
        
        <tr>
        	<td colspan="2" class="payment_display"><div id="payment_gateway_extra">            
            	<?php
				if (sizeof($rows = Tbl_PaymentGatewayExtra::model()->findAll('id_payment_gateway=:id_payment_gateway',array(':id_payment_gateway'=>$model->payment_gateway['id'])))) {
				?>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <?php
					foreach ($rows as $row) {
					?>
                    <tr>
                    	<td valign="top" width="30%"><strong><?php echo $row['name']; ?></strong></td>
                        <td valign="top">
							<?php
                            echo CHtml::activeTextField($model,'payment_gateway_extra['.$row['name'].']',array('style' => 'width: 250px;', 'id'=>'payment_gateway_extra_'.$row['name'],'value'=>$row['value']));
                            ?>                        
                            <br /><span id="payment_gateway_extra[<?php echo $row['name']; ?>]_errorMsg" class="error"></span>
                        </td>
                    </tr>
    	            <?php
					}					
				?>
                </table>
				<?php				
				}
				?>
                </div>
            </td>
        </tr>         
        <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
          <td class="payment_display" valign="top" colspan="2" <?php echo !$model->payment_gateway['id'] ? 'style="display:none;"':''; ?>>
              <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_WITCH_CARD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'accepted-credit-cards'); ?>
            
                 <div style="margin-top:10px;">
                <?php
               foreach (Tbl_ConfigCreditCard::model()->findAll() as $value) {
                   echo '<div style="float: left; margin-right:30px;">'.CHtml::activeCheckBox($model,'payment_gateway[credit_card]['.$value['id'].']',array('checked'=>$model->payment_gateway['credit_cards'][$value['id']]['active']?1:0,'style'=>'margin-right:10px;','id'=>'credit_card_'.$value['id'],'value'=>$value['id'])).'<label for="credit_card_'.$value['id'].'" style="margin:0:padding:0;display:inline;"><img src="'.Html::imageUrl($value['image']).'" height="25" /></label></div>';
               }
                ?><div style="clear:both"></div>
                 <br /><span id="payment_gateway[credit_card]_errorMsg" class="error"></span>
                </div>                   
            </td>
        </tr>                
  
            <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
                <td valign="top" colspan="2">
                    <hr />
                </td>
            </tr>
            <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
                <td valign="top"><div style="float:left"> <?php echo '<img src="'.Html::imageUrl('cc/pp.png').'" height="25" />';?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-paypal'); ?></div>
                </td>
                <td valign="top">
					<?php 
                    echo CHtml::radioButton($model_name.'[paypal][enable_paypal]',$model->paypal['enable_paypal']?1:0,array('value'=>1,'id'=>'enable_paypal_1')).'&nbsp;<label for="enable_paypal_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[paypal][enable_paypal]',!$model->paypal['enable_paypal']?1:0,array('value'=>0,'id'=>'enable_paypal_0')).'&nbsp;<label for="enable_paypal_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    <br /><span id="paypal[enable_paypal]_errorMsg" class="error"></span>
					</td>
                </tr>
            </tr>       
            <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>><td colspan="2"><em><?php echo Yii::t('views/config/edit_payment_options','LABEL_PAYPAL_DESCRIPTION');?></em></td></tr>
            <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
                <td class="paypal_display" valign="top" width="15%" <?php echo !$model->paypal['enable_paypal'] ? 'style="display:none;"':''; ?>>
                    <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_PAYPAL_API_USERNAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'api-username'); ?>
                </td>
                <td class="paypal_display" valign="top" <?php echo !$model->paypal['enable_paypal'] ? 'style="display:none;"':''; ?>>
                     <div>
                    <?php
                    echo CHtml::activeTextField($model,'paypal[paypal_api_username]',array('style' => 'width: 250px;', 'id'=>'paypal[paypal_api_username]'));
                    ?>
                    <br /><span id="paypal[paypal_api_username]_errorMsg" class="error"></span>
                    </div>                   
                </td>
            </tr>             
            <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
                <td class="paypal_display" valign="top" width="15%" <?php echo !$model->paypal['enable_paypal'] ? 'style="display:none;"':''; ?>>
                    <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_PAYPAL_API_PASSWORD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'api-password'); ?>
                </td>
                <td class="paypal_display" valign="top" <?php echo !$model->paypal['enable_paypal'] ? 'style="display:none;"':''; ?>>
                     <div>
                    <?php
                    echo CHtml::activeTextField($model,'paypal[paypal_api_password]',array('style' => 'width: 250px;', 'id'=>'paypal[paypal_api_password]'));
                    ?>
                    <br /><span id="paypal[paypal_api_password]_errorMsg" class="error"></span>
                    </div>                   
                </td>
            </tr> 
            <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
                <td class="paypal_display" valign="top" width="15%" <?php echo !$model->paypal['enable_paypal'] ? 'style="display:none;"':''; ?>>
                    <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_PAYPAL_API_SIGNATURE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'api-signature'); ?>
                </td>
                <td class="paypal_display" valign="top" <?php echo !$model->paypal['enable_paypal'] ? 'style="display:none;"':''; ?>>
                     <div>
                    <?php
                    echo CHtml::activeTextField($model,'paypal[paypal_api_signature]',array('style' => 'width: 250px;', 'id'=>'paypal[paypal_api_signature]'));
                    ?>
                    <br /><span id="paypal[paypal_api_signature]_errorMsg" class="error"></span>
                    </div>                   
                </td>
            </tr>                
        	<tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
	            <td colspan="2"><hr /></td>
			</tr>    
        	<tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
	            <td valign="top">
                	<strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_ENABLE_CASH_PAYMENTS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-cash-payments'); ?>
                </td>
                <td valign="top">
                <?php 
                echo CHtml::radioButton($model_name.'[enable_cash_payments]',$model->enable_cash_payments?1:0,array('value'=>1,'id'=>'enable_cash_payments_1')).'&nbsp;<label for="enable_cash_payments_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[enable_cash_payments]',!$model->enable_cash_payments?1:0,array('value'=>0,'id'=>'enable_cash_payments_0')).'&nbsp;<label for="enable_cash_payments_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                ?>	
                <br /><span id="enable_cash_payments_errorMsg" class="error"></span>
                </td>
			</tr>    
        	<tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
	            <td colspan="2"><hr /></td>
			</tr>    
        	<tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
	            <td valign="top">
                	<strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_ENABLE_CHECK_PAYMENTS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-check-payments'); ?>
                </td>
                <td valign="top">
                <?php 
                echo CHtml::radioButton($model_name.'[enable_check_payments]',$model->enable_check_payments?1:0,array('value'=>1,'id'=>'enable_check_payments_1')).'&nbsp;<label for="enable_check_payments_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[enable_check_payments]',!$model->enable_check_payments?1:0,array('value'=>0,'id'=>'enable_check_payments_0')).'&nbsp;<label for="enable_check_payments_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                ?>	
                <br /><span id="enable_check_payments_errorMsg" class="error"></span>
                </td>
			</tr>        
            <tr class="enabled_disabled_payment enabled_disabled_check_payments" <?php echo !$model->enable_payment || $model->enable_payment && !$model->enable_check_payments ? 'style="display:none;"':''; ?>>
            	<td valign="top" colspan="2">
                	<strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_CHECK_PAYMENT_DESCRIPTION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'check-payment-description'); ?>
                    <div style="margin-top:10px;">
                    <?php
                    echo CHtml::activeTextArea($model,'check_payment_description',array('style' => 'width: 400px;', 'id'=>'check_payment_description','rows'=>5));
                    ?>
                    <br /><span id="check_payment_description_errorMsg" class="error"></span>
                    </div>
                </td>
            </tr>       
        	<tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
	            <td colspan="2"><hr /></td>
			</tr>                           
		    <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_payment_options','LABEL_ENABLE_AUTO_COMPLETED_ORDER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-auto-completed-order'); ?>       				
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[enable_auto_completed_order]',$model->enable_auto_completed_order?1:0,array('value'=>1,'id'=>'enable_auto_completed_order_1')).'&nbsp;<label for="enable_auto_completed_order_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[enable_auto_completed_order]',!$model->enable_auto_completed_order?1:0,array('value'=>0,'id'=>'enable_auto_completed_order_0')).'&nbsp;<label for="enable_auto_completed_order_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>
            <tr class="enabled_disabled_payment" <?php echo !$model->enable_payment ? 'style="display:none;"':''; ?>>
            	<td valign="top" colspan="2">
                <em><?php echo Yii::t('views/config/edit_payment_options','LABEL_ENABLE_AUTO_COMPLETED_ORDER_DESCRIPTION');?></em>
                </td>
            </tr>
            </table>                                                   
    </div>     
</div>
</div>
<?php
$script = '
$(function(){
	/*if('.$model->payment_gateway['id'].'){
		payment_change('.$model->payment_gateway['id'].');
	}else{
		$(".payment_display").hide();
	}*/
	
	$("#enable_paypal_0").on("click",function(){ $(".paypal_display").hide(); });
	$("#enable_paypal_1").on("click",function(){ $(".paypal_display").show(); });
	
	$("#enable_payment_0").on("click",function(){ $(".enabled_disabled_payment").hide(); });
	$("#enable_payment_1").on("click",function(){ $(".enabled_disabled_payment").show();payment_change($("#name").val()); });
	
	$("#enable_check_payments_0").on("click",function(){ $(".enabled_disabled_check_payments").hide(); });
	$("#enable_check_payments_1").on("click",function(){ $(".enabled_disabled_check_payments").show(); });
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

function payment_change(current_id){
	if(current_id!="" && current_id!=0){
		$(".payment_display,.enabled_disabled_payment_merchant_id").show();

		if(current_id==3 || current_id==2 || current_id==4){
			if (current_id==4) $(".enabled_disabled_payment_merchant_id").hide();
			
			$(".display_user_id_pin").show();
			if(current_id==2 || current_id==4){
				$("#label_pin_display").html("").append("'.Yii::t('views/config/edit_payment_options','LABEL_PASSWORD').'");
			}else{
				$("#label_pin_display").html("").append("'.Yii::t('views/config/edit_payment_options','LABEL_PIN').'");
			}
			
		}else{
			$(".display_user_id_pin").hide();
		}
		
		$.ajax({
			url: "'.CController::createUrl('get_payment_gateway_extra').'",
			data: { id:current_id },
			success:function(data){
				$("#payment_gateway_extra").html("").append(data);
			}
		});
	}else{
		$(".payment_display").hide();
	}
}
';

echo Html::script($script); 
?>