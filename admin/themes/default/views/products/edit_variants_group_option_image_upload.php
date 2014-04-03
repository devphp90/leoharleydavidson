<?php 
$include_path = Yii::app()->params['includes_js_path'];	

$script = '$(function(){
	// bind upload file input
	$("#'.$container.'_button2").uploadify({
		"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
		"uploader" : "'.CController::createUrl('upload_image_variant_group_option',array('id'=>$id)).'",
		"checkExisting" : false,
		"formData" : {"PHPSESSID":"'.session_id().'"},
		"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
		//"buttonImage" : true,
		"multi" : true,
		"buttonText" : "'.Yii::t('global','LABEL_IMAGE_BTN_FILES').'",
		"width" : 170,
		"fileTypeDesc" : "Images (*.gif, *.jpeg, *.jpg, *.png)",
		"fileTypeExts" : "*.gif;*.jpeg;*.jpg;*.png",		
		// 5 mb limit per file		
		"fileSizeLimit" : 5242880,

		"queueID" : "'.$container.'_queue2",
		
		"onUploadSuccess" : function(file,data,response){
			if (data != "true") {				
				$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
				
				'.$container.'.wins.tabs.obj.setCustomStyle("errors",null,null,"color:#FF0000;");
				'.$container.'.wins.tabs.errors.grid.obj.addRow(null,file.name+","+data,null);				
			}
		},
		"onQueueComplete" : function(stats){
			'.$container.'.wins.tabs.images.layout.B.dataview.load();	
		}
	});		
});';

echo Html::script($script); 
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">		
	<div id="<?php echo $container; ?>_button2"></div>
	<div id="<?php echo $container; ?>_queue2" style="margin-top:15px;"></div>
</div>
</div>