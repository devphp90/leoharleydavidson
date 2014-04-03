<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$columns = Html::getColumnsMaxLength(Tbl_ProductDownloadableVideos::tableName());	

$help_hint_path = '/catalog/products/downloadable-videos-files/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id_scorm_certificate_product')); ?>
<?php echo CHtml::activeHiddenField($model,'id_product_downloadable_files',array('id'=>$container.'_id_product_downloadable_files','value'=>$id_product_downloadable_files)); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_scorm_certificate_options','LABEL_CERTIFICATE_FIELD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select_certificate'); ?><br />
        <div>
        <?php
		echo CHtml::activeDropDownList($model, 'id_scorm_certificate', CHtml::listData(Tbl_ScormCertificate::model()->findAll(), 'id', 'name'), array('id'=>$container.'_id_scorm_certificate'));
        ?>
        <br /><span id="<?php echo $container; ?>_id_scorm_certificate_errorMsg" class="error"></span>
        </div>    
	</div>
    
    <?php
	$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	$sql = 'SELECT
	*
	FROM
	scorm_certificate_additional_field
	ORDER BY sort_order ASC';
	$command=$connection->createCommand($sql);
	foreach ($command->queryAll(true) as $row) {
		 echo ' <div class="row">
        	<strong>'.$row['name'].'</strong><br />'.CHtml::activeTextField($model,'additional_field['.$row['id'].']',array('style' => 'width: 250px;')).' </div>';
			
	}
	?>           
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_scorm_certificate_options','LABEL_CONDITION_GRID_TITLE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'condition-grid'); ?><br />
       <div style="width:550px">
            <div id="<?php echo $container;?>_my_toolbar_here"></div>
            <div id="<?php echo $container;?>_condition" style="height:130px;"></div>
        </div>                
	</div>  
</div>
</div>
<?php
$script = '

var '.$container.'_grid_conditions = new Object();
'.$container.'_grid_conditions.obj = new dhtmlXGridObject("'.$container.'_condition");

'.$container.'_grid_conditions.obj.selMultiRows = true;
'.$container.'_grid_conditions.obj.setImagePath(dhx_globalImgPath);		
'.$container.'_grid_conditions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_FIELD').','.Yii::t('global','LABEL_VALUE').'",null,["text-align:center;"]);

'.$container.'_grid_conditions.obj.setInitWidths("40,*,220");
'.$container.'_grid_conditions.obj.setColAlign("center,left,left");
'.$container.'_grid_conditions.obj.setColSorting("na,str,str");
'.$container.'_grid_conditions.obj.enableResizing("false,true,true");
'.$container.'_grid_conditions.obj.setSkin(dhx_skin);
'.$container.'_grid_conditions.obj.enableDragAndDrop(false);
'.$container.'_grid_conditions.obj.enableMultiselect(false);
'.$container.'_grid_conditions.obj.enableMultiline(true);
'.$container.'_grid_conditions.obj.enableRowsHover(true,dhx_rowhover_pointer);

'.$container.'_grid_conditions.obj.init();

'.$container.'_grid_conditions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_certificate_condition',array("id_scorm_certificate_product"=>$model->id)).'";
'.$container.'_grid_conditions.obj.loadXML('.$container.'_grid_conditions.obj.xmlOrigFileUrl);

'.$container.'_grid_conditions.toolbar = new Object();
'.$container.'_grid_conditions.toolbar.obj = new dhtmlXToolbarObject("'.$container.'_my_toolbar_here");
'.$container.'_grid_conditions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$container.'_grid_conditions.toolbar.obj.addButton("add", null, "'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$container.'_grid_conditions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");


'.$container.'_grid_conditions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/products/edit_shipping_options','LABEL_PRICE_SHIPPING_REGION').'";

	switch (id) {
		case "add":
			'.$container.'_grid_conditions.toolbar.add();
			break;
		case "delete":			
			var checked = '.$container.'_grid_conditions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/products/edit_scorm_certificate_options','LABEL_ALERT_DELETE_CONDITION').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					$.ajax({
						url: "'.CController::createUrl('delete_condition_custom_field').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid('.$container.'_grid_conditions.obj);
							'.$container.'.wins_certificate.grid.load($("#'.$container.'_id").val());	
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/products/edit_scorm_certificate_options','LABEL_ALERT_NO_CHECKED_CONDITION').'");	
			}
			break;	
	}
});
'.$container.'_grid_conditions.toolbar.add = function(current_id, name){
	
	'.$container.'.wins_add_custom_field = new Object();
	
	name = name ? "'.Yii::t('global','LABEL_EDIT').' "+name:"'.Yii::t('global','LABEL_BTN_ADD').'";	

	'.$container.'.wins_add_custom_field.obj = '.$container.'.dhxWins.createWindow("addCustomFieldsWindow", 20, 20, 500, 200);
	'.$container.'.wins_add_custom_field.obj.setText(name);
	'.$container.'.wins_add_custom_field.obj.button("park").hide();
	'.$container.'.wins_add_custom_field.obj.keepInViewport(true);
	'.$container.'.wins_certificate_option.obj.setModal(false);
	'.$container.'.wins_add_custom_field.obj.setModal(true);
	//'.$container.'.wins_add_custom_field.obj.center();	
				
	

	'.$container.'.wins_add_custom_field.layout = new Object();
	'.$container.'.wins_add_custom_field.layout.obj = '.$container.'.wins_add_custom_field.obj.attachLayout("1C");
	'.$container.'.wins_add_custom_field.layout.A = new Object();
	'.$container.'.wins_add_custom_field.layout.A.obj = '.$container.'.wins_add_custom_field.layout.obj.cells("a");			
	'.$container.'.wins_add_custom_field.layout.A.obj.hideHeader();
	
	'.$container.'.wins_add_custom_field.toolbar = new Object();
	'.$container.'.wins_add_custom_field.toolbar.obj = '.$container.'.wins_add_custom_field.layout.A.obj.attachToolbar();
	'.$container.'.wins_add_custom_field.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	
	'.$container.'.wins_add_custom_field.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
	//'.$container.'.wins_add_custom_field.toolbar.obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
	
	'.$container.'.wins_add_custom_field.toolbar.obj.attachEvent("onClick",function(id){
		switch (id) {
			case "save":	
				//'.$container.'.wins_add_custom_field.toolbar.save(id);
				'.$container.'.wins_add_custom_field.toolbar.save(id,1);
				break;
			case "save_close":
				'.$container.'.wins_add_custom_field.toolbar.save(id,1);
				break;
		}
	});	
	
	
	
	
	
	
	
	
	
	'.$container.'.wins_add_custom_field.toolbar.save = function(id,close){
		  var obj = '.$container.'.wins_add_custom_field.toolbar.obj;
		  $.ajax({
			  url: "'.CController::createUrl('save_condition_custom_field').'",
			  type: "POST",
			  data: $("#"+'.$container.'.wins_add_custom_field.layout.obj.cont.obj.id+" *").serialize(),
			  dataType: "json",
			  beforeSend: function(){	
				  // clear all errors					
				  $("#"+'.$container.'.wins_add_custom_field.layout.obj.cont.obj.id+" span.error").html("");
				  $("#"+'.$container.'.wins_add_custom_field.layout.obj.cont.obj.id+" *").removeClass("error");
				  
				  obj.disableItem(id);			
			  },
			  complete: function(jqXHR, textStatus){
				  if (typeof obj.enableItem == "function") obj.enableItem(id);
  
				  if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$container.'.wins_add_custom_field.obj.close();
			  },
			  success: function(data){						
				  if (data) {
					  if (data.errors) {
						  $.each(data.errors, function(key, value){
							  var id_tag_container = "'.$container.'_"+key;
							  var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
							  
							  if (!$(id_tag_selector).hasClass("error")) { 
								  $(id_tag_selector).addClass("error");
								  if (value) {		
									  value = String(value);
									  var id_errormsg_container = id_tag_container+"_errorMsg";
									  var id_errormsg_selector = "#"+id_errormsg_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
									  if (!$(id_errormsg_selector).hasClass("error")) { 
										  $(id_errormsg_selector).addClass("error");
									  }
									  
									  if ($(id_errormsg_selector).length) { 
										  $(id_errormsg_selector).html(value); 
									  }
								  }						
							  }
						  });																															
					  } else {
  
						  	load_grid('.$container.'_grid_conditions.obj);
							'.$container.'.wins_certificate.grid.load($("#'.$container.'_id").val());		
						
							alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
							
							// Verify if popup window is close
							if(!close){
								var id_tag_container = "'.$container.'_id_custom_fields";
								var id_tag_selector = "#"+id_tag_container.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
								var id_tag_container2 = "'.$container.'_id_custom_fields_option";
								var id_tag_selector2 = "#"+id_tag_container2.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");	
								$("#'.$container.'_id_scorm_certificate_condition").val(data.id);
								'.$container.'.wins_add_custom_field.obj.setText("'.Yii::t('global','LABEL_EDIT').' "+$(id_tag_selector+" option[value="+$(id_tag_selector).val()+"]").text()+" - "+$(id_tag_selector2+" option[value="+$(id_tag_selector2).val()+"]").text());	
							}
							'.$container.'_grid_conditions.obj.selectRowById(data.id);
					  }
				  } else {
					  alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				  }
			  }
		  });	
	  }	
	
	
	
	
	
	
	
	
	
	
			
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
						
				
	
	$.ajax({
		url: "'.CController::createUrl('edit_scorm_certificate_conditions', array("container"=>$container)).'",
		type: "POST",
		data: { "id":current_id, "id_scorm_certificate_product":$("#'.$container.'_id_scorm_certificate_product").val() },
		success: function(data){
			'.$container.'.wins_add_custom_field.layout.A.obj.attachHTMLString(data);		
		}
	});	
					

	
	// clean variables
	'.$container.'.wins_add_custom_field.obj.attachEvent("onClose",function(win){
		'.$container.'.wins_add_custom_field.obj.setModal(false);
		'.$container.'.wins_add_custom_field = new Object();
		'.$container.'.wins_certificate_option.obj.setModal(true);
		return true;
	});			

	
	
	
	
		
}

'.$container.'_grid_conditions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$container.'_grid_conditions.toolbar.add(rId,this.cellById(rId,1).getValue()+" - "+this.cellById(rId,2).getValue());
});';



echo Html::script($script); 
?>