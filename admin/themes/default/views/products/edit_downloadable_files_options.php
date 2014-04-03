<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$columns = Html::getColumnsMaxLength(Tbl_ProductDownloadableFiles::tableName());	

$help_hint_path = '/catalog/products/downloadable-videos-files/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<?php echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo (isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_name_maxlength">'.($columns['name']-strlen($model->name)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
        <div>
        <?php	
        echo CHtml::activeTextField($model,'name',array('style' => 'width: 100%;','maxlength'=>$columns['name'], 'id'=>$container.'_name'));
        ?>
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>               
    </div>
    
    <?php
		if ($has_variants) {
	?>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_VARIANT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'variant'); ?><br />
        <div>
        <?php
		//create query 
		$sql = 'SELECT 
		product_variant.id,
		GROUP_CONCAT(product_variant_group_option_description.name ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS name
		FROM 
		product_variant 
		INNER JOIN 
		(
			product_variant_option 
			CROSS JOIN 
			product_variant_group 
			CROSS JOIN 
			product_variant_group_option 
			CROSS JOIN 
			product_variant_group_option_description
		)
		ON 
		(
			product_variant.id = product_variant_option.id_product_variant 
			AND 
			product_variant_option.id_product_variant_group = product_variant_group.id 
			AND 
			product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
			AND 
			product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
			AND 
			product_variant_group_option_description.language_code = :language_code
		)
		INNER JOIN 
		product 
		ON 
		(product_variant.id_product = product.id)					
		WHERE
		product_variant.id_product = :id_product
		AND
		product_variant.active = 1
		GROUP BY 
		product_variant.id
		ORDER BY 
		product_variant.sort_order ASC';
		
		$command=$connection->createCommand($sql);		
		
        echo CHtml::activeDropDownList($model,'id_product_variant',CHtml::listData($command->queryAll(true,array(':language_code'=>Yii::app()->language,':id_product'=>$model->id_product)),'id','name'),array( 'id'=>$container.'_id_product_variant','prompt'=>'--'));
        ?>
        <br /><span id="<?php echo $container; ?>_id_product_variant_errorMsg" class="error"></span>
        </div>    
	</div>                
    <?php			
		}
	?>
    
    <div class="row" id="<?php echo $container.'_downloadable_videos_name_container'; ?>" style="width:100%; height:125px;"></div>

  
    <div>
        <div style="float:left"><div id="<?php echo $container; ?>_button"></div></div>
        <div id="<?php echo $container; ?>_please_wait" style="float:left; margin-left:30px; margin-top:1px; display:none"><img src="<?php echo Html::imageUrl("ajax-loader-big.gif");?>" height="32" width="32" /></div>
        <div style="clear:both"></div>
        <div id="<?php echo $container; ?>_queue"></div>
        <div id="<?php echo $container; ?>_filename_label" class="row">
        <?php
        if (!empty($model->filename)) {
			
			echo '<div style="color:#090">'.CHtml::htmlButton(Yii::t('views/products/edit_downloadable_files_options','BUTTON_SEE_FILE'),array('id'=>$container.'_id_button_select','class'=>'select_customer_button','onclick'=>'javascript:window.open(\''.CController::createUrl('download_downloadable_file',array('id'=>$model->id)).'\');')).'&nbsp;&nbsp;&nbsp;'.$model->filename.'</div>';		
		}
		?></div>
        <span id="<?php echo $container; ?>_filename_errorMsg" class="error"></span>
        <?php echo CHtml::activeHiddenField($model,'filename',array('id'=>$container.'_filename')); ?>
    </div>
    <hr />

    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_downloadable_files_options','LABEL_NO_DAYS_EXPIRE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'no-days-expire'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'no_days_expire',array('size'=>5, 'id'=>$container.'_no_days_expire','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_no_days_expire_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_downloadable_files_options','LABEL_NO_DOWNLOADS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'no-downloads'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'no_downloads',array('size'=>5, 'id'=>$container.'_no_downloads','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_no_downloads_errorMsg" class="error"></span>
        </div>                
	</div>       
</div>
</div>
<?php
$include_path = Yii::app()->params['includes_js_path'];	

$script = '
$(function(){
	// bind upload file input
	$("#'.$container.'_button").uploadify({
		"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
		"uploader" : "'.CController::createUrl('upload_downloadable_file',array('id'=>$model->id_product)).'",
		"checkExisting" : false,
		"formData" : {"PHPSESSID":"'.session_id().'"},
		"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
		//"buttonImage" : true,
		"multi" : false,
		"buttonText" : "'.Yii::t('global','LABEL_IMAGE_BTN_FILE').'",
		"width" : 170,
		"fileTypeDesc" : "Files (*.*)",
		"fileTypeExts" : "*.*",		
		// 5 mb limit per file		
		"fileSizeLimit" : 0,
		"requeueErrors" : false,

		"queueID" : "'.$container.'_queue",
		"onUploadStart" : function(file) {
           $("#'.$container.'_please_wait").show();
        }, 
		"onUploadError" : function(file, errorCode, errorMsg, errorString) {
           $("#'.$container.'_please_wait").hide();
        },
		"onUploadSuccess" : function(file,data,response){
			$("#'.$container.'_please_wait").hide();
			if (data.indexOf("file:") == -1) {	
				$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);		
			} else {
				var filename = data.replace("file:","");				
				
				$("#'.$container.'_filename").val(filename);
				
				$("#'.$container.'_filename_label").html("").append("<div style=\"color:#090\">'.addslashes(CHtml::htmlButton(Yii::t('views/products/edit_downloadable_files_options','BUTTON_SEE_FILE'),array('id'=>$container.'_id_button_select','class'=>'select_customer_button','onclick'=>'javascript:get_image();'))).'&nbsp;&nbsp;&nbsp;"+filename+"</div>");
				
				if($("#'.$container.'_id").val()>0){
					'.$container.'.wins.toolbar.save("save");	
				}
			}
		}
	});	
		
});

function get_image(){
	window.open("'.CController::createUrl('download_downloadable_file').'?filename="+$("#'.$container.'_filename").val()+"&id="+$("#'.$container.'_id").val());
}

'.$container.'.wins.tabbar = new Object();
'.$container.'.wins.tabbar.obj = new dhtmlXTabBar("'.$container.'_downloadable_videos_name_container");
'.$container.'.wins.tabbar.obj.setImagePath(dhx_globalImgPath);	
'.$container.'.wins.tabbar.obj.clearAll();
'.$container.'.wins.tabbar.obj.loadXML("'.CController::createUrl('xml_list_downloadable_files_description',array('container'=>$container,'id'=>$model->id)).'", function(){});
';

echo Html::script($script); 
?>