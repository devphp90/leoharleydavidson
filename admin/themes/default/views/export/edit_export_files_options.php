<?php 
$model_name = get_class($model);
$include_path = Yii::app()->params['includes_js_path'];	

$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$help_hint_path = '/settings/import-export/export/';
?>
<div style="width:100%; height:100%; overflow:auto;" id="div_<?php echo $container;?>_export_files_options_container">	
<div style="padding:10px;">	
	<?php echo CHtml::activeHiddenField($model,'id_export_tpl',array('id'=>$container.'_id_export_tpl')).CHtml::activeHiddenField($model,'type',array('id'=>$container.'_type')); ?>
    <?php 
	switch ($model->type) {
		// products
		case 0:
			$columns = Html::getColumnsMaxLength(Tbl_Product::tableName());	
			$columns = array_merge($columns,Html::getColumnsMaxLength(Tbl_ProductDescription::tableName()));	
	?>
    <div>
    	<div style="clear:both; margin-bottom:10px;"><strong><?php echo Yii::t('views/export/edit_export_files_options','LABEL_FILTER_RESULTS');?></strong></div>
        <div style="float:left;">
            <div class="row">
                <strong><?php echo Yii::t('global','LABEL_ENABLED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
                <div>
                <?php 
                echo CHtml::radioButton($model_name.'[filters][active]',$model->filters['active'] == -1?1:0,array('value'=>-1,'id'=>$container.'_active_all')).'&nbsp;<label for="'.$container.'_active_all" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PROMPT').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][active]',$model->filters['active']==1?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][active]',$model->filters['active']==0?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                ?>
                </div>                
            </div>
            <div class="row">
                <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_DISPLAY_IN_CATALOG');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-in-catalog'); ?><br />
                <div>
                <?php 
                echo CHtml::radioButton($model_name.'[filters][display_in_catalog]',$model->filters['display_in_catalog']==-1?1:0,array('value'=>-1,'id'=>$container.'_display_in_catalog_all')).'&nbsp;<label for="'.$container.'_display_in_catalog_all" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PROMPT').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][display_in_catalog]',$model->filters['display_in_catalog']==1?1:0,array('value'=>1,'id'=>$container.'_display_in_catalog_1')).'&nbsp;<label for="'.$container.'_display_in_catalog_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][display_in_catalog]',$model->filters['display_in_catalog']==0?1:0,array('value'=>0,'id'=>$container.'_display_in_catalog_0')).'&nbsp;<label for="'.$container.'_display_in_catalog_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                ?>
                </div>        
            </div>    
            <div class="row">
                <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_FEATURED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'featured'); ?><br />
                <div>
                <?php 
                echo CHtml::radioButton($model_name.'[filters][featured]',$model->filters['featured']==-1?1:0,array('value'=>-1,'id'=>$container.'_featured_all')).'&nbsp;<label for="'.$container.'_featured_all" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PROMPT').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][featured]',$model->filters['featured']==1?1:0,array('value'=>1,'id'=>$container.'_featured_1')).'&nbsp;<label for="'.$container.'_featured_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][featured]',$model->filters['featured']==0?1:0,array('value'=>0,'id'=>$container.'_featured_0')).'&nbsp;<label for="'.$container.'_featured_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                ?>
                </div>        
            </div>
            <div class="row">
                <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_USED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'used'); ?><br />
                <div>
                <?php 		
                echo CHtml::radioButton($model_name.'[filters][used]',$model->filters['used']==-1?1:0,array('value'=>-1,'id'=>$container.'_used_all')).'&nbsp;<label for="'.$container.'_used_all" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PROMPT').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][used]',$model->filters['used']==1?1:0,array('value'=>1,'id'=>$container.'_used_1')).'&nbsp;<label for="'.$container.'_used_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][used]',$model->filters['used']==0?1:0,array('value'=>0,'id'=>$container.'_used_0')).'&nbsp;<label for="'.$container.'_used_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                ?>
                </div>        
            </div>            	
            <div class="row">
                <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_DOWNLOADABLE_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'downloadable-product'); ?><br />
                <div>
                <?php 
                echo CHtml::radioButton($model_name.'[filters][downloadable]',$model->filters['downloadable']==-1?1:0,array('value'=>-1,'id'=>$container.'_downloadable_all')).'&nbsp;<label for="'.$container.'_downloadable_all" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_PROMPT').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][downloadable]',$model->filters['downloadable']==1?1:0,array('value'=>1,'id'=>$container.'_downloadable_1')).'&nbsp;<label for="'.$container.'_downloadable_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[filters][downloadable]',$model->filters['downloadable']==0?1:0,array('value'=>0,'id'=>$container.'_downloadable_0')).'&nbsp;<label for="'.$container.'_downloadable_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                ?>
                </div>        
            </div>      
        </div>
        <div style="float:left; margin-left:20px;">             
            <div class="row">
                <strong><?php echo Yii::t('global','LABEL_SKU');?></strong><?php echo (isset($columns['sku']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_sku_maxlength">'.($columns['sku']-strlen($model->filters['sku'])).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'sku'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'filters[sku]',array('style' => 'width: 250px;','maxlength'=>$columns['sku'], 'id'=>$container.'_sku','class'=>$class));
                ?>
                <br /><span id="<?php echo $container; ?>_sku_errorMsg" class="error"></span>
                </div>                
            </div>    
            <div class="row">
                <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo (isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_name_maxlength">'.($columns['name']-strlen($model->filters['name'])).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'filters[name]',array('style' => 'width: 250px;','maxlength'=>$columns['name'], 'id'=>$container.'_name','class'=>$class));
                ?>
                <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
                </div>                
            </div>       
            <div class="row">
                <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_BRAND');?></strong><?php echo (isset($columns['brand']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_brand_maxlength">'.($columns['brand']-strlen($model->filters['brand'])).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'brand'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'filters[brand]',array('style' => 'width: 150px;','maxlength'=>$columns['brand'], 'id'=>$container.'_brand','class'=>$class));
                ?>
                <br /><span id="<?php echo $container; ?>_brand_errorMsg" class="error"></span>
                </div>                
            </div> 
            <div class="row">
                <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_MODEL');?></strong><?php echo (isset($columns['model']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_model_maxlength">'.($columns['model']-strlen($model->filters['model'])).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'model'); ?><br />
                <div>
                <?php
                echo CHtml::activeTextField($model,'filters[model]',array('style' => 'width: 150px;','maxlength'=>$columns['model'], 'id'=>$container.'_model','class'=>$class));
                ?>
                <br /><span id="<?php echo $container; ?>_model_errorMsg" class="error"></span>
                </div>                
            </div>      
        </div>     
        <div style="clear:both;"></div>
	</div>                     
    <?php			
			break;
	}
	?> 
</div>
</div>
<?php
$script = '$(function(){

});';

echo Html::script($script); 
?>