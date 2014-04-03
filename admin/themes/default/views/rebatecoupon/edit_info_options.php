<?php 
$model_name = get_class($model);
$columns = Html::getColumnsMaxLength(Tbl_RebateCoupon::tableName());

$app = Yii::app();
switch($app->params['weighing_unit']){
	case '0':
		$weighing_unit = Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT_LB');
	break;
	case '1':
		$weighing_unit = Yii::t('views/config/edit_shipping_options','LABEL_WEIGHT_UNIT_KG');
	break;	
}

$help_hint_path = '/marketing/rebates-coupons/information/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    
    
    <div style="float:left; width:50%; margin-right:10px; border-right: solid 1px #B3B3FF; padding-right:10px;">
    <div id="<?php echo $container;?>_all_coupon" style="display:<?php echo($model->type != 5 ? 'block' : 'none');?>">
    	<div style="margin-bottom:10px">
            <?php 
            echo CHtml::radioButton($model_name.'[coupon]',$model->coupon?1:0,array('value'=>1,'id'=>$container.'_coupon_1')).'&nbsp;<label for="'.$container.'_coupon_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_COUPON').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[coupon]',!$model->coupon?1:0,array('value'=>0,'id'=>$container.'_coupon_0')).'&nbsp;<label for="'.$container.'_coupon_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_REBATE').'</label>'; 
            ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'coupon'); ?>
        </div>
        <div id="<?php echo $container;?>_info_coupon" style="display:<?php echo($model->coupon ? 'block' : 'none');?>">
            <div style="float:left">
          		<strong><?php echo Yii::t('global','LABEL_CODE')?></strong><?php echo isset($columns['coupon_code']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_coupon_code_maxlength">'.($columns['coupon_code']-strlen($model->coupon_code)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'coupon-code'); ?>
                <div>
                <?php
                echo CHtml::activeTextField($model,'coupon_code',array('size'=>20,'maxlength'=>$columns['coupon_code'], 'id'=>$container.'_coupon_code'));
                ?>
                <br /><span id="<?php echo $container; ?>_coupon_code_errorMsg" class="error"></span>
                </div>     
            </div>
            <div style="float:left; margin-left:5px;">
          		<strong><?php echo Yii::t('views/rebatecoupon/edit_info_options','LABEL_MAX_USE')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'max-use'); ?>
                <div>
                <?php
                echo CHtml::activeTextField($model,'coupon_max_usage',array('size'=>5, 'id'=>$container.'_coupon_max_usage','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                ?>
                <br /><span id="<?php echo $container; ?>_coupon_max_usage_errorMsg" class="error"></span>
                </div>    
            </div>  
            <div style="float:left; margin-left:5px;">
          		<strong><?php echo Yii::t('views/rebatecoupon/edit_info_options','LABEL_MAX_USE_CUSTOMER')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'max-use-customer'); ?>
                <div>
                <?php
                echo CHtml::activeTextField($model,'coupon_max_usage_customer',array('size'=>5, 'id'=>$container.'_coupon_max_usage_customer','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                ?>
                <br /><span id="<?php echo $container; ?>_coupon_max_usage_customer_errorMsg" class="error"></span>
                </div>       
            </div>
            <div style="clear:both"></div>    
        </div>   
    </div>
    <div class="row">
        <strong><?php echo Yii::t('views/rebatecoupon/edit_info_options','LABEL_TYPE')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'type'); ?>
        <div>
        <?php 
        echo CHtml::dropDownList($model_name.'[type]',$model->type,array('-1'=>Yii::t('controllers/RebatecouponController','LABEL_REBATE_-1'),'0'=>Yii::t('controllers/RebatecouponController','LABEL_REBATE_0'),'1'=>Yii::t('controllers/RebatecouponController','LABEL_REBATE_1'),'5'=>Yii::t('controllers/RebatecouponController','LABEL_REBATE_5'),'2'=>Yii::t('controllers/RebatecouponController','LABEL_REBATE_2'),'3'=>Yii::t('controllers/RebatecouponController','LABEL_REBATE_3'),'4'=>Yii::t('controllers/RebatecouponController','LABEL_REBATE_4')),array('id'=>$container.'_type')); 
		
        ?>
        <br /><span id="<?php echo $container; ?>_type_errorMsg" class="error"></span>
        </div>        
    </div>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
        ?>
        </div>        
    </div>
           
                      
	<div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_name_maxlength">'.($columns['name']-strlen($model->name)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('style' => 'width: 250px;','maxlength'=>$columns['name'], 'id'=>$container.'_name'));
        ?>
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>                
	</div> 
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_DESCRIPTION')?></strong><?php echo isset($columns['description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_description_maxlength">'.($columns['description']-strlen($model->description)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'description'); ?>
        <div id="<?php echo $container; ?>_rebate_coupon_description" style="width:98%;height:155px;"></div>                
	</div>   
    <div class="row">
        <div style="float:left; padding-right:5px;">
            <strong><?php echo Yii::t('global','LABEL_START_DATE')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-date'); ?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'start_date',array('size'=>18, 'id'=>$container.'_start_date'));
            ?>
            <br /><span id="<?php echo $container; ?>_start_date_errorMsg" class="error"></span>
            </div>                
        </div>                
        <div style="float:left; padding-right:5px;">
            <strong><?php echo Yii::t('views/rebatecoupon/edit_info_options','LABEL_END_DATE')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-date'); ?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'end_date',array('size'=>18, 'id'=>$container.'_end_date'));
            ?>
            <br /><span id="<?php echo $container; ?>_end_date_errorMsg" class="error"></span>
            </div>                
        </div>               
        <div style="clear:both;"></div>
        <span id="<?php echo $container; ?>_dates_errorMsg" class="error"></span>
	</div> 
    </div>
    <div id="<?php echo $container;?>_rebate_coupon_form" style="float:left; width:45%"></div>  
    <div style="clear:both"></div>
         
</div>
</div>
<?php
$script = '
$(function(){
	$("#'.$container.'_start_date,#'.$container.'_end_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});	
	
	$("#'.$container.'_coupon_1").click(function(){
		$("#'.$container.'_info_coupon").show();
		'.$container.'_change_type();		
	});
	
	$("#'.$container.'_coupon_0").click(function(){
		$("#'.$container.'_info_coupon").hide();
		'.$container.'_change_type();			
	});
	
	'.$container.'.layout.A.tabbar = new Object();
	
	'.$container.'.layout.A.tabbar.obj = new dhtmlXTabBar("'.$container.'_rebate_coupon_description","top");
	'.$container.'.layout.A.tabbar.obj.setImagePath(dhx_globalImgPath);
	'.$container.'.layout.A.tabbar.obj.loadXML("'.CController::createUrl('xml_rebate_coupon_description',array('container'=>$container,'id'=>$model->id)).'", function(){
		'.$container.'.load_og_form();		
	});
	
	
	$("#'.$container.'_type").change(function(){
		'.$container.'_change_type();		
	});
	
	'.$container.'_change_type();
	
	$("#'.$container.'_discount_type").change(function(){
		'.$container.'_change_discount_type();
	});
	
	
	
});

function '.$container.'_change_discount_type(){
	if($("#'.$container.'_discount_type").val()==1){
		$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('global','LABEL_PERCENT').'");
	}else{
		$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('global','LABEL_PRICE').'");
	}
}

function '.$container.'_change_type(){
	$("#'.$container.'_all_coupon").show();
	switch($("#'.$container.'_type").val()){
		//Percent/Fixed amount off product price
		case "0":
			var content;
			
			content = "<h3>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_CONDITION_TO_BE_MET').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_MAX_QTY_ALLOWED_PURCHASE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'max-qty-allowed')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'max_qty_allowed',array('size'=>5, 'id'=>$container.'_max_qty_allowed','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_max_qty_allowed_errorMsg\" class=\"error\"></span></div></div>";
			
			content = content + "<h3 style=\"margin-top:20px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_IF_CONDITION_MET').'</h3><div style=\"float:left;\"><strong>'.Yii::t('global','LABEL_DISCOUNT_TYPE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount-type')).'<br /><div>'.Html::remove_blank_line_addslashes(CHtml::activeDropDownList($model,'discount_type',array(0=>Yii::t('global','LABEL_FIXED'),1=>Yii::t('global','LABEL_PERCENTAGE')),array( 'id'=>$container.'_discount_type'))).'<br /><span id=\"'.$container.'_discount_type_errorMsg\" class=\"error\"></span></div></div><div style=\"float:left; margin-left:5px;\"><strong id=\"'.$container.'_title_price_percent\">'.($model->discount_type?Yii::t('global','LABEL_PERCENT'):Yii::t('global','LABEL_PRICE')).'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'discount',array('size'=>5, 'id'=>$container.'_discount','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_discount_errorMsg\" class=\"error\"></span></div></div><div style=\"clear:both;\"></div>";
			
			if($("#'.$container.'_coupon_1:checked").is(":checked")){
				content = content + "<h3 style=\"margin-top:20px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_OTHERS_CONDITION').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_APPLICABLE_PRODUCT_ALREADY_SALE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'apply-products-on-sale')).'<div>'.Html::remove_blank_line_addslashes(CHtml::radioButton($model_name.'[applicable_on_sale]',$model->applicable_on_sale?1:0,array('value'=>1,'id'=>$container.'_applicable_on_sale_1'))).'&nbsp;<label for=\"'.$container.'_applicable_on_sale_1\" style=\"display:inline; text-align: left;\">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.Html::remove_blank_line_addslashes(CHtml::radioButton($model_name.'[applicable_on_sale]',!$model->applicable_on_sale?1:0,array('value'=>0,'id'=>$container.'_applicable_on_sale_0'))).'&nbsp;<label for=\"'.$container.'_applicable_on_sale_0\" style=\"display:inline; text-align: left;\">'.Yii::t('global','LABEL_NO').'</label></div></div>";
			}
			
			$("#'.$container.'_rebate_coupon_form").html("").append(content);
		break;
		//Percent/Fixed amount off cart total
		case "1":
			var content;
			content = "<h3>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_CONDITION_TO_BE_MET').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_MIN_CART_VALUE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'min-cart-value')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'min_cart_value',array('size'=>10, 'id'=>$container.'_min_cart_value','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_min_cart_value_errorMsg\" class=\"error\"></span></div></div>";
			
			content = content + "<h3 style=\"margin-top:20px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_IF_CONDITION_MET').'</h3><div style=\"float:left;\"><strong>'.Yii::t('global','LABEL_DISCOUNT_TYPE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount-type')).'<br /><div>'.Html::remove_blank_line_addslashes(CHtml::activeDropDownList($model,'discount_type',array(0=>Yii::t('global','LABEL_FIXED'),1=>Yii::t('global','LABEL_PERCENTAGE')),array( 'id'=>$container.'_discount_type'))).'<br /><span id=\"'.$container.'_discount_type_errorMsg\" class=\"error\"></span></div></div><div style=\"float:left; margin-left:5px;\"><strong id=\"'.$container.'_title_price_percent\">'.($model->discount_type?Yii::t('global','LABEL_PERCENT'):Yii::t('global','LABEL_PRICE')).'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'discount',array('size'=>5, 'id'=>$container.'_discount','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_discount_errorMsg\" class=\"error\"></span></div></div><div style=\"clear:both;\"></div>";
			
			$("#'.$container.'_rebate_coupon_form").html("").append(content);
		break;
		//Buy X Get Y
		case "2":
			var content;
			content = "<h3>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_CONDITION_TO_BE_MET').'</h3><div style=\"margin-bottom:5px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_EXPLICATION_BUYX_GETY').'</div><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_BUY_X_QTY').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'buy-x-qty')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'buy_x_qty',array('size'=>5, 'id'=>$container.'_buy_x_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_buy_x_qty_errorMsg\" class=\"error\"></span></div></div><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_GET_Y_QTY').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'get-y-qty')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'get_y_qty',array('size'=>5, 'id'=>$container.'_get_y_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_get_y_qty_errorMsg\" class=\"error\"></span></div></div><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_MAX_QTY_ALLOWED_PURCHASE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'max-qty-allowed')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'max_qty_allowed',array('size'=>5, 'id'=>$container.'_max_qty_allowed','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_max_qty_allowed_errorMsg\" class=\"error\"></span></div></div><h3 style=\"margin-top:20px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_IF_CONDITION_MET').'</h3><div style=\"float:left;\"><strong>'.Yii::t('global','LABEL_DISCOUNT_TYPE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount-type')).'<br /><div>'.Html::remove_blank_line_addslashes(CHtml::activeDropDownList($model,'discount_type',array(0=>Yii::t('global','LABEL_FIXED'),1=>Yii::t('global','LABEL_PERCENTAGE')),array( 'id'=>$container.'_discount_type'))).'<br /><span id=\"'.$container.'_discount_type_errorMsg\" class=\"error\"></span></div></div><div style=\"float:left; margin-left:5px;\"><strong id=\"'.$container.'_title_price_percent\">'.($model->discount_type?Yii::t('global','LABEL_PERCENT'):Yii::t('global','LABEL_PRICE')).'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'discount',array('size'=>5, 'id'=>$container.'_discount','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_discount_errorMsg\" class=\"error\"></span></div></div><div style=\"clear:both;\"></div>";

			if($("#'.$container.'_coupon_1:checked").is(":checked")){
				content = content + "<h3 style=\"margin-top:20px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_OTHERS_CONDITION').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_APPLICABLE_PRODUCT_ALREADY_SALE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'apply-products-on-sale')).'<div>'.Html::remove_blank_line_addslashes(CHtml::radioButton($model_name.'[applicable_on_sale]',$model->applicable_on_sale?1:0,array('value'=>1,'id'=>$container.'_applicable_on_sale_1'))).'&nbsp;<label for=\"'.$container.'_applicable_on_sale_1\" style=\"display:inline; text-align: left;\">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.Html::remove_blank_line_addslashes(CHtml::radioButton($model_name.'[applicable_on_sale]',!$model->applicable_on_sale?1:0,array('value'=>0,'id'=>$container.'_applicable_on_sale_0'))).'&nbsp;<label for=\"'.$container.'_applicable_on_sale_0\" style=\"display:inline; text-align: left;\">'.Yii::t('global','LABEL_NO').'</label></div></div>";
			}
			
			$("#'.$container.'_rebate_coupon_form").html("").append(content);
			
		break;
		//Free gift with $ purchase
		case "3":
			$("#'.$container.'_rebate_coupon_form").html("").append("<h3>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_CONDITION_TO_BE_MET').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_MIN_CART_VALUE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'min-cart-value')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'min_cart_value',array('size'=>10, 'id'=>$container.'_min_cart_value','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_min_cart_value_errorMsg\" class=\"error\"></span></div></div><h3 style=\"margin-top:20px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_OTHERS_CONDITION').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_ALL_PRODUCT_SELECTED').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'all-product')).'<br />'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_ALL_PRODUCT_SELECTED_DESCRIPTION').'<div>'.Html::remove_blank_line_addslashes(CHtml::radioButton($model_name.'[all_product]',$model->all_product?1:0,array('value'=>1,'id'=>$container.'_all_product_1'))).'&nbsp;<label for=\"'.$container.'_all_product_1\" style=\"display:inline; text-align: left;\">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.Html::remove_blank_line_addslashes(CHtml::radioButton($model_name.'[all_product]',!$model->all_product?1:0,array('value'=>0,'id'=>$container.'_all_product_0'))).'&nbsp;<label for=\"'.$container.'_all_product_0\" style=\"display:inline; text-align: left;\">'.Yii::t('global','LABEL_NO').'</label></div></div>");
		break;
		//Free shipping
		case "4":
			$("#'.$container.'_rebate_coupon_form").html("").append("<h3>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_CONDITION_TO_BE_MET').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_MIN_CART_VALUE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'min-cart-value')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'min_cart_value',array('size'=>10, 'id'=>$container.'_min_cart_value','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_min_cart_value_errorMsg\" class=\"error\"></span></div></div><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_MAX_WEIGHT').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'max-weight')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'max_weight',array('size'=>10, 'id'=>$container.'_max_weight','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'&nbsp;<span style=\"font-size:14px;\">' . $weighing_unit . '</span><br /><span id=\"'.$container.'_max_weight_errorMsg\" class=\"error\"></span></div></div><strong>'.Yii::t('views/config/edit_shipping_options','LABEL_FREE_SHIPPING_REGION').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'free-shipping-product-exceptions')).'<br /><div style=\"width:450px\"><div id=\"'.$container.'_my_toolbar_here\"></div><div id=\"'.$container.'_free_shipping_region\" style=\"height:150px;\"></div><div id=\"'.$container.'_recinfoArea\"></div></div><br /><strong>'.Yii::t('views/config/edit_shipping_options','LABEL_FREE_SHIPPING_PRODUCT_EXCEPTIONS').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'free-shipping-product-exceptions')).'<br /><div style=\"width:450px\"><div id=\"'.$container.'_my_toolbar_free_shipping_product_exceptions\"></div><div id=\"'.$container.'_free_shipping_product_exceptions\" style=\"height:150px;\"></div><div id=\"'.$container.'_recinfoArea_free_shipping_product_exceptions\"></div></div>");
			

			//Free Shipping Region Grid
			var '.$container.'_dhxWins = new dhtmlXWindows();
			'.$container.'_dhxWins.enableAutoViewport(false);
			'.$container.'_dhxWins.attachViewportTo("'.$containerLayout.'");
			'.$container.'_dhxWins.setImagePath(dhx_globalImgPath);
			
			var '.$container.'_grid_regions = new Object();
			'.$container.'_grid_regions.obj = new dhtmlXGridObject("'.$container.'_free_shipping_region");
			
			'.$container.'_grid_regions.obj.selMultiRows = true;
			'.$container.'_grid_regions.obj.setImagePath(dhx_globalImgPath);		
			'.$container.'_grid_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);
			
			'.$container.'_grid_regions.obj.setInitWidths("40,204,204");
			'.$container.'_grid_regions.obj.setColAlign("center,left,left");
			'.$container.'_grid_regions.obj.setColSorting("na,na,na");
			'.$container.'_grid_regions.obj.enableResizing("false,true,true");
			'.$container.'_grid_regions.obj.setSkin(dhx_skin);
			'.$container.'_grid_regions.obj.enableDragAndDrop(false);
			'.$container.'_grid_regions.obj.enableMultiselect(false);
			'.$container.'_grid_regions.obj.enableAutoWidth(true,448,448);
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
			
			'.$container.'_grid_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_free_shipping',array()).'";
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
				var title = "'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_FREE_SHIPPING_REGION').'";
			
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
									data: ids.join("&"),
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
				
				'.$container.'_wins.obj = '.$container.'_dhxWins.createWindow("editRegionWindow", 10, 10, 500, 200);
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
										data: { "ids[]":current_id },
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
					data: { "id":current_id },
					success: function(data){
						'.$container.'_wins.grid_regions.A.obj.attachHTMLString(data);		
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
							$(".div_'.$containerObj.'").removeClass("error_background");
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
									$(".div_'.$containerObj.'").addClass("error_background");																														
								} else {					
									load_grid('.$container.'_grid_regions.obj);
									
									alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
									
									// Verify if popup window is close
									if(!close){
										var nom_edit = $("#'.$container.'_country_code option:selected").text();
										nom_edit = nom_edit + " - " + $("#'.$container.'_state_code option:selected").text();
			
										$("#'.$container.'_id").val(data.id);
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


			//free shipping product exceptions
			var '.$container.'_free_shipping_product_exceptions = new Object();
			'.$container.'_free_shipping_product_exceptions.obj = new dhtmlXGridObject("'.$container.'_free_shipping_product_exceptions");
			
			'.$container.'_free_shipping_product_exceptions.obj.selMultiRows = true;
			'.$container.'_free_shipping_product_exceptions.obj.setImagePath(dhx_globalImgPath);		
			'.$container.'_free_shipping_product_exceptions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_NAME').'",null,["text-align:center;"]);
			
			'.$container.'_free_shipping_product_exceptions.obj.setInitWidths("40,*,*");
			'.$container.'_free_shipping_product_exceptions.obj.setColAlign("center,left,left");
			'.$container.'_free_shipping_product_exceptions.obj.setColSorting("na,na,na");
			'.$container.'_free_shipping_product_exceptions.obj.enableResizing("false,true,true");
			'.$container.'_free_shipping_product_exceptions.obj.setSkin(dhx_skin);
			'.$container.'_free_shipping_product_exceptions.obj.enableDragAndDrop(false);
			'.$container.'_free_shipping_product_exceptions.obj.enableMultiselect(false);
			'.$container.'_free_shipping_product_exceptions.obj.enableAutoWidth(true,348,348);
			'.$container.'_free_shipping_product_exceptions.obj.enableRowsHover(true,dhx_rowhover_pointer);
			
			//Paging
			'.$container.'_free_shipping_product_exceptions.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
			'.$container.'_free_shipping_product_exceptions.obj.enablePaging(true, 100, 3, "'.$container.'_recinfoArea_free_shipping_product_exceptions");
			'.$container.'_free_shipping_product_exceptions.obj.setPagingSkin("toolbar", dhx_skin);
			'.$container.'_free_shipping_product_exceptions.obj.i18n.paging={
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
			
			'.$container.'_free_shipping_product_exceptions.obj.init();
			
			'.$container.'_free_shipping_product_exceptions.obj.xmlOrigFileUrl = "'.CController::createUrl('config/xml_list_free_shipping_product_exceptions',array()).'";
			'.$container.'_free_shipping_product_exceptions.obj.loadXML('.$container.'_free_shipping_product_exceptions.obj.xmlOrigFileUrl);
			
			
			'.$container.'_free_shipping_product_exceptions.toolbar = new Object();
			'.$container.'_free_shipping_product_exceptions.toolbar.obj = new dhtmlXToolbarObject("'.$container.'_my_toolbar_free_shipping_product_exceptions");
			'.$container.'_free_shipping_product_exceptions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
			'.$container.'_free_shipping_product_exceptions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
			'.$container.'_free_shipping_product_exceptions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
			
			'.$container.'_free_shipping_product_exceptions.toolbar.obj.attachEvent("onClick", function(id){	
				var obj = this;
				var title = "'.Yii::t('views/config/edit_general_options','LABEL_TITLE_DISPLAY_PRICE_EXCEPTIONS').'";
			
				switch (id) {
					case "add":
						
						'.$container.'_free_shipping_product_exceptions.toolbar.add();
						break;
					case "delete":			
						var checked = '.$container.'_free_shipping_product_exceptions.obj.getCheckedRows(0);
						
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
									url: "'.CController::createUrl('config/delete_free_shipping_product_exceptions').'",
									type: "POST",
									data: ids.join("&"),
									beforeSend: function(){		
										obj.disableItem(id);
									},
									complete: function(){
										if (typeof obj.enableItem == "function") obj.enableItem(id);	
									},							
									success: function(data){													
										load_grid('.$container.'_free_shipping_product_exceptions.obj);
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
			'.$container.'_free_shipping_product_exceptions.toolbar.add = function(current_id){
				var wins = new Object();
				name="'.Yii::t('views/config/edit_general_options','LABEL_DISPLAY_PRICE_EXCEPTIONS').'";
				
				wins.obj = '.$container.'_dhxWins.createWindow("editRegionWindow", 10, 10, 700, 480);
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
				
				wins.free_shipping_product_exceptions = new Object();
				wins.free_shipping_product_exceptions.obj = wins.obj.attachLayout("1C");
				wins.free_shipping_product_exceptions.A = new Object();
				wins.free_shipping_product_exceptions.A.obj = wins.free_shipping_product_exceptions.obj.cells("a");
				wins.free_shipping_product_exceptions.A.obj.hideHeader();	
				wins.free_shipping_product_exceptions.A.grid = new Object();
				
				wins.free_shipping_product_exceptions.A.grid.obj = wins.free_shipping_product_exceptions.A.obj.attachGrid();
				wins.free_shipping_product_exceptions.A.grid.obj.setImagePath(dhx_globalImgPath);	
				wins.free_shipping_product_exceptions.A.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_PRODUCT_TYPE').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,"text-align:center;","text-align:center;","text-align:right"]);
				wins.free_shipping_product_exceptions.A.grid.obj.attachHeader(",#text_filter_custom,#text_filter_custom,#select_filter_custom_product_type,");
				
				// custom text filter input
				wins.free_shipping_product_exceptions.A.grid.obj._in_header_text_filter_custom=text_filter_custom;
				wins.free_shipping_product_exceptions.A.grid.obj._in_header_select_filter_custom_product_type=select_filter_custom_product_type;
				
				
				wins.free_shipping_product_exceptions.A.grid.obj.setInitWidths("40,*,200,150,110");
				wins.free_shipping_product_exceptions.A.grid.obj.setColAlign("center,left,left,left,right");
				wins.free_shipping_product_exceptions.A.grid.obj.setColSorting("na,na,na,na,na");
				wins.free_shipping_product_exceptions.A.grid.obj.enableResizing("false,true,true,true,true");
				wins.free_shipping_product_exceptions.A.grid.obj.setSkin(dhx_skin);
				wins.free_shipping_product_exceptions.A.grid.obj.enableDragAndDrop(false);
				wins.free_shipping_product_exceptions.A.grid.obj.enableAlterCss("even_grid_no_action","uneven_grid_no_action");
				//wins.free_shipping_product_exceptions.A.grid.obj.enableRowsHover(true,dhx_rowhover);
				
				//Paging
				wins.free_shipping_product_exceptions.A.obj.attachStatusBar().setText("<div id=\''.$containerObj.'_recinfoArea_win\'></div>");
				wins.free_shipping_product_exceptions.A.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
				wins.free_shipping_product_exceptions.A.grid.obj.enablePaging(true, 100, 3, "'.$containerObj.'_recinfoArea_win");
				wins.free_shipping_product_exceptions.A.grid.obj.setPagingSkin("toolbar", dhx_skin);
				wins.free_shipping_product_exceptions.A.grid.obj.i18n.paging={
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
				wins.free_shipping_product_exceptions.A.grid.obj.init();
				
				// set filter input names
				wins.free_shipping_product_exceptions.A.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="name";
				wins.free_shipping_product_exceptions.A.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="sku";
				wins.free_shipping_product_exceptions.A.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("SELECT")[0].name="product_type";
				
				// we create a variable to store the default url used to get our grid data, so we can reuse it later
				wins.free_shipping_product_exceptions.A.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('config/xml_list_free_shipping_product_exceptions_add').'";
				
				// load the initial grid
				load_grid(wins.free_shipping_product_exceptions.A.grid.obj);		
				
				wins.free_shipping_product_exceptions.A.grid.obj.attachEvent("onXLS", function(grid_obj){
					ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
				}); 
				
				wins.free_shipping_product_exceptions.A.grid.obj.attachEvent("onXLE", function(grid_obj,count){
					ajaxOverlay(grid_obj.entBox.id,0);
				});	
				
				// clean variables
				wins.obj.attachEvent("onClose",function(win){
					wins = new Object();
					return true;
				});			
				
				
				wins.toolbar.save = function(id,close){
					var obj = wins.toolbar.obj;
					
					var checked = wins.free_shipping_product_exceptions.A.grid.obj.getCheckedRows(0);
					if (checked) {		
						
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}			
				
						$.ajax({
							url: "'.CController::createUrl('config/save_free_shipping_product_exceptions',array()).'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){			
								// clear all errors					
								$("#"+wins.free_shipping_product_exceptions.obj.cont.obj.id+" span.error").html("");
								$("#"+wins.free_shipping_product_exceptions.obj.cont.obj.id+" *").removeClass("error");
							
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
								load_grid(wins.free_shipping_product_exceptions.A.grid.obj);
								load_grid('.$container.'_free_shipping_product_exceptions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_ADDED_SUCCESS').'");
							}
						});	
					} else {
						alert("'.Yii::t('views/products/edit_suggestion','LABEL_ALERT_NO_CHECKED_PRODUCT').'");		
					}	
				}
			}
		break;
		//Percent/Fixed amount off first purchase
		case "5":
			var content;
			content = "<h3>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_CONDITION_TO_BE_MET').'</h3><div><strong>'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_MIN_CART_VALUE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'min-cart-value')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'min_cart_value',array('size'=>10, 'id'=>$container.'_min_cart_value','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_min_cart_value_errorMsg\" class=\"error\"></span></div></div><h3 style=\"margin-top:20px\">'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_TITLE_IF_CONDITION_MET').'</h3><div style=\"float:left;\"><strong>'.Yii::t('global','LABEL_DISCOUNT_TYPE').'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount-type')).'<br /><div>'.Html::remove_blank_line_addslashes(CHtml::activeDropDownList($model,'discount_type',array(0=>Yii::t('global','LABEL_FIXED'),1=>Yii::t('global','LABEL_PERCENTAGE')),array( 'id'=>$container.'_discount_type'))).'<br /><span id=\"'.$container.'_discount_type_errorMsg\" class=\"error\"></span></div></div><div style=\"float:left; margin-left:5px;\"><strong id=\"'.$container.'_title_price_percent\">'.($model->discount_type?Yii::t('global','LABEL_PERCENT'):Yii::t('global','LABEL_PRICE')).'</strong>&nbsp;&nbsp;'.str_replace('"','\"',Html::help_hint($help_hint_path.'discount')).'<div>'.Html::remove_blank_line_addslashes(CHtml::activeTextField($model,'discount',array('size'=>5, 'id'=>$container.'_discount','onkeyup'=>'rewrite_number($(this).attr("id"));'))).'<br /><span id=\"'.$container.'_discount_errorMsg\" class=\"error\"></span></div></div><div style=\"clear:both;\"></div>";

			$("#'.$container.'_all_coupon").hide();
			
			$("#'.$container.'_rebate_coupon_form").html("").append(content);
		break;
		default:
			$("#'.$container.'_rebate_coupon_form").html("").append("");	
	}
}


';

echo Html::script($script); 
?>