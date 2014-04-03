<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/custom-form-fields/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <h3><?php echo Yii::t('views/config/edit_custom_form_fields_options','LABEL_TITLE');?></h3>
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_custom_form_fields_options','LABEL_FORM');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'form'); ?><br />
        <div>
        <?php
			$options=array(
				-1 => '--',
				// account creation
				0 => Yii::t('views/config/edit_custom_form_fields_options','LABEL_FORM_0'),
				// contact
				1 => Yii::t('views/config/edit_custom_form_fields_options','LABEL_FORM_1'),
			);
			
			echo CHtml::activeDropDownList($model,'form',$options,array('id'=>$container.'_select_form'));        
        ?>
        <br /><span id="<?php echo $container; ?>_form_errorMsg" class="error"></span>
        </div>                            
    </div>     
    
    <div style="width:100%; overflow:hidden;">
	    <div id="<?php echo $container.'_custom_fields_toolbar'; ?>" style="width:100%;"></div>
    	<div id="<?php echo $container.'_custom_fields_grid'; ?>" style="width:100%; height:400px;"></div>         
    </div>
</div>
</div>