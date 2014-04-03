<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/category-filters/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
    	<table border="0" cellpadding="2" cellspacing="2" width="100%">
        	<tr>
            	<td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_FEATURED_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-featured-products-menu'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_FEATURED_PRODUCT_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_featured_products_menu]',$model->settings['cf_show_featured_products_menu']?1:0,array('value'=>1,'id'=>'show_featured_products_1')).'&nbsp;<label for="show_featured_products_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_featured_products_menu]',!$model->settings['cf_show_featured_products_menu']?1:0,array('value'=>0,'id'=>'show_featured_products_0')).'&nbsp;<label for="show_featured_products_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_NEW_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-new-products-menu'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_NEW_PRODUCT_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_new_products_menu]',$model->settings['cf_show_new_products_menu']?1:0,array('value'=>1,'id'=>'show_new_products_1')).'&nbsp;<label for="show_new_products_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_new_products_menu]',!$model->settings['cf_show_new_products_menu']?1:0,array('value'=>0,'id'=>'show_new_products_0')).'&nbsp;<label for="show_new_products_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>   
        	<tr>
            	<td valign="top">
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_CONSIDERED_NEW_PRODUCT');?></em>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'new-products-no-days'); ?>
				</td>
                <td valign="top">
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'settings[cf_new_products_no_days]',array('size'=>5, 'id'=>'settings[cf_new_products_no_days]','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="settings[cf_new_products_no_days]_errorMsg" class="error"></span>
                    </div>                 
                </td>
			</tr>      
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_TOP_SELLERS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-top-sellers-menu'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_TOP_SELLERS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_top_sellers_menu]',$model->settings['cf_show_top_sellers_menu']?1:0,array('value'=>1,'id'=>'show_top_sellers_1')).'&nbsp;<label for="show_top_sellers_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_top_sellers_menu]',!$model->settings['cf_show_top_sellers_menu']?1:0,array('value'=>0,'id'=>'show_top_sellers_0')).'&nbsp;<label for="show_top_sellers_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>         
            <?php if ($model->settings['enable_rebate']) { ?>     
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_ON_SALE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-on-sale-menu'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_ON_SALE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_on_sale_menu]',$model->settings['cf_show_on_sale_menu']?1:0,array('value'=>1,'id'=>'show_on_sale_1')).'&nbsp;<label for="show_on_sale_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_on_sale_menu]',!$model->settings['cf_show_on_sale_menu']?1:0,array('value'=>0,'id'=>'show_on_sale_0')).'&nbsp;<label for="show_on_sale_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>       
            <?php } ?>    
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_COMBO_DEALS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-combo-deals-menu'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_COMBO_DEALS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_combo_deals_menu]',$model->settings['cf_show_combo_deals_menu']?1:0,array('value'=>1,'id'=>'show_combo_deals_1')).'&nbsp;<label for="show_combo_deals_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_combo_deals_menu]',!$model->settings['cf_show_combo_deals_menu']?1:0,array('value'=>0,'id'=>'show_combo_deals_0')).'&nbsp;<label for="show_combo_deals_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_BUNDLED_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-bundled-products-menu'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_BUNDLED_PRODUCT_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_bundled_product_menu]',$model->settings['cf_show_bundled_product_menu']?1:0,array('value'=>1,'id'=>'show_bundled_product_1')).'&nbsp;<label for="show_bundled_product_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_bundled_product_menu]',!$model->settings['cf_show_bundled_product_menu']?1:0,array('value'=>0,'id'=>'show_bundled_product_0')).'&nbsp;<label for="show_bundled_product_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>  
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_PRICE_RANGE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-price-range'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_PRICE_RANGE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_price_range]',$model->settings['cf_show_price_range']?1:0,array('value'=>1,'id'=>'show_price_range_1')).'&nbsp;<label for="show_price_range_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_price_range]',!$model->settings['cf_show_price_range']?1:0,array('value'=>0,'id'=>'show_price_range_0')).'&nbsp;<label for="show_price_range_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>
             <tr id="price_range_increment" style="display:<?php echo($model->settings['cf_show_price_range']?'block':'none');?>">
               <td valign="top"><strong><?php echo Yii::t('views/config/edit_general_options','LABEL_PRICE_INCREMENT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price-range-increment'); ?><br />
        <em><?php echo Yii::t('views/config/edit_general_options','LABEL_PRICE_INCREMENT_DESCRIPTION');?></em></td>
               <td valign="top"> <div>
        <?php
        echo CHtml::activeTextField($model,'settings[price_increment]',array('size'=>10, 'id'=>'price_increment','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>
        <br /><span id="price_increment_errorMsg" class="error"></span>
        </div>  </td>
             </tr>       
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_BRANDS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-brands'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_BRANDS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_brands]',$model->settings['cf_show_brands']?1:0,array('value'=>1,'id'=>'show_brands_1')).'&nbsp;<label for="show_brands_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_brands]',!$model->settings['cf_show_brands']?1:0,array('value'=>0,'id'=>'show_brands_0')).'&nbsp;<label for="show_brands_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>      
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_RATINGS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-ratings'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_category_filters_options','LABEL_SHOW_RATINGS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][cf_show_ratings]',$model->settings['cf_show_ratings']?1:0,array('value'=>1,'id'=>'show_ratings_1')).'&nbsp;<label for="show_ratings_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][cf_show_ratings]',!$model->settings['cf_show_ratings']?1:0,array('value'=>0,'id'=>'show_ratings_0')).'&nbsp;<label for="show_ratings_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>                                                                                                   
		</table>                                                
    </div>     
</div>
</div>
<?php
$script = '
$(function(){	
	$("#show_price_range_1").click(function(){
		$("#price_range_increment").show("slow");		
	});
	$("#show_price_range_0").click(function(){
		$("#price_range_increment").hide("slow");		
	});
});

';

echo Html::script($script); 
?>  