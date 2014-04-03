<?php 
$model_name = get_class($model);

$app = Yii::app();

$help_hint_path = '/catalog/products/shipping/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_HEAVY_WEIGHT');?></strong><br />
        <em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_HEAVY_WEIGHT_DESCRIPTION');?></em>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[heavy_weight]',$model->heavy_weight?1:0,array('value'=>1,'id'=>$container.'_heavy_weight_1')).'&nbsp;<label for="'.$container.'_heavy_weight_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[heavy_weight]',!$model->heavy_weight?1:0,array('value'=>0,'id'=>$container.'_heavy_weight_0')).'&nbsp;<label for="'.$container.'_heavy_weight_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?> <br /><span id="<?php echo $container; ?>_heavy_weight_errorMsg" class="error"></span>
        </div>          
    </div> 
    
    <hr />
    
    <div id="<?php echo $container.'_display_all_shipping';?>"<?php echo $model->heavy_weight?' style="display:none;"':'';?>>
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_USE_SHIPPING_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'use-shipping-price'); ?><br />
        <em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_USE_SHIPPING_PRICE_DESCRIPTION');?></em>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[use_shipping_price]',$model->use_shipping_price?1:0,array('value'=>1,'id'=>$container.'_use_shipping_price_1')).'&nbsp;<label for="'.$container.'_use_shipping_price_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[use_shipping_price]',!$model->use_shipping_price?1:0,array('value'=>0,'id'=>$container.'_use_shipping_price_0')).'&nbsp;<label for="'.$container.'_use_shipping_price_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?> <br /><span id="<?php echo $container; ?>_use_shipping_price_errorMsg" class="error"></span>
        </div>          
    </div> 
     <div id="<?php echo $container;?>_use_weight">
        <strong><?php echo Yii::t('global','LABEL_WEIGHT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'weight'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'weight',array('size'=>5, 'id'=>$container.'_weight','onkeyup'=>'rewrite_number($(this).attr("id"));'));
		switch($app->params['weighing_unit']){
			case '0':
				$weighing_unit = Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT_LB');
				break;
			case '1':
				$weighing_unit = Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT_KG');
				break;	
		}
		echo '&nbsp;<span style="font-size:14px;">' . $weighing_unit . '</span>';
        ?>
        <br /><span id="<?php echo $container; ?>_weight_errorMsg" class="error"></span>
        </div>                
	</div>  
    
     <div id="<?php echo $container;?>_use_measurements">
     <div style="font-style:italic;"><?php echo Yii::t('views/products/edit_shipping_options','LABEL_MEASUREMENT_DESCRIPTION');?></div>
     	<?php
		switch($app->params['weighing_unit']){
			case '0':
				$measuring_unit = Yii::t('views/config/edit_shipping_options','LABEL_MEASUREMENT_UNIT_IN');
				break;
			case '1':
				$measuring_unit = Yii::t('views/config/edit_shipping_options','LABEL_MEASUREMENT_UNIT_CM');
				break;	
		}		
     	?>
     	<div style="float:left; margin-right:10px;">
            <strong><?php echo Yii::t('global','LABEL_LENGTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'length'); ?><br />
            <div>
            <?php
            echo CHtml::activeTextField($model,'length',array('size'=>5, 'id'=>$container.'_length','onkeyup'=>'rewrite_number($(this).attr("id"));'));

            echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
            ?>
            <br /><span id="<?php echo $container; ?>_length_label_errorMsg" class="error"></span>
            </div>                
		</div>            
        
     	<div style="float:left; margin-right:10px;">
            <strong><?php echo Yii::t('global','LABEL_WIDTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'width'); ?><br />
            <div>
            <?php
            echo CHtml::activeTextField($model,'width',array('size'=>5, 'id'=>$container.'_width','onkeyup'=>'rewrite_number($(this).attr("id"));'));
            
            echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
            ?>
            <br /><span id="<?php echo $container; ?>_width_label_errorMsg" class="error"></span>
            </div>                
		</div>            
        
     	<div style="float:left; margin-right:10px;">
            <strong><?php echo Yii::t('global','LABEL_HEIGHT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'height'); ?><br />
            <div>
            <?php
            echo CHtml::activeTextField($model,'height',array('size'=>5, 'id'=>$container.'_height','onkeyup'=>'rewrite_number($(this).attr("id"));'));
            
            echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
            ?>
            <br /><span id="<?php echo $container; ?>_height_label_errorMsg" class="error"></span>
            </div>                
		</div>           
        
        <div style="clear:both;"></div>
	</div>     
    
    <?php
    //Verify if shipping gateway provide extra care
	$extra_care_on = 0;
	if(Tbl_ShippingGateway::model()->find('provides_extra_care = 1 AND active = 1')){
		$extra_care_on = 1;
	?>
    <div id="<?php echo $container;?>_extra_care">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_EXTRA_CARE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'extra-care'); ?><br />
        <em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_EXTRA_CARE_DESCRIPTION');?></em>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[extra_care]',$model->extra_care?1:0,array('value'=>1,'id'=>$container.'_extra_care_1')).'&nbsp;<label for="'.$container.'_extra_care_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[extra_care]',!$model->extra_care?1:0,array('value'=>0,'id'=>$container.'_extra_care_0')).'&nbsp;<label for="'.$container.'_extra_care_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?> <br /><span id="<?php echo $container; ?>_extra_care_errorMsg" class="error"></span>
        </div>          
    </div>
    <?php
	}
	?>
    

      
    <div id="<?php echo $container;?>_use_shipping_price">      
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_SHIPPING_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipping-price'); ?><br />
       <div style="width:550px">
            <div id="<?php echo $container;?>_my_toolbar_here"></div>
            <div id="<?php echo $container;?>_product_price_shipping_region" style="height:150px;"></div>
            <div id="<?php echo $container;?>_recinfoArea"></div>
        </div>                
	</div> 
    </div>
    <div class="row" style="margin-top:10px;"> <strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ENABLE_LOCAL_PICKUP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-local-pickup'); ?><br />
        <em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_ENABLE_LOCAL_PICKUP_DESCRIPTION');?></em>
        <div>
            <?php
            echo CHtml::radioButton($model_name.'[enable_local_pickup]',($model->enable_local_pickup==1)?1:0,array('value'=>1,'id'=>'enable_local_pickup_1')).'&nbsp;<label for="enable_local_pickup_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[enable_local_pickup]',($model->enable_local_pickup==0)?1:0,array('value'=>0,'id'=>'enable_local_pickup_0')).'&nbsp;<label for="enable_local_pickup_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[enable_local_pickup]',($model->enable_local_pickup==-1)?1:0,array('value'=>-1,'id'=>'enable_local_pickup_2')).'&nbsp;<label for="enable_local_pickup_2" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_GENERAL_CONFIGURATION').'</label>'; 
            ?>
        </div>                 
    </div>
    
    
    
    
    
    
    
      <div class="row" style="margin-top:10px;"><hr /></div>

                  <div style="float:left">
                      <div style="width:350px">
                          <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_SHIP_ONLY_REGION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'ship-only-to'); ?><br /><em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_SHIP_ONLY_REGION_DESCRIPTION');?></em></div>
                          <div style="clear:both"></div>
                          <div id="<?php echo $container;?>_my_toolbar_here_ship_only"></div>
                          <div id="<?php echo $container;?>_ship_only_region" style="height:150px;"></div> 
                          <div id="<?php echo $container;?>_recinfoArea_ship_only"></div>
                      </div>
                  </div>
                  <div style="float:left; margin-left:20px;">
                      <div style="width:350px">
                          <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'do-not-ship-to'); ?><br /><em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION_DESCRIPTION');?></em></div>
                          <div style="clear:both"></div>
                          <div id="<?php echo $container;?>_my_toolbar_here_not_ship"></div>
                          <div id="<?php echo $container;?>_not_ship_region" style="height:150px;"></div> 
                          <div id="<?php echo $container;?>_recinfoArea_not_ship"></div>
                      </div>
                  </div>
                  <div style="clear:both"></div>
    
    
    
</div>     

     
</div>
</div>

<?php
$script = '
$(function(){
	
	
	$("#'.$container.'_use_shipping_price_0").click(function(){
		'.$container.'_change_shipping_price();
	});
	
	$("#'.$container.'_use_shipping_price_1").click(function(){
		'.$container.'_change_shipping_price();
	});	
	
	
	$("#'.$container.'_heavy_weight_0").click(function(){
		$("#'.$container.'_display_all_shipping").show();
	});
	
	$("#'.$container.'_heavy_weight_1").click(function(){
		$("#'.$container.'_display_all_shipping").hide();
	});	
	
});	

function '.$container.'_change_shipping_price(){
	//Must verify after the grid is load else, it dont show
	if(('.$model->use_shipping_price.' && $("#'.$container.'_use_shipping_price_1").is(":checked")) || $("#'.$container.'_use_shipping_price_1").is(":checked")){
		$("#'.$container.'_use_shipping_price").show();
		$("#'.$container.'_use_weight").hide();
		$("#'.$container.'_use_measurements").hide();
		'.($extra_care_on?'$("#'.$container.'_extra_care").hide();':'').'
	}else{
		$("#'.$container.'_use_shipping_price").hide();
		$("#'.$container.'_use_weight").show();
		$("#'.$container.'_use_measurements").show();
		'.($extra_care_on?'$("#'.$container.'_extra_care").show();':'').'
	}
}


var '.$container.'_dhxWins = new dhtmlXWindows();
'.$container.'_dhxWins.enableAutoViewport(false);
'.$container.'_dhxWins.attachViewportTo("'.$containerLayout.'");
'.$container.'_dhxWins.setImagePath(dhx_globalImgPath);

//Free Shipping Region Grid
var '.$container.'_grid_regions = new Object();
'.$container.'_grid_regions.obj = new dhtmlXGridObject("'.$container.'_product_price_shipping_region");

'.$container.'_grid_regions.obj.selMultiRows = true;
'.$container.'_grid_regions.obj.setImagePath(dhx_globalImgPath);		
'.$container.'_grid_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,,"text-align:right;"]);

'.$container.'_grid_regions.obj.setInitWidthsP("10,35,35,20");
'.$container.'_grid_regions.obj.setColAlign("center,left,left,right");
'.$container.'_grid_regions.obj.setColSorting("na,na,na,na");
'.$container.'_grid_regions.obj.enableResizing("false,true,true,true");
'.$container.'_grid_regions.obj.setSkin(dhx_skin);
'.$container.'_grid_regions.obj.enableDragAndDrop(false);
'.$container.'_grid_regions.obj.enableMultiselect(false);
'.$container.'_grid_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
'.$container.'_grid_regions.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$container.'_grid_regions.obj.enablePaging(true, 100, 3, "'.$container.'_recinfoArea");
'.$container.'_grid_regions.obj.setPagingSkin("toolbar", dhx_skin);
'.$container.'_grid_regions.obj.i18n.paging={
			  results:"'.Yii::t('global','LABEL_GRID_PAGING_RESULTS').'",
			  records:"'.Yii::t('global','LABEL_GRID_PAGING_RECORDS_FROM').'",
			  to:"'.Yii::t('global','LABEL_GRID_PAGING_TO').'",
			  page:"'.Yii::t('global','LABEL_GRID_PAGING_PAGE').'",
			  perpage:"'.Yii::t('global','LABEL_GRID_PAGING_ROWS_PER_PAGE').'",
			  first:"'.Yii::t('global','LABEL_GRID_PAGING_TO_FIRST_PAGE').'",
			  previous:"'.Yii::t('global','LABEL_GRID_PAGING_PREVIOUS_PAGE').'",
			  found:"'.Yii::t('global','LABEL_GRID_PAGING_FOUND_RECORDS').'",
			  next:"'.Yii::t('global','LABEL_GRID_PAGING_NEXT_PAGE').'",
			  last:"'.Yii::t('global','LABEL_GRID_PAGING_TO_LAST_PAGE').'",
			  of:"'.Yii::t('global','LABEL_GRID_PAGING_OF').'",
			  notfound:"'.Yii::t('global','LABEL_GRID_PAGING_NO_RECORDS_FOUND').'" }

'.$container.'_grid_regions.obj.init();

'.$container.'_grid_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_price_shipping',array("id_product"=>$model->id)).'";
'.$container.'_grid_regions.obj.loadXML('.$container.'_grid_regions.obj.xmlOrigFileUrl);

'.$container.'_grid_regions.toolbar = new Object();
'.$container.'_grid_regions.toolbar.obj = new dhtmlXToolbarObject("'.$container.'_my_toolbar_here");
'.$container.'_grid_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$container.'_grid_regions.toolbar.obj.addButton("add", null, "'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$container.'_grid_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$container.'_grid_regions.toolbar.obj.addSeparator("sep1", null);
'.$container.'_grid_regions.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$container.'_grid_regions.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$container.'_grid_regions.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$container.'_grid_regions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/products/edit_shipping_options','LABEL_PRICE_SHIPPING_REGION').'";

	switch (id) {
		case "add":
			'.$container.'_grid_regions.toolbar.add();
			break;
		case "delete":			
			var checked = '.$container.'_grid_regions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_ALERT_DELETE_REGION').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					$.ajax({
						url: "'.CController::createUrl('delete_region').'",
						type: "POST",
						data: ids.join("&")+"&id_product='.$model->id.'",
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid('.$container.'_grid_regions.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_ALERT_NO_REGION_CHECKED').'");	
			}
			break;
		case "export_pdf":
			printGridPopup('.$container.'_grid_regions.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup('.$container.'_grid_regions.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup('.$container.'_grid_regions.obj,"printview",[0],title);
			break;				
	}
});
'.$container.'_grid_regions.toolbar.add = function(current_id){
	
	var '.$container.'_wins = new Object();
	if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+'.$container.'_grid_regions.obj.cellById(current_id,1).getValue()+" - "+'.$container.'_grid_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('global','LABEL_BTN_ADD').'";} 
	
	'.$container.'_wins.obj = '.$container.'_dhxWins.createWindow("editRegionWindow", 10, 10, 500, 260);
	'.$container.'_wins.obj.setText(name);
	'.$container.'_wins.obj.button("park").hide();
	'.$container.'_wins.obj.keepInViewport(true);
	'.$container.'_wins.obj.setModal(true);
				
	'.$container.'_wins.toolbar = new Object();
	
	'.$container.'_wins.toolbar.load = function(current_id){
		
		var obj = '.$container.'_wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 

		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			
			switch (id) {
				case "save":	
					'.$container.'_wins.toolbar.save(id);
					break;
				case "save_close":
					'.$container.'_wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_ALERT_DELETE_REGION').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_region').'",
							type: "POST",
							data: { "ids[]":current_id, "id_product":'.$model->id.' },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								'.$container.'_wins.obj.close();
							},
							success: function(data){
								load_grid('.$container.'_grid_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	'.$container.'_wins.toolbar.obj = '.$container.'_wins.obj.attachToolbar();
	'.$container.'_wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$container.'_wins.toolbar.load(current_id);
	
	'.$container.'_wins.grid_regions = new Object();
	'.$container.'_wins.grid_regions.obj = '.$container.'_wins.obj.attachLayout("1C");
	'.$container.'_wins.grid_regions.A = new Object();
	'.$container.'_wins.grid_regions.A.obj = '.$container.'_wins.grid_regions.obj.cells("a");
	'.$container.'_wins.grid_regions.A.obj.hideHeader();	
	
	$.ajax({
		url: "'.CController::createUrl('edit_regions_options',array('container'=>$container)).'",
		type: "POST",
		data: { "id":current_id, "id_product":'.$model->id.' },
		success: function(data){
			'.$container.'_wins.grid_regions.A.obj.attachHTMLString(data);		
		}
	});	
			
	
	// clean variables
	'.$container.'_wins.obj.attachEvent("onClose",function(win){
		'.$container.'_wins = new Object();
		load_grid('.$container.'_grid_regions.obj);
		return true;
	});			
	
	
	'.$container.'_wins.toolbar.save = function(id,close){
		var obj = '.$container.'_wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_regions_options',array()).'",
			type: "POST",
			data: $("#"+'.$container.'_wins.grid_regions.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$container.'_wins.grid_regions.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$container.'_wins.grid_regions.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$container.'_wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_popup_'.$container.'").removeClass("error_background");
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
						// Apply class error to the background of the main div
						$(".div_popup_'.$container.'").addClass("error_background");																															
					} else {					
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var nom_edit = $("#'.$container.'_country_code option:selected").text();
							nom_edit = nom_edit + " - " + $("#'.$container.'_state_code option:selected").text();

							$("#'.$container.'_popup_id").val(data.id);
							'.$container.'_wins.toolbar.load(data.id);
							'.$container.'_wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
						}else{
							'.$container.'_grid_regions.obj.selectRowById(data.id);
						}
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}	
}

'.$container.'_grid_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$container.'_grid_regions.toolbar.add(rId);
});
'.$container.'_grid_regions.obj.attachEvent("onXLE", function(grid_obj,count){
	'.$container.'_change_shipping_price();
});
			
			
	
	
	
	
//Ship Only
var '.$container.'_grid_ship_only_regions = new Object();
'.$container.'_grid_ship_only_regions.obj = new dhtmlXGridObject("'.$container.'_ship_only_region");

'.$container.'_grid_ship_only_regions.obj.selMultiRows = true;
'.$container.'_grid_ship_only_regions.obj.setImagePath(dhx_globalImgPath);		
'.$container.'_grid_ship_only_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);

'.$container.'_grid_ship_only_regions.obj.setInitWidths("40,154,154");
'.$container.'_grid_ship_only_regions.obj.setColAlign("center,left,left");
'.$container.'_grid_ship_only_regions.obj.setColSorting("na,na,na");
'.$container.'_grid_ship_only_regions.obj.enableResizing("false,true,true");
'.$container.'_grid_ship_only_regions.obj.setSkin(dhx_skin);
'.$container.'_grid_ship_only_regions.obj.enableDragAndDrop(false);
'.$container.'_grid_ship_only_regions.obj.enableMultiselect(false);
'.$container.'_grid_ship_only_regions.obj.enableAutoWidth(true,348,348);
'.$container.'_grid_ship_only_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
'.$container.'_grid_ship_only_regions.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$container.'_grid_ship_only_regions.obj.enablePaging(true, 100, 3, "'.$container.'_recinfoArea_ship_only");
'.$container.'_grid_ship_only_regions.obj.setPagingSkin("toolbar", dhx_skin);
'.$container.'_grid_ship_only_regions.obj.i18n.paging={
		results:"'.Yii::t('global','LABEL_GRID_PAGING_RESULTS').'",
		records:"'.Yii::t('global','LABEL_GRID_PAGING_RECORDS_FROM').'",
		to:"'.Yii::t('global','LABEL_GRID_PAGING_TO').'",
		page:"'.Yii::t('global','LABEL_GRID_PAGING_PAGE').'",
		perpage:"'.Yii::t('global','LABEL_GRID_PAGING_ROWS_PER_PAGE').'",
		first:"'.Yii::t('global','LABEL_GRID_PAGING_TO_FIRST_PAGE').'",
		previous:"'.Yii::t('global','LABEL_GRID_PAGING_PREVIOUS_PAGE').'",
		found:"'.Yii::t('global','LABEL_GRID_PAGING_FOUND_RECORDS').'",
		next:"'.Yii::t('global','LABEL_GRID_PAGING_NEXT_PAGE').'",
		last:"'.Yii::t('global','LABEL_GRID_PAGING_TO_LAST_PAGE').'",
		of:"'.Yii::t('global','LABEL_GRID_PAGING_OF').'",
		notfound:"'.Yii::t('global','LABEL_GRID_PAGING_NO_RECORDS_FOUND').'" }

'.$container.'_grid_ship_only_regions.obj.init();

'.$container.'_grid_ship_only_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_ship_only_region',array("id_product"=>$model->id)).'";
'.$container.'_grid_ship_only_regions.obj.loadXML('.$container.'_grid_ship_only_regions.obj.xmlOrigFileUrl);

'.$container.'_grid_ship_only_regions.toolbar = new Object();
'.$container.'_grid_ship_only_regions.toolbar.obj = new dhtmlXToolbarObject("'.$container.'_my_toolbar_here_ship_only");
'.$container.'_grid_ship_only_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$container.'_grid_ship_only_regions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$container.'_grid_ship_only_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$container.'_grid_ship_only_regions.toolbar.obj.addSeparator("sep1", null);
'.$container.'_grid_ship_only_regions.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$container.'_grid_ship_only_regions.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$container.'_grid_ship_only_regions.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$container.'_grid_ship_only_regions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_TITLE_FREE_SHIPPING_REGIONS').'";

	switch (id) {
		case "add":
			
			'.$container.'_grid_ship_only_regions.toolbar.add();
			break;
		case "delete":			
			var checked = '.$container.'_grid_ship_only_regions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_ship_only_region').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid('.$container.'_grid_ship_only_regions.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "export_pdf":
			printGridPopup('.$container.'_grid_ship_only_regions.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup('.$container.'_grid_ship_only_regions.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup('.$container.'_grid_ship_only_regions.obj,"printview",[0],title);
			break;				
	}
});
'.$container.'_grid_ship_only_regions.toolbar.add = function(current_id){
	var '.$container.'_wins = new Object();
	if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+'.$container.'_grid_ship_only_regions.obj.cellById(current_id,1).getValue()+" - "+'.$container.'_grid_ship_only_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('views/config/edit_shipping_options','LABEL_ADD').'";} 
	
	'.$container.'_wins.obj = '.$container.'_dhxWins.createWindow("editRegionWindow", 10, 10, 500, 200);
	'.$container.'_wins.obj.setText(name);
	'.$container.'_wins.obj.button("park").hide();
	'.$container.'_wins.obj.keepInViewport(true);
	'.$container.'_wins.obj.setModal(true);
	//'.$container.'_wins.obj.center();
				
	'.$container.'_wins.toolbar = new Object();
	
	'.$container.'_wins.toolbar.load = function(current_id){
		var obj = '.$container.'_wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 

		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$container.'_wins.toolbar.save(id);
					break;
				case "save_close":
					'.$container.'_wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_ship_only_region').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								'.$container.'_wins.obj.close();
							},
							success: function(data){
								load_grid('.$container.'_grid_ship_only_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	'.$container.'_wins.toolbar.obj = '.$container.'_wins.obj.attachToolbar();
	'.$container.'_wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$container.'_wins.toolbar.load(current_id);
	
	'.$container.'_wins.'.$container.'_grid_ship_only_regions = new Object();
	'.$container.'_wins.'.$container.'_grid_ship_only_regions.obj = '.$container.'_wins.obj.attachLayout("1C");
	'.$container.'_wins.'.$container.'_grid_ship_only_regions.A = new Object();
	'.$container.'_wins.'.$container.'_grid_ship_only_regions.A.obj = '.$container.'_wins.'.$container.'_grid_ship_only_regions.obj.cells("a");
	'.$container.'_wins.'.$container.'_grid_ship_only_regions.A.obj.hideHeader();	
	
	$.ajax({
		url: "'.CController::createUrl('edit_ship_only_region',array('container'=>$container)).'",
		type: "POST",
		data: { "id":current_id, "id_product":'.$model->id.'  },
		success: function(data){
			'.$container.'_wins.'.$container.'_grid_ship_only_regions.A.obj.attachHTMLString(data);		
		}
	});	
	
	// clean variables
	'.$container.'_wins.obj.attachEvent("onClose",function(win){
		'.$container.'_wins = new Object();
		
		return true;
	});			
	
	
	'.$container.'_wins.toolbar.save = function(id,close){
		var obj = '.$container.'_wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_ship_only_region',array()).'",
			type: "POST",
			data: $("#"+'.$container.'_wins.'.$container.'_grid_ship_only_regions.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$container.'_wins.'.$container.'_grid_ship_only_regions.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$container.'_wins.'.$container.'_grid_ship_only_regions.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$container.'_wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_popup_'.$container.'").removeClass("error_background");
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
						// Apply class error to the background of the main div
						$(".div_popup_'.$container.'").addClass("error_background");																															
					} else {					
						load_grid('.$container.'_grid_ship_only_regions.obj);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var nom_edit = $("#'.$container.'_country_code option:selected").text();
							nom_edit = nom_edit + " - " + $("#'.$container.'_state_code option:selected").text();

							$("#'.$container.'_popup_id").val(data.id);
							'.$container.'_wins.toolbar.load(data.id);
							'.$container.'_wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
						}else{
							'.$container.'_grid_ship_only_regions.obj.selectRowById(data.id);
						}
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

'.$container.'_grid_ship_only_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$container.'_grid_ship_only_regions.toolbar.add(rId);
});




//Do Not Ship
var '.$container.'_grid_do_not_ship_regions = new Object();
'.$container.'_grid_do_not_ship_regions.obj = new dhtmlXGridObject("'.$container.'_not_ship_region");

'.$container.'_grid_do_not_ship_regions.obj.selMultiRows = true;
'.$container.'_grid_do_not_ship_regions.obj.setImagePath(dhx_globalImgPath);		
'.$container.'_grid_do_not_ship_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);

'.$container.'_grid_do_not_ship_regions.obj.setInitWidths("40,154,154");
'.$container.'_grid_do_not_ship_regions.obj.setColAlign("center,left,left");
'.$container.'_grid_do_not_ship_regions.obj.setColSorting("na,na,na");
'.$container.'_grid_do_not_ship_regions.obj.enableResizing("false,true,true");
'.$container.'_grid_do_not_ship_regions.obj.setSkin(dhx_skin);
'.$container.'_grid_do_not_ship_regions.obj.enableDragAndDrop(false);
'.$container.'_grid_do_not_ship_regions.obj.enableMultiselect(false);
'.$container.'_grid_do_not_ship_regions.obj.enableAutoWidth(true,348,348);
'.$container.'_grid_do_not_ship_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
'.$container.'_grid_do_not_ship_regions.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
'.$container.'_grid_do_not_ship_regions.obj.enablePaging(true, 100, 3, "'.$container.'_recinfoArea_not_ship");
'.$container.'_grid_do_not_ship_regions.obj.setPagingSkin("toolbar", dhx_skin);
'.$container.'_grid_do_not_ship_regions.obj.i18n.paging={
		results:"'.Yii::t('global','LABEL_GRID_PAGING_RESULTS').'",
		records:"'.Yii::t('global','LABEL_GRID_PAGING_RECORDS_FROM').'",
		to:"'.Yii::t('global','LABEL_GRID_PAGING_TO').'",
		page:"'.Yii::t('global','LABEL_GRID_PAGING_PAGE').'",
		perpage:"'.Yii::t('global','LABEL_GRID_PAGING_ROWS_PER_PAGE').'",
		first:"'.Yii::t('global','LABEL_GRID_PAGING_TO_FIRST_PAGE').'",
		previous:"'.Yii::t('global','LABEL_GRID_PAGING_PREVIOUS_PAGE').'",
		found:"'.Yii::t('global','LABEL_GRID_PAGING_FOUND_RECORDS').'",
		next:"'.Yii::t('global','LABEL_GRID_PAGING_NEXT_PAGE').'",
		last:"'.Yii::t('global','LABEL_GRID_PAGING_TO_LAST_PAGE').'",
		of:"'.Yii::t('global','LABEL_GRID_PAGING_OF').'",
		notfound:"'.Yii::t('global','LABEL_GRID_PAGING_NO_RECORDS_FOUND').'" }

'.$container.'_grid_do_not_ship_regions.obj.init();

'.$container.'_grid_do_not_ship_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_do_not_ship_region',array("id_product"=>$model->id)).'";
'.$container.'_grid_do_not_ship_regions.obj.loadXML('.$container.'_grid_do_not_ship_regions.obj.xmlOrigFileUrl);

'.$container.'_grid_do_not_ship_regions.toolbar = new Object();
'.$container.'_grid_do_not_ship_regions.toolbar.obj = new dhtmlXToolbarObject("'.$container.'_my_toolbar_here_not_ship");
'.$container.'_grid_do_not_ship_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$container.'_grid_do_not_ship_regions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
'.$container.'_grid_do_not_ship_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
'.$container.'_grid_do_not_ship_regions.toolbar.obj.addSeparator("sep1", null);
'.$container.'_grid_do_not_ship_regions.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
'.$container.'_grid_do_not_ship_regions.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
'.$container.'_grid_do_not_ship_regions.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

'.$container.'_grid_do_not_ship_regions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_TITLE_FREE_SHIPPING_REGIONS').'";

	switch (id) {
		case "add":
			
			'.$container.'_grid_do_not_ship_regions.toolbar.add();
			break;
		case "delete":			
			var checked = '.$container.'_grid_do_not_ship_regions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_do_not_ship_region').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid('.$container.'_grid_do_not_ship_regions.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "export_pdf":
			printGridPopup('.$container.'_grid_do_not_ship_regions.obj,"pdf",[0],title);
			break;	
		case "export_excel":
			printGridPopup('.$container.'_grid_do_not_ship_regions.obj,"excel",[0],title);
			break;		
		case "print":
			printGridPopup('.$container.'_grid_do_not_ship_regions.obj,"printview",[0],title);
			break;				
	}
});
'.$container.'_grid_do_not_ship_regions.toolbar.add = function(current_id){
	var '.$container.'_wins = new Object();
	if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+'.$container.'_grid_do_not_ship_regions.obj.cellById(current_id,1).getValue()+" - "+'.$container.'_grid_do_not_ship_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('views/config/edit_shipping_options','LABEL_ADD').'";} 
	
	'.$container.'_wins.obj = '.$container.'_dhxWins.createWindow("editRegionWindow", 10, 10, 500, 200);
	'.$container.'_wins.obj.setText(name);
	'.$container.'_wins.obj.button("park").hide();
	'.$container.'_wins.obj.keepInViewport(true);
	'.$container.'_wins.obj.setModal(true);
	//'.$container.'_wins.obj.center();
				
	'.$container.'_wins.toolbar = new Object();
	
	'.$container.'_wins.toolbar.load = function(current_id){
		var obj = '.$container.'_wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 

		
		if (current_id) {
			obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
		}
	
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					'.$container.'_wins.toolbar.save(id);
					break;
				case "save_close":
					'.$container.'_wins.toolbar.save(id,1);
					break;
				case "delete":
					if (confirm("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_DELETE').'")) {
						$.ajax({
							url: "'.CController::createUrl('delete_do_not_ship_region').'",
							type: "POST",
							data: { "ids[]":current_id },
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								if (typeof obj.enableItem == "function") obj.enableItem(id);
								'.$container.'_wins.obj.close();
							},
							success: function(data){
								load_grid('.$container.'_grid_do_not_ship_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});					
					}			
					break;
			}
		});	
	}	
	
	'.$container.'_wins.toolbar.obj = '.$container.'_wins.obj.attachToolbar();
	'.$container.'_wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	'.$container.'_wins.toolbar.load(current_id);
	
	'.$container.'_wins.'.$container.'_grid_do_not_ship_regions = new Object();
	'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.obj = '.$container.'_wins.obj.attachLayout("1C");
	'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.A = new Object();
	'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.A.obj = '.$container.'_wins.'.$container.'_grid_do_not_ship_regions.obj.cells("a");
	'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.A.obj.hideHeader();	
	
	$.ajax({
		url: "'.CController::createUrl('edit_do_not_ship_region',array('container'=>$container)).'",
		type: "POST",
		data: { "id":current_id, "id_product":'.$model->id.'  },
		success: function(data){
			'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.A.obj.attachHTMLString(data);		
		}
	});	
	
	// clean variables
	'.$container.'_wins.obj.attachEvent("onClose",function(win){
		'.$container.'_wins = new Object();
		
		return true;
	});			
	
	
	'.$container.'_wins.toolbar.save = function(id,close){
		var obj = '.$container.'_wins.toolbar.obj;
		$.ajax({
			url: "'.CController::createUrl('save_do_not_ship_region',array()).'",
			type: "POST",
			data: $("#"+'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.obj.cont.obj.id+" *").serialize(),
			dataType: "json",
			beforeSend: function(){			
				// clear all errors					
				$("#"+'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.obj.cont.obj.id+" span.error").html("");
				$("#"+'.$container.'_wins.'.$container.'_grid_do_not_ship_regions.obj.cont.obj.id+" *").removeClass("error");
			
				obj.disableItem(id);			
			},
			complete: function(jqXHR, textStatus){
				if (typeof obj.enableItem == "function") obj.enableItem(id);
				
				if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$container.'_wins.obj.close();
			},
			success: function(data){						
				// Remove class error to the background of the main div
				$(".div_popup_'.$container.'").removeClass("error_background");
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
						// Apply class error to the background of the main div
						$(".div_popup_'.$container.'").addClass("error_background");																															
					} else {					
						load_grid('.$container.'_grid_do_not_ship_regions.obj);
						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
						
						// Verify if popup window is close
						if(!close){
							var nom_edit = $("#'.$container.'_country_code option:selected").text();
							nom_edit = nom_edit + " - " + $("#'.$container.'_state_code option:selected").text();

							$("#'.$container.'_popup_id").val(data.id);
							'.$container.'_wins.toolbar.load(data.id);
							'.$container.'_wins.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
						}else{
							'.$container.'_grid_do_not_ship_regions.obj.selectRowById(data.id);
						}
					}
				} else {
					alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
				}
			}
		});	
	}
}

'.$container.'_grid_do_not_ship_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	'.$container.'_grid_do_not_ship_regions.toolbar.add(rId);
});
		
';

echo Html::script($script); 
?>