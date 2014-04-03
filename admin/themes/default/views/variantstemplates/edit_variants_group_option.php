<?php 
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	
$image_base_url = $app->params['product_images_base_url'].'swatch/';

echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));
echo CHtml::activeHiddenField($model,'id_tpl_product_variant_group',array('id'=>'id_tpl_product_variant_group'));
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px; position:relative;">	
	<div id="colorpicker" style="position: absolute; left:0; top:0;"></div>
    <div id="colorpicker2" style="position: absolute; left:0; top:0;"></div>
    <div id="colorpicker3" style="position: absolute; left:0; top:0;"></div>
	<?php 
	// swatch
	if ($input_type == 2) {
	?>
    <div class="row">
        <strong><?php echo Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_SWATCH_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'swatch-type'); ?><br />
        <div>
        <?php
        echo CHtml::activeDropDownList($model,'swatch_type',array(0=>Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_COLOR_ONE'),1=>Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_COLOR_TWO'),2=>Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_COLOR_THREE'), 3=>Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_FILE')),array( 'id'=>'swatch_type'));
        ?>
        <br /><span id="swatch_type_errorMsg" class="error"></span>
        </div>   
        <div id="swatch_type_color" <?php echo ($model->swatch_type == 3) ? 'style="display:none;"':''; ?>>
            <div> 
                <strong><?php echo Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_COLOR');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'color'); ?><br />
                <div>            
                <?php
                echo CHtml::activeTextField($model,'color',array('size' => 10,'maxlength'=>7, 'id'=>'color', 'style'=>'float:left; margin-right:5px;','readonly'=>'readonly','onclick'=>'javascript:select_colorpicker(\''.'color\');'));
                ?>
                <div style="height:20px; width:20px; background-color:<?php echo $model->color ? $model->color:'#FFF'; ?>; border:1px solid; float:left; cursor:pointer;" id="colorpick" onclick="javascript:select_colorpicker('color');"></div>
                <div style="clear:both;"></div>
                <span id="color_errorMsg" class="error"></span>
                </div>         
            </div>
            
            <div style="<?php echo ($model->swatch_type != 1 && $model	->swatch_type != 2) ? 'display:none;':''; ?>" id="two_color"> 
                <strong><?php echo Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_COLOR_2');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'color-2'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'color2',array('size' => 10,'maxlength'=>7, 'id'=>'color2', 'style'=>'float:left; margin-right:5px;','readonly'=>'readonly','onclick'=>'javascript:select_colorpicker(\''.'color2\');'));
                ?>
                <div style="height:20px; width:20px; background-color:<?php echo $model->color2 ? $model->color2:'#FFF'; ?>; border:1px solid; float:left; cursor:pointer;" id="color2pick" onclick="javascript:select_colorpicker('color2');"></div>
                <div style="clear:both;"></div>            
                <span id="color2_errorMsg" class="error"></span>
                </div>         
            </div>
    
            <div style="<?php echo ($model->swatch_type != 2) ? 'display:none;':''; ?>" id="three_color"> 
                <strong><?php echo Yii::t('views/variantstemplates/edit_variants_group_option','LABEL_COLOR_3');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'color-3'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'color3',array('size' => 10,'maxlength'=>7, 'id'=>'color3', 'style'=>'float:left; margin-right:5px;','readonly'=>'readonly','onclick'=>'javascript:select_colorpicker(\''.'color3\');'));
                ?>
                <div style="height:20px; width:20px; background-color:<?php echo $model->color3 ? $model->color3:'#FFF'; ?>; border:1px solid; float:left; cursor:pointer;" id="color3pick" onclick="javascript:select_colorpicker('color3');"></div>
                <div style="clear:both;"></div>               
                <span id="color3_errorMsg" class="error"></span>
                </div>         
            </div>  
		</div>                  
		<div id="swatch_type_file" <?php echo ($model->swatch_type != 3) ? 'style="display:none;"':''; ?>>
        	<div style="height:20px; width:20px; border:1px solid; margin-bottom:10px; float:left; margin-right: 5px;" id="filename_img"><?php echo $model->old_filename ? '<img src="'.$image_base_url.$model->old_filename.'" border="0" />':''; ?></div><span style="float:left; margin-top:8px;">(20x20)</span>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'file'); ?>
            <div style="clear:both;"></div>
            <div id="button"></div>
            <div id="queue" style="margin-top:15px; margin-bottom:5px;"></div>
            <?php
            echo CHtml::activeHiddenField($model,'filename',array('id'=>'filename'));
			echo CHtml::activeHiddenField($model,'old_filename',array('id'=>'old_filename'));
            ?>
            <span id="filename_errorMsg" class="error"></span>                        
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
	$("#swatch_type").change(function(){
		switch ($(this).val()) {
			case "0":
				$("#swatch_type_file").hide();			
				$("#swatch_type_color").show();
				$("#two_color").hide();
				$("#three_color").hide();			
				break;
			case "1":
				$("#swatch_type_file").hide();			
				$("#swatch_type_color").show();
				$("#two_color").show();
				$("#three_color").hide();						
				break;				
			case "2":								
				$("#swatch_type_file").hide();			
				$("#swatch_type_color").show();
				$("#two_color").show();
				$("#three_color").show();									
				break;	
			case "3":
				$("#swatch_type_color").hide();
				$("#swatch_type_file").show();
				break;
		}
	});	
	
	// bind upload file input
	$("#button").uploadify({
		"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
		"uploader" : "'.CController::createUrl('upload_image_variant_group_option_swatch').'",
		"checkExisting" : false,
		"formData" : {"PHPSESSID":"'.session_id().'"},
		"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
		"buttonText" : "'.Yii::t('global','LABEL_IMAGE_BTN_FILE').'",
		"width" : 170,
		"fileTypeDesc" : "Images (*.gif, *.jpeg, *.jpg, *.png)",
		"fileTypeExts" : "*.gif;*.jpeg;*.jpg;*.png",		
		// 5 mb limit per file		
		"fileSizeLimit" : 5242880,
		"auto" : true,

		"queueID" : "queue",
		
		"onUploadSuccess" : function(file,data,response){
			if (data.indexOf("file:") == -1) {				
				$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
			} else {
				var filename = data.replace("file:","");				
				
				$("#filename").val(filename);
				$("#filename_img").html("").append(\'<img src="'.$image_base_url.'\'+filename+\'?\'+Date.parse(new Date())+\'" border="0" />\');
			}
		}
	});			
});
	
select_colorpicker = function(container){
	var colorpick = new Object();
	colorpick.obj = new dhtmlXColorPicker("colorpicker");
	colorpick.obj.setImagePath(dhx_globalImgPath);
	colorpick.obj.init();		
	colorpick.obj.setOnSelectHandler(function (color) {
		//some code
		$("#"+container).val(color);
		$("#"+container+"pick").css("background-color",color);
	});
}
';
}

echo Html::script($script); 
?>