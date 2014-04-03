<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;

$help_hint_path = '/statistics/reports/customer-report/';

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id_scorm_certificate_condition'));
echo CHtml::activeHiddenField($model,'id_scorm_certificate_product'); 
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div>
    	<strong><?php echo Yii::t('views/reports/customer_report','LABEL_SELECT_CUSTOM_FIELD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-custom-field'); ?><br />
        <div>
		<select id="<?php echo $container;?>_id_custom_fields" name="ProductScormConditionForm[id_custom_fields]">
			<option value="">-</option>
            <option value="-1"<?php echo ($model->id_custom_fields==-1?' selected="selected"':'')?>><?php echo Yii::t('global','LABEL_SCORE_BETWEEN_X_Y');?></option>
        		<?php
				$sql = 'SELECT
				custom_fields.id,
				custom_fields_description.name,
				custom_fields.type
				FROM
				custom_fields
				INNER JOIN 
				custom_fields_description
				ON
				(custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code = :language_code) 
				WHERE
				custom_fields.form = 0 
				AND
				custom_fields.type IN (0, 1, 2, 5)
				ORDER BY 
				custom_fields.sort_order ASC';	
				
				$command=$connection->createCommand($sql);		
					
					
				foreach ($command->queryAll(true, array(':language_code'=>Yii::app()->language)) as $row) {
					echo '<option value="'.$row['id'].'"'.($model->id_custom_fields==$row['id']?' selected="selected"':'').'>'.$row['name'].'</option>';
				}
				?>
        </select><br /><span id="<?php echo $container;?>_id_custom_fields_errorMsg" class="error"></span>  
        </div> 
        <div id="<?php echo $container;?>_options"></div>
        <div id="<?php echo $container;?>_score_between" style="display:<?php echo($model->id_custom_fields==-1?'block':'none');?>">
            <div style="float:left"><?php echo '<strong>' . Yii::t('global','LABEL_FROM').'</strong>&nbsp;'.CHtml::activeTextField($model,'score_from',array('style' => 'width: 50px;'));?><br /><span id="<?php echo $container;?>_score_from_errorMsg" class="error"></span> </div>
            <div style="float:left; margin-left:20px"><?php echo '<strong>' . Yii::t('global','LABEL_TO').'</strong>&nbsp;'.CHtml::activeTextField($model,'score_to',array('style' => 'width: 50px;'));?><br /><span id="<?php echo $container;?>_id_score_to_errorMsg" class="error"></span> </div>
        </div>
	</div>  
</div>
</div>
<?php
$script .= '
$(function(){
	
	'.(($model->id_custom_fields && $model->id_custom_fields > 0)?$container.'_get_options('.$model->id_custom_fields.','.$model->id_custom_fields_option.')':'').'
	
	
});	

$("#'.$container.'_id_custom_fields").on("change",function(){
	$("#"+'.$container.'.wins_add_custom_field.layout.obj.cont.obj.id+" span.error").html("");
	$("#"+'.$container.'.wins_add_custom_field.layout.obj.cont.obj.id+" *").removeClass("error");
	$("#'.$container.'_score_between").hide();
	if ($(this).val().length && $(this).val()!=-1) {
		'.$container.'_get_options($(this).val());	
	} else {
		$("#'.$container.'_options").html("");
		if($(this).val()==-1){
			$("#'.$container.'_score_between").show();
		}
	}
});

function '.$container.'_get_options(id_custom_fields, current_id){
	
		$.ajax({
			url: "'.Ccontroller::createUrl('get_custom_field_options', array("container"=>$container)).'",
			type: "POST",
			data: { "id_custom_fields":id_custom_fields, "id":current_id },
			success: function(data){
				$("#'.$container.'_options").html("").append(data);
			}
		});
	
}
';

echo Html::script($script); 
?>