<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/general/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>" id="div_<?php echo $container;?>">	
<div style="width:100%; height:100%; overflow:auto;">	
    <div class="row" style="padding:10px;">
    	<table border="0" cellpadding="2" cellspacing="2" width="100%">
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_MAINTENANCE_MODE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'maintenance-mode'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_MAINTENANCE_MODE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][maintenance_mode]',$model->settings['maintenance_mode']?1:0,array('value'=>1,'id'=>'maintenance_mode_1')).'&nbsp;<label for="maintenance_mode_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][maintenance_mode]',!$model->settings['maintenance_mode']?1:0,array('value'=>0,'id'=>'maintenance_mode_0')).'&nbsp;<label for="maintenance_mode_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr> 
            <tr>
            	<td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_SITE_NAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'site-name'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_SITE_NAME_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'settings[site_name]',array('size'=>30,'maxlength'=>30, 'id'=>'settings[site_name]'));
                    ?>
                    <br /><span id="settings[site_name]_errorMsg" class="error"></span>
                    </div>                 
                </td>
			</tr>
        	<tr>
            	<td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_WEBMASTER_EMAIL');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'webmaster-email'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_WEBMASTER_EMAIL_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'settings[webmaster_email]',array('size'=>30, 'id'=>'settings[webmaster_email]'));
                    ?>
                    <br /><span id="settings[webmaster_email]_errorMsg" class="error"></span>
                    </div>                 
                </td>
			</tr> 
            <tr>
            	<td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_NO_REPLY_EMAIL');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'no-reply-email'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_NO_REPLY_EMAIL_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'settings[no_reply_email]',array('size'=>30, 'id'=>'settings[no_reply_email]'));
                    ?>
                    <br /><span id="settings[no_reply_email]_errorMsg" class="error"></span>
                    </div>                 
                </td>
			</tr>      
        	<tr>
            	<td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_LANGUAGE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'default-language'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_LANGUAGE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php						
					echo CHtml::activeDropDownList($model, 'settings[language]', CHtml::listData(Tbl_Language::model()->active()->findAll(), 'code', 'name'), array());
					?>
                    </div>                 
                </td>
			</tr>      
        	<tr>
            	<td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_CURRENCY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'default-currency'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_CURRENCY_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
					<?php						
                    echo CHtml::activeDropDownList($model, 'settings[currency]', CHtml::listData(Tbl_Currency::model()->findAll(), 'code', 'code'), array());
                    ?>   
                    </div>                 
                </td>
			</tr>   
        	<tr>
            	<td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_IMAGE_ORIENTATION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'images-orientation'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_IMAGE_ORIENTATION_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
					<?php						
                    echo CHtml::activeDropDownList($model, 'settings[images_orientation]', array('portrait'=>Yii::t('views/config/edit_general_options','LABEL_IMAGE_ORIENTATION_PORTRAIT'),'landscape'=>Yii::t('views/config/edit_general_options','LABEL_IMAGE_ORIENTATION_LANDSCAPE')), array());
                    ?>      
                    </div>                 
                </td>
			</tr> 
             <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_PRODUCT_USED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'default-product-used'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_PRODUCT_USED_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][default_product_used]',$model->settings['default_product_used']?1:0,array('value'=>1,'id'=>'default_product_used_1')).'&nbsp;<label for="default_product_used_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][default_product_used]',!$model->settings['default_product_used']?1:0,array('value'=>0,'id'=>'default_product_used_0')).'&nbsp;<label for="default_product_used_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>';
                    ?>
                    </div>                 
                </td>
			</tr>
            
             <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_PRODUCT_TAXABLE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'default-product-taxable'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DEFAULT_PRODUCT_TAXABLE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][default_product_taxable]',$model->settings['default_product_taxable']?1:0,array('value'=>1,'id'=>'default_product_taxable_1')).'&nbsp;<label for="default_product_taxable_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][default_product_taxable]',!$model->settings['default_product_taxable']?1:0,array('value'=>0,'id'=>'default_product_taxable_0')).'&nbsp;<label for="default_product_taxable_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>';
                    ?>
                    </div>                 
                </td>
			</tr>
            
             <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_CART_UNLIMITED_COUPONS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enable-unlimited-coupon-cart'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_CART_UNLIMITED_COUPONS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_unlimited_coupon_cart]',$model->settings['enable_unlimited_coupon_cart']?1:0,array('value'=>1,'id'=>'enable_unlimited_coupon_cart_1')).'&nbsp;<label for="enable_unlimited_coupon_cart_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_unlimited_coupon_cart]',!$model->settings['enable_unlimited_coupon_cart']?1:0,array('value'=>0,'id'=>'enable_unlimited_coupon_cart_0')).'&nbsp;<label for="enable_unlimited_coupon_cart_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>
             <tr>
               <td valign="top"><strong><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCTS_LISTING_SORTBY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'products-listing-default-sort-by'); ?><br />
        <em><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCTS_LISTING_SORTBY_DESCRIPTION');?></em></td>
               <td valign="top"><div>
        <?php
        echo CHtml::activeDropDownList($model,'settings[product_sort_by]',array(0=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_1'),1=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_2'),2=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_3'),3=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_4'),4=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_5'),5=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_6'),6=>Yii::t('views/categories/edit_info_options','LABEL_PRODUCTS_LISTING_SORTBY_7')),array( 'id'=>'product_sort_by'));
        ?>
        </div>   </td>
             </tr>
             
             <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_ORDER_EMAIL_NOTIFICATION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'order-email-notification'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_ORDER_EMAIL_NOTIFICATION_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_order_email_notification]',$model->settings['enable_order_email_notification']?1:0,array('value'=>1,'id'=>'enable_order_email_notification_1')).'&nbsp;<label for="enable_order_email_notification_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_order_email_notification]',!$model->settings['enable_order_email_notification']?1:0,array('value'=>0,'id'=>'enable_order_email_notification_0')).'&nbsp;<label for="enable_order_email_notification_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>      
            
            <tr>
            	<td class="display_email_notification" valign="top" <?php echo $model->settings['enable_order_email_notification']?'':'style="display:none"'?>>
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_ORDER_EMAIL_NOTIFICATION_EMAIL');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'order-email-notification-email'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_ORDER_EMAIL_NOTIFICATION_EMAIL_DESCRIPTION');?></em>
				</td>
                <td class="display_email_notification" valign="top" <?php echo $model->settings['enable_order_email_notification']?'':'style="display:none"'?>>
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'settings[order_email_notification_email]',array('size'=>30,'maxlength'=>255, 'id'=>'settings[order_email_notification_email]'));
                    ?>
                    <br /><span id="settings[order_email_notification_email]_errorMsg" class="error"></span>
                    </div>                 
                </td>
			</tr>                   
             
            
               <tr>
               <td colspan="2">
               <h1><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_TITLE');?></h1>
               </td>
               </tr>
        	
            
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-price'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_price]',$model->settings['display_price']?1:0,array('value'=>1,'id'=>'display_price_1')).'&nbsp;<label for="display_price_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_price]',!$model->settings['display_price']?1:0,array('value'=>0,'id'=>'display_price_0')).'&nbsp;<label for="display_price_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>          
            <tr>
            	<td valign="top" colspan="2" class="display_exempt_price" <?php echo $model->settings['display_price']?'':'style="display:none"'?>>
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE_EXCEPTIONS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-price-exceptions'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE_EXCEPTIONS_DESCRIPTION');?></em>
                    <br />
                    <div style="width:700px">
                        <div id="my_toolbar_display_price_exceptions"></div>
                        <div id="display_price_exceptions" style="height:150px;"></div>
                        <div id="recinfoArea_display_price_exceptions"></div>
                      </div>                 
                </td>
			</tr>       
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_ALLOW_ADD_TO_CART');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'allow-add-to-cart'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_ALLOW_ADD_TO_CART_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][allow_add_to_cart]',$model->settings['allow_add_to_cart']?1:0,array('value'=>1,'id'=>'allow_add_to_cart_1')).'&nbsp;<label for="allow_add_to_cart_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][allow_add_to_cart]',!$model->settings['allow_add_to_cart']?1:0,array('value'=>0,'id'=>'allow_add_to_cart_0')).'&nbsp;<label for="allow_add_to_cart_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>          
            <tr>
            	<td valign="top" colspan="2" class="display_allow_add_to_cart" <?php echo $model->settings['allow_add_to_cart']?'':'style="display:none"'?>>
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_ALLOW_ADD_TO_CART_EXCEPTIONS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'allow-add-to-cart-exceptions'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_ALLOW_ADD_TO_CART_EXCEPTIONS_DESCRIPTION');?></em>
                    <br />
                    <div style="width:700px">
                        <div id="my_toolbar_allow_add_to_cart_exceptions"></div>
                        <div id="allow_add_to_cart_exceptions" style="height:150px;"></div>
                        <div id="recinfoArea_allow_add_to_cart_exceptions"></div>
                      </div>                 
                </td>
			</tr>                        
            
        	<!--<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_CUSTOMER_TYPES');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_CUSTOMER_TYPES_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_customer_type]',$model->settings['enable_customer_type']?1:0,array('value'=>1,'id'=>'enable_customer_type_1')).'&nbsp;<label for="enable_customer_type_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_customer_type]',!$model->settings['enable_customer_type']?1:0,array('value'=>0,'id'=>'enable_customer_type_0')).'&nbsp;<label for="enable_customer_type_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>  
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_INVENTORY');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_INVENTORY_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_inventory]',$model->settings['enable_inventory']?1:0,array('value'=>1,'id'=>'enable_inventory_1')).'&nbsp;<label for="enable_inventory_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_inventory]',!$model->settings['enable_inventory']?1:0,array('value'=>0,'id'=>'enable_inventory_0')).'&nbsp;<label for="enable_inventory_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_VARIANTS');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_VARIANTS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_variant]',$model->settings['enable_variant']?1:0,array('value'=>1,'id'=>'enable_variant_1')).'&nbsp;<label for="enable_variant_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_variant]',!$model->settings['enable_variant']?1:0,array('value'=>0,'id'=>'enable_variant_0')).'&nbsp;<label for="enable_variant_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr> 
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_OPTIONS');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_OPTIONS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_option]',$model->settings['enable_option']?1:0,array('value'=>1,'id'=>'enable_option_1')).'&nbsp;<label for="enable_option_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_option]',!$model->settings['enable_option']?1:0,array('value'=>0,'id'=>'enable_option_0')).'&nbsp;<label for="enable_option_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>                        
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_SUGGESTED');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_SUGGESTED_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_suggestion]',$model->settings['enable_suggestion']?1:0,array('value'=>1,'id'=>'enable_suggestion_1')).'&nbsp;<label for="enable_suggestion_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_suggestion]',!$model->settings['enable_suggestion']?1:0,array('value'=>0,'id'=>'enable_suggestion_0')).'&nbsp;<label for="enable_suggestion_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_RELATED');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_PRODUCT_RELATED_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_related]',$model->settings['enable_related']?1:0,array('value'=>1,'id'=>'enable_related_1')).'&nbsp;<label for="enable_related_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_related]',!$model->settings['enable_related']?1:0,array('value'=>0,'id'=>'enable_related_0')).'&nbsp;<label for="enable_related_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>        
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_REBATES_COUPONS');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_REBATES_COUPONS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_rebate]',$model->settings['enable_rebate']?1:0,array('value'=>1,'id'=>'enable_rebate_1')).'&nbsp;<label for="enable_rebate_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_rebate]',!$model->settings['enable_rebate']?1:0,array('value'=>0,'id'=>'enable_rebate_0')).'&nbsp;<label for="enable_rebate_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>   
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_GIFT_CERTIFICATES');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_GIFT_CERTIFICATES_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_gift_certificate]',$model->settings['enable_gift_certificate']?1:0,array('value'=>1,'id'=>'enable_gift_certificate_1')).'&nbsp;<label for="enable_gift_certificate_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_gift_certificate]',!$model->settings['enable_gift_certificate']?1:0,array('value'=>0,'id'=>'enable_gift_certificate_0')).'&nbsp;<label for="enable_gift_certificate_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>      
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_RATINGS');?></strong><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_RATINGS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_rating]',$model->settings['enable_rating']?1:0,array('value'=>1,'id'=>'enable_rating_1')).'&nbsp;<label for="enable_rating_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_rating]',!$model->settings['enable_rating']?1:0,array('value'=>0,'id'=>'enable_rating_0')).'&nbsp;<label for="enable_rating_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>-->
            
             
             <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_SHOW_QTY_REMAINING');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-qty-remaining'); ?><br />
                    <em><?php echo Yii::t('views/config/edit_general_options','LABEL_SHOW_QTY_REMAINING_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div style="float:left">
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][enable_show_qty_remaining]',$model->settings['enable_show_qty_remaining']?1:0,array('value'=>1,'id'=>'enable_show_qty_remaining_1')).'&nbsp;<label for="enable_show_qty_remaining_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][enable_show_qty_remaining]',!$model->settings['enable_show_qty_remaining']?1:0,array('value'=>0,'id'=>'enable_show_qty_remaining_0')).'&nbsp;<label for="enable_show_qty_remaining_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div> 
                    <div id="enable_show_qty_remaining_start_at_div" style="float:left; margin-left:8px; font-size:10px;display:<?php echo($model->settings['enable_show_qty_remaining']?'block':'none');?>">
                    <div style="float:left; padding-top:5px;">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_SHOW_QTY_REMAINING_START');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-qty-remaining-start-at'); ?>
                    </div>
                     <div style="float:left; margin-left:5px;">
                    <?php
                    echo CHtml::activeTextField($model,'settings[enable_show_qty_remaining_start_at]',array('size'=>3,'maxlength'=>30, 'id'=>'settings[enable_show_qty_remaining_start_at]','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    </div>
                    </div>                
                </td>
			</tr> 
            
            
             <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_SHOW_MULTIPLE_VARIANTS_FORM');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'show-multiple-variants-form'); ?><br />
                    <em><?php echo Yii::t('views/config/edit_general_options','LABEL_SHOW_MULTIPLE_VARIANTS_FORM_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                	<div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_multiple_variants_form]',$model->settings['display_multiple_variants_form']?1:0,array('value'=>1,'id'=>'display_multiple_variants_form_1')).'&nbsp;<label for="display_multiple_variants_form_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_multiple_variants_form]',!$model->settings['display_multiple_variants_form']?1:0,array('value'=>0,'id'=>'display_multiple_variants_form_0')).'&nbsp;<label for="display_multiple_variants_form_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>
                </td>
			</tr>             
            
            
            
            
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_FEATURED_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-featured-product'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_FEATURED_PRODUCT_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_featured_products]',$model->settings['display_menu_featured_products']?1:0,array('value'=>1,'id'=>'display_menu_featured_products_1')).'&nbsp;<label for="display_menu_featured_products_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_featured_products]',!$model->settings['display_menu_featured_products']?1:0,array('value'=>0,'id'=>'display_menu_featured_products_0')).'&nbsp;<label for="display_menu_featured_products_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>       
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_NEW_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-new-product'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_NEW_PRODUCT_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_new_products]',$model->settings['display_menu_new_products']?1:0,array('value'=>1,'id'=>'display_menu_new_products_1')).'&nbsp;<label for="display_menu_new_products_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_new_products]',!$model->settings['display_menu_new_products']?1:0,array('value'=>0,'id'=>'display_menu_new_products_0')).'&nbsp;<label for="display_menu_new_products_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_ON_SALE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-on-sale'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_ON_SALE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_on_sale]',$model->settings['display_menu_on_sale']?1:0,array('value'=>1,'id'=>'display_menu_on_sale_1')).'&nbsp;<label for="display_menu_on_sale_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_on_sale]',!$model->settings['display_menu_on_sale']?1:0,array('value'=>0,'id'=>'display_menu_on_sale_0')).'&nbsp;<label for="display_menu_on_sale_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_TOP_SELLER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-top-seller'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_TOP_SELLER_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_top_sellers]',$model->settings['display_menu_top_sellers']?1:0,array('value'=>1,'id'=>'display_menu_top_sellers_1')).'&nbsp;<label for="display_menu_top_sellers_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_top_sellers]',!$model->settings['display_menu_top_sellers']?1:0,array('value'=>0,'id'=>'display_menu_top_sellers_0')).'&nbsp;<label for="display_menu_top_sellers_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_NEWS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-news'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_NEWS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_news]',$model->settings['display_menu_news']?1:0,array('value'=>1,'id'=>'display_menu_news_1')).'&nbsp;<label for="display_menu_news_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_news]',!$model->settings['display_menu_news']?1:0,array('value'=>0,'id'=>'display_menu_news_0')).'&nbsp;<label for="display_menu_news_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>         
            
 			<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_STORE_LOCATIONS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-store-locations'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_STORE_LOCATIONS_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_store_locations]',$model->settings['display_menu_store_locations']?1:0,array('value'=>1,'id'=>'display_menu_store_locations_1')).'&nbsp;<label for="display_menu_store_locations_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_store_locations]',!$model->settings['display_menu_store_locations']?1:0,array('value'=>0,'id'=>'display_menu_store_locations_0')).'&nbsp;<label for="display_menu_store_locations_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>                      
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_ADD_WISHLIST');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-add-wishlist'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_ADD_WISHLIST_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_add_wishlist]',$model->settings['display_menu_add_wishlist']?1:0,array('value'=>1,'id'=>'display_menu_add_wishlist_1')).'&nbsp;<label for="display_menu_add_wishlist_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_add_wishlist]',!$model->settings['display_menu_add_wishlist']?1:0,array('value'=>0,'id'=>'display_menu_add_wishlist_0')).'&nbsp;<label for="display_menu_add_wishlist_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE_ALERT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-price-alert'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE_ALERT_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_price_alert]',$model->settings['display_menu_price_alert']?1:0,array('value'=>1,'id'=>'display_menu_price_alert_1')).'&nbsp;<label for="display_menu_price_alert_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_price_alert]',!$model->settings['display_menu_price_alert']?1:0,array('value'=>0,'id'=>'display_menu_price_alert_0')).'&nbsp;<label for="display_menu_price_alert_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_RATE_PRODUCT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-rate-product'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_RATE_PRODUCT_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_rate_product]',$model->settings['display_menu_rate_product']?1:0,array('value'=>1,'id'=>'display_menu_rate_product_1')).'&nbsp;<label for="display_menu_rate_product_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_rate_product]',!$model->settings['display_menu_rate_product']?1:0,array('value'=>0,'id'=>'display_menu_rate_product_0')).'&nbsp;<label for="display_menu_rate_product_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     
            
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRINT_PAGE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-print-page'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRINT_PAGE_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[settings][display_menu_print_page]',$model->settings['display_menu_print_page']?1:0,array('value'=>1,'id'=>'display_menu_print_page_1')).'&nbsp;<label for="display_menu_print_page_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[settings][display_menu_print_page]',!$model->settings['display_menu_print_page']?1:0,array('value'=>0,'id'=>'display_menu_print_page_0')).'&nbsp;<label for="display_menu_print_page_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>
                    </div>                 
                </td>
			</tr>     

                  <tr>
               <td colspan="2">
               <h1><?php echo Yii::t('controllers/ConfigController','LABEL_CATEGORY_FILTERS');?></h1>
               </td>
               </tr>
                  
                  
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
             <tr id="price_range_increment" <?php echo($model->settings['cf_show_price_range']?'':'style="display:none;"');?>>
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
	$("#enable_show_qty_remaining_1").click(function(){
		$("#enable_show_qty_remaining_start_at_div").show();		
	});
	$("#enable_show_qty_remaining_0").click(function(){
		$("#enable_show_qty_remaining_start_at_div").hide();		
	});
	
	$("#enable_order_email_notification_1").click(function(){
		$(".display_email_notification").show();		
	});
	$("#enable_order_email_notification_0").click(function(){
		$(".display_email_notification").hide();		
	});
	
	$("#allow_add_to_cart_1").click(function(){
		$(".display_allow_add_to_cart").show();		
	});
	$("#allow_add_to_cart_0").click(function(){
		$(".display_allow_add_to_cart").hide();		
	});
	
	$("#display_price_1").click(function(){
		$(".display_exempt_price").show();		
	});
	$("#display_price_0").click(function(){
		$(".display_exempt_price").hide();		
	});
	
});

var dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(false);
dhxWins.attachViewportTo("div_'.$container.'");
dhxWins.setImagePath(dhx_globalImgPath);	

var wins = new Object();

//display price exceptions
var grid_display_price_exceptions = new Object();
grid_display_price_exceptions.obj = new dhtmlXGridObject("display_price_exceptions");

grid_display_price_exceptions.obj.selMultiRows = true;
grid_display_price_exceptions.obj.setImagePath(dhx_globalImgPath);		
grid_display_price_exceptions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_NAME').'",null,["text-align:center;"]);

grid_display_price_exceptions.obj.setInitWidths("40,*,*");
grid_display_price_exceptions.obj.setColAlign("center,left,left");
grid_display_price_exceptions.obj.setColSorting("na,na,na");
grid_display_price_exceptions.obj.enableResizing("false,true,true");
grid_display_price_exceptions.obj.setSkin(dhx_skin);
grid_display_price_exceptions.obj.enableDragAndDrop(false);
grid_display_price_exceptions.obj.enableMultiselect(false);
grid_display_price_exceptions.obj.enableAutoWidth(true,348,348);
grid_display_price_exceptions.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
grid_display_price_exceptions.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
grid_display_price_exceptions.obj.enablePaging(true, 100, 3, "recinfoArea_display_price_exceptions");
grid_display_price_exceptions.obj.setPagingSkin("toolbar", dhx_skin);
grid_display_price_exceptions.obj.i18n.paging={
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

grid_display_price_exceptions.obj.init();

grid_display_price_exceptions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_display_price_exceptions',array()).'";
grid_display_price_exceptions.obj.loadXML(grid_display_price_exceptions.obj.xmlOrigFileUrl);


grid_display_price_exceptions.toolbar = new Object();
grid_display_price_exceptions.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_display_price_exceptions");
grid_display_price_exceptions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
grid_display_price_exceptions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
grid_display_price_exceptions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");

grid_display_price_exceptions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_general_options','LABEL_TITLE_DISPLAY_PRICE_EXCEPTIONS').'";

	switch (id) {
		case "add":
			
			grid_display_price_exceptions.toolbar.add();
			break;
		case "delete":			
			var checked = grid_display_price_exceptions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_general_options','LABEL_ALERT_DELETE_DISPLAY_PRICE_EXCEPTION').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_display_price_exceptions').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid(grid_display_price_exceptions.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/products/edit_suggestion','LABEL_ALERT_NO_CHECKED_PRODUCT').'");	
			}
			break;
	}
});
grid_display_price_exceptions.toolbar.add = function(current_id){
	var wins = new Object();
	name="'.Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE_EXCEPTIONS').'";
	
	wins.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 700, 480);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	//wins.obj.center();
				
	wins.toolbar = new Object();
	
	wins.toolbar.load = function(current_id){
		var obj = wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins.toolbar.save(id);
					break;
				case "save_close":
					wins.toolbar.save(id,1);
					break;
			}
		});			
	}	
	
	wins.toolbar.obj = wins.obj.attachToolbar();
	wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins.toolbar.load(current_id);
	
	wins.grid_display_price_exceptions = new Object();
	wins.grid_display_price_exceptions.obj = wins.obj.attachLayout("1C");
	wins.grid_display_price_exceptions.A = new Object();
	wins.grid_display_price_exceptions.A.obj = wins.grid_display_price_exceptions.obj.cells("a");
	wins.grid_display_price_exceptions.A.obj.hideHeader();	
	wins.grid_display_price_exceptions.A.grid = new Object();
	
	wins.grid_display_price_exceptions.A.grid.obj = wins.grid_display_price_exceptions.A.obj.attachGrid();
	wins.grid_display_price_exceptions.A.grid.obj.setImagePath(dhx_globalImgPath);	
	wins.grid_display_price_exceptions.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRODUCT_TYPE').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,"text-align:center;","text-align:center;","text-align:right"]);
	wins.grid_display_price_exceptions.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#select_filter_custom_product_type,");
	
	// custom text filter input
	wins.grid_display_price_exceptions.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
	wins.grid_display_price_exceptions.A.grid.obj._in_header_select_filter_custom_product_type=select_filter_custom_product_type;
	
	
	wins.grid_display_price_exceptions.A.grid.obj.setInitWidths("40,*,200,150,110");
	wins.grid_display_price_exceptions.A.grid.obj.setColAlign("center,left,left,left,right");
	wins.grid_display_price_exceptions.A.grid.obj.setColSorting("na,na,na,na,na");
	wins.grid_display_price_exceptions.A.grid.obj.enableResizing("false,true,true,true,true");
	wins.grid_display_price_exceptions.A.grid.obj.setSkin(dhx_skin);
	wins.grid_display_price_exceptions.A.grid.obj.enableDragAndDrop(false);
	wins.grid_display_price_exceptions.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
	//wins.grid_display_price_exceptions.A.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	//Paging
	wins.grid_display_price_exceptions.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_win\'></div>");
	wins.grid_display_price_exceptions.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	wins.grid_display_price_exceptions.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_win");
	wins.grid_display_price_exceptions.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
	wins.grid_display_price_exceptions.A.grid.obj.i18n.paging={
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
	wins.grid_display_price_exceptions.A.grid.obj.init();
	
	// set filter input names
	wins.grid_display_price_exceptions.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	wins.grid_display_price_exceptions.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
	wins.grid_display_price_exceptions.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("SELECT")[0].name="product_type";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	wins.grid_display_price_exceptions.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_display_price_exceptions_add').'";
	
	// load the initial grid
	load_grid(wins.grid_display_price_exceptions.A.grid.obj);		
	
	wins.grid_display_price_exceptions.A.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	wins.grid_display_price_exceptions.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});	
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		wins = new Object();
		return true;
	});			
	
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		
		var checked = wins.grid_display_price_exceptions.A.grid.obj.getCheckedRows(0);
		if (checked) {		
			
			checked = checked.split(",");
			var ids=[];
			
			for (var i=0;i<checked.length;++i) {
				if (checked[i]) {
					ids.push("ids[]="+checked[i]);									
				}
			}			
	
			$.ajax({
				url: "'.CController::createUrl('save_display_price_exceptions',array()).'",
				type: "POST",
				data: ids.join("&"),
				beforeSend: function(){			
					// clear all errors					
					$("#"+wins.grid_display_price_exceptions.obj.cont.obj.id+" span.error").html("");
					$("#"+wins.grid_display_price_exceptions.obj.cont.obj.id+" *").removeClass("error");
				
					obj.disableItem(id);			
				},
				complete: function(){
					
					if(close){
						wins.obj.close();
					}else{
						if (typeof obj.enableItem == "function") obj.enableItem(id);
					}
				},
				success: function(data){	
					load_grid(wins.grid_display_price_exceptions.A.grid.obj);
					load_grid(grid_display_price_exceptions.obj);
					alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
				}
			});	
		} else {
			alert("'.Yii::t('views/products/edit_suggestion','LABEL_ALERT_NO_CHECKED_PRODUCT').'");		
		}	
	}
}



//allow add to cart exceptions
var grid_allow_add_to_cart_exceptions = new Object();
grid_allow_add_to_cart_exceptions.obj = new dhtmlXGridObject("allow_add_to_cart_exceptions");

grid_allow_add_to_cart_exceptions.obj.selMultiRows = true;
grid_allow_add_to_cart_exceptions.obj.setImagePath(dhx_globalImgPath);		
grid_allow_add_to_cart_exceptions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_NAME').'",null,["text-align:center;"]);

grid_allow_add_to_cart_exceptions.obj.setInitWidths("40,*,*");
grid_allow_add_to_cart_exceptions.obj.setColAlign("center,left,left");
grid_allow_add_to_cart_exceptions.obj.setColSorting("na,na,na");
grid_allow_add_to_cart_exceptions.obj.enableResizing("false,true,true");
grid_allow_add_to_cart_exceptions.obj.setSkin(dhx_skin);
grid_allow_add_to_cart_exceptions.obj.enableDragAndDrop(false);
grid_allow_add_to_cart_exceptions.obj.enableMultiselect(false);
grid_allow_add_to_cart_exceptions.obj.enableAutoWidth(true,348,348);
grid_allow_add_to_cart_exceptions.obj.enableRowsHover(true,dhx_rowhover_pointer);

//Paging
grid_allow_add_to_cart_exceptions.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
grid_allow_add_to_cart_exceptions.obj.enablePaging(true, 100, 3, "recinfoArea_allow_add_to_cart_exceptions");
grid_allow_add_to_cart_exceptions.obj.setPagingSkin("toolbar", dhx_skin);
grid_allow_add_to_cart_exceptions.obj.i18n.paging={
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

grid_allow_add_to_cart_exceptions.obj.init();

grid_allow_add_to_cart_exceptions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_allow_add_to_cart_exceptions',array()).'";
grid_allow_add_to_cart_exceptions.obj.loadXML(grid_allow_add_to_cart_exceptions.obj.xmlOrigFileUrl);


grid_allow_add_to_cart_exceptions.toolbar = new Object();
grid_allow_add_to_cart_exceptions.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_allow_add_to_cart_exceptions");
grid_allow_add_to_cart_exceptions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
grid_allow_add_to_cart_exceptions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
grid_allow_add_to_cart_exceptions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");

grid_allow_add_to_cart_exceptions.toolbar.obj.attachEvent("onClick", function(id){	
	var obj = this;
	var title = "'.Yii::t('views/config/edit_general_options','LABEL_TITLE_ALLOW_ADD_TO_CART_EXCEPTIONS').'";

	switch (id) {
		case "add":
			
			grid_allow_add_to_cart_exceptions.toolbar.add();
			break;
		case "delete":			
			var checked = grid_allow_add_to_cart_exceptions.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/config/edit_general_options','LABEL_ALERT_DELETE_ADD_TO_CART_EXCEPTION').'")) {
					checked = checked.split(",");
					var ids=[];
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							ids.push("ids[]="+checked[i]);									
						}
					}
					
					$.ajax({
						url: "'.CController::createUrl('delete_allow_add_to_cart_exceptions').'",
						type: "POST",
						data: ids.join("&"),
						beforeSend: function(){		
							obj.disableItem(id);
						},
						complete: function(){
							if (typeof obj.enableItem == "function") obj.enableItem(id);	
						},							
						success: function(data){													
							load_grid(grid_allow_add_to_cart_exceptions.obj);
							alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
						}
					});						
				}
			} else {
				alert("'.Yii::t('views/products/edit_suggestion','LABEL_ALERT_NO_CHECKED_PRODUCT').'");	
			}
			break;
	}
});
grid_allow_add_to_cart_exceptions.toolbar.add = function(current_id){
	var wins = new Object();
	name="'.Yii::t('views/config/edit_general_options','LABEL_ALLOW_ADD_TO_CART_EXCEPTIONS').'";
	
	wins.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 700, 480);
	wins.obj.setText(name);
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	//wins.obj.center();
				
	wins.toolbar = new Object();
	
	wins.toolbar.load = function(current_id){
		var obj = wins.toolbar.obj;
		
		obj.clearAll();
		obj.detachAllEvents();
		
		obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");
		obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif"); 
		
		obj.attachEvent("onClick",function(id){
			switch (id) {
				case "save":	
					wins.toolbar.save(id);
					break;
				case "save_close":
					wins.toolbar.save(id,1);
					break;
			}
		});			
	}	
	
	wins.toolbar.obj = wins.obj.attachToolbar();
	wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	wins.toolbar.load(current_id);
	
	wins.grid_allow_add_to_cart_exceptions = new Object();
	wins.grid_allow_add_to_cart_exceptions.obj = wins.obj.attachLayout("1C");
	wins.grid_allow_add_to_cart_exceptions.A = new Object();
	wins.grid_allow_add_to_cart_exceptions.A.obj = wins.grid_allow_add_to_cart_exceptions.obj.cells("a");
	wins.grid_allow_add_to_cart_exceptions.A.obj.hideHeader();	
	wins.grid_allow_add_to_cart_exceptions.A.grid = new Object();
	
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj = wins.grid_allow_add_to_cart_exceptions.A.obj.attachGrid();
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setImagePath(dhx_globalImgPath);	
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRODUCT_TYPE').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,"text-align:center;","text-align:center;","text-align:right"]);
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#select_filter_custom_product_type,");
	
	// custom text filter input
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj._in_header_select_filter_custom_product_type=select_filter_custom_product_type;
	
	
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setInitWidths("40,*,200,150,110");
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setColAlign("center,left,left,left,right");
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setColSorting("na,na,na,na,na");
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.enableResizing("false,true,true,true,true");
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setSkin(dhx_skin);
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.enableDragAndDrop(false);
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
	//wins.grid_allow_add_to_cart_exceptions.A.grid.obj.enableRowsHover(true,dhx_rowhover);
	
	//Paging
	wins.grid_allow_add_to_cart_exceptions.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_win\'></div>");
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_win");
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.i18n.paging={
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
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.init();
	
	// set filter input names
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("SELECT")[0].name="product_type";
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_allow_add_to_cart_exceptions_add').'";
	
	// load the initial grid
	load_grid(wins.grid_allow_add_to_cart_exceptions.A.grid.obj);		
	
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	wins.grid_allow_add_to_cart_exceptions.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});	
	
	// clean variables
	wins.obj.attachEvent("onClose",function(win){
		wins = new Object();
		return true;
	});			
	
	
	wins.toolbar.save = function(id,close){
		var obj = wins.toolbar.obj;
		
		var checked = wins.grid_allow_add_to_cart_exceptions.A.grid.obj.getCheckedRows(0);
		if (checked) {		
			
			checked = checked.split(",");
			var ids=[];
			
			for (var i=0;i<checked.length;++i) {
				if (checked[i]) {
					ids.push("ids[]="+checked[i]);									
				}
			}			
	
			$.ajax({
				url: "'.CController::createUrl('save_allow_add_to_cart_exceptions',array()).'",
				type: "POST",
				data: ids.join("&"),
				beforeSend: function(){			
					// clear all errors					
					$("#"+wins.grid_allow_add_to_cart_exceptions.obj.cont.obj.id+" span.error").html("");
					$("#"+wins.grid_allow_add_to_cart_exceptions.obj.cont.obj.id+" *").removeClass("error");
				
					obj.disableItem(id);			
				},
				complete: function(){
					
					if(close){
						wins.obj.close();
					}else{
						if (typeof obj.enableItem == "function") obj.enableItem(id);
					}
				},
				success: function(data){	
					load_grid(wins.grid_allow_add_to_cart_exceptions.A.grid.obj);
					load_grid(grid_allow_add_to_cart_exceptions.obj);
					alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
				}
			});	
		} else {
			alert("'.Yii::t('views/products/edit_suggestion','LABEL_ALERT_NO_CHECKED_PRODUCT').'");		
		}	
	}
}
';

echo Html::script($script); 
?>      