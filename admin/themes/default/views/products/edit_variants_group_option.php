<?php 
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	
$image_base_url = $app->params['product_images_base_url'].'swatch/';

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
echo CHtml::activeHiddenField($model,'id_product_variant_group',array('id'=>$container.'_id_product_variant_group'));

$help_hint_path = '/catalog/products/variants/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px; position:relative;">	
	<div id="<?php echo $container.'_colorpicker'; ?>" style="position: absolute; left:0; top:0;"></div>
    <div id="<?php echo $container.'_colorpicker2'; ?>" style="position: absolute; left:0; top:0;"></div>
    <div id="<?php echo $container.'_colorpicker3'; ?>" style="position: absolute; left:0; top:0;"></div>
	<?php 
	// swatch
	if ($input_type == 2) {
	?>
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_variants_group_option','LABEL_SWATCH_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'swatch-type'); ?><br />
        <div>
        <?php
        echo CHtml::activeDropDownList($model,'swatch_type',array(0=>Yii::t('views/products/edit_variants_group_option','LABEL_COLOR_ONE'),1=>Yii::t('views/products/edit_variants_group_option','LABEL_COLOR_TWO'),2=>Yii::t('views/products/edit_variants_group_option','LABEL_COLOR_THREE'), 3=>Yii::t('views/products/edit_variants_group_option','LABEL_FILE')),array( 'id'=>$container.'_swatch_type'));
        ?>
        <br /><span id="<?php echo $container; ?>_swatch_type_errorMsg" class="error"></span>
        </div>   
        <div id="<?php echo $container.'_swatch_type_color'; ?>" <?php echo ($model->swatch_type == 3) ? 'style="display:none;"':''; ?>>
            <div style="float:left; margin-right: 10px;"> 
                <strong><?php echo Yii::t('views/products/edit_variants_group_option','LABEL_COLOR');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'color'); ?><br />
                <div>            
                <?php
                echo CHtml::activeTextField($model,'color',array('size' => 10,'maxlength'=>7, 'id'=>$container.'_color', 'style'=>'float:left; margin-right:5px;','readonly'=>'readonly','onclick'=>'javascript:'.$container.'.colorpicker(\''.$container.'_color\');'));
                ?>
                <div style="height:20px; width:20px; background-color:<?php echo $model->color ? $model->color:'#FFF'; ?>; border:1px solid; float:left; cursor:pointer;" id="<?php echo $container.'_colorpick'; ?>" onclick="javascript:<?php echo $container.'.colorpicker(\''.$container.'_color\');'; ?>"></div>
                <div style="clear:both;"></div>
                <span id="<?php echo $container; ?>_color_errorMsg" class="error"></span>
                </div>         
            </div>
            
            <div style="float: left; margin-right: 10px; <?php echo ($model->swatch_type != 1 && $model	->swatch_type != 2) ? 'display:none;':''; ?>" id="<?php echo $container.'_two_color'; ?>"> 
                <strong><?php echo Yii::t('views/products/edit_variants_group_option','LABEL_COLOR_2');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'color-2'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'color2',array('size' => 10,'maxlength'=>7, 'id'=>$container.'_color2', 'style'=>'float:left; margin-right:5px;','readonly'=>'readonly','onclick'=>'javascript:'.$container.'.colorpicker(\''.$container.'_color2\');'));
                ?>
                <div style="height:20px; width:20px; background-color:<?php echo $model->color2 ? $model->color2:'#FFF'; ?>; border:1px solid; float:left; cursor:pointer;" id="<?php echo $container.'_color2pick'; ?>" onclick="javascript:<?php echo $container.'.colorpicker(\''.$container.'_color2\');'; ?>"></div>
                <div style="clear:both;"></div>            
                <span id="<?php echo $container; ?>_color2_errorMsg" class="error"></span>
                </div>         
            </div>
    
            <div style="float: left; margin-right: 10px; <?php echo ($model->swatch_type != 2) ? 'display:none;':''; ?>" id="<?php echo $container.'_three_color'; ?>"> 
                <strong><?php echo Yii::t('views/products/edit_variants_group_option','LABEL_COLOR_3');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'color-3'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'color3',array('size' => 10,'maxlength'=>7, 'id'=>$container.'_color3', 'style'=>'float:left; margin-right:5px;','readonly'=>'readonly','onclick'=>'javascript:'.$container.'.colorpicker(\''.$container.'_color3\');'));
                ?>
                <div style="height:20px; width:20px; background-color:<?php echo $model->color3 ? $model->color3:'#FFF'; ?>; border:1px solid; float:left; cursor:pointer;" id="<?php echo $container.'_color3pick'; ?>" onclick="javascript:<?php echo $container.'.colorpicker(\''.$container.'_color3\');'; ?>"></div>
                <div style="clear:both;"></div>               
                <span id="<?php echo $container; ?>_color3_errorMsg" class="error"></span>
                </div>         
            </div>  
		</div>                  
		<div id="<?php echo $container.'_swatch_type_file'; ?>" <?php echo ($model->swatch_type != 3) ? 'style="display:none;"':''; ?>>
        	<div style="height:20px; width:20px; border:1px solid; margin-bottom:10px; float:left; margin-right: 5px;" id="<?php echo $container.'_filename_img'; ?>"><?php echo $model->old_filename ? '<img src="'.$image_base_url.$model->old_filename.'" border="0" />':''; ?></div><span style="float:left; margin-top:8px;">(20x20)</span>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'file'); ?>
            <div style="clear:both;"></div>
            <div id="<?php echo $container; ?>_button"></div>
            <div id="<?php echo $container; ?>_queue" style="margin-top:15px; margin-bottom:5px;"></div>
            <?php
            echo CHtml::activeHiddenField($model,'filename',array('id'=>$container.'_filename'));
			echo CHtml::activeHiddenField($model,'old_filename',array('id'=>$container.'_old_filename'));
            ?>
            <span id="<?php echo $container; ?>_filename_errorMsg" class="error"></span>                        
        </div>
    </div>        
    <?php } ?>
    <div style="clear:both;"></div>
</div>
</div>
<?php 
$script = '';

if ($input_type == 2) {
	$script .= '
$(function(){
	$("#'.$container.'_swatch_type").change(function(){
		switch ($(this).val()) {
			case "0":
				$("#'.$container.'_swatch_type_file").hide();			
				$("#'.$container.'_swatch_type_color").show();
				$("#'.$container.'_two_color").hide();
				$("#'.$container.'_three_color").hide();			
				break;
			case "1":
				$("#'.$container.'_swatch_type_file").hide();			
				$("#'.$container.'_swatch_type_color").show();
				$("#'.$container.'_two_color").show();
				$("#'.$container.'_three_color").hide();						
				break;				
			case "2":								
				$("#'.$container.'_swatch_type_file").hide();			
				$("#'.$container.'_swatch_type_color").show();
				$("#'.$container.'_two_color").show();
				$("#'.$container.'_three_color").show();									
				break;	
			case "3":
				$("#'.$container.'_swatch_type_color").hide();
				$("#'.$container.'_swatch_type_file").show();
				break;
		}
	});	
	
	// bind upload file input
	$("#'.$container.'_button").uploadify({
		"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
		"uploader" : "'.CController::createUrl('upload_image_variant_group_option_swatch').'",
		"checkExisting" : false,
		"formData" : {"PHPSESSID":"'.session_id().'"},
		"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
		"buttonText" : "'.Yii::t('global','LABEL_IMAGE_BTN_FILES').'",
		"width" : 170,
		"fileTypeDesc" : "Images (*.gif, *.jpeg, *.jpg, *.png)",
		"fileTypeExts" : "*.gif;*.jpeg;*.jpg;*.png",		
		// 5 mb limit per file		
		"fileSizeLimit" : 5242880,
		"auto" : true,

		"queueID" : "'.$container.'_queue",
		
		"onUploadSuccess" : function(file,data,response){
			if (data.indexOf("file:") == -1) {				
				$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
			} else {
				var filename = data.replace("file:","");				
				
				$("#'.$container.'_filename").val(filename);
				$("#'.$container.'_filename_img").html("").append(\'<img src="'.$image_base_url.'\'+filename+\'?\'+Date.parse(new Date())+\'" border="0" />\');
			}
		}
	});			
});
	
'.$container.'.colorpicker = function(container){
	'.$container.'.colorpick = new Object();
	'.$container.'.colorpick.obj = new dhtmlXColorPicker("'.$container.'_colorpicker");
	'.$container.'.colorpick.obj.setImagePath(dhx_globalImgPath);
	'.$container.'.colorpick.obj.init();		
	'.$container.'.colorpick.obj.setOnSelectHandler(function (color) {
		//some code
		$("#"+container).val(color);
		$("#"+container+"pick").css("background-color",color);
	});
}
';
}

echo Html::script($script); 
?>