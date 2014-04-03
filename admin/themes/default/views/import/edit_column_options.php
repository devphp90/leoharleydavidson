<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	
$help_hint_path = '/settings/import-export/import/';
?>
<div style="width:100%; height:100%; overflow:auto;" id="div_<?php echo $container;?>_column_options">

<div style="padding:10px; position:relative;">	
<?php echo CHtml::activeHiddenField($model,'id_import_tpl',array('id'=>$container.'_id_import_tpl')); ?>
	<div class="row">        
        <strong><?php echo Yii::t('views/import/edit_options','LABEL_COLUMNS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'columns'); ?><br />
        <div>
        <?php
			$total_languages = Tbl_Language::model()->active()->count();
		
			$columns = array();
			
			$columns = array(
						// name					
						1=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_1'),
						// short_desc
						2=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_2'),
						// description
						3=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_3'),
						// meta_description
						4=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_4'),
						// meta_keywords
						5=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_5'),
						// alias
						6=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_6'),
						// sku
						7=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_7'),
						// brand
						8=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_8'),
						// model
						9=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_9'),
						// cost_price
						10=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_10'),
						// price
						11=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_11'),
						// special_price
						12=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_12'),
						// special_price_from_date
						13=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_13'),
						// special_price_to_date
						14=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_14'),
						// cover_image
						15=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_15'),
						// images
						16=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_16'),
						// qty
						17=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_17'),
						// notify_qty
						18=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_18'),
						// out_of_stock
						19=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_19'),
						// out_of_stock_enabled
						20=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_20'),
						// weight
						21=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_21'),
						// enable_local_pickup
						22=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_22'),
						// used
						23=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_23'),
						// featured
						24=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_24'),
						// taxable
						25=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_25'),
						// status
						26=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_26').'('.Yii::t('global','LABEL_ENABLED').', '.Yii::t('global','LABEL_DISABLED').')',
						// length
						29=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_29'),
						// width
						30=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_30'),
						// height
						31=>Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_31'),
					);
		
			switch ($model->type_import_tpl) {
				// add products
				case 0:
					
				// add / update products
				case 1:
					// category	
					$columns[27] = Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_27');
					$columns[28] = Yii::t('views/import/edit_options','LABEL_COLUMN_PRODUCT_28');
					
				// update products
				case 2:
					
					
					$sql = 'SELECT 
					import_tpl_columns.id_import_columns,
					COUNT(import_tpl_columns.id) AS total
					FROM
					import_tpl_columns
					WHERE
					import_tpl_columns.id_import_tpl = :id_import_tpl
					GROUP BY 
					import_tpl_columns.id_import_columns';
					
					$command=$connection->createCommand($sql);
					
					foreach ($command->queryAll(true, array(':id_import_tpl'=>$model->id_import_tpl)) as $row) {
						if ($row['id_import_columns'] > 6 && $row['id_import_columns'] != 16 && $row['id_import_columns'] != 27 && $row['id_import_columns'] != 28 && isset($columns[$row['id_import_columns']]) || ($row['id_import_columns'] <= 6 || $row['id_import_columns'] == 27 || $row['id_import_columns'] == 28) && $row['total'] == $total_languages) unset($columns[$row['id_import_columns']]);
					}					
					break;
			}
            
        	echo CHtml::activeDropDownList($model,'id_import_columns',$columns,array('id'=>$container.'_id_import_columns','prompt'=>'--'));        
        ?>
        <br /><span id="<?php echo $container; ?>_id_import_columns_errorMsg" class="error"></span>
        </div>   
    </div>
    <div class="row" style="display:none;" id="<?php echo $container.'_select_languages_container'; ?>">  
    	<strong><?php echo Yii::t('views/import/edit_options','LABEL_SELECT_LANGUAGES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-languages'); ?><br />
        <div style="padding-left:5px; min-height:40px;" id="<?php echo $container.'_select_languages_options'; ?>"></div>     
        <br /><span id="<?php echo $container; ?>_select_languages_errorMsg" class="error"></span>     
    </div>
    <div class="row" style="display:none;" id="<?php echo $container.'_additional_images_qty_container'; ?>">
        <strong><?php echo Yii::t('views/import/edit_options','LABEL_ADDITIONAL_IMAGES_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'additional-images-qty'); ?><br />
        <div>
        <?php		
        echo CHtml::activeTextField($model,'additional_images_qty',array('size' => '5', 'id'=>$container.'_additional_images_qty'));
        ?>
        <br /><span id="<?php echo $container; ?>_additional_images_qty_errorMsg" class="error"></span>
        </div>  
    </div>
</div>
</div>
<?php 
$script = '
$(function(){		
	$("#'.$container.'_id_import_columns").on("change",function(){
		switch ('.$model->type_import_tpl.'){
			// products
			case 0:
			case 1:
			case 2:
				switch ($(this).val()){
					// category
					case "27":
					// sub category
					case "28":
					// name
					case "1":
					// short_desc			
					case "2":
					// description
					case "3":		
					// meta_description	
					case "4":
					// meta_keywords
					case "5":
					// alias
					case "6":
						$("#'.$container.'_additional_images_qty_container").hide();
						$("#'.$container.'_select_languages_container").show();	
					
						$.ajax({
							url: "'.CController::createUrl('get_column_languages',array('container'=>$container,'id'=>$model->id_import_tpl)).'&id_import_columns="+$(this).val(),
							beforeSend: function(jqXHR, settings) {
								$("#'.$container.'_select_languages_options").html("");
								ajaxOverlay("'.$container.'_select_languages_options",1);
							},
							complete: function(){
								ajaxOverlay("'.$container.'_select_languages_options",0);
							},							
							success: function(data){
								$("#'.$container.'_select_languages_options").append(data);	
							}
						});					
						break;
					// additional images
					case "16":						
						$("#'.$container.'_select_languages_container").hide();
						$("#'.$container.'_additional_images_qty_container").show();
						break;
					default:
						$("#'.$container.'_select_languages_container, #'.$container.'_additional_images_qty_container").hide();
						break;
				}
				break;
		}
	});
});
';

echo Html::script($script); 
?>