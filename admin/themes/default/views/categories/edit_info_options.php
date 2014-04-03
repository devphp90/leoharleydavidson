<?php 
$model_name = get_class($model);

$help_hint_path = '/catalog/categories/information/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <h1><?php echo Yii::t('global','LABEL_TITLE_PARAMETERS');?></h1>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
        ?>
        </div>        
    </div>    
    <div class="row">
        <strong><?php echo Yii::t('views/categories/edit_info_options','LABEL_FEATURED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'featured'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[featured]',$model->featured?1:0,array('value'=>1,'id'=>$container.'_featured_1')).'&nbsp;<label for="'.$container.'_featured_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[featured]',!$model->featured?1:0,array('value'=>0,'id'=>$container.'_featured_0')).'&nbsp;<label for="'.$container.'_featured_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div>    
    <div class="row">
        <strong><?php echo Yii::t('views/categories/edit_info_options','LABEL_DISPLAY_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-type'); ?><br />
        <div>
        <?php
        echo CHtml::activeDropDownList($model,'display_type',array(0=>Yii::t('views/categories/edit_info_options','LABEL_DISPLAY_TYPE_0'),1=>Yii::t('views/categories/edit_info_options','LABEL_DISPLAY_TYPE_1')),array( 'id'=>$container.'_display_type'));
        ?>
        <br /><span id="<?php echo $container; ?>_display_type_errorMsg" class="error"></span>
        </div>                
	</div>    
    <div class="row <?php echo $container.'_description_only';?>"<?php echo($model->display_type?' style="display:none;"':'');?>>
        <strong><?php echo Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'products-listing-default-sort-by'); ?><br />
        <em><?php echo Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_DESCRIPTION');?></em>
        <div>
        <?php
        echo CHtml::activeDropDownList($model,'product_sort_by',array(0=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_1'),1=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_2'),2=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_3'),3=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_4'),4=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_5'),5=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_6'),6=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_7')),array( 'id'=>$container.'_product_sort_by'));
        ?>
        <br /><span id="<?php echo $container; ?>_product_sort_by_errorMsg" class="error"></span>
        </div>                
	</div>     
    <div class="row <?php echo $container.'_description_only';?>"<?php echo($model->display_type?' style="display:none;"':'');?>>
        <strong><?php echo Yii::t('views/categories/edit_info_options','LABEL_PRICE_INCREMENT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price-range-increment'); ?><br />
        <em><?php echo Yii::t('views/categories/edit_info_options','LABEL_PRICE_INCREMENT_DESCRIPTION');?></em>
        <div>
        <?php
        echo CHtml::activeTextField($model,'price_increment',array('size'=>10, 'id'=>$container.'_price_increment','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_price_increment_errorMsg" class="error"></span>
        </div>                
	</div>                 
    <div class="row">
        <strong><?php echo Yii::t('views/categories/edit_info_options','LABEL_DISPLAY_ON');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-category-start-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'start_date',array('size'=>20, 'id'=>$container.'_start_date'));
        ?>
        <br /><span id="<?php echo $container; ?>_start_date_errorMsg" class="error"></span>
        </div>                
	</div>   
    <div class="row">
        <strong><?php echo Yii::t('views/categories/edit_info_options','LABEL_DISABLE_ON');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-category-end-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'end_date',array('size'=>20, 'id'=>$container.'_end_date'));
        ?>
        <br /><span id="<?php echo $container; ?>_end_date_errorMsg" class="error"></span>
        </div>                
	</div>          
    <div class="row">
    	<strong><?php echo Yii::t('views/categories/edit_info_options','LABEL_PARENT_CATEGORY_UNDER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'parent-category'); ?><br />
        <em><?php echo Yii::t('views/categories/edit_info_options','LABEL_PARENT_CATEGORY_UNDER_DESCRIPTION');?></em>
    </div><span id="<?php echo $container; ?>_category_errorMsg" class="error"></span>
    <div class="row" id="<?php echo $container.'_categories'; ?>" style="background-color:#f5f5f5;border :1px solid Silver; padding:5px; width:300px;">
    </div>
</div>
</div>
<?php
$script = '
$(function(){
	$("#'.$container.'_start_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	$("#'.$container.'_end_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});	
	
	
	$("#'.$container.'_display_type").change(function(){
		if(this.value == 0){
			$(".'.$container.'_description_only").show();
		}else{
			$(".'.$container.'_description_only").hide();
		}
	});
	
	
	
});

'.$container.'.layout.B.tree = new Object();
'.$container.'.layout.B.tree.obj = new dhtmlXTreeObject("'.$container.'_categories","100%","100%",0);
'.$container.'.layout.B.tree.obj.setSkin(dhx_skin);
'.$container.'.layout.B.tree.obj.setImagePath(dhx_globalImgPath);
'.$container.'.layout.B.tree.obj.enableRadioButtons(true);
'.$container.'.layout.B.tree.obj.enableSingleRadioMode(true);
'.$container.'.layout.B.tree.obj.loadXML("'.CController::createUrl('xml_list_categories',array('id'=>$model->id)).'",function(){
	var obj = '.$container.'.layout.B.tree.obj;
	'.($model->id_parent ? 'obj.setCheck('.$model->id_parent.',1);':'').'
	
	'.$container.'.load_og_form();
});	

'.$container.'.layout.B.tree.obj.attachEvent("onSelect", function(id){
	this.setCheck(id,this.isItemChecked(id)?0:1);	
});

';

echo Html::script($script); 
?>