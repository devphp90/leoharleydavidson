<?php 
$model_name = get_class($model);
$script = '
';

//echo Html::script($script); 

$help_hint_path = '/catalog/products/options/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product'));
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">		
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_INPUT_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'input-type'); ?><br />
        <div>
        <?php
		echo CHtml::activeDropDownList($model,'input_type',array(0=>Yii::t('global','LABEL_DROP_DOWN_LIST'),1=>Yii::t('global','LABEL_RADIO_BUTTON'),3=>Yii::t('global','LABEL_CHECKBOX'),4=>Yii::t('global','LABEL_MULTI_SELECT'),5=>Yii::t('global','LABEL_TEXTFIELD'),6=>Yii::t('global','LABEL_TEXTAREA'),7=>Yii::t('global','LABEL_FILE'),8=>Yii::t('global','LABEL_DATE'),9=>Yii::t('global','LABEL_DATE_TIME'),10=>Yii::t('global','LABEL_TIME')),array( 'id'=>$container.'_input_type'));
        ?>
        <br /><span id="<?php echo $container; ?>_input_type_errorMsg" class="error"></span>
        </div>           
	</div>
        <div class="row" id="<?php echo $container;?>_more_options"></div>
</div>
</div>
<?php 
$script = '
var previous_input_change="Null";
$(function(){
	
	$("#'.$container.'_input_type").change(function(){
		input_change();
	});
	input_change();
});

function input_change(){
	if($("#'.$container.'_input_type").val()== 8 || $("#'.$container.'_input_type").val()== 9 || $("#'.$container.'_input_type").val()== 10){
		if(previous_input_change != 8 && previous_input_change != 9 && previous_input_change != 10){
			$("#'.$container.'_more_options").html("").append(\'<strong>'.Yii::t('views/options/edit_options_group','LABEL_FROM_TO').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'from-to').'<br /><div>'.CHtml::radioButton($model_name.'[from_to]',($model->from_to)?1:0,array('value'=>1,'id'=>$container.'_from_to_1')).'&nbsp;<label for="'.$container.'_from_to_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[from_to]',(!$model->from_to)?1:0,array('value'=>0,'id'=>$container.'_from_to_0')).'&nbsp;<label for="'.$container.'_from_to_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'.'</div>\');
		}
	}else if($("#'.$container.'_input_type").val()== 5 || $("#'.$container.'_input_type").val()== 6){
		if(previous_input_change != 5 && previous_input_change != 6){
			$("#'.$container.'_more_options").html("").append(\'<strong>'.Yii::t('views/options/edit_options_group','LABEL_MAXLENGTH').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'maxlength').'<br /><div>'.CHtml::activeTextField($model,'maxlength',array('style' => 'width: 100px;', 'id'=>$container.'_maxlength','onkeyup'=>'rewrite_number($(this).attr("id"));')).'</div>\');
		}
	}else if($("#'.$container.'_input_type").val()== 1 || $("#'.$container.'_input_type").val()== 3 || $("#'.$container.'_input_type").val()== 4){
		if(previous_input_change != 1 && previous_input_change != 3 && previous_input_change != 4){
			$("#'.$container.'_more_options").html("").append(\'<strong>'.Yii::t('global','LABEL_USER_DEFINED_QTY').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'user-defined-qty').'<br /><div>'.CHtml::radioButton($model_name.'[user_defined_qty]',$model->user_defined_qty?1:0,array('value'=>1,'id'=>$container.'_user_defined_qty_1','onclick'=>''.$container.'_change_max_qty()')).'&nbsp;<label for="'.$container.'_user_defined_qty_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[user_defined_qty]',!$model->user_defined_qty?1:0,array('value'=>0,'id'=>$container.'_user_defined_qty_0','onclick'=>''.$container.'_change_max_qty()')).'&nbsp;<label for="'.$container.'_user_defined_qty_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'.'</div><div id="'.$container.'_display_max_qty" style="display:'.($model->user_defined_qty ? 'block':'none').'"><strong>'.Yii::t('global','LABEL_MAX_QTY_IN_CART').'</strong><br /><div>'.CHtml::activeTextField($model,'max_qty',array('size'=>5, 'id'=>$container.'_max_qty','onkeyup'=>'rewrite_number($(this).attr("id"));')).'</div></div>\');
		}
	}else{
		$("#'.$container.'_more_options").html("").append("");
	}
	previous_input_change=$("#'.$container.'_input_type").val();
		
}

function '.$container.'_change_max_qty(){
	if($("#'.$container.'_user_defined_qty_1").is(":checked")){
		$("#'.$container.'_display_max_qty").show();
	}else{
		$("#'.$container.'_display_max_qty").hide();
	}
}


';

echo Html::script($script); 
?>