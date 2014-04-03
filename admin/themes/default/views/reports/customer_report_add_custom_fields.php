<?php 
$help_hint_path = '/statistics/reports/customer-report/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div>
    	<strong><?php echo Yii::t('views/reports/customer_report','LABEL_SELECT_CUSTOM_FIELD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-custom-field'); ?><br />
        <div>
		<?php
            echo CHtml::dropDownList('id_custom_fields','',CHtml::listData($custom_fields,'id','name'),array('id'=>'select_custom_field','prompt'=>'--'));        
        ?>  
        </div> 
        <div id="options"></div>
	</div>  
</div>
</div>
<?php
$script = '
var custom_fields=[];'."\r\n";
	
foreach ($custom_fields as $row) $script .= 'custom_fields['.$row['id'].']='.$row['type'].";\r\n";
	
$script .= '
$("#select_custom_field").on("change",function(){
	if ($(this).val().length) {
		get_options($(this).val(),custom_fields[$(this).val()]);	
	} else $("#options").html("");
});

function get_options(current_id, type){
	$.ajax({
		url: "'.Ccontroller::createUrl('get_custom_field_options').'",
		data: { id:current_id, type:type },
		success: function(data){
			$("#options").html("").append(data);
		}
	});
}
';

echo Html::script($script); 
?>