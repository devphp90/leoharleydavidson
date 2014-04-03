<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$columns = Html::getColumnsMaxLength(Tbl_Product::tableName());	

$help_hint_path = '/catalog/products/information/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<?php echo CHtml::activeHiddenField($model,'product_type',array('id'=>$container.'_product_type')); ?>
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
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_DISPLAY_IN_CATALOG');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-in-catalog'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[display_in_catalog]',$model->display_in_catalog?1:0,array('value'=>1,'id'=>$container.'_display_in_catalog_1')).'&nbsp;<label for="'.$container.'_display_in_catalog_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display_in_catalog]',!$model->display_in_catalog?1:0,array('value'=>0,'id'=>$container.'_display_in_catalog_0')).'&nbsp;<label for="'.$container.'_display_in_catalog_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div>    
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_FEATURED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'featured'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[featured]',$model->featured?1:0,array('value'=>1,'id'=>$container.'_featured_1')).'&nbsp;<label for="'.$container.'_featured_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[featured]',!$model->featured?1:0,array('value'=>0,'id'=>$container.'_featured_0')).'&nbsp;<label for="'.$container.'_featured_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div>
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_USED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'used'); ?><br />
        <div>
        <?php 
		$app = Yii::app();
		
		if(!$model->id){
			$used = $app->params['default_product_used'];
		}else{
			$used = $model->used;
		}
		
        echo CHtml::radioButton($model_name.'[used]',$used?1:0,array('value'=>1,'id'=>$container.'_used_1')).'&nbsp;<label for="'.$container.'_used_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[used]',!$used?1:0,array('value'=>0,'id'=>$container.'_used_0')).'&nbsp;<label for="'.$container.'_used_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div>            	
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_DOWNLOADABLE_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'downloadable-product'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[downloadable]',$model->downloadable?1:0,array('value'=>1,'id'=>$container.'_downloadable_1')).'&nbsp;<label for="'.$container.'_downloadable_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[downloadable]',!$model->downloadable?1:0,array('value'=>0,'id'=>$container.'_downloadable_0')).'&nbsp;<label for="'.$container.'_downloadable_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div>       
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_SKU');?></strong><?php echo (isset($columns['sku']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_sku_maxlength">'.($columns['sku']-strlen($model->sku)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'sku'); ?><br />
        <div>
        <?php
		$readonly = 0;
		$class = '';
		//Verify if product is sold, we put the field SKU readonly
		$criteria=new CDbCriteria; 
		$criteria->condition='id_product=:id_product'; 
		$criteria->params=array(':id_product'=>$model->id);
		if(Tbl_OrdersItemProduct::model()->find($criteria)){
			$readonly = 1;
			$class = 'disabled';
		}
		
        echo CHtml::activeTextField($model,'sku',array('style' => 'width: 250px;','maxlength'=>$columns['sku'], 'id'=>$container.'_sku','readonly'=>$readonly,'class'=>$class));
        ?>
        <br /><span id="<?php echo $container; ?>_sku_errorMsg" class="error"></span>
        </div>                
	</div>     
    <?php
	switch ($model->product_type) {
		// product
		case 0:
	?>
    <hr />
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_COST_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'cost-price'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'cost_price',array('size'=>10, 'id'=>$container.'_cost_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_cost_price_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_RETAIL_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'retail-price'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'price',array('size'=>10, 'id'=>$container.'_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_price_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_SPECIAL_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'special_price',array('size'=>10, 'id'=>$container.'_special_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_special_price_errorMsg" class="error"></span>
        </div>                
	</div>   
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_SPECIAL_FROM_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price-start-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'special_price_from_date',array('size'=>20, 'id'=>$container.'_special_price_from_date'));
        ?>
        <br /><span id="<?php echo $container; ?>_special_price_from_date_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_SPECIAL_TO_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price-end-date'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'special_price_to_date',array('size'=>20, 'id'=>$container.'_special_price_to_date'));
        ?>
        <br /><span id="<?php echo $container; ?>_special_price_to_date_errorMsg" class="error"></span>
        </div>                
	</div> 
    <hr />
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_MIN_QTY_IN_CART');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'min-qty-allowed-in-cart'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'min_qty',array('size'=>5, 'id'=>$container.'_min_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_min_qty_errorMsg" class="error"></span>
        </div>                
	</div>        
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_MAX_QTY_IN_CART');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'max-qty-allowed-in-cart'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'max_qty',array('size'=>5, 'id'=>$container.'_max_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_max_qty_errorMsg" class="error"></span>
        </div>                
	</div> 
    <hr />              
	<div class="row">
        <strong><?php echo Yii::t('global','LABEL_TAXABLE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'taxable'); ?><br />
        <div>
        <?php 		
		if(!$model->id){
			$taxable = $app->params['default_product_taxable'];
		}else{
			$taxable = $model->taxable;
		}
		
		
        echo CHtml::radioButton($model_name.'[taxable]',$taxable?1:0,array('value'=>1,'id'=>$container.'_taxable_1')).'&nbsp;<label for="'.$container.'_taxable_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[taxable]',!$taxable?1:0,array('value'=>0,'id'=>$container.'_taxable_0')).'&nbsp;<label for="'.$container.'_taxable_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
	</div>        
    <div class="row" id="<?php echo $container;?>_display_tax_group" <?php echo !$taxable ? 'style="display:none;"':''; ?>>    
        <strong><?php echo Yii::t('global','LABEL_TAX_GROUP');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'tax-group'); ?><br />
        <div>
        <?php
		$sql = 'SELECT 
		tax_group.id,
		tax_group.name
		FROM 
		tax_group
		INNER JOIN tax_rule_exception
		ON
		tax_group.id = tax_rule_exception.id_tax_group
		GROUP BY tax_group.id 
		ORDER BY 
		tax_group.name ASC';	
		$command=$connection->createCommand($sql);			
		
        echo CHtml::activeDropDownList($model,'id_tax_group',CHtml::listData($command->queryAll(true),'id','name'),array( 'id'=>$container.'_id_tax_group','prompt'=>'--'));
        ?>
        <br /><span id="<?php echo $container; ?>_tax_group_errorMsg" class="error"></span>
        </div>                
	</div>
    <hr />      
	<div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_BRAND');?></strong><?php echo (isset($columns['brand']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_brand_maxlength">'.($columns['brand']-strlen($model->brand)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'brand'); ?><br />
        <div>
        <?php 
		echo CHtml::activeDropDownList($model,'brand',($model->brand ? array($model->brand=>$model->brand):array()),array('style'=>'width:150px;','maxlength'=>$columns['brand'],'id'=>$container.'_brand'));
		?><span id="<?php echo $container; ?>_brand_errorMsg" class="error"></span>
        </div>         
	</div>
    <div class="row">    
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_MODEL');?></strong><?php echo (isset($columns['model']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_model_maxlength">'.($columns['model']-strlen($model->model)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'model'); ?><br />
        <div>
        <?php 
		echo CHtml::activeDropDownList($model,'model',($model->model ? array($model->model=>$model->model):array()),array('style'=>'width:150px;','maxlength'=>$columns['model'],'id'=>$container.'_model'));
		?><span id="<?php echo $container; ?>_model_errorMsg" class="error"></span>
        </div>                
	</div>
    <hr />     
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_DISPLAY_ON');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-product-on'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'date_displayed',array('size'=>20, 'id'=>$container.'_date_displayed'));
        ?>
        <br /><span id="<?php echo $container; ?>_date_displayed_errorMsg" class="error"></span>
        </div>                
	</div>   
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_SHOW_MULTIPLE_VARIANTS_FORM');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-multiple-variants-form'); ?><br />
        <em><?php echo Yii::t('views/config/edit_general_options','LABEL_SHOW_MULTIPLE_VARIANTS_FORM_DESCRIPTION');?></em>
        <div>
        <?php
        echo CHtml::radioButton($model_name.'[display_multiple_variants_form]',!$model->display_multiple_variants_form ?1:0,array('value'=>0,'id'=>$container.'_display_multiple_variants_form_0')).'&nbsp;<label for="'.$container.'_display_multiple_variants_form_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_GENERAL_CONFIGURATION').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display_multiple_variants_form]',$model->display_multiple_variants_form == 1?1:0,array('value'=>1,'id'=>$container.'_display_multiple_variants_form_1')).'&nbsp;<label for="'.$container.'_display_multiple_variants_form_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display_multiple_variants_form]',$model->display_multiple_variants_form == 2?1:0,array('value'=>2,'id'=>$container.'_display_multiple_variants_form_2')).'&nbsp;<label for="'.$container.'_display_multiple_variants_form_2" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        <br /><span id="<?php echo $container; ?>_date_displayed_errorMsg" class="error"></span>
        </div>                
	</div>          
    <?php		
			break;
		// combo deals
		case 1:
	?>
    <div>
        <div class="row" style="float:left; padding-right:5px;">
            <strong><?php echo Yii::t('global','LABEL_DISCOUNT_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'discount-type'); ?><br />
            <div>
            <?php
            echo CHtml::activeDropDownList($model,'discount_type',array(0=>Yii::t('global','LABEL_FIXED'),1=>Yii::t('global','LABEL_PERCENTAGE')),array( 'id'=>$container.'_discount_type'));
            ?>
            <br /><span id="<?php echo $container; ?>_discount_type_errorMsg" class="error"></span>
            </div>              
        </div>     
        <div class="row" style="float:left; padding-right:5px;">
            <strong><?php echo Yii::t('global','LABEL_DISCOUNT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'discount'); ?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'discount',array('size'=>5, 'id'=>$container.'_discount','onkeyup'=>'rewrite_number($(this).attr("id"));'));
            ?>
            <br /><span id="<?php echo $container; ?>_discount_errorMsg" class="error"></span>
            </div>                
        </div>
	    <div style="clear:both;"></div>      
    </div>    
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_USER_DEFINED_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'user-defined-qty'); ?><br />
        <em><?php echo Yii::t('views/products/edit_info_options','LABEL_USER_DEFINED_QTY_DESCRIPTION');?></em><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[user_defined_qty]',$model->user_defined_qty?1:0,array('value'=>1,'id'=>$container.'_user_defined_qty_1')).'&nbsp;<label for="'.$container.'_user_defined_qty_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[user_defined_qty]',!$model->user_defined_qty?1:0,array('value'=>0,'id'=>$container.'_user_defined_qty_0')).'&nbsp;<label for="'.$container.'_user_defined_qty_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div> 
    <hr />       
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_MIN_QTY_IN_CART');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'min-qty-allowed-in-cart'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'min_qty',array('size'=>5, 'id'=>$container.'_min_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_min_qty_errorMsg" class="error"></span>
        </div>                
	</div>          
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_MAX_QTY_IN_CART');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'max-qty-allowed-in-cart'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'max_qty',array('size'=>5, 'id'=>$container.'_max_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_max_qty_errorMsg" class="error"></span>
        </div>                
	</div> 
    <hr />    
	<div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_BRAND');?></strong><?php echo (isset($columns['brand']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_brand_maxlength">'.($columns['brand']-strlen($model->brand)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'brand'); ?><br />
        <div>
        <?php 
		echo CHtml::activeDropDownList($model,'brand',($model->brand ? array($model->brand=>$model->brand):array()),array('style'=>'width:150px;','maxlength'=>$columns['brand'],'id'=>$container.'_brand'));
		?>
        <br /><span id="<?php echo $container; ?>_brand_errorMsg" class="error"></span>
        </div>         
	</div>
    <div class="row">    
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_MODEL');?></strong><?php echo (isset($columns['model']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_model_maxlength">'.($columns['model']-strlen($model->model)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'model'); ?><br />
        <div>
        <?php 
		echo CHtml::activeDropDownList($model,'model',($model->model ? array($model->model=>$model->model):array()),array('style'=>'width:150px;','maxlength'=>$columns['model'],'id'=>$container.'_model'));
		?>
        <br /><span id="<?php echo $container; ?>_model_errorMsg" class="error"></span>
        </div>                
	</div>
    <hr />       
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_DISPLAY_ON');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-product-on'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'date_displayed',array('size'=>20, 'id'=>$container.'_date_displayed'));
        ?>
        <br /><span id="<?php echo $container; ?>_date_displayed_errorMsg" class="error"></span>
        </div>                
	</div>      
    <?php		
			break;
		// bundled products
		case 2:
	?>
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_PRODUCT_USE_REGULAR_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'use-regular-price'); ?><br />
        <em><?php echo Yii::t('views/products/edit_info_options','LABEL_PRODUCT_USE_REGULAR_PRICE_DESCRIPTION');?></em><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[use_product_current_price]',$model->use_product_current_price?1:0,array('value'=>1,'id'=>$container.'_use_product_current_price_1')).'&nbsp;<label for="'.$container.'_use_product_current_price_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[use_product_current_price]',!$model->use_product_current_price?1:0,array('value'=>0,'id'=>$container.'_use_product_current_price_0')).'&nbsp;<label for="'.$container.'_use_product_current_price_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    </div> 
    <div id="<?php echo $container.'_set_bundled_product_price'; ?>" <?php echo $model->use_product_current_price ? 'style="display:block;"':'style="display:none;"'; ?>>    
        <div class="row">
             <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_PRODUCT_USE_SPECIAL_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'use-special-price'); ?><br />
            <em><?php echo Yii::t('views/products/edit_info_options','LABEL_PRODUCT_USE_SPECIAL_PRICE_DESCRIPTION');?></em>
            <div>
             <?php 
			echo CHtml::radioButton($model_name.'[use_product_special_price]',$model->use_product_special_price?1:0,array('value'=>1,'id'=>$container.'_use_product_special_price_1')).'&nbsp;<label for="'.$container.'_use_product_special_price_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[use_product_special_price]',!$model->use_product_special_price?1:0,array('value'=>0,'id'=>$container.'_use_product_special_price_0')).'&nbsp;<label for="'.$container.'_use_product_special_price_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
			?>
            </div>                
        </div> 
    </div> 
    <hr />
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_MIN_QTY_IN_CART');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'min-qty-allowed-in-cart'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'min_qty',array('size'=>5, 'id'=>$container.'_min_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_min_qty_errorMsg" class="error"></span>
        </div>                
	</div>        
     <div class="row">
        <strong><?php echo Yii::t('global','LABEL_MAX_QTY_IN_CART');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'max-qty-allowed-in-cart'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'max_qty',array('size'=>5, 'id'=>$container.'_max_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="<?php echo $container; ?>_max_qty_errorMsg" class="error"></span>
        </div>                
	</div>
    <hr />            
	<div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_BRAND');?></strong><?php echo (isset($columns['brand']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_brand_maxlength">'.($columns['brand']-strlen($model->brand)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'brand'); ?><br />
        <div>
        <?php 
		echo CHtml::activeDropDownList($model,'brand',($model->brand ? array($model->brand=>$model->brand):array()),array('style'=>'width:150px;','maxlength'=>$columns['brand'],'id'=>$container.'_brand'));
		?>
        <br /><span id="<?php echo $container; ?>_brand_errorMsg" class="error"></span>
        </div>         
	</div>
    <div class="row">    
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_MODEL');?></strong><?php echo (isset($columns['model']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_model_maxlength">'.($columns['model']-strlen($model->model)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'model'); ?><br />
        <div>
        <?php 
		echo CHtml::activeDropDownList($model,'model',($model->model ? array($model->model=>$model->model):array()),array('style'=>'width:150px;','maxlength'=>$columns['model'],'id'=>$container.'_model'));
		?>
        <br /><span id="<?php echo $container; ?>_model_errorMsg" class="error"></span>
        </div>                
	</div> 
    <hr />      
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_info_options','LABEL_DISPLAY_ON');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-product-on'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'date_displayed',array('size'=>20, 'id'=>$container.'_date_displayed'));
        ?>
        <br /><span id="<?php echo $container; ?>_date_displayed_errorMsg" class="error"></span>
        </div>                
	</div>               
    <?php		
			break;
	}
	?>               
</div>
</div>
<?php

$script = '
$(function(){
	
	$("#'.$container.'_taxable_1").click(function(){
		$("#'.$container.'_display_tax_group").show();
	});
	
	$("#'.$container.'_taxable_0").click(function(){
		$("#'.$container.'_display_tax_group").hide();
	});
	
	$("#'.$container.'_product_type").bind({
		change: '.$container.'_change_product_type,
		keyup: '.$container.'_change_product_type
	});
	
	function '.$container.'_change_product_type(){
		$.ajax({
			url: "'.CController::createUrl('edit_info_options',array('container'=>$container,'id'=>$model->id)).'",
			data: { "product_type":$(this).val() },
			type: "POST",
			success: function(data){
				'.$container.'.layout.B.obj.attachHTMLString(data);		
			}
		});				
	}
	
	$("#'.$container.'_date_displayed").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	$("#'.$container.'_special_price_from_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	$("#'.$container.'_special_price_to_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	
	$("#'.$container.'_use_product_current_price_0,#'.$container.'_use_product_current_price_1").bind({
		change: '.$container.'_use_bundled_product_current_price,
		keyup: '.$container.'_use_bundled_product_current_price
	});
	
	function '.$container.'_use_bundled_product_current_price(){
		switch ($(this).val()){
			case "0":
				$("#'.$container.'_set_bundled_product_price").hide();			
				break;
			case "1":				
				$("#'.$container.'_set_bundled_product_price").show();
				break;	
		}
	}
});


// convert div to combobox
'.$container.'.layout.B.combo = new Object();
'.$container.'.layout.B.combo.search_brand = new Object();
'.$container.'.layout.B.combo.search_brand.obj = new dhtmlXCombo("'.$container.'_brand", "'.$model_name.'[brand]", 150);
// enable autocomplete mode
'.$container.'.layout.B.combo.search_brand.obj.enableFilteringMode(true, "'.CController::createUrl('xml_list_brand').'", false);


'.$container.'.layout.B.combo.search_model = new Object();
'.$container.'.layout.B.combo.search_model.obj = new dhtmlXCombo("'.$container.'_model", "'.$model_name.'[model]", 150);
// enable autocomplete mode
'.$container.'.layout.B.combo.search_model.obj.enableFilteringMode(true, "'.CController::createUrl('xml_list_model').'", false);
';

echo Html::script($script); 
?>