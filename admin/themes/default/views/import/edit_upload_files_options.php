<?php 
$include_path = Yii::app()->params['includes_js_path'];	

$script = '$(function(){
	var current_id;
	
	// bind upload file input
	$("#'.$container.'_button").uploadify({
		"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
		"uploader" : "'.CController::createUrl('upload_file',array('id'=>$id)).'",
		"formData" : {"PHPSESSID":"'.session_id().'"},
		//"buttonImage" : true,
		"multi" : false,
		"auto"  : false,
		"buttonText" : "'.Yii::t('global','LABEL_IMAGE_BTN_FILES').'",
		"width" : 170,
		"fileTypeDesc" : "Files (*.csv)",
		"fileTypeExts" : "*.csv",		
		// 5 mb limit per file		
		"fileSizeLimit" : 0,	// no limit
		"requeueErrors" : false,
		//"removeTimeout" : 1,
		"removeCompleted" : false,

		"queueID" : "'.$container.'_queue",
		"onUploadSuccess" : function(file,data,response){
			if (data.indexOf("id_import_tpl_files:") == -1) {	
				$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
				
				'.$container.'.wins.tabs.obj.setCustomStyle("errors",null,null,"color:#FF0000;");
				'.$container.'.wins.tabs.errors.grid.obj.addRow(null,file.name+","+data,null);				
			} else {
				var id = data.replace("id_import_tpl_files:","");		
				
				current_id = id;								
			}
		},
		"onQueueComplete" : function(stats){
			'.$container.'.layout.C.grid.load(function(){
				$("#'.$container.'_queue div.uploadify-queue-item").remove();
				'.$container.'.wins.obj.close();
				'.$container.'.layout.C.grid.dblclick(current_id,1);				
			});	
		}
	});		
});';

echo Html::script($script); 
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">		
	<div id="<?php echo $container; ?>_button"></div>
	<div id="<?php echo $container; ?>_queue" style="margin-top:15px;"></div>
</div>
</div>