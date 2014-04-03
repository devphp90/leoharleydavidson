<?php 
$app = Yii::app();

$containerObj = $containerJS.'_obj';
$containerLayout = 'logo_layout';

$targetPath = '/_images/';

$script = '
edit_obj = new Object();
edit_obj.dhxWins = new Object();
edit_obj.wins = new Object();

edit_obj.dhxWins = new dhtmlXWindows();
edit_obj.dhxWins.enableAutoViewport(false);
edit_obj.dhxWins.attachViewportTo("'.$containerLayout.'");
edit_obj.dhxWins.setImagePath(dhx_globalImgPath);		

edit_obj.layout = new Object();
edit_obj.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");
edit_obj.layout.A = new Object();
edit_obj.layout.A.obj = edit_obj.layout.obj.cells("a");
edit_obj.layout.A.obj.hideHeader();

edit_obj.layout.toolbar = new Object();

edit_obj.layout.toolbar.load = function(){
	edit_obj.layout.toolbar.obj.clearAll();	
	edit_obj.layout.toolbar.obj.detachAllEvents();
	edit_obj.layout.toolbar.obj.addButton("upload",null,"'.Yii::t('views/config/edit_images','LABEL_BTN_UPLOAD_LOGO').'","toolbar/upload.png","toolbar/upload_dis.png");  

	edit_obj.layout.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "upload":								
				edit_obj.wins.obj = edit_obj.dhxWins.createWindow("edit_obj_uploadWindow", 10, 10, 400, 300);
				
				edit_obj.wins.obj.setText("'.Yii::t('views/config/edit_images','LABEL_BTN_UPLOAD_LOGO').'");
				edit_obj.wins.obj.button("park").hide();
				edit_obj.wins.obj.keepInViewport(true);
				edit_obj.wins.obj.setModal(true);				
				
				edit_obj.wins.tabs = new Object();
				edit_obj.wins.tabs.obj = edit_obj.wins.obj.attachTabbar();
				edit_obj.wins.tabs.obj.setImagePath(dhx_globalImgPath);
				edit_obj.wins.tabs.obj.addTab("images","'.Yii::t('views/products/edit_images','LABEL_TAB_IMAGES').'","*");
				edit_obj.wins.tabs.obj.addTab("errors","'.Yii::t('views/products/edit_images','LABEL_TAB_ERRORS').'","*");
				edit_obj.wins.tabs.obj.setTabActive("images");
				
				edit_obj.wins.tabs.images = new Object();
				edit_obj.wins.tabs.images.toolbar = new Object();
				edit_obj.wins.tabs.images.toolbar.obj = edit_obj.wins.tabs.obj.cells("images").attachToolbar();
				edit_obj.wins.tabs.images.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				edit_obj.wins.tabs.images.toolbar.obj.addButton("upload",null,"'.Yii::t('global','LABEL_BTN_UPLOAD').'","toolbar/upload.png","toolbar/upload_dis.png");
		
				
				edit_obj.wins.tabs.errors = new Object();
				edit_obj.wins.tabs.errors.toolbar = new Object();
				edit_obj.wins.tabs.errors.toolbar.obj = edit_obj.wins.tabs.obj.cells("errors").attachToolbar();
				edit_obj.wins.tabs.errors.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				edit_obj.wins.tabs.errors.toolbar.obj.addButton("clear",null,"'.Yii::t('global','LABEL_BTN_CLEAR_ALL').'","toolbar/delete.png","toolbar/delete_dis.png"); 

				
				edit_obj.wins.tabs.errors.grid = new Object();
				edit_obj.wins.tabs.errors.grid.obj = edit_obj.wins.tabs.obj.cells("errors").attachGrid();
				edit_obj.wins.tabs.errors.grid.obj.setImagePath(dhx_globalImgPath);
				edit_obj.wins.tabs.errors.grid.obj.setHeader("'.Yii::t('views/products/edit_images','LABEL_TAB_IMAGES').', '.Yii::t('views/products/edit_images','LABEL_TAB_ERRORS').'");
				edit_obj.wins.tabs.errors.grid.obj.setInitWidths("*,*");
				edit_obj.wins.tabs.errors.grid.obj.setColAlign("left,left");
				edit_obj.wins.tabs.errors.grid.obj.setColSorting("na,na");
				edit_obj.wins.tabs.errors.grid.obj.setSkin(dhx_skin);
				edit_obj.wins.tabs.errors.grid.obj.init();				
												

				$.ajax({
					url: "'.CController::createUrl('edit_images_upload',array()).'",
					type: "POST",
					success: function(data){
						edit_obj.wins.tabs.obj.setContentHTML("images",data);		
					}
				});	

				edit_obj.wins.tabs.images.toolbar.obj.attachEvent("onClick",function(id){
					switch (id) {
						case "upload":						
							if ($("#queue div").hasClass("uploadify-queue-item")) {				
								$("#button").uploadify("upload","*");
							} else {
								alert("'.Yii::t('views/products/edit_images','LABEL_ALERT_NO_SELECTED').'");	
							}
							break;
					}
				});
				
				edit_obj.wins.tabs.errors.toolbar.obj.attachEvent("onClick",function(id){
					switch (id) {
						case "clear":						
							edit_obj.wins.tabs.obj.setCustomStyle("errors",null,null,null);
							edit_obj.wins.tabs.errors.grid.obj.clearAll();
							break;
					}

				});				


				// clean variables
				edit_obj.wins.obj.attachEvent("onClose",function(win){
					if ($("#queue div").hasClass("uploadify-queue-item") && !confirm("'.Yii::t('views/products/edit_images','LABEL_CONFIRM_CLOSE_IMAGES_IN_QUEUE').'")) return false;
					
					var swfuploadify = window["uploadify_button"];
					if (swfuploadify && typeof swfuploadify.destroy == "function") swfuploadify.destroy();
								
					edit_obj.wins = new Object();															
					return true;
				});
										
				break;
		}
	});	
};

edit_obj.layout.toolbar.obj = edit_obj.layout.obj.attachToolbar();
edit_obj.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
edit_obj.layout.toolbar.load();

edit_obj.layout.A.dataview = new Object();
edit_obj.layout.A.dataview.obj = edit_obj.layout.A.obj.attachDataView({
    type: {
        template: function(obj){	
			return \'<div style="text-align:center;padding-top:10px;"><img border="0" src="'.$targetPath.'\'+obj.filename+\'" height="70"/></div><div style="text-align:center; margin-top:20px; padding-top: 5px; color:#b5b5b5; font-size:15px;">\'+((obj.print==1) ? "'.Yii::t('views/config/edit_images','LABEL_PRINT_VERSION').'":"'.Yii::t('views/config/edit_images','LABEL_WEB_VERSION').'")+\'</div>\';
		},		
		height: 120,
		width: 400,
		margin:10
    },	
	select:false,
	drag:false,
	auto_scroll: false
});

edit_obj.layout.A.dataview.load = function(){
	edit_obj.layout.A.dataview.obj.clearAll();
	edit_obj.layout.A.dataview.obj.load("'.CController::createUrl('xml_list_product_images').'");

};

edit_obj.layout.A.dataview.load();

layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<div id="logo_layout" style="width:100%; height:100%; padding:0; margin:0;"></div>