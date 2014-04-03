<?php
$containerJS = 'Tab'.$container;
$language = Yii::app()->language;

$columns = Html::getColumnsMaxLength(Tbl_CustomerType::tableName());

$script = '
var '.$containerJS.' = new Object();
// variables used to check modification
'.$containerJS.'.og_form = [];

'.$containerJS.'.layout = new Object();
'.$containerJS.'.layout.obj = tabs.obj.cells("'.$container.'");
'.$containerJS.'.layout.obj.attachObject("'.$container.'");

'.$containerJS.'.layout.toolbar = new Object();

'.$containerJS.'.layout.toolbar.load = function(current_id){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
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
				'.$containerJS.'.layout.toolbar.save_customer_type(id);
				break;
			case "save_close":
				'.$containerJS.'.layout.toolbar.save_customer_type(id,true);
				break;
			case "delete":
				if (confirm("'.Yii::t('views/customertypes/edit','LABEL_ALERT_DELETE').'")) {
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

'.$containerJS.'.layout.toolbar.save_customer_type = function(id,close){
	var obj = '.$containerJS.'.layout.toolbar.obj;
	
	$.ajax({
		url: "'.CController::createUrl('edit',array('container'=>$container)).'",
		type: "POST",
		data: $("#'.$container.'").serialize(),
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
					if (tabs.obj.getLabel("'.$container.'") != $("#'.$container.'_name").val()) {
						tabs.obj.setLabel("'.$container.'",$("#'.$container.'_name").val());	
					}
										
					load_grid(tabs.list.grid.obj);		
					
					'.$containerJS.'.og_form = [];
					'.$containerJS.'.load_og_form();
								
					if (close){
						tabs.close_tab(tabs.obj,"'.$container.'",true);	
					} else {
						$("#'.$container.'_id").val(data.id);
						'.$containerJS.'.layout.toolbar.load(data.id);	
						
						// when we create a new product and save, store its id and container value in array of opened tabs
						tabs.totalOpened[data.id] = "'.$container.'";						
					}
					
					alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");					
				}
			} else {
				alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
			}
		}
	});			
};

'.$containerJS.'.layout.toolbar.obj = '.$containerJS.'.layout.obj.attachToolbar();
'.$containerJS.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerJS.'.layout.toolbar.load('.$model->id.');

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

$help_hint_path = '/customers/customer-types/';
?>
<!-- 
Using a form tag instead of a div for the layout container.
This way we can serialize all the form objects in the layout.
Also this way we do not need to add a prefix to all objects in the form, in case of multiple forms.
But we do need to add a prefix for each individual form object id's
-->
<?php $form=$this->beginWidget('CActiveForm',array(
	'id'=>$container,
	'htmlOptions'=>array('style'=>'width:100%; height:100%; padding:0; margin:0;'),
));
$model_name = get_class($model);
?>
<?php echo $form->hiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
	<div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_name_maxlength">'.($columns['name']-strlen($model->name)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?>
        <div>
        <?php
        echo $form->textField($model,'name',array('style' => 'width: 250px;','maxlength'=>$columns['name'], 'id'=>$container.'_name'));
        ?>          
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>    
        </div>
	</div>        
	<div class="row">
        <strong><?php echo Yii::t('views/customertypes/edit','LABEL_PRECENTAGE_DISCOUNT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'percent-discount'); ?>
        <div>
        <?php echo $form->textField($model,'percent_discount',array('size'=>10,'maxlength'=>'3','id'=>$container.'_percent_discount','onkeyup'=>'rewrite_number($(this).attr("id"));')); ?>         
        <br /><span id="<?php echo $container; ?>_percent_discount_errorMsg" class="error"></span>       
        </div>                
	</div>
    <div class="row">    
        <strong><?php echo Yii::t('views/customertypes/edit','LABEL_PRECENTAGE_APPLY_ON_REBATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'apply-on-rebate'); ?>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[apply_on_rebate]',$model->apply_on_rebate?1:0,array('value'=>1,'id'=>$container.'_apply_on_rebate_1')).'&nbsp;<label for="'.$container.'_apply_on_rebate_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[apply_on_rebate]',!$model->apply_on_rebate?1:0,array('value'=>0,'id'=>$container.'_apply_on_rebate_0')).'&nbsp;<label for="'.$container.'_apply_on_rebate_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>              
        </div>
    </div>
    <div class="row">    
        <strong><?php echo Yii::t('views/customertypes/edit','LABEL_PRECENTAGE_TAXABLE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'taxable'); ?>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[taxable]',$model->taxable?1:0,array('value'=>1,'id'=>$container.'_taxable_1')).'&nbsp;<label for="'.$container.'_taxable_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[taxable]',!$model->taxable?1:0,array('value'=>0,'id'=>$container.'_taxable_0')).'&nbsp;<label for="'.$container.'_taxable_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>              
        </div>
	</div>
	
</div>
</div>
<?php $this->endWidget(); ?>