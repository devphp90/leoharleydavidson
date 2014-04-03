<?php 
$orders = Tbl_Orders::model()->findByPk($id);

$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';
$include_path = Yii::app()->params['includes_js_path'];	

$connection=Yii::app()->db;
$sql = "SELECT 
COUNT(orders_item_product_downloadable_files.id) AS total_files,
COUNT(orders_item_product_downloadable_videos.id) AS total_videos
FROM 
orders_item_product

LEFT JOIN
(orders_item CROSS JOIN orders)
ON
(orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)

LEFT JOIN
(orders_item_product AS oip CROSS JOIN orders_item AS oi CROSS JOIN orders AS o) 
ON
(orders_item_product.id_orders_item_product = oip.id AND oip.id_orders_item = oi.id AND oi.id_orders = o.id)

LEFT JOIN
orders_item_product_downloadable_files
ON
(orders_item_product.id = orders_item_product_downloadable_files.id_orders_item_product)

LEFT JOIN
orders_item_product_downloadable_videos
ON
(orders_item_product.id = orders_item_product_downloadable_videos.id_orders_item_product)

WHERE
(orders.id IS NOT NULL AND orders.id = '".$id."')
OR
(o.id IS NOT NULL AND o.id = '".$id."')";	
		
		/* Select queries return a resultset */
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true);
		$totalCount = $row['total_files']+$row['total_videos'];	
			
		

$script = '

'.$containerObj.' = new Object();
'.$containerObj.'.status = '.$orders->status.';
'.$containerObj.'.priority = '.$orders->priority.';
'.$containerObj.'.downloadable = '.$totalCount.';

'.$containerObj.'.dhxWins = new dhtmlXWindows();
'.$containerObj.'.dhxWins.enableAutoViewport(false);
'.$containerObj.'.dhxWins.attachViewportTo(tabs.obj.entBox.id);
'.$containerObj.'.dhxWins.setImagePath(dhx_globalImgPath);

'.$containerObj.'.wins = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();


'.$containerObj.'.layout.toolbar = new Object();

'.$containerObj.'.layout.toolbar.load = function(current_id){
	'.$containerObj.'.layout.toolbar.obj.clearAll();	
	'.$containerObj.'.layout.toolbar.obj.detachAllEvents();
	'.$containerObj.'.layout.toolbar.obj.addButton("set_status",null,"'.Yii::t('views/orders/edit_info','LABEL_BTN_SET_STATUS').'","toolbar/status.png","toolbar/status_dis.png");  
	'.$containerObj.'.layout.toolbar.obj.addButton("set_priority",null,"'.Yii::t('views/orders/edit_info','LABEL_BTN_SET_PRIORITY').'","toolbar/exclamation.png","toolbar/exclamation_dis.png"); 
	if('.$containerObj.'.downloadable>0){ 
	'.$containerObj.'.layout.toolbar.obj.addButton("reset_downloadables",null,"'.Yii::t('views/orders/edit_info','LABEL_BTN_RESET_DOWNLOADABLES').'","toolbar/refresh.png","toolbar/refresh_dis.png");  
	}
	'.$containerObj.'.layout.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	if ('.$containerObj.'.status == -1) {
		'.$containerObj.'.layout.toolbar.obj.disableItem("set_status");
		'.$containerObj.'.layout.toolbar.obj.disableItem("set_priority");
	}

	'.$containerObj.'.layout.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "set_status":
				'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("setOrderStatusWindow", 0, 0, 310, 220);
				'.$containerObj.'.wins.obj.setText("'.Yii::t('views/orders/set_order_status','LABEL_SET_ORDER_STATUS').'");
				'.$containerObj.'.wins.obj.button("park").hide();
				'.$containerObj.'.wins.obj.button("minmax1").hide();
				'.$containerObj.'.wins.obj.keepInViewport(true);
				'.$containerObj.'.wins.obj.setModal(true);
				'.$containerObj.'.wins.obj.denyResize();
				'.$containerObj.'.wins.obj.center();		
				var pos = '.$containerObj.'.wins.obj.getPosition();
				'.$containerObj.'.wins.obj.setPosition(pos[0],10);		
			
				$.ajax({
					url: "'.CController::createUrl('set_order_status',array('container'=>$container,'id'=>$id)).'",
					type: "POST",
					success: function(data){
						'.$containerObj.'.wins.obj.attachHTMLString(data);		
					}
				});			
				
				'.$containerObj.'.wins.toolbar = new Object();
				'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
				'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerObj.'.wins.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
				
				'.$containerObj.'.wins.toolbar.obj.attachEvent("onClick",function(id){
					switch (id) {
						case "save":		
							var status = $("#'.$container.'_status").val();
							var update_qty = $("input[name=update_qty]:checked", "#set_order_status").val();

							if ('.$containerObj.'.status != status) {
								if (status == -1 && confirm("'.Yii::t('views/orders/set_order_status','LABEL_ALERT_SET_STATUS').'") || status != -1) {
									$.ajax({
										url: "'.CController::createUrl('save_status',array('id'=>$id)).'",
										type: "POST",
										data: { "status":status,"update_qty":update_qty },
										success: function(data){
											//'.$containerJS.'.layout.A.dataview.obj.callEvent("onBeforeSelect",["edit_info"]);		
											$.ajax({
												url: "'.CController::createUrl('get_status',array('id'=>$id)).'",
												type: "POST",
												success: function(data){										
													'.$containerObj.'.status = status;
													
													if (status == -1) {
														'.$containerObj.'.layout.toolbar.obj.disableItem("set_status");
														'.$containerObj.'.layout.toolbar.obj.disableItem("set_priority");
													} 													
													
													$("#'.$containerObj.'_order_status").html("").append(data);													
													
													load_grid(tabs.list.grid.obj);
													
													'.$containerObj.'.wins.obj.close();
												}
											});												
										}
									});											
								}
								
								return false;															
							}
							
							'.$containerObj.'.wins.obj.close();
							break;							
					}
				});	
				
				// clean variables
				'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
					'.$containerObj.'.wins = new Object();						
					
					return true;
				});								
				break;
			case "set_priority":
				'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("setOrderStatusWindow", 0, 0, 300, 180);
				'.$containerObj.'.wins.obj.setText("'.Yii::t('views/orders/set_order_priority','LABEL_SET_ORDER_PRIORITY').'");
				'.$containerObj.'.wins.obj.button("park").hide();
				'.$containerObj.'.wins.obj.button("minmax1").hide();
				'.$containerObj.'.wins.obj.keepInViewport(true);
				'.$containerObj.'.wins.obj.setModal(true);
				'.$containerObj.'.wins.obj.denyResize();
				'.$containerObj.'.wins.obj.center();		
				var pos = '.$containerObj.'.wins.obj.getPosition();
				'.$containerObj.'.wins.obj.setPosition(pos[0],10);		
			
				$.ajax({
					url: "'.CController::createUrl('set_order_priority',array('container'=>$container,'id'=>$id)).'",
					type: "POST",
					success: function(data){
						'.$containerObj.'.wins.obj.attachHTMLString(data);		
					}
				});			
				
				'.$containerObj.'.wins.toolbar = new Object();
				'.$containerObj.'.wins.toolbar.obj = '.$containerObj.'.wins.obj.attachToolbar();
				'.$containerObj.'.wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
				'.$containerObj.'.wins.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
				
				'.$containerObj.'.wins.toolbar.obj.attachEvent("onClick",function(id){
					switch (id) {
						case "save":		
							var priority = $("#'.$container.'_priority").val();
							
							if ('.$containerObj.'.priority != priority) {
								$.ajax({
									url: "'.CController::createUrl('save_priority',array('id'=>$id)).'",
									type: "POST",
									data: { "priority":priority },
									success: function(data){
										$.ajax({
											url: "'.CController::createUrl('get_priority',array('id'=>$id)).'",
											type: "POST",
											success: function(data){										
												'.$containerObj.'.priority = priority;
												
												$("#'.$containerObj.'_order_priority").html("").append(data);	
												
												load_grid(tabs.list.grid.obj);
																								
												'.$containerObj.'.wins.obj.close();
											}
										});												
									}
								});																			
								return false;															
							}
							
							'.$containerObj.'.wins.obj.close();
							break;							
					}
				});	
				
				// clean variables
				'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
					'.$containerObj.'.wins = new Object();						
					
					return true;
				});								
				break;	
			case "reset_downloadables":
				'.$containerObj.'.wins.obj = '.$containerObj.'.dhxWins.createWindow("resetDownloadablesWindow", 0, 0, 600, 320);
				'.$containerObj.'.wins.obj.setText("'.Yii::t('views/orders/edit_info','LABEL_BTN_RESET_DOWNLOADABLES').'");
				'.$containerObj.'.wins.obj.button("park").hide();
				'.$containerObj.'.wins.obj.button("minmax1").hide();
				'.$containerObj.'.wins.obj.keepInViewport(true);
				'.$containerObj.'.wins.obj.setModal(true);
				'.$containerObj.'.wins.obj.denyResize();
				'.$containerObj.'.wins.obj.center();		
				var pos = '.$containerObj.'.wins.obj.getPosition();
				'.$containerObj.'.wins.obj.setPosition(pos[0],10);		
			
				'.$containerObj.'.wins.layout = new Object();
				'.$containerObj.'.wins.layout.obj = '.$containerObj.'.wins.obj.attachLayout("2U");				
				
				'.$containerObj.'.wins.layout.A = new Object();
				'.$containerObj.'.wins.layout.A.obj = '.$containerObj.'.wins.layout.obj.cells("a");							
				'.$containerObj.'.wins.layout.A.obj.hideHeader();		
				
				'.$containerObj.'.wins.layout.A.grid = new Object();
				'.$containerObj.'.wins.layout.A.grid.obj = '.$containerObj.'.wins.layout.A.obj.attachGrid();
				'.$containerObj.'.wins.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
				'.$containerObj.'.wins.layout.A.grid.obj.setHeader("'.Yii::t('views/orders/edit_info','LABEL_DOWNLOADABLE_VIDEOS').'",null,[]);
				
				'.$containerObj.'.wins.layout.A.grid.obj.setInitWidthsP("*");
				'.$containerObj.'.wins.layout.A.grid.obj.setColAlign("left");
				'.$containerObj.'.wins.layout.A.grid.obj.setColSorting("na");
				'.$containerObj.'.wins.layout.A.grid.obj.setSkin(dhx_skin);
				'.$containerObj.'.wins.layout.A.grid.obj.enableDragAndDrop(false);
				'.$containerObj.'.wins.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
				'.$containerObj.'.wins.layout.A.grid.obj.enableMultiline(true);
				
				'.$containerObj.'.wins.layout.A.grid.obj.init();	
				
				// we create a variable to store the default url used to get our grid data, so we can reuse it later
				'.$containerObj.'.wins.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_downloadable_videos',array('id'=>$id)).'";
				
				'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
					ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
				}); 
				
				'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
					ajaxOverlay(grid_obj.entBox.id,0);
				});			
				
				'.$containerObj.'.wins.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
					'.$containerObj.'.wins2 = new Object();
					'.$containerObj.'.wins2.obj = '.$containerObj.'.dhxWins.createWindow("resetDownloadableVideoWindow", 0, 0, 600, 320);
					'.$containerObj.'.wins2.obj.setText(this.cellById(rId,0).getValue());
					'.$containerObj.'.wins2.obj.button("park").hide();
					'.$containerObj.'.wins2.obj.button("minmax1").hide();
					'.$containerObj.'.wins2.obj.keepInViewport(true);
					'.$containerObj.'.wins.obj.setModal(false);
					'.$containerObj.'.wins2.obj.setModal(true);
					'.$containerObj.'.wins2.obj.denyResize();
					'.$containerObj.'.wins2.obj.center();		
					var pos = '.$containerObj.'.wins2.obj.getPosition();
					'.$containerObj.'.wins2.obj.setPosition(pos[0],20);		
				
					'.$containerObj.'.wins2.layout = new Object();
					'.$containerObj.'.wins2.layout.obj = '.$containerObj.'.wins2.obj.attachLayout("1C");				
					
					'.$containerObj.'.wins2.layout.A = new Object();
					'.$containerObj.'.wins2.layout.A.obj = '.$containerObj.'.wins2.layout.obj.cells("a");							
					'.$containerObj.'.wins2.layout.A.obj.hideHeader();		
					
					$.ajax({
						url: "'.CController::createUrl('reset_downloadable_video_options',array('container'=>$containerObj)).'&id="+rId,
						success: function(data){
							'.$containerObj.'.wins2.layout.A.obj.attachHTMLString(data);		
						}
					});		
					
					'.$containerObj.'.wins2.toolbar = new Object();
					'.$containerObj.'.wins2.toolbar.obj = '.$containerObj.'.wins2.obj.attachToolbar();
					'.$containerObj.'.wins2.toolbar.obj.setIconsPath(dhx_globalImgPath);	
					'.$containerObj.'.wins2.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
					'.$containerObj.'.wins2.toolbar.obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
					
					'.$containerObj.'.wins2.toolbar.obj.attachEvent("onClick",function(id){
						switch (id) {
							case "save":	
								'.$containerObj.'.wins2.toolbar.save(id);
								break;
							case "save_close":
								'.$containerObj.'.wins2.toolbar.save(id,1);
								break;			
						}
					});		
					
					'.$containerObj.'.wins2.toolbar.save = function(id,close){
						var obj = '.$containerObj.'.wins2.toolbar.obj;
						
						$.ajax({
							url: "'.CController::createUrl('save_downloadable_video').'",
							type: "POST",
							data: $("#"+'.$containerObj.'.wins2.layout.obj.cont.obj.id+" *").serialize(),
							dataType: "json",
							beforeSend: function(){			
								// clear all errors					
								$("#"+'.$containerObj.'.wins2.layout.obj.cont.obj.id+" span.error").html("");
								$("#"+'.$containerObj.'.wins2.layout.obj.cont.obj.id+" *").removeClass("error");
							
								obj.disableItem(id);			
							},
							complete: function(jqXHR, textStatus){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
	
								if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$containerObj.'.wins2.obj.close();
							},
							success: function(data){						
								// Remove class error to the background of the main div
								$(".div_'.$containerObj.'").removeClass("error_background");
								if (data) {
									if (data.errors) {
										$.each(data.errors, function(key, value){
											var id_tag_container = "'.$containerObj.'_"+key;
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
										// Apply class error to the background of the main div
										$(".div_'.$containerObj.'").addClass("error_background");																														
									} else {		
										alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
									}
								} else {
									alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
								}
							}
						});	
					}				
					
					// clean variables
					'.$containerObj.'.wins2.obj.attachEvent("onClose",function(win){
						this.setModal(false);
						'.$containerObj.'.wins2 = new Object();						
						'.$containerObj.'.wins.obj.setModal(true);
						
						return true;
					});	
				});
				
				load_grid('.$containerObj.'.wins.layout.A.grid.obj);				
				
				'.$containerObj.'.wins.layout.B = new Object();
				'.$containerObj.'.wins.layout.B.obj = '.$containerObj.'.wins.layout.obj.cells("b");							
				'.$containerObj.'.wins.layout.B.obj.hideHeader();										
				
				'.$containerObj.'.wins.layout.B.grid = new Object();
				'.$containerObj.'.wins.layout.B.grid.obj = '.$containerObj.'.wins.layout.B.obj.attachGrid();
				'.$containerObj.'.wins.layout.B.grid.obj.setImagePath(dhx_globalImgPath);	
				'.$containerObj.'.wins.layout.B.grid.obj.setHeader("'.Yii::t('views/orders/edit_info','LABEL_DOWNLOADABLE_FILES').'",null,[]);
				
				'.$containerObj.'.wins.layout.B.grid.obj.setInitWidthsP("*");
				'.$containerObj.'.wins.layout.B.grid.obj.setColAlign("left");
				'.$containerObj.'.wins.layout.B.grid.obj.setColSorting("na");
				'.$containerObj.'.wins.layout.B.grid.obj.setSkin(dhx_skin);
				'.$containerObj.'.wins.layout.B.grid.obj.enableDragAndDrop(false);
				'.$containerObj.'.wins.layout.B.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
				'.$containerObj.'.wins.layout.B.grid.obj.enableMultiline(true);
				
				'.$containerObj.'.wins.layout.B.grid.obj.init();	
				
				// we create a variable to store the default url used to get our grid data, so we can reuse it later
				'.$containerObj.'.wins.layout.B.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_downloadable_files',array('id'=>$id)).'";
				
				'.$containerObj.'.wins.layout.B.grid.obj.attachEvent("onXLS", function(grid_obj){
					ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
				}); 
				
				'.$containerObj.'.wins.layout.B.grid.obj.attachEvent("onXLE", function(grid_obj,count){
					ajaxOverlay(grid_obj.entBox.id,0);
				});			
				
				'.$containerObj.'.wins.layout.B.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
					'.$containerObj.'.wins2 = new Object();
					'.$containerObj.'.wins2.obj = '.$containerObj.'.dhxWins.createWindow("resetDownloadableFileWindow", 0, 0, 600, 320);
					'.$containerObj.'.wins2.obj.setText(this.cellById(rId,0).getValue());
					'.$containerObj.'.wins2.obj.button("park").hide();
					'.$containerObj.'.wins2.obj.button("minmax1").hide();
					'.$containerObj.'.wins2.obj.keepInViewport(true);
					'.$containerObj.'.wins.obj.setModal(false);
					'.$containerObj.'.wins2.obj.setModal(true);
					'.$containerObj.'.wins2.obj.denyResize();
					'.$containerObj.'.wins2.obj.center();		
					var pos = '.$containerObj.'.wins2.obj.getPosition();
					'.$containerObj.'.wins2.obj.setPosition(pos[0],20);		
				
					'.$containerObj.'.wins2.layout = new Object();
					'.$containerObj.'.wins2.layout.obj = '.$containerObj.'.wins2.obj.attachLayout("1C");				
					
					'.$containerObj.'.wins2.layout.A = new Object();
					'.$containerObj.'.wins2.layout.A.obj = '.$containerObj.'.wins2.layout.obj.cells("a");							
					'.$containerObj.'.wins2.layout.A.obj.hideHeader();		
					
					$.ajax({
						url: "'.CController::createUrl('reset_downloadable_file_options',array('container'=>$containerObj)).'&id="+rId,
						success: function(data){
							'.$containerObj.'.wins2.layout.A.obj.attachHTMLString(data);		
						}
					});		
					
					'.$containerObj.'.wins2.toolbar = new Object();
					'.$containerObj.'.wins2.toolbar.obj = '.$containerObj.'.wins2.obj.attachToolbar();
					'.$containerObj.'.wins2.toolbar.obj.setIconsPath(dhx_globalImgPath);	
					'.$containerObj.'.wins2.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
					'.$containerObj.'.wins2.toolbar.obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
					
					'.$containerObj.'.wins2.toolbar.obj.attachEvent("onClick",function(id){
						switch (id) {
							case "save":	
								'.$containerObj.'.wins2.toolbar.save(id);
								break;
							case "save_close":
								'.$containerObj.'.wins2.toolbar.save(id,1);
								break;			
						}
					});		
					
					'.$containerObj.'.wins2.toolbar.save = function(id,close){
						var obj = '.$containerObj.'.wins2.toolbar.obj;
						
						$.ajax({
							url: "'.CController::createUrl('save_downloadable_file').'",
							type: "POST",
							data: $("#"+'.$containerObj.'.wins2.layout.obj.cont.obj.id+" *").serialize(),
							dataType: "json",
							beforeSend: function(){			
								// clear all errors					
								$("#"+'.$containerObj.'.wins2.layout.obj.cont.obj.id+" span.error").html("");
								$("#"+'.$containerObj.'.wins2.layout.obj.cont.obj.id+" *").removeClass("error");
							
								obj.disableItem(id);			
							},
							complete: function(jqXHR, textStatus){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
	
								if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$containerObj.'.wins2.obj.close();
							},
							success: function(data){						
								// Remove class error to the background of the main div
								$(".div_'.$containerObj.'").removeClass("error_background");
								if (data) {
									if (data.errors) {
										$.each(data.errors, function(key, value){
											var id_tag_container = "'.$containerObj.'_"+key;
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
										// Apply class error to the background of the main div
										$(".div_'.$containerObj.'").addClass("error_background");																														
									} else {		
										alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
									}
								} else {
									alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
								}
							}
						});	
					}				
					
					// clean variables
					'.$containerObj.'.wins2.obj.attachEvent("onClose",function(win){
						this.setModal(false);
						'.$containerObj.'.wins2 = new Object();						
						'.$containerObj.'.wins.obj.setModal(true);
						
						return true;
					});	
				});				
				
				load_grid('.$containerObj.'.wins.layout.B.grid.obj);								
				
				// clean variables
				'.$containerObj.'.wins.obj.attachEvent("onClose",function(win){
					'.$containerObj.'.wins = new Object();						
					
					return true;
				});								
				
				break;
			case "print":
				window.open("'.CController::createUrl('edit_info_print',array('id'=>$id)).'","_blank");
				break;			
		}
	});	
};

'.$containerObj.'.layout.toolbar.obj = '.$containerObj.'.layout.obj.attachToolbar();
'.$containerObj.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.toolbar.load('.$id.');

$.ajax({
	url: "'.CController::createUrl('edit_info_options',array('container'=>$containerObj,'containerLayout'=>$containerLayout,'id'=>$id)).'",
	type: "POST",
	beforeSend: function(){
		'.$containerJS.'.layout.A.dataview.ajaxRequests++;
	},	
	success: function(data){
		'.$containerJS.'.layout.A.dataview.ajaxComplete();
		'.$containerObj.'.layout.A.obj.attachHTMLString(data);		
	}
});

$(window).resize(function(){
	setTimeout("'.$containerObj.'.layout.obj.setSizes()",500);
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<form id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></form>