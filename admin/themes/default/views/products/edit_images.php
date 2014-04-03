<?php 
$app = Yii::app();

$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$image_base_url = $app->params['product_images_base_url'].'thumb/';

switch ($app->params['images_orientation']) {
    case 'portrait':
        $dataview_item_width = $app->params['portrait_thumb_width'];
        $dataview_item_height = $app->params['portrait_thumb_height'];
        break;
    case 'landscape':
        $dataview_item_width = $app->params['landscape_thumb_width'];
        $dataview_item_height = $app->params['landscape_thumb_height'];
        break;
}

$script = '
'.$containerObj.' = new Object();
'.$containerObj.'.dhxWins = new Object();
'.$containerObj.'.wins = new Object();

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo("'.$containerLayout.'");
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);		

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");
'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.toolbar = new Object();

'.$containerObj.'.layout.toolbar.load = function(){
	'.$containerObj.'.layout.toolbar.obj.clearAll();	
	'.$containerObj.'.layout.toolbar.obj.detachAllEvents();
	'.$containerObj.'.layout.toolbar.obj.addButton("upload",null,"'.Yii::t('global','LABEL_BTN_UPLOAD_IMAGE').'","toolbar/upload.png","toolbar/upload_dis.png");  
	'.$containerObj.'.layout.toolbar.obj.addSeparator("sep01", null);
	'.$containerObj.'.layout.toolbar.obj.addButton("set_cover",null,"'.Yii::t('global','LABEL_BTN_SET_COVER_IMAGE').'","toolbar/green_check.png","toolbar/green_check_dis.png");
	'.$containerObj.'.layout.toolbar.obj.addSeparator("sep02", null);
	'.$containerObj.'.layout.toolbar.obj.addButton("select_all",null,"'.Yii::t('global','LABEL_BTN_SELECT_ALL_IMAGE').'",null,null);
	'.$containerObj.'.layout.toolbar.obj.addButton("unselect_all",null,"'.Yii::t('global','LABEL_BTN_UNSELECT_ALL_IMAGE').'",null,null);
	'.$containerObj.'.layout.toolbar.obj.addSeparator("sep03", null);
	'.$containerObj.'.layout.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");		

	'.$containerObj.'.layout.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "upload":								
				'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_uploadWindow", 10, 10, 400, 380);
				
				'.$containerObj.'.wins.obj.setText("'.Yii::t('global','LABEL_BTN_UPLOAD_IMAGE').'");
				'.$containerObj.'.wins.obj.button("park").hide();
				'.$containerObj.'.wins.obj.keepInViewport(true);
				'.$containerObj.'.wins.obj.setModal(true);				
				
				'.$containerObj.'.wins.tabs = new Object();
				'.$containerObj.'.wins.tabs.obj = '.$containerObj.'.wins.obj.attachTabbar();
				'.$containerObj.'.wins.tabs.obj.setImagePath(dhx_globalImgPath);
				'.$containerObj.'.wins.tabs.obj.addTab("images","'.Yii::t('views/products/edit_images','LABEL_TAB_IMAGES').'","*");
				'.$containerObj.'.wins.tabs.obj.addTab("errors","'.Yii::t('views/products/edit_images','LABEL_TAB_ERRORS').'","*");
				'.$containerObj.'.wins.tabs.obj.setTabActive("images");
				
				'.$containerObj.'.wins.tabs.images = new Object();
				'.$containerObj.'.wins.tabs.images.toolbar = new Object();
				'.$containerObj.'.wins.tabs.images.toolbar.obj = '.$containerObj.'.wins.tabs.obj.cells("images").attachToolbar();
				'.$containerObj.'.wins.tabs.images.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerObj.'.wins.tabs.images.toolbar.obj.addButton("upload",null,"'.Yii::t('global','LABEL_BTN_UPLOAD').'","toolbar/upload.png","toolbar/upload_dis.png");
		
				
				'.$containerObj.'.wins.tabs.errors = new Object();
				'.$containerObj.'.wins.tabs.errors.toolbar = new Object();
				'.$containerObj.'.wins.tabs.errors.toolbar.obj = '.$containerObj.'.wins.tabs.obj.cells("errors").attachToolbar();
				'.$containerObj.'.wins.tabs.errors.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerObj.'.wins.tabs.errors.toolbar.obj.addButton("clear",null,"'.Yii::t('global','LABEL_BTN_CLEAR_ALL').'","toolbar/delete.png","toolbar/delete_dis.png"); 

				
				'.$containerObj.'.wins.tabs.errors.grid = new Object();
				'.$containerObj.'.wins.tabs.errors.grid.obj = '.$containerObj.'.wins.tabs.obj.cells("errors").attachGrid();
				'.$containerObj.'.wins.tabs.errors.grid.obj.setImagePath(dhx_globalImgPath);
				'.$containerObj.'.wins.tabs.errors.grid.obj.setHeader("'.Yii::t('views/products/edit_images','LABEL_TAB_IMAGES').', '.Yii::t('views/products/edit_images','LABEL_TAB_ERRORS').'");
				'.$containerObj.'.wins.tabs.errors.grid.obj.setInitWidths("*,*");
				'.$containerObj.'.wins.tabs.errors.grid.obj.setColAlign("left,left");
				'.$containerObj.'.wins.tabs.errors.grid.obj.setColSorting("na,na");
				'.$containerObj.'.wins.tabs.errors.grid.obj.setSkin(dhx_skin);
				'.$containerObj.'.wins.tabs.errors.grid.obj.init();				
												

				$.ajax({
					url: "'.CController::createUrl('edit_images_upload',array('container'=>$containerObj,'id'=>$id)).'",
					type: "POST",
					success: function(data){
						'.$containerObj.'.wins.tabs.obj.setContentHTML("images",data);		
					}
				});	

				'.$containerObj.'.wins.tabs.images.toolbar.obj.attachEvent("onClick",function(id){
					switch (id) {
						case "upload":		
							if ($("#'.$containerObj.'_queue div").hasClass("uploadify-queue-item")) {				
								$("#'.$containerObj.'_button").uploadify("upload","*");
							} else {
								alert("'.Yii::t('views/products/edit_images','LABEL_ALERT_NO_SELECTED').'");	
							}
							break;
					}
				});
				
				'.$containerObj.'.wins.tabs.errors.toolbar.obj.attachEvent("onClick",function(id){
					switch (id) {
						case "clear":						
							'.$containerObj.'.wins.tabs.obj.setCustomStyle("errors",null,null,null);
							'.$containerObj.'.wins.tabs.errors.grid.obj.clearAll();
							break;
					}

				});				


				// clean variables
				'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
					if ($("#'.$containerObj.'_queue div").hasClass("uploadify-queue-item") && !confirm("'.Yii::t('views/products/edit_images','LABEL_CONFIRM_CLOSE_IMAGES_IN_QUEUE').'")) return false;
					
					var swfuploadify = window["uploadify_'.$containerObj.'_button"];
					if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();
								
					'.$containerObj.'.wins = new Object();															
					return true;
				});
										
				break;
			case "set_cover":
				var rows='.$containerObj.'.layout.A.dataview.obj.getSelected({ as_array: true });
				
				if (rows.length) {
					var id_product_image;
					for (var i=0;i<1;++i) {
						if (rows[i]) {
							id_product_image = rows[i];
						}
					}							

					$.ajax({
						url: "'.CController::createUrl('set_cover_image',array('id'=>$id)).'",
						type: "POST",
						data: { "id_product_image":id_product_image },
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},								
						success: function(data){
							'.$containerObj.'.layout.A.dataview.load();	
						}
					});				
				} else {
					alert("'.Yii::t('views/products/edit_images','LABEL_ALERT_NO_SELECTED').'");	
				}
				break;				
			case "select_all":
				'.$containerObj.'.layout.A.dataview.obj.selectAll();
				break;
			case "unselect_all":
				'.$containerObj.'.layout.A.dataview.obj.unselectAll();
				break;				
			case "delete":
				var rows='.$containerObj.'.layout.A.dataview.obj.getSelected({ as_array: true });
				
				if (rows.length) {
					if (confirm("'.Yii::t('views/products/edit_images','LABEL_ALERT_DELETE_IMAGE').'")) {
						var ids=[];
					
						for (var i=0;i<rows.length;++i) {
							if (rows[i]) {
								ids.push("ids[]="+rows[i]);									
							}
						}
						
						$.ajax({
							url: "'.CController::createUrl('delete_image',array('id'=>$id)).'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);	
							},							
							success: function(data){													
								'.$containerObj.'.layout.A.dataview.load();
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});						
					}
				} else {
					alert("'.Yii::t('views/products/edit_images','LABEL_ALERT_NO_SELECTED').'");	
				}				
				break;
		}
	});	
};

'.$containerObj.'.layout.toolbar.obj = '.$containerObj.'.layout.obj.attachToolbar();
'.$containerObj.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.toolbar.load();
var current_width;
var current_height;
'.$containerObj.'.layout.A.dataview = new Object();
'.$containerObj.'.layout.A.dataview.obj = '.$containerObj.'.layout.A.obj.attachDataView({
    type: {
        template: function(obj){	
			var tooltip = "'.Yii::t('views/products/edit_images','LABEL_DOUBLE_CLICK').'";
		
			if (obj.force_crop == "1") {				
				tooltip = tooltip+" '.Yii::t('views/products/edit_images','LABEL_ALERT_IMAGE_NEED_CROP').'";
			}
			
			return \'<div style="width:'.($dataview_item_width).'px; height:100%; padding:10px; text-align: center; " \'+((!obj.$selected && obj.force_crop == "1") ? \'class="force_crop"\':"")+\'><img border="0" src="'.$image_base_url.'\'+obj.filename+\'" width="\'+obj.width_current+\'" height="\'+obj.height_current+\'" ondragstart="javascript:return false;" title="\'+tooltip+\'" style="border: 1px solid #303030;" />\'+((obj.cover == "1") ? \'<div style="margin-top:5px">'.CHtml::image(Html::themeImageUrl('green_check.png'),'',array('width'=>20,'height'=>20,'border'=>0)).'</div>\':"")+\'</div>\';
		},		
		height: '.($dataview_item_height+40).',
		width: '.($dataview_item_width+20).',
		padding:0
    },	
	select:"multiselect",
	drag:true,
	auto_scroll: true
});

'.$containerObj.'.layout.A.dataview.load = function(){
	'.$containerObj.'.layout.A.dataview.obj.clearAll();
	'.$containerObj.'.layout.A.dataview.obj.load("'.CController::createUrl('xml_list_product_images',array('id'=>$id)).'");
};

'.$containerObj.'.layout.A.dataview.load();

'.$containerObj.'.layout.A.dataview.obj.attachEvent("onItemDblClick", function (id, ev, html){	
	'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("'.$containerObj.'_cropWindow", 10, 10, 600, 410);
	'.$containerObj.'.wins.obj.setText("'.Yii::t('views/products/edit_images','LABEL_CROP_IMAGE').'");
	'.$containerObj.'.wins.obj.button("park").hide();
	'.$containerObj.'.wins.obj.keepInViewport(true);
	'.$containerObj.'.wins.obj.setModal(true);
	
	'.$containerObj.'.wins.toolbar = new Object();
	'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
	'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$containerObj.'.wins.toolbar.obj.addButton("crop_save",null,"'.Yii::t('global','LABEL_BTN_CROP_IMAGE_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
	'.$containerObj.'.wins.toolbar.obj.addSeparator("sep01", null);
	'.$containerObj.'.wins.toolbar.obj.addButton("rotate_left",null,"'.Yii::t('global','LABEL_BTN_ROTATE_LEFT_IMAGE').'","toolbar/rotate-left.png","toolbar/rotate-left-dis.png"); 
	'.$containerObj.'.wins.toolbar.obj.addButton("rotate_right",null,"'.Yii::t('global','LABEL_BTN_ROTATE_RIGHT_IMAGE').'","toolbar/rotate-right.png","toolbar/rotate-right-dis.png"); 
	
	'.$containerObj.'.wins.crop_image = function(id, rotate){
		$.ajax({
			url: "'.CController::createUrl('edit_images_crop',array('container'=>$containerObj,'id_product'=>$id)).'",
			type: "POST",
			data: { "id":id, "rotate":rotate },
			cache: false,
			success: function(data){
				'.$containerObj.'.wins.obj.attachHTMLString(data);		
			}
		});				
	}
	
	'.$containerObj.'.wins.crop_image(id);
	
	'.$containerObj.'.wins.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "crop_save":	
				// check if we have a selection
				if ($("#'.$containerObj.'_w").val() > 0) {									
					// ajax request				
					$.ajax({
						url: "'.CController::createUrl('crop_and_save').'",
						type: "POST",
						data: $("#'.$containerObj.'_form").serialize(),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data) {
							if (data == "true") { 				
								'.$containerObj.'.layout.A.dataview.load();					
								'.$containerObj.'.wins.obj.close();
							} else {
								alert(data);
							}
						}
					});
				} else {
					alert("'.Yii::t('views/products/edit_images','LABEL_ALERT_MUST_MAKE_SELECTION').'");	
				}
				break;
			case "rotate_left":
				var current_id = parseInt($("#'.$containerObj.'_id").val());
				var rotate = parseInt($("#'.$containerObj.'_rotate").val());
			
				'.$containerObj.'.wins.crop_image(current_id,rotate-90);
				break;
			case "rotate_right":
				var current_id = parseInt($("#'.$containerObj.'_id").val());
				var rotate = parseInt($("#'.$containerObj.'_rotate").val());
			
				'.$containerObj.'.wins.crop_image(current_id,rotate+90);			
				break;
		}
	});	
	
	// clean variables
	'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
		'.$containerObj.'.wins = new Object();
		
		return true;
	});			
		
	//any custom logic here
	return true;
});

'.$containerObj.'.layout.A.dataview.obj.attachEvent("onAfterDrop", function (context,e){
	//any custom logic here	
	var obj = this;
	var iTotal = obj.dataCount();
	var ids=[];	
	
	for (var i=0; i<iTotal; i++) {
		ids.push("ids[]="+obj.idByIndex(i));						
	}
	
	$.ajax({
		url: "'.CController::createUrl('save_image_sort_order',array('id'=>$id)).'",
		type: "POST",
		data: ids.join("&")
	});			
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>