<?php 
$model_name = get_class($model);
$model_name_shipping_gateway = get_class($model_shipping_gateway);

$help_hint_path = '/settings/general/shipping/';
?>

<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">
  <div style="padding:10px;">
    <div class="row">
      <table border="0" cellpadding="2" cellspacing="2" width="100%">
        <tr>
          <td valign="top" colspan="2"><div style="float:left"> <strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIPPING');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-shipping'); ?><br />
              <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIPPING_DESCRIPTION');?></em> </div>
            <div style="float:left; margin-left:50px;">
              <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_shipping]',$model->settings['enable_shipping']?1:0,array('value'=>1,'id'=>'enable_shipping_1')).'&nbsp;<label for="enable_shipping_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_shipping]',!$model->settings['enable_shipping']?1:0,array('value'=>0,'id'=>'enable_shipping_0')).'&nbsp;<label for="enable_shipping_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
            </div>
            <div style="clear:both"></div></td>
        </tr>
        <tr class="enabled_disabled_shipping">
          <td colspan="2"><hr /></td>
        </tr>
        <tr class="enabled_disabled_shipping">
          <td valign="top" colspan="2" nowrap="nowrap"><div style="float:left">
              <div style="width:350px">
                <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIP_ONLY_REGION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'ship-only-to'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIP_ONLY_REGION_DESCRIPTION');?></em></div>
                <div style="clear:both"></div>
                <div id="my_toolbar_here_ship_only"></div>
                <div id="ship_only_region" style="height:150px;"></div>

              </div>
            </div>
            <div style="float:left; margin-left:20px;">
              <div style="width:350px">
                <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'do-not-ship-to'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION_DESCRIPTION');?></em></div>
                <div style="clear:both"></div>
                <div id="my_toolbar_here_not_ship"></div>
                <div id="not_ship_region" style="height:150px;"></div>

              </div>
            </div></td>
        </tr>
        <tr class="enabled_disabled_shipping">
          <td colspan="2"><hr /></td>
        </tr>
        <tr class="enabled_disabled_shipping">
          <td colspan="2">
          
          
          
          
          
          <table border="0" cellpadding="2" cellspacing="2" class="enabled_disabled_shipping">
              <tr>
                <td colspan="2"><strong style="font-size:16px;"><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIPPING_GATEWAY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipping-gateway'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIPPING_GATEWAY_DESCRIPTION');?></em></td>
              </tr>
              <tr>
                <td colspan="2"><div>
                    <?php 
                    echo Html::generateShippingGatewayList($model_name.'[shipping_gateway_id]', $model_shipping_gateway->id, array('onchange'=>'','prompt'=>'--', 'id'=>'shipping_gateway_id')); 
                    ?>
                  </div></td>
              </tr>
              <tr id="display_access_key">
                <td style="width:200px"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ACCESS_KEY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'access-key'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ACCESS_KEY_DESCRIPTION');?></em></td>
                <td valign="top"><div>
                    <?php
					echo CHtml::activeTextField($model_shipping_gateway,'access_key',array('style' => 'width: 250px;', 'id'=>'shipping_gateway_access_key', 'name'=>$model_name . '[shipping_gateway_access_key]'));
					?>
                    <br />
                    <span id="shipping_gateway_access_key_errorMsg" class="error"></span> </div></td>
              </tr>
              <tr id="display_meter_number">
                <td style="width:200px"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_METER_NUMBER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'meter-number'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_METER_NUMBER_DESCRIPTION');?></em></td>
                <td valign="top"><div>
                    <?php
					echo CHtml::activeTextField($model_shipping_gateway,'meter_number',array('style' => 'width: 250px;', 'id'=>'shipping_gateway_meter_number', 'name'=>$model_name . '[shipping_gateway_meter_number]'));
					?>
                    <br />
                    <span id="shipping_gateway_meter_number_errorMsg" class="error"></span> </div></td>
              </tr>              
              <tr id="display_merchant_id">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_MERCHANT_ID');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'merchant-id'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_MERCHANT_ID_DESCRIPTION');?></em></td>
                <td valign="top"><div>
                    <?php
					echo CHtml::activeTextField($model_shipping_gateway,'merchant_id',array('style' => 'width: 250px;', 'id'=>'shipping_gateway_merchant_id', 'name'=>$model_name . '[shipping_gateway_merchant_id]'));
					?>
                    <br />
                    <span id="shipping_gateway_merchant_id_errorMsg" class="error"></span> </div></td>
              </tr>
              <tr id="display_merchant_password">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_MERCHANT_PASSWORD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'merchant-password'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_MERCHANT_PASSWORD_DESCRIPTION');?></em></td>
                <td valign="top"><div>
                    <?php
					echo CHtml::activeTextField($model_shipping_gateway,'merchant_password',array('style' => 'width: 250px;', 'id'=>'shipping_gateway_merchant_password', 'name'=>$model_name . '[shipping_gateway_merchant_password]'));
					?>
                    <br />
                    <span id="shipping_gateway_merchant_password_errorMsg" class="error"></span> </div></td>
              </tr>
              <tr id="display_weight_unit">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'weighing-unit'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT_DESCRIPTION');?></em></td>
                <td valign="top">
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][weighing_unit]',!$model->settings['weighing_unit']?1:0,array('value'=>0,'id'=>'weighing_unit_0')).'&nbsp;<label for="weighing_unit_0" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT_LB_IN').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][weighing_unit]',$model->settings['weighing_unit']?1:0,array('value'=>1,'id'=>'weighing_unit_1')).'&nbsp;<label for="weighing_unit_1" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT_KG_CM').'</label>'; 
                    ?>
                  </td>
              </tr>
<?php /*              <tr id="display_measurement_unit">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_MEASUREMENT_UNIT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'measurement-unit'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_MEASUREMENT_UNIT_DESCRIPTION');?></em></td>
                <td valign="top">
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][measurement_unit]',!$model->settings['measurement_unit']?1:0,array('value'=>0,'id'=>'measurement_unit_0')).'&nbsp;<label for="measurement_unit_0" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_shipping_options','LABEL_MEASUREMENT_UNIT_IN').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][measurement_unit]',$model->settings['measurement_unit']?1:0,array('value'=>1,'id'=>'measurement_unit_1')).'&nbsp;<label for="measurement_unit_1" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_shipping_options','LABEL_MEASUREMENT_UNIT_CM').'</label>'; 
                    ?>
                  </td>
              </tr>              */ ?>
              <tr class="display_merchant_shipping_sender">
          		<td colspan="2" style="padding-top:20px"><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SENDER_DESCRIPTION');?></td>
        		</tr>
              <tr class="display_merchant_shipping_sender">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SENDER_CITY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'ship-from-city'); ?></td>
                <td valign="top"><div>
                    <?php
        echo CHtml::activeTextField($model,'settings[shipping_sender_city]',array('style' => 'width: 250px;', 'id'=>'settings[shipping_sender_city]'));
        ?>
                    <br />
                    <span id="settings[shipping_sender_city]_errorMsg" class="error"></span> </div></td>
              </tr>
              <tr class="display_merchant_shipping_sender">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SENDER_COUNTRY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'ship-from-country'); ?></td>
                <td valign="top"><div>
                    <?php
        echo Html::generateCountryList($model_name.'[settings][shipping_sender_country_code]', $model->settings[shipping_sender_country_code], '', array('onchange'=>'js: get_province_list(this.value);','prompt'=>'--', 'id'=>'settings[shipping_sender_country_code]'));
        ?>
                    <br />
                    <span id="settings[shipping_sender_country_code]_errorMsg" class="error"></span> </div></td>
              </tr>
              <tr class="display_merchant_shipping_sender">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SENDER_STATE_PROVINCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'ship-from-state'); ?></td>
                <td valign="top"><div id="list_states_shipping_sender">
                    <?php
        echo Html::generateStateList($model_name.'[settings][shipping_sender_state_code]', $model->settings[company_country_code], $model->settings[shipping_sender_state_code], '', array( 'style'=>'min-width:80px;','prompt'=>'--', 'id'=>'settings[shipping_sender_state_code]'));
        ?>
                    <br />
                    <span id="settings[shipping_sender_state_code]_errorMsg" class="error"></span> </div></td>
              </tr>
              <tr class="display_merchant_shipping_sender">
                <td><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SENDER_ZIP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'ship-from-zip'); ?></td>
                <td valign="top"><div>
                    <?php
        echo CHtml::activeTextField($model,'settings[shipping_sender_zip]',array('style' => 'width: 250px;', 'id'=>'settings[shipping_sender_zip]'));
        ?>
                    <br />
                    <span id="settings[shipping_sender_zip]_errorMsg" class="error"></span> </div></td>
              </tr>
            </table>
            
            
            
            
            
            
            
            
            </td>
        </tr>
        <tr class="enabled_disabled_shipping">
          <td colspan="2"><hr /></td>
        </tr>
        <tr class="enabled_disabled_shipping">
          <td valign="top" nowrap="nowrap"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ENABLE_LOCAL_PICKUP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-local-pickup'); ?><br />
            <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ENABLE_LOCAL_PICKUP_DESCRIPTION');?></em></td>
          <td valign="top"><div>
              <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_local_pickup]',$model->settings['enable_local_pickup']?1:0,array('value'=>1,'id'=>'enable_local_pickup_1')).'&nbsp;<label for="enable_local_pickup_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_local_pickup]',!$model->settings['enable_local_pickup']?1:0,array('value'=>0,'id'=>'enable_local_pickup_0')).'&nbsp;<label for="enable_local_pickup_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
            </div>
            
            
            
            
            
            
            
            
            
            
            
            </td>
        </tr>
        <tr class="enabled_disabled_shipping">
        	<td valign="top" colspan="2">

            
            <div style="width:580px;display:<?php echo $model->settings['enable_local_pickup']?'block':'none';?>" id="address_pickup">
                <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ADDRESS_PICKUP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'address-pickup'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ADDRESS_PICKUP_DESCRIPTION');?></em></div>
                <div style="clear:both"></div>
                <div id="my_toolbar_here_address_pickup"></div>
                <div id="address_pickup_list" style="height:150px;"></div>
              </div>    
            
            
            
            
            
            
            
            
         </td>
        </tr>
        
        
        
        
        
        <tr class="enabled_disabled_shipping">
          <td colspan="2"><hr /></td>
        </tr>
       
        <tr class="enabled_disabled_shipping">
        	<td valign="top" colspan="2">
              <div style="width:400px">
                <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_FIXED_SHIPPING_PRICE_BASED_ON_CART_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipping-price-cart'); ?><br />
                  <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_FIXED_SHIPPING_PRICE_BASED_ON_CART_PRICE_DESCRIPTION');?></em></div>
                <div style="clear:both"></div>
                <div id="my_toolbar_here_fixed_shipping_price"></div>
                <div id="fixed_shipping_price" style="height:150px;"></div>

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
	
	$("#enable_shipping_1").click(function(){
		$(".enabled_disabled_shipping").show();
		change_shipping_gateway();		
	});
	
	$("#enable_shipping_0").click(function(){
		$(".enabled_disabled_shipping").hide();		
	});
	

	
	$("#shipping_gateway_id").change(function(){
		change_shipping_gateway();		
	});
	
	
	
	if('.$model->settings['enable_shipping'].'){
		change_shipping_gateway();	
	}else{
		$(".enabled_disabled_shipping").hide();	
	}

	
	$("#enable_local_pickup_0").click(function(){
		$("#address_pickup").hide();
	});
	
	$("#enable_local_pickup_1").click(function(){
		$("#address_pickup").show();
	});	
	
	

});

function get_province_list(current_id){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list_shipping_sender').'",
		type: "POST",
		data: { "country":current_id },
		success: function(data) {					
			$("#list_states_shipping_sender").html("").append(data);	
		}
	});		
}


function change_shipping_gateway(){
	switch($("#shipping_gateway_id").val()){
		//UPS
		case "1":
			$("#display_access_key").show();
			$("#display_meter_number").hide();			
			$("#display_merchant_id").show();
			$("#display_merchant_password").show();
			$("#display_weight_unit").show();
			$("#display_measurement_unit").show();
			$(".display_merchant_shipping_sender").show();
			break;
		//Canada Post
		case "2":
			$("#display_access_key").hide();
			$("#display_meter_number").hide();			
			$("#display_merchant_id").show();
			$("#display_merchant_password").hide();
			$("#display_weight_unit").show();
			$("#display_measurement_unit").show();
			$(".display_merchant_shipping_sender").hide();
			break;
		// FedEx
		case "4":
			$("#display_access_key").show();
			$("#display_meter_number").show();			
			$("#display_merchant_id").show();
			$("#display_merchant_password").show();
			$("#display_weight_unit").show();
			$("#display_measurement_unit").show();
			$(".display_merchant_shipping_sender").show();		
			break;
		
		default:
			$("#display_access_key").hide();
			$("#display_meter_number").hide();
			$("#display_merchant_id").hide();
			$("#display_merchant_password").hide();
			$("#display_weight_unit").hide();
			$("#display_measurement_unit").hide();
			$(".display_merchant_shipping_sender").hide();	
			break;
	}
}


var dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(false);
dhxWins.attachViewportTo("shipping_layout");
dhxWins.setImagePath(dhx_globalImgPath);





//Ship Only
var grid_ship_only_regions = new Object();
grid_ship_only_regions.obj = new dhtmlXGridObject("ship_only_region");

grid_ship_only_regions.obj.selMultiRows = true;
grid_ship_only_regions.obj.setImagePath(dhx_globalImgPath);		
grid_ship_only_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);

grid_ship_only_regions.obj.setInitWidths("40,154,154");
grid_ship_only_regions.obj.setColAlign("center,left,left");
grid_ship_only_regions.obj.setColSorting("na,na,na");
grid_ship_only_regions.obj.enableResizing("false,true,true");
grid_ship_only_regions.obj.setSkin(dhx_skin);
grid_ship_only_regions.obj.enableDragAndDrop(false);
grid_ship_only_regions.obj.enableMultiselect(false);
grid_ship_only_regions.obj.enableAutoWidth(true,348,348);
grid_ship_only_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);


grid_ship_only_regions.obj.init();

grid_ship_only_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_ship_only_region',array()).'";
grid_ship_only_regions.obj.loadXML(grid_ship_only_regions.obj.xmlOrigFileUrl);

grid_ship_only_regions.toolbar = new Object();
grid_ship_only_regions.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_here_ship_only");
grid_ship_only_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
grid_ship_only_regions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
grid_ship_only_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");


grid_ship_only_regions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_TITLE_FREE_SHIPPING_REGIONS').'";

	switch (id) {
		case "add":
			
			grid_ship_only_regions.toolbar.add();
			break;
		case "delete":			
			var checked = grid_ship_only_regions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_ship_only_region').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid(grid_ship_only_regions.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
					
	}
});
grid_ship_only_regions.toolbar.add = function(current_id){
	var wins = new Object();
	if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+grid_ship_only_regions.obj.cellById(current_id,1).getValue()+" - "+grid_ship_only_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('views/config/edit_shipping_options','LABEL_ADD').'";} 
	
	wins.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 500, 200);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	//wins.obj.center();
				
	wins.toolbar = new Object();
	
	wins.toolbar.load = function(current_id){
		var obj = wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 

		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins.toolbar.save(id);
					break;
				case "save_close":
					wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_ship_only_region').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								wins.obj.close();
							},
							success: function(data){
								load_grid(grid_ship_only_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	wins.toolbar.obj = wins.obj.attachToolbar();
	wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins.toolbar.load(current_id);
	
	wins.grid_ship_only_regions = new Object();
	wins.grid_ship_only_regions.obj = wins.obj.attachLayout("1C");
	wins.grid_ship_only_regions.A = new Object();
	wins.grid_ship_only_regions.A.obj = wins.grid_ship_only_regions.obj.cells("a");
	wins.grid_ship_only_regions.A.obj.hideHeader();	
	
	$.ajax({
		url: "'.CController::createUrl('edit_ship_only_region',array()).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			wins.grid_ship_only_regions.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		wins = new Object();
		
		return true;
	});			
	
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_ship_only_region',array()).'",
			type: "POST",
			data: $("#"+wins.grid_ship_only_regions.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+wins.grid_ship_only_regions.obj.cont.obj.id+" span.error").html("");
				$("#"+wins.grid_ship_only_regions.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_popup_'.$containerObj.'").removeClass("error_background");
				if (data) {
					if (data.errors) {
						
						$.each(data.errors, function(key, value){
							var id_tag_container = "_"+key;
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							
							if (!$(id_tag_selector).hasClass("error")) { 
								$(id_tag_selector).addClass("error");
								
								if (value) {		
									value = String(value);
									var id_errormsg_container = id_tag_container+"_errorMsg";
									var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
									
									if (!$(id_errormsg_selector).hasClass("error")) { 
										$(id_errormsg_selector).addClass("error");
									}
									
									if ($(id_errormsg_selector).length) { 
										$(id_errormsg_selector).html(value); 
									}
								}						
							}
						});
						// Apply class error to the background of the main div
						$(".div_popup_'.$containerObj.'").addClass("error_background");																															
					} else {					
						load_grid(grid_ship_only_regions.obj);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var nom_edit = $("#_country_code option:selected").text();
							nom_edit = nom_edit + " - " + $("#_state_code option:selected").text();

							$("#id").val(data.id);
							wins.toolbar.load(data.id);
							wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
						}else{
							grid_ship_only_regions.obj.selectRowById(data.id);
						}
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

grid_ship_only_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	grid_ship_only_regions.toolbar.add(rId);
});


















//Do Not Ship
var grid_do_not_ship_regions = new Object();
grid_do_not_ship_regions.obj = new dhtmlXGridObject("not_ship_region");

grid_do_not_ship_regions.obj.selMultiRows = true;
grid_do_not_ship_regions.obj.setImagePath(dhx_globalImgPath);		
grid_do_not_ship_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);

grid_do_not_ship_regions.obj.setInitWidths("40,154,154");
grid_do_not_ship_regions.obj.setColAlign("center,left,left");
grid_do_not_ship_regions.obj.setColSorting("na,na,na");
grid_do_not_ship_regions.obj.enableResizing("false,true,true");
grid_do_not_ship_regions.obj.setSkin(dhx_skin);
grid_do_not_ship_regions.obj.enableDragAndDrop(false);
grid_do_not_ship_regions.obj.enableMultiselect(false);
grid_do_not_ship_regions.obj.enableAutoWidth(true,348,348);
grid_do_not_ship_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);


grid_do_not_ship_regions.obj.init();

grid_do_not_ship_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_do_not_ship_region',array()).'";
grid_do_not_ship_regions.obj.loadXML(grid_do_not_ship_regions.obj.xmlOrigFileUrl);

grid_do_not_ship_regions.toolbar = new Object();
grid_do_not_ship_regions.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_here_not_ship");
grid_do_not_ship_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
grid_do_not_ship_regions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
grid_do_not_ship_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");


grid_do_not_ship_regions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_TITLE_FREE_SHIPPING_REGIONS').'";

	switch (id) {
		case "add":
			
			grid_do_not_ship_regions.toolbar.add();
			break;
		case "delete":			
			var checked = grid_do_not_ship_regions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_do_not_ship_region').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid(grid_do_not_ship_regions.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
				
	}
});
grid_do_not_ship_regions.toolbar.add = function(current_id){
	var wins = new Object();
	if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+grid_do_not_ship_regions.obj.cellById(current_id,1).getValue()+" - "+grid_do_not_ship_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('views/config/edit_shipping_options','LABEL_ADD').'";} 
	
	wins.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 500, 200);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	//wins.obj.center();
				
	wins.toolbar = new Object();
	
	wins.toolbar.load = function(current_id){
		var obj = wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 

		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins.toolbar.save(id);
					break;
				case "save_close":
					wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_do_not_ship_region').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								wins.obj.close();
							},
							success: function(data){
								load_grid(grid_do_not_ship_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	wins.toolbar.obj = wins.obj.attachToolbar();
	wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins.toolbar.load(current_id);
	
	wins.grid_do_not_ship_regions = new Object();
	wins.grid_do_not_ship_regions.obj = wins.obj.attachLayout("1C");
	wins.grid_do_not_ship_regions.A = new Object();
	wins.grid_do_not_ship_regions.A.obj = wins.grid_do_not_ship_regions.obj.cells("a");
	wins.grid_do_not_ship_regions.A.obj.hideHeader();	
	
	$.ajax({
		url: "'.CController::createUrl('edit_do_not_ship_region',array()).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			wins.grid_do_not_ship_regions.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		wins = new Object();
		
		return true;
	});			
	
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_do_not_ship_region',array()).'",
			type: "POST",
			data: $("#"+wins.grid_do_not_ship_regions.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+wins.grid_do_not_ship_regions.obj.cont.obj.id+" span.error").html("");
				$("#"+wins.grid_do_not_ship_regions.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_popup_'.$containerObj.'").removeClass("error_background");
				if (data) {
					if (data.errors) {
						
						$.each(data.errors, function(key, value){
							var id_tag_container = "_"+key;
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							
							if (!$(id_tag_selector).hasClass("error")) { 
								$(id_tag_selector).addClass("error");
								
								if (value) {		
									value = String(value);
									var id_errormsg_container = id_tag_container+"_errorMsg";
									var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
									
									if (!$(id_errormsg_selector).hasClass("error")) { 
										$(id_errormsg_selector).addClass("error");
									}
									
									if ($(id_errormsg_selector).length) { 
										$(id_errormsg_selector).html(value); 
									}
								}						
							}
						});
						// Apply class error to the background of the main div
						$(".div_popup_'.$containerObj.'").addClass("error_background");																															
					} else {					
						load_grid(grid_do_not_ship_regions.obj);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var nom_edit = $("#_country_code option:selected").text();
							nom_edit = nom_edit + " - " + $("#_state_code option:selected").text();

							$("#id").val(data.id);
							wins.toolbar.load(data.id);
							wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
						}else{
							grid_do_not_ship_regions.obj.selectRowById(data.id);
						}
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

grid_do_not_ship_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	grid_do_not_ship_regions.toolbar.add(rId);
});





// fixed shippiung price
var fixed_shipping_price = new Object();
fixed_shipping_price.obj = new dhtmlXGridObject("fixed_shipping_price");

fixed_shipping_price.obj.selMultiRows = true;
fixed_shipping_price.obj.setImagePath(dhx_globalImgPath);		
fixed_shipping_price.obj.setHeader("#master_checkbox,'.Yii::t('views/config/edit_shipping_options','LABEL_PRICE').','.Yii::t('views/config/edit_shipping_options','LABEL_MAX_CART_PRICE').'",null,["text-align:center;","text-align:right;","text-align:right;"]);

fixed_shipping_price.obj.setInitWidths("40,180,180");
fixed_shipping_price.obj.setColAlign("center,right,right");
fixed_shipping_price.obj.setColSorting("na,na,na");
fixed_shipping_price.obj.enableResizing("false,true,true");
fixed_shipping_price.obj.setSkin(dhx_skin);
fixed_shipping_price.obj.enableDragAndDrop(false);
fixed_shipping_price.obj.enableMultiselect(false);
fixed_shipping_price.obj.enableAutoWidth(true,400,400);
fixed_shipping_price.obj.enableRowsHover(true,dhx_rowhover_pointer);



fixed_shipping_price.obj.init();

fixed_shipping_price.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_fixed_shipping_price',array()).'";
fixed_shipping_price.obj.loadXML(fixed_shipping_price.obj.xmlOrigFileUrl);

fixed_shipping_price.toolbar = new Object();
fixed_shipping_price.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_here_fixed_shipping_price");
fixed_shipping_price.toolbar.obj.setIconsPath(dhx_globalImgPath);	
fixed_shipping_price.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
fixed_shipping_price.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");


fixed_shipping_price.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_FIXED_SHIPPING_PRICE_BASED_ON_CART_PRICE').'";

	switch (id) {
		case "add":
			
			fixed_shipping_price.toolbar.add();
			break;
		case "delete":			
			var checked = fixed_shipping_price.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE_FIXED_SHIPPING_PRICE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_fixed_shipping_price').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid(fixed_shipping_price.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED_FIXED_SHIPPING_PRICE').'");	
			}
			break;
					
	}
});
fixed_shipping_price.toolbar.add = function(current_id){
	var wins = new Object();
	name = "'.Yii::t('views/config/edit_shipping_options','LABEL_FIXED_SHIPPING_PRICE_BASED_ON_CART_PRICE').'"; 
	
	wins.obj = dhxWins.createWindow("editFixedShippingPriceWindow", 10, 10, 500, 200);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	//wins.obj.center();
				
	wins.toolbar = new Object();
	
	wins.toolbar.load = function(current_id){
		var obj = wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 

		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins.toolbar.save(id);
					break;
				case "save_close":
					wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE_FIXED_SHIPPING_PRICE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_fixed_shipping_price').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								wins.obj.close();
							},
							success: function(data){
								load_grid(fixed_shipping_price.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	wins.toolbar.obj = wins.obj.attachToolbar();
	wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins.toolbar.load(current_id);
	
	wins.fixed_shipping_price = new Object();
	wins.fixed_shipping_price.obj = wins.obj.attachLayout("1C");
	wins.fixed_shipping_price.A = new Object();
	wins.fixed_shipping_price.A.obj = wins.fixed_shipping_price.obj.cells("a");
	wins.fixed_shipping_price.A.obj.hideHeader();	
	
	$.ajax({
		url: "'.CController::createUrl('edit_fixed_shipping_price',array()).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			wins.fixed_shipping_price.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		wins = new Object();
		
		return true;
	});			
	
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_fixed_shipping_price',array()).'",
			type: "POST",
			data: $("#"+wins.fixed_shipping_price.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+wins.fixed_shipping_price.obj.cont.obj.id+" span.error").html("");
				$("#"+wins.fixed_shipping_price.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_popup_'.$containerObj.'").removeClass("error_background");
				if (data) {
					if (data.errors) {
						
						$.each(data.errors, function(key, value){
							var id_tag_container = key;
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							
							if (!$(id_tag_selector).hasClass("error")) { 
								$(id_tag_selector).addClass("error");
								
								if (value) {		
									value = String(value);
									var id_errormsg_container = id_tag_container+"_errorMsg";
									var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
									
									if (!$(id_errormsg_selector).hasClass("error")) { 
										$(id_errormsg_selector).addClass("error");
									}
									
									if ($(id_errormsg_selector).length) { 
										$(id_errormsg_selector).html(value); 
									}
								}						
							}
						});
						// Apply class error to the background of the main div
						$(".div_popup_'.$containerObj.'").addClass("error_background");																															
					} else {					
						load_grid(fixed_shipping_price.obj);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							$("#id").val(data.id);
							wins.toolbar.load(data.id);
						}else{
							fixed_shipping_price.obj.selectRowById(data.id);
						}
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

fixed_shipping_price.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	fixed_shipping_price.toolbar.add(rId);
});




//Address Pickup
var grid_address_pickup = new Object();
grid_address_pickup.obj = new dhtmlXGridObject("address_pickup_list");

grid_address_pickup.obj.selMultiRows = true;
grid_address_pickup.obj.setImagePath(dhx_globalImgPath);		
grid_address_pickup.obj.setHeader("#master_checkbox,'.Yii::t('views/config/edit_company_info_options','LABEL_ADDRESS').','.Yii::t('views/config/edit_company_info_options','LABEL_CITY').','.Yii::t('views/config/edit_company_info_options','LABEL_COUNTRY').','.Yii::t('views/config/edit_company_info_options','LABEL_STATE_PROVINCE').','.Yii::t('views/config/edit_company_info_options','LABEL_ZIP').','.'",null,["text-align:center;"]);

grid_address_pickup.obj.setInitWidths("40,160,100,100,100,80");
grid_address_pickup.obj.setColAlign("center,left,left,left,left,left");
grid_address_pickup.obj.setColSorting("na,na,na,na,na,na");
grid_address_pickup.obj.enableResizing("false,true,true,true,true,true");
grid_address_pickup.obj.setSkin(dhx_skin);
grid_address_pickup.obj.enableDragAndDrop(false);
grid_address_pickup.obj.enableMultiselect(false);
grid_address_pickup.obj.enableAutoWidth(true,580,580);
grid_address_pickup.obj.enableRowsHover(true,dhx_rowhover_pointer);


grid_address_pickup.obj.init();

grid_address_pickup.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_address_pickup',array()).'";
grid_address_pickup.obj.loadXML(grid_address_pickup.obj.xmlOrigFileUrl);

grid_address_pickup.toolbar = new Object();
grid_address_pickup.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_here_address_pickup");
grid_address_pickup.toolbar.obj.setIconsPath(dhx_globalImgPath);	
grid_address_pickup.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
grid_address_pickup.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");


grid_address_pickup.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_TITLE_FREE_SHIPPING_REGIONS').'";

	switch (id) {
		case "add":
			
			grid_address_pickup.toolbar.add();
			break;
		case "delete":			
			var checked = grid_address_pickup.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE_ADDRESS_PICKUP').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_address_pickup').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid(grid_address_pickup.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED_ADDRESS_PICKUP').'");	
			}
			break;
			
	}
});
grid_address_pickup.toolbar.add = function(current_id){
	var wins = new Object();
	if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+grid_address_pickup.obj.cellById(current_id,1).getValue()+" - "+grid_address_pickup.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('views/config/edit_shipping_options','LABEL_ADD').'";} 
	
	wins.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 500, 400);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	//wins.obj.center();
				
	wins.toolbar = new Object();
	
	wins.toolbar.load = function(current_id){
		var obj = wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 

		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins.toolbar.save(id);
					break;
				case "save_close":
					wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE_ADDRESS_PICKUP').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_address_pickup').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								wins.obj.close();
							},
							success: function(data){
								load_grid(grid_address_pickup.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	wins.toolbar.obj = wins.obj.attachToolbar();
	wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins.toolbar.load(current_id);
	
	wins.grid_address_pickup = new Object();
	wins.grid_address_pickup.obj = wins.obj.attachLayout("1C");
	wins.grid_address_pickup.A = new Object();
	wins.grid_address_pickup.A.obj = wins.grid_address_pickup.obj.cells("a");
	wins.grid_address_pickup.A.obj.hideHeader();	
	
	$.ajax({
		url: "'.CController::createUrl('edit_address_pickup',array()).'",
		type: "POST",
		data: { "id":current_id },
		success: function(data){
			wins.grid_address_pickup.A.obj.attachHTMLString(data);		
		}
	});				
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		wins = new Object();
		
		return true;
	});			
	
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_address_pickup',array()).'",
			type: "POST",
			data: $("#"+wins.grid_address_pickup.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+wins.grid_address_pickup.obj.cont.obj.id+" span.error").html("");
				$("#"+wins.grid_address_pickup.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_popup_'.$containerObj.'").removeClass("error_background");
				if (data) {
					if (data.errors) {
						
						$.each(data.errors, function(key, value){
							var id_tag_container = key;
							var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							
							if (!$(id_tag_selector).hasClass("error")) { 
								$(id_tag_selector).addClass("error");
								
								if (value) {		
									value = String(value);
									var id_errormsg_container = id_tag_container+"_errorMsg";
									var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
									
									if (!$(id_errormsg_selector).hasClass("error")) { 
										$(id_errormsg_selector).addClass("error");
									}
									
									if ($(id_errormsg_selector).length) { 
										$(id_errormsg_selector).html(value); 
									}
								}						
							}
						});
						// Apply class error to the background of the main div
						$(".div_popup_'.$containerObj.'").addClass("error_background");																															
					} else {					
						load_grid(grid_address_pickup.obj);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var nom_edit = $("#_country_code option:selected").text();
							nom_edit = nom_edit + " - " + $("#_state_code option:selected").text();

							$("#id").val(data.id);
							wins.toolbar.load(data.id);
							wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
						}else{
							grid_address_pickup.obj.selectRowById(data.id);
						}
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

grid_address_pickup.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	grid_address_pickup.toolbar.add(rId);
});




';

echo Html::script($script); 
?>
