<?php 
$help_hint_path = '/statistics/reports/customer-report/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div>
		<?php echo CHtml::htmlButton(Yii::t('views/reports/customer_report','LABEL_BTN_ADD_CUSTOM_FIELDS_FILTER'),array('id'=>'id_button_select','class'=>'select_customer_button')); ?>
        <div id="custom_fields">
	        <div id="clear" style="clear:both;"></div>
        </div>
	</div>  
</div>
</div>
<?php
$script = '
wins_list = new Object();
$(function(){
	$("#id_button_select").click(function(){
			name = "'.Yii::t('views/reports/customer_report','LABEL_BTN_ADD_CUSTOM_FIELDS_FILTER').'";

			wins_list.obj = dhxWins.createWindow("addCustomFieldsWindow", 10, 10, 500, 200);
			wins_list.obj.setText(name);
			wins_list.obj.button("park").hide();
			wins_list.obj.keepInViewport(true);
			wins_list.obj.setModal(true);
			//wins_list.obj.center();	
						
			
		
			wins_list.layout = new Object();
			wins_list.layout.obj = wins_list.obj.attachLayout("1C");
			wins_list.layout.A = new Object();
			wins_list.layout.A.obj = wins_list.layout.obj.cells("a");			
			wins_list.layout.A.obj.hideHeader();
			
			wins_list.toolbar = new Object();
			wins_list.toolbar.obj = wins_list.layout.A.obj.attachToolbar();
			wins_list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
			
			wins_list.toolbar.obj.addButton("save",null,"'.Yii::t('views/reports/customer_report','LABEL_APPLY_FILTER').'","toolbar/save.gif","toolbar/save_dis.gif");
			wins_list.toolbar.obj.addButton("save_close",null,"'.Yii::t('views/reports/customer_report','LABEL_APPLY_FILTER_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
			
			wins_list.toolbar.obj.attachEvent("onClick",function(id){
				switch (id) {
					case "save":	
						wins_list.toolbar.save(id);
						break;
					case "save_close":
						wins_list.toolbar.save(id,1);
						break;
				}
			});			
			
			wins_list.toolbar.save = function(id,close){				
				var custom_field_obj = $("#select_custom_field option:selected");
				var custom_field_option_obj = $("#select_custom_field_option option:selected");
				
				if (custom_field_obj.val()) {
					var custom_field_name = custom_field_obj.html();
					var custom_field_value = custom_field_obj.val();
					var custom_field_option = custom_field_option_obj.length ? custom_field_option_obj.val():"";
					var element;
					
					// check if already exists
					if (!$("#custom_fields_"+custom_field_value+"_"+custom_field_option).length) {					
						element = "<div><div class=\'report_filter_custom_fields\'>"+custom_field_name+" : "+custom_field_option_obj.html()+"<input type=\'hidden\' name=\'custom_fields["+custom_field_value+"][]\' value=\'"+custom_field_option+"\' id=\'custom_fields_"+custom_field_value+"_"+custom_field_option+"\' /></div><div class=\'report_filter_custom_fields_close\' onclick=\'javascript:$(this).parent().remove(); layout.B.load();\'><strong>X</strong></div></div>";
					
						$("#custom_fields").append(element);
					
						layout.B.load();
					}
					
					// Verify if popup window is close
					if(close) wins_list.obj.close();
				} else alert("'.Yii::t('views/reports/customer_report','LABEL_ALERT_SELECT_CUSTOM_FIELD').'");
			}					
						
			
			$.ajax({
				url: "'.CController::createUrl('customer_report_add_custom_fields').'",
				type: "POST",
				success: function(data){
					wins_list.layout.A.obj.attachHTMLString(data);		
				}
			});					

			
			// clean variables
			wins_list.obj.attachEvent("onClose",function(win){
				wins_list = new Object();
				return true;
			});			
	});
			
	
		
});
';

echo Html::script($script); 
?>