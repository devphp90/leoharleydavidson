<?php
$containerJS = 'Tab'.$container;
$language = Yii::app()->language;

$script = '
var '.$containerJS.' = new Object();
// variables used to check modification
'.$containerJS.'.og_form = [];

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = tabs.obj.cells("'.$container.'");
'.$containerJS.'.layout.obj.attachObject("'.$container.'");

'.$containerJS.'.dhxWins = new dhtmlXWindows();
'.$containerJS.'.dhxWins.enableAutoViewport(false);
'.$containerJS.'.dhxWins.attachViewportTo("'.$container.'");
'.$containerJS.'.dhxWins.setImagePath(dhx_globalImgPath);	

'.$containerJS.'.wins_list = new Object();

'.$containerJS.'.layout.toolbar = new Object();

'.$containerJS.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
	obj.clearAll();	
	obj.detachAllEvents();
	obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
	obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif");    
	obj.addButton("save_email",null,"'.Yii::t('views/giftcertificates/edit','LABEL_BTN_SAVE_SEND_EMAIL').'","toolbar/mail.png","toolbar/mail_dis.png");   
	
	if (current_id) {
		obj.addButton("delete",null,"'.Yii::t('global','LABEL_BTN_DELETE').'","toolbar/delete.png","toolbar/delete-dis.png"); 
	}
	
	obj.attachEvent("onClick",function(id){
		switch (id) {
			case "save":	
				'.$containerJS.'.layout.toolbar.save(id,false,false);
				break;
			case "save_close":
				'.$containerJS.'.layout.toolbar.save(id,true,false);
				break;
			case "save_email":
				'.$containerJS.'.layout.toolbar.save(id,false,true);
				break;
			case "delete":
				if (confirm("'.Yii::t('views/giftcertificates/edit','LABEL_ALERT_DELETE').'")) {
					obj.disableItem(id);
					
					$.ajax({
						url: "'.CController::createUrl('delete').'",
						type: "POST",
						data: { "ids[]":current_id },
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);
						},						
						success: function(data){		
							'.$containerJS.'.og_form = [];
							'.$containerJS.'.load_og_form();
										
							tabs.close_tab(tabs.obj, "'.$container.'", true);
							load_grid(tabs.list.grid.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});					
				}
				break;	
		}
	});	
};

'.$containerJS.'.layout.toolbar.save = function(id,close,email){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
	$.ajax({
		url: "'.CController::createUrl('edit',array('container'=>$container)).'",
		type: "POST",
		data: $("#'.$container.'").serialize()+"&email="+email,
		dataType: "json",
		beforeSend: function(){		
			obj.disableItem(id);
		},
		complete: function(){
			if (typeof obj.enableItem == "function") obj.enableItem(id);
		},
		success: function(data){
			// clear all errors					
			$("#'.$container.' span.error").html("");
			$("#'.$container.' *").removeClass("error");
			tabs.obj.setCustomStyle("'.$container.'",null,null,null);
			// Remove class error to the background of the main div
			$(".div_'.$container.'").removeClass("error_background");
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
					$(".div_'.$container.'").addClass("error_background");
					tabs.obj.setCustomStyle("'.$container.'",null,null,"color:#FF0000;");
				} else {								
					// if name changed, rename
					if (tabs.obj.getLabel("'.$container.'") != $("#'.$container.'_code").val()) {
						tabs.obj.setLabel("'.$container.'",$("#'.$container.'_code").val());	
					}
										
							
								
					if (close){
						tabs.close_tab(tabs.obj,"'.$container.'",true);	
					}else {
						$("#'.$container.'_id").val(data.id);
						'.$containerJS.'.layout.toolbar.load(data.id);	
						
						// when we create a new product and save, store its id and container value in array of opened tabs
						tabs.totalOpened[data.id] = "'.$container.'";						
					}
					load_grid(tabs.list.grid.obj);
					if (email){
						alert("'.Yii::t('views/giftcertificates/edit','LABEL_ALERT_SENT_BY_EMAIL_SUCCESS').'");
					}else {
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");					
					}
					
					'.$containerJS.'.og_form = [];
					'.$containerJS.'.load_og_form();
					

				}
			} else {
				alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(jqXHR.responseText);
		}
	});
	
};

'.$containerJS.'.layout.toolbar.obj = '.$containerJS.'.layout.obj.attachToolbar();
'.$containerJS.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.toolbar.load('.$model->id.');


$(function(){
	

	$("#'.$container.'_id_button_select").click(function(){
		
			name = "'.Yii::t('global','LABEL_BTN_ADD_CUSTORMER').'";
			
		
			'.$containerJS.'.wins_list.obj = '.$containerJS.'.dhxWins.createWindow("addCustomerWindow", 10, 10, 600, 440);
			'.$containerJS.'.wins_list.obj.setText(name);
			'.$containerJS.'.wins_list.obj.button("park").hide();
			'.$containerJS.'.wins_list.obj.keepInViewport(true);
			'.$containerJS.'.wins_list.obj.setModal(true);
			//'.$containerJS.'.wins_list.obj.center();	
		
			'.$containerJS.'.wins_list.layout = new Object();
			'.$containerJS.'.wins_list.layout.obj = '.$containerJS.'.wins_list.obj.attachLayout("1C");
			'.$containerJS.'.wins_list.layout.A = new Object();
			'.$containerJS.'.wins_list.layout.A.obj = '.$containerJS.'.wins_list.layout.obj.cells("a");
			
			'.$containerJS.'.wins_list.layout.A.obj.hideHeader();
			
			'.$containerJS.'.wins_list.layout.A.grid = new Object();
			
			'.$containerJS.'.wins_list.layout.A.grid.obj = '.$containerJS.'.wins_list.layout.A.obj.attachGrid();
			'.$containerJS.'.wins_list.layout.A.grid.obj.setImagePath(dhx_globalImgPath);	
			'.$containerJS.'.wins_list.layout.A.grid.obj.setHeader("'.Yii::t('views/giftcertificates/edit','LABEL_LIST_GRID_NAME').','.Yii::t('views/giftcertificates/edit','LABEL_LIST_GRID_EMAIL').','.Yii::t('views/giftcertificates/edit','LABEL_LIST_GRID_PHONE').'");
			'.$containerJS.'.wins_list.layout.A.grid.obj.attachHeader("#text_filter_custom,,");
			
			// custom text filter input
			'.$containerJS.'.wins_list.layout.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
			
			'.$containerJS.'.wins_list.layout.A.grid.obj.setInitWidths("*,200,150");
			'.$containerJS.'.wins_list.layout.A.grid.obj.enableResizing("false,false,false");
			'.$containerJS.'.wins_list.layout.A.grid.obj.setColAlign("left,left,left");
			'.$containerJS.'.wins_list.layout.A.grid.obj.setColSorting("na,na,na");
			'.$containerJS.'.wins_list.layout.A.grid.obj.setSkin(dhx_skin);
			'.$containerJS.'.wins_list.layout.A.grid.obj.enableDragAndDrop(false);
			'.$containerJS.'.wins_list.layout.A.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
			
			
			
			//Paging
			'.$containerJS.'.wins_list.layout.A.obj.attachStatusBar().setText("<div id=\''.$containerJS.'_recinfoArea\'></div>");
			'.$containerJS.'.wins_list.layout.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
			'.$containerJS.'.wins_list.layout.A.grid.obj.enablePaging(true, 100, 3, "'.$containerJS.'_recinfoArea");
			'.$containerJS.'.wins_list.layout.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
			'.$containerJS.'.wins_list.layout.A.grid.obj.i18n.paging={
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
			'.$containerJS.'.wins_list.layout.A.grid.obj.init();
			
			
			// set filter input names
			'.$containerJS.'.wins_list.layout.A.grid.obj.hdr.rows[2].cells[0].getElementsByTagName("INPUT")[0].name="customer_name";
			
			// we create a variable to store the default url used to get our grid data, so we can reuse it later
			'.$containerJS.'.wins_list.layout.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_customer_add').'";
			
			// load the initial grid
			load_grid('.$containerJS.'.wins_list.layout.A.grid.obj);		
			
			'.$containerJS.'.wins_list.layout.A.grid.obj.attachEvent("onXLS", function(grid_obj){
				ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
			}); 
			
			'.$containerJS.'.wins_list.layout.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
				ajaxOverlay(grid_obj.entBox.id,0);
			});	
			
			'.$containerJS.'.wins_list.layout.A.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
				$.ajax({
					url: "'.CController::createUrl('add_customer').'",
					type: "POST",
					data: { "id":rId },
					dataType: "json",
					complete: function(){
						'.$containerJS.'.wins_list.obj.close();
					},
					success: function(data){	
						if (data.errors) {
							alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");
						}else{
							$("#'.$container.'_id_customer").val(data.id);
							$("#'.$container.'_customer_name").html("").append(data.info);
							'.$containerJS.'.load_og_form();
						}
					}
				});
			});
			
			// clean variables
			'.$containerJS.'.wins_list.obj.attachEvent("onClose",function(win){
				'.$containerJS.'.wins_list = new Object();
				return true;
			});	
	});
});

// load original form values
'.$containerJS.'.load_og_form = function()
{
	// layout b
	$("#'.$container.' [name]").each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) '.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			'.$containerJS.'.og_form[$(this).attr("name")] = $(this).val();	
		}	
	});	
};

// check if any modifications has been made
'.$containerJS.'.has_modifications = function()
{
	// check for modifications
	var str_array=[];

	// layout 
	$("#'.$container.' [name]").each(function(){
		if (($(this).is(":radio") || $(this).is(":checkbox")) && $(this).prop("checked")) {
			if ($(this).prop("checked")) str_array[$(this).attr("name")] = $(this).val();	
		} else if (!$(this).is(":radio") && !$(this).is(":checkbox")) { 
			str_array[$(this).attr("name")] = $(this).val();	
		}	
	});
	
	return (count(array_diff_assoc('.$containerJS.'.og_form,str_array)) || count(array_diff_assoc(str_array,'.$containerJS.'.og_form)) ? 1:0);
};

$(function(){ 
	'.$containerJS.'.load_og_form();
});

';

echo Html::script($script);

$help_hint_path = '/marketing/gift-certificates/';
?>

<!-- 
Using a form tag instead of a div for the layout container.
This way we can serialize all the form objects in the layout.
Also this way we do not need to add a prefix to all objects in the form, in case of multiple forms.
But we do need to add a prefix for each individual form object id's
-->
<?php 
$model_name = get_class($model);
$form=$this->beginWidget('CActiveForm',array(
	'id'=>$container,
	'htmlOptions'=>array('style'=>'width:100%; height:100%; padding:0; margin:0;'),
));
?>
<?php echo $form->hiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">
<div style="padding:10px;">	
	<div style="float:left; width:20%">
    <h3><?php echo Yii::t('views/giftcertificates/edit','LABEL_TITLE_INFO');?></h3>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
        ?>
        </div>        
    </div>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_CODE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'code'); ?>
        <div>
        <?php
        echo $form->textField($model,'code',array('style'=>'width:200px;', 'id'=>$container.'_code'));
        ?>          
        <br /><span id="<?php echo $container; ?>_code_errorMsg" class="error"></span>    
        </div>
	</div>        
	<div class="row">
        <strong><?php echo Yii::t('global','LABEL_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price'); ?>
        <div>
        <?php echo $form->textField($model,'price',array('style'=>'width:150px;', 'id'=>$container.'_price','onkeyup'=>'rewrite_number($(this).attr("id"));')); ?>         
        <br /><span id="<?php echo $container; ?>_price_errorMsg" class="error"></span>       
        </div>                
	</div>
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_CUSTOMER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'customer'); ?>
        <?php echo $form->hiddenField($model,'id_customer',array('id'=>$container.'_id_customer')); ?>
        <div style="margin-top:5px">
		<?php echo CHtml::htmlButton(Yii::t('global','LABEL_BTN_SELECT'),array('id'=>$container.'_id_button_select','class'=>'select_customer_button')); ?>  
        </div>      
        <div style="margin-top:10px;" id="<?php echo $container; ?>_customer_name"><?php echo $model->customer_name;?></div>                  
	</div>
    </div>
    <div style="float:left; margin-left:10px;; width:40%">
    <h3><?php echo Yii::t('views/giftcertificates/edit','LABEL_TITLE_PERSON');?></h3>
     <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_LANGUAGE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'language'); ?>
        <div>
        <?php echo Html::generateLanguageList($model_name.'[language_code]',$model->language_code,array('id'=>$container.'_language_code')); ?>          
        <br /><span id="<?php echo $container; ?>_language_code_errorMsg" class="error"></span>       
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_NAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?>
        <div>
        <?php echo $form->textField($model,'person_name',array('style'=>'width:200px;', 'id'=>$container.'_person_name')); ?>         
        <br /><span id="<?php echo $container; ?>_person_name_errorMsg" class="error"></span>       
        </div>                
	</div>
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_EMAIL');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'email'); ?>
        <div>
        <?php echo $form->textField($model,'person_email',array('style'=>'width:200px;', 'id'=>$container.'_person_email')); ?>         
        <br /><span id="<?php echo $container; ?>_person_email_errorMsg" class="error"></span>       
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_ADDRESS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'address'); ?>
        <div>
        <?php echo $form->textArea($model,'person_address',array('style'=>'width:98%; height:69px;','id'=>$container.'_person_address')); ?>         
        <br /><span id="<?php echo $container; ?>_person_address_errorMsg" class="error"></span>       
        </div>                
	</div>
    
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_MESSAGE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'message'); ?>
        <div>
        <?php echo $form->textArea($model,'person_message',array('style'=>'width:98%; height:60px;','id'=>$container.'_person_message')); ?>         
        <br /><span id="<?php echo $container; ?>_person_message_errorMsg" class="error"></span>       
        </div>                
	</div>
     
    </div> 

    <div style="float:left; margin-left:10px;; width:35%">
    <h3><?php echo Yii::t('views/giftcertificates/edit','LABEL_TITLE_SHIPPING');?></h3>
     <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_SHIPPING_METHOD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipping-method'); ?>
        <div>
        <?php echo $form->dropDownList($model,'shipping_method',array(0=>Yii::t('views/giftcertificates/edit','LABEL_SHIPPING_METHOD_0'),1=>Yii::t('views/giftcertificates/edit','LABEL_SHIPPING_METHOD_1')),array('id'=>$container.'_shipping_method')); ?>         
        <br /><span id="<?php echo $container; ?>_shipping_method_errorMsg" class="error"></span>       
        </div>                
	</div> 
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_SENT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'sent'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[sent]',$model->sent?1:0,array('value'=>1,'id'=>$container.'_sent_1')).'&nbsp;<label for="'.$container.'_sent_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[sent]',!$model->sent?1:0,array('value'=>0,'id'=>$container.'_sent_0')).'&nbsp;<label for="'.$container.'_sent_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div> 
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_DATE_SENT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'date-sent'); ?>
        <div><?php echo $form->hiddenField($model,'date_sent',array('id'=>$container.'_date_sent')); ?><?php echo(($model->date_sent!='0000-00-00 00:00:00')?$model->date_sent:'-');?></div>                
	</div>
     
    <div class="row">
        <strong><?php echo Yii::t('views/giftcertificates/edit','LABEL_COMMENTS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'comments'); ?>
        <div>
        <?php echo $form->textArea($model,'comments',array('style'=>'width:98%; height:60px;','id'=>$container.'_comments')); ?>         
        <br /><span id="<?php echo $container; ?>_comments_errorMsg" class="error"></span>       
        </div>                
	</div>
    </div>    
</div>
</div>
<?php $this->endWidget(); ?>
