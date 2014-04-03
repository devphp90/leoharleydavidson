<?php 
$include_path = Yii::app()->params['includes_js_path'];	

$script = '$(function(){
	// bind upload file input
	$("#button").uploadify({
		"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
		"uploader" : "'.CController::createUrl('upload_image',array()).'",
		"checkExisting" : false,
		"formData" : {"PHPSESSID":"'.session_id().'"},
		"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
		//"buttonImage" : true,
		"multi" : false,
		"buttonText" : "'.Yii::t('views/config/edit_images','LABEL_LOGO_BTN_FILES').'",
		"width" : 170,
		"fileTypeDesc" : "Images (*.gif, *.jpeg, *.jpg, *.png)",
		"fileTypeExts" : "*.gif;*.jpeg;*.jpg;*.png",		
		// 5 mb limit per file		
		"fileSizeLimit" : 52428800,
		"requeueErrors" : false,
		"auto"  : false,

		"queueID" : "queue",
		
		"onUploadSuccess" : function(file,data,response){
			if (data != "true") {				
				$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
				
				edit_obj.wins.tabs.obj.setCustomStyle("errors",null,null,"color:#FF0000;");
				edit_obj.wins.tabs.errors.grid.obj.addRow(null,file.name+","+data,null);				
			}
		},
		"onQueueComplete" : function(stats){
			edit_obj.layout.A.dataview.load();	
		}
	});		
});';

echo Html::script($script); 
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_popup_<?php echo $container;?>">	
<div style="padding:10px;">		
	<div id="button"></div>
	<div id="queue" style="margin-top:15px;"></div>
</div>
</div>