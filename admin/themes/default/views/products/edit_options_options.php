<?php 
$model_name = get_class($model);

$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$columns = Html::getColumnsMaxLength(Tbl_Options::tableName());		

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
echo CHtml::activeHiddenField($model,'id_options_group',array('id'=>$container.'_id_options_group'));
echo CHtml::activeHiddenField($model,'input_type');

$app = Yii::app();

$help_hint_path = '/catalog/products/options/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
    <div class="row">
    	<table border="0" cellpadding="0" cellspacing="0" width="100%">
        	<?php if ($model->input_type < 5) { ?>
            <tr>
        	  <td valign="top"> <strong><?php echo Yii::t('global','LABEL_SKU');?></strong><?php echo (isset($columns['sku']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_sku_maxlength">'.($columns['sku']-strlen($model->sku)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'sku'); ?><br />
                    <div>
                    <?php
					$readonly = 0;
					$class = '';
					//Verify if product is sold, we put the field SKU readonly
					$criteria=new CDbCriteria; 
					$criteria->condition='id_options=:id_options'; 
					$criteria->params=array(':id_options'=>$model->id);
					if(Tbl_OrdersItemOption::model()->find($criteria)){
						$readonly = 1;
						$class = 'disabled';
					}
					
					
                     echo CHtml::activeTextField($model,'sku',array('style' => 'width: 250px;','maxlength'=>$columns['sku'], 'id'=>$container.'_sku','readonly'=>$readonly,'class'=>$class));
                    ?>
                    <br /><span id="sku_errorMsg" class="error"></span>
                    </div> </td>
       	  </tr>
          <?php }?>
        	<tr>
            	<td valign="top">
                    <div style="float:left;">
                    <strong><?php echo Yii::t('global','LABEL_COST_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'cost-price'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'cost_price',array('size'=>10, 'id'=>$container.'_cost_price'));
                    ?>
                    <br /><span id="cost_price_errorMsg" class="error"></span>
                    </div>  
                    </div>    
                    <div style="float:left; margin-left:5px;">
                    <strong id="<?php echo $container?>_title_price_percent"><?php echo($model->price_type?Yii::t('views/products/edit_options_options','LABEL_PERCENT'):Yii::t('views/products/edit_options_options','LABEL_PRICE'));?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'price',array('size'=>10, 'id'=>$container.'_price'));
                    ?>
                    <br /><span id="price_errorMsg" class="error"></span>
                    </div>  
                    </div> 
                                 
          	  </td>
           	</tr>
             <tr>
            <td>
            <div style="float:left">
                    <strong id="<?php echo $container?>_title_special"><?php echo($model->price_type?Yii::t('global','LABEL_SPECIAL_PERCENT'):Yii::t('global','LABEL_SPECIAL_PRICE'));?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'special_price',array('size'=>10, 'id'=>$container.'_special_price'));
                    ?>
                    <br /><span id="special_price_errorMsg" class="error"></span>              
					</div>   
                    </div>  
              <div style="float:left; margin-left:7px">
                <strong><?php echo Yii::t('global','LABEL_SPECIAL_FROM_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price-start-date'); ?><br />
                <div>
                  <?php
        echo CHtml::activeTextField($model,'special_price_from_date',array('size'=>15, 'id'=>$container.'_special_price_from_date'));
        ?>
                  <br /><span id="special_price_from_date_errorMsg" class="error"></span>
                </div>                
              </div> 
              <div style="float:left; margin-left: 5px;">
                <strong><?php echo Yii::t('global','LABEL_SPECIAL_TO_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price-end-date'); ?><br />
                <div>
                  <?php
        echo CHtml::activeTextField($model,'special_price_to_date',array('size'=>15, 'id'=>$container.'_special_price_to_date'));
        ?>
                  <br /><span id="special_price_to_date_errorMsg" class="error"></span>
                </div>                
              </div> 
              <div style="clear:both"></div>         
            </td>
            </tr>
            <tr>
            <td>
            <div style="float:left; margin-bottom:10px;">
        <strong><?php echo Yii::t('global','LABEL_TAXABLE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'taxable'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[taxable]',$model->taxable?1:0,array('value'=>1,'id'=>$container.'_taxable_1')).'&nbsp;<label for="'.$container.'_taxable_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[taxable]',!$model->taxable?1:0,array('value'=>0,'id'=>$container.'_taxable_0')).'&nbsp;<label for="'.$container.'_taxable_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
	</div>        
    <div style="float:left; margin-left:10px;<?php echo !$model->taxable ? ' display:none;':''; ?>" id="<?php echo $container;?>_display_tax_group">    
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
            </td>
            </tr>
            <?php 
		if ($model->input_type < 5 && $model->id && $app->params['enable_shipping']) { ?>
          <tr>
            <td>
            <h2><?php echo Yii::t('views/products/edit_options_options','LABEL_SHIPPING');?></h2>
            
            
            <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_USE_SHIPPING_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'use-shipping-price'); ?><br />
        <em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_USE_SHIPPING_PRICE_DESCRIPTION');?></em>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[use_shipping_price]',$model->use_shipping_price?1:0,array('value'=>1,'id'=>$container.'_use_shipping_price_1')).'&nbsp;<label for="'.$container.'_use_shipping_price_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[use_shipping_price]',!$model->use_shipping_price?1:0,array('value'=>0,'id'=>$container.'_use_shipping_price_0')).'&nbsp;<label for="'.$container.'_use_shipping_price_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>          
    </div>    
    <div id="<?php echo $container;?>_use_shipping_price" <?php echo !$model->use_shipping_price ? 'style="display:none;"':''; ?>>      
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_SHIPPING_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipping-price'); ?><br />
       <div style="width:580px">
            <div id="<?php echo $container;?>_my_toolbar_here"></div>
            <div id="<?php echo $container;?>_options_price_shipping_region" style="height:150px;"></div>
        </div>                
	</div> 
    </div>
            
            
            
            </td>
            </tr>

        	<tr>
        	  <td valign="top">
              <div id="<?php echo $container;?>_use_weight" <?php echo $model->use_shipping_price ? 'style="display:none;"':''; ?>>
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
                    <br /><span id="weight_errorMsg" class="error"></span>
                    </div> 
              </div>
              
              
              
                 </td>
      	  </tr>
          <tr>
          	<td valign="top">
                 <div id="use_measurements">
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
                        echo CHtml::activeTextField($model,'length',array('size'=>5, 'id'=>'length','onkeyup'=>'rewrite_number($(this).attr("id"));'));
            
                        echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
                        ?>
                        <br /><span id="length_label_errorMsg" class="error"></span>
                        </div>                
                    </div>            
                    
                    <div style="float:left; margin-right:10px;">
                        <strong><?php echo Yii::t('global','LABEL_WIDTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'width'); ?><br />
                        <div>
                        <?php
                        echo CHtml::activeTextField($model,'width',array('size'=>5, 'id'=>'width','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                        
                        echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
                        ?>
                        <br /><span id="width_label_errorMsg" class="error"></span>
                        </div>                
                    </div>            
                    
                    <div style="float:left; margin-right:10px;">
                        <strong><?php echo Yii::t('global','LABEL_HEIGHT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'height'); ?><br />
                        <div>
                        <?php
                        echo CHtml::activeTextField($model,'height',array('size'=>5, 'id'=>'height','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                        
                        echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
                        ?>
                        <br /><span id="height_label_errorMsg" class="error"></span>
                        </div>                
                    </div>           
                    
                    <div style="clear:both;"></div>
                </div>
            </td>
		</tr>     
		<?php
        //Verify if shipping gateway provide extra care
        if(Tbl_ShippingGateway::model()->find('provides_extra_care = 1 AND active = 1')){
        ?>
          <tr>
        	  <td valign="top">
				<div id="<?php echo $container;?>_use_extra_care" <?php echo $model->use_shipping_price ? 'style="display:none;"':''; ?>>
					<strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_EXTRA_CARE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'extra-care'); ?><br />
					<em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_EXTRA_CARE_DESCRIPTION');?></em>
					<div>
					<?php 
					echo CHtml::radioButton($model_name.'[extra_care]',$model->extra_care?1:0,array('value'=>1,'id'=>$container.'_extra_care_1')).'&nbsp;<label for="'.$container.'_extra_care_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[extra_care]',!$model->extra_care?1:0,array('value'=>0,'id'=>$container.'_extra_care_0')).'&nbsp;<label for="'.$container.'_extra_care_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
					?> <br /><span id="<?php echo $container; ?>_extra_care_errorMsg" class="error"></span>
					</div>          
				</div>
             
              </td>
      	  </tr>
			<?php
            }
            ?>
          <tr>
        	  <td valign="top">

                  <div style="float:left">
                      <div style="width:280px">
                          <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIP_ONLY_REGION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'ship-only-to'); ?><br /><em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_SHIP_ONLY_REGION_DESCRIPTION');?></em></div>
                          <div style="clear:both"></div>
                          <div id="<?php echo $container;?>_my_toolbar_here_ship_only"></div>
                          <div id="<?php echo $container;?>_ship_only_region" style="height:150px;"></div> 
                      </div>
                  </div>
                  <div style="float:left; margin-left:20px;">
                      <div style="width:280px">
                          <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'do-not-ship-to'); ?><br /><em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION_DESCRIPTION');?></em></div>
                          <div style="clear:both"></div>
                          <div id="<?php echo $container;?>_my_toolbar_here_not_ship"></div>
                          <div id="<?php echo $container;?>_not_ship_region" style="height:150px;"></div> 
                      </div>
                  </div>
                  <div style="clear:both"></div>               
              </td>
      	  </tr>

            <?php } ?>
		</table>     
	</div>
    <?php if ($model->input_type < 5) { ?>
    <h2><?php echo Yii::t('views/products/edit_options_options','LABEL_INVENTORY');?></h2>
<div class="row">               
        <strong><?php echo Yii::t('global','LABEL_TRACK_INVENTORY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'track-inventory'); ?><br />
        <?php 
        echo CHtml::radioButton($model_name.'[track_inventory]',$model->track_inventory?1:0,array('value'=>1,'id'=>$container.'_track_inventory_1')).'&nbsp;<label for="'.$container.'_track_inventory_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[track_inventory]',!$model->track_inventory?1:0,array('value'=>0,'id'=>$container.'_track_inventory_0')).'&nbsp;<label for="'.$container.'_track_inventory_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>    
	</div>    
    <div class="row" id="<?php echo $container.'_track_inventory'; ?>" <?php echo !$model->track_inventory ? 'style="display:none;"':''; ?>>                   
    	<table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('global','LABEL_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'qty'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'qty',array('size'=>5, 'id'=>'qty'));
                    ?>
                    <br /><span id="qty_errorMsg" class="error"></span>
                    </div>   
                 </td>
                 </tr>
                 <tr>
                 <td valign="top">
                    <div style="float:left;"> 
                    <strong><?php echo Yii::t('global','LABEL_NOTIFY_INVENTORY_LOW');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'notify-when-inventory-is-low'); ?><br />
                    <?php 
                    echo CHtml::radioButton($model_name.'[notify]',$model->notify?1:0,array('value'=>1,'id'=>$container.'_notify_1')).'&nbsp;<label for="'.$container.'_notify_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[notify]',!$model->notify?1:0,array('value'=>0,'id'=>$container.'_notify_0')).'&nbsp;<label for="'.$container.'_notify_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?> 
                    </div>  
                    <div style="float:left; margin-left:5px;">
                    <strong><?php echo Yii::t('global','LABEL_NOTIFY_QTY_REACHES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'notify-when-qty-reaches'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'notify_qty',array('size'=>5, 'id'=>$container.'_notify_qty'));
                    ?>
                    <br /><span id="notify_qty_errorMsg" class="error"></span>
                    </div>
                    </div>           
              </td>
            </tr>
            <tr>              
                <td valign="top">
                    <strong><?php echo Yii::t('global','LABEL_OUT_OF_STOCK_QTY_REACHES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'appear-out-of-stock-when-qty-reaches'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'out_of_stock',array('size'=>5, 'id'=>$container.'_out_of_stock'));
                    ?>
                    <br /><span id="out_of_stock_errorMsg" class="error"></span>
                    </div> 
                    </td>
                    </tr>
                    <!--
              <tr>              
               <td valign="top">   
                    <strong><?php echo Yii::t('global','LABEL_ALLOW_BACKORDERS');?></strong><br />
                    <?php 
                    echo CHtml::radioButton($model_name.'[allow_backorders]',$model->allow_backorders?1:0,array('value'=>1,'id'=>$container.'_allow_backorders_1')).'&nbsp;<label for="'.$container.'_allow_backorders_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[allow_backorders]',!$model->allow_backorders?1:0,array('value'=>0,'id'=>$container.'_allow_backorders_0')).'&nbsp;<label for="'.$container.'_allow_backorders_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>               
              </td>
            </tr>-->
              <tr>
                <td valign="top">
                <strong><?php echo Yii::t('global','LABEL_IN_STOCK');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'in-stock'); ?><br />
        <?php 
        echo CHtml::radioButton($model_name.'[in_stock]',$model->in_stock?1:0,array('value'=>1,'id'=>$container.'_in_stock_1')).'&nbsp;<label for="'.$container.'_in_stock_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[in_stock]',!$model->in_stock?1:0,array('value'=>0,'id'=>$container.'_in_stock_0')).'&nbsp;<label for="'.$container.'_in_stock_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>    
                </td>
              </tr>
		</table>                            
    </div>
    <?php } ?>
    <?php 
		switch ($model->input_type) {
			// textfield
			case 5:
			// textarea
			case 6:
	?>
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_options_options','LABEL_MAXLENGTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'maxlength'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'maxlength',array('size'=>5, 'id'=>$container.'_maxlength'));
        ?>
        <br /><span id="maxlength_errorMsg" class="error"></span>
        </div>   
    </div>
    <?php				
				break;
			// file
			case 7:
				break;
			// date
			case 8:
				break;
			// date time
			case 9:
				break;
			// time
			case 10:
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
	
	$("#'.$container.'_track_inventory_1").click(function(){
		$("#'.$container.'_track_inventory").show();
	});
	
	$("#'.$container.'_track_inventory_0").click(function(){
		$("#'.$container.'_track_inventory").hide();
	});
	
	$("#'.$container.'_price_type").change(function(){
		if($("#'.$container.'_price_type").val()==1){
			$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('views/products/edit_options_options','LABEL_PERCENT').'");
			$("#'.$container.'_title_special").html("").append("'.Yii::t('global','LABEL_SPECIAL_PERCENT').'");
		}else{
			$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('views/products/edit_options_options','LABEL_PRICE').'");
			$("#'.$container.'_title_special").html("").append("'.Yii::t('global','LABEL_SPECIAL_PRICE').'");
		}
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

';		

if ($model->input_type < 5 && $model->id && $app->params['enable_shipping']) {
	$script .= '$("#'.$container.'_use_shipping_price_0").click(function(){
		$("#'.$container.'_use_shipping_price").hide();
		$("#'.$container.'_use_weight").show();
		$("#'.$container.'_use_measurements").show();
		$("#'.$container.'_use_extra_care").show();
	});
	
	$("#'.$container.'_use_shipping_price_1").click(function(){
		//Disable the button if new option because we need an id to add region
		'.$container.'_grid_regions.toolbar.obj.forEachItem(function(itemId){
			if($("#'.$container.'_id").val()>0){
				'.$container.'_grid_regions.toolbar.obj.enableItem(itemId);
			}else{
				'.$container.'_grid_regions.toolbar.obj.disableItem(itemId);
			}
		});
		$("#'.$container.'_use_shipping_price").show();
		$("#'.$container.'_use_weight").hide();
		$("#'.$container.'_use_measurements").hide();
		$("#'.$container.'_use_extra_care").hide();
	});	
	
	var '.$container.'_dhxWins = new dhtmlXWindows();
	'.$container.'_dhxWins.enableAutoViewport(true);
	'.$container.'_dhxWins.attachViewportTo("'.$containerLayout.'");
	'.$container.'_dhxWins.setImagePath(dhx_globalImgPath);
	
	//Options Free Shipping Region Grid
	var '.$container.'_grid_regions = new Object();
	'.$container.'_grid_regions.obj = new dhtmlXGridObject("'.$container.'_options_price_shipping_region");
	
	'.$container.'_grid_regions.obj.selMultiRows = true;
	'.$container.'_grid_regions.obj.setImagePath(dhx_globalImgPath);		
	'.$container.'_grid_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,,"text-align:right;"]);
	
	'.$container.'_grid_regions.obj.setInitWidths("40,229,229,80");
	'.$container.'_grid_regions.obj.setColAlign("center,left,left,right");
	'.$container.'_grid_regions.obj.setColSorting("na,na,na,na");
	'.$container.'_grid_regions.obj.enableResizing("false,true,true,true");
	'.$container.'_grid_regions.obj.setSkin(dhx_skin);
	'.$container.'_grid_regions.obj.enableDragAndDrop(false);
	'.$container.'_grid_regions.obj.enableMultiselect(false);
	'.$container.'_grid_regions.obj.enableAutoWidth(true,578,578);
	'.$container.'_grid_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);
	

	
	'.$container.'_grid_regions.obj.init();
	'.$container.'_grid_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_price_shipping_options',array("id_options"=>$model->id)).'";
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
							url: "'.CController::createUrl('delete_region_options').'",
							type: "POST",
							data: ids.join("&")+"&id_options='.$model->id.'",
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

		var '.$container.'_wins_region = new Object();
		if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+'.$container.'_grid_regions.obj.cellById(current_id,1).getValue()+" - "+'.$container.'_grid_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('global','LABEL_BTN_ADD').'";} 
		
		'.$container.'_wins_region.obj = '.$container.'_dhxWins.createWindow("editRegionWindow", 10, 10, 500, 260);
		'.$container.'_wins_region.obj.setText(name);
		'.$container.'_wins_region.obj.button("park").hide();
		'.$container.'_wins_region.obj.keepInViewport(true);
		'.$container.'_wins_region.obj.setModal(true);
					
		'.$container.'_wins_region.toolbar = new Object();
		
		'.$container.'_wins_region.toolbar.load = function(current_id){
			
			var obj = '.$container.'_wins_region.toolbar.obj;
			
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
						'.$container.'_wins_region.toolbar.save(id);
						break;
					case "save_close":
						'.$container.'_wins_region.toolbar.save(id,1);
						break;
					case "delete":
						if (confirm("'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_ALERT_DELETE_REGION').'")) {
							$.ajax({
								url: "'.CController::createUrl('delete_region_options').'",
								type: "POST",
								data: { "ids[]":current_id, "id_options":'.$model->id.' },
								beforeSend: function(){		
									obj.disableItem(id);
								},
								complete: function(){
									if (typeof obj.enableItem == "function") obj.enableItem(id);
									'.$container.'_wins_region.obj.close();
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
		
		'.$container.'_wins_region.toolbar.obj = '.$container.'_wins_region.obj.attachToolbar();
		'.$container.'_wins_region.toolbar.obj.setIconsPath(dhx_globalImgPath);	
		'.$container.'_wins_region.toolbar.load(current_id);
		
		'.$container.'_wins_region.grid_regions = new Object();
		'.$container.'_wins_region.grid_regions.obj = '.$container.'_wins_region.obj.attachLayout("1C");
		'.$container.'_wins_region.grid_regions.A = new Object();
		'.$container.'_wins_region.grid_regions.A.obj = '.$container.'_wins_region.grid_regions.obj.cells("a");
		'.$container.'_wins_region.grid_regions.A.obj.hideHeader();	
	
		
		$.ajax({
			url: "'.CController::createUrl('edit_regions_options_options',array('container'=>$container)).'",
			type: "POST",
			data: { "id":current_id, "id_options":'.$model->id.' },
			success: function(data){
				'.$container.'_wins_region.grid_regions.A.obj.attachHTMLString(data);		
			}
		});
				
		
		// clean variables
		'.$container.'_wins_region.obj.attachEvent("onClose",function(win){
			'.$container.'_wins_region = new Object();
			load_grid('.$container.'_grid_regions.obj);
			return true;
		});			
		
		
		'.$container.'_wins_region.toolbar.save = function(id,close){
			var obj = '.$container.'_wins_region.toolbar.obj;
			$.ajax({
				url: "'.CController::createUrl('save_regions_options_options',array()).'",
				type: "POST",
				data: $("#"+'.$container.'_wins_region.grid_regions.obj.cont.obj.id+" *").serialize(),
				dataType: "json",
				beforeSend: function(){			
					// clear all errors					
					$("#"+'.$container.'_wins_region.grid_regions.obj.cont.obj.id+" span.error").html("");
					$("#"+'.$container.'_wins_region.grid_regions.obj.cont.obj.id+" *").removeClass("error");
				
					obj.disableItem(id);			
				},
				complete: function(jqXHR, textStatus){
					if (typeof obj.enableItem == "function") obj.enableItem(id);
					
					if (close && !jQuery.parseJSON(jqXHR.responseText).errors) '.$container.'_wins_region.obj.close();
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
								'.$container.'_wins_region.toolbar.load(data.id);
								'.$container.'_wins_region.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
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
	
	
	
	
	
	
	
	
	//Ship Only
	var '.$container.'_grid_ship_only_regions = new Object();
	'.$container.'_grid_ship_only_regions.obj = new dhtmlXGridObject("'.$container.'_ship_only_region");
	
	'.$container.'_grid_ship_only_regions.obj.selMultiRows = true;
	'.$container.'_grid_ship_only_regions.obj.setImagePath(dhx_globalImgPath);		
	'.$container.'_grid_ship_only_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);
	
	'.$container.'_grid_ship_only_regions.obj.setInitWidths("40,119,119");
	'.$container.'_grid_ship_only_regions.obj.setColAlign("center,left,left");
	'.$container.'_grid_ship_only_regions.obj.setColSorting("na,na,na");
	'.$container.'_grid_ship_only_regions.obj.enableResizing("false,true,true");
	'.$container.'_grid_ship_only_regions.obj.setSkin(dhx_skin);
	'.$container.'_grid_ship_only_regions.obj.enableDragAndDrop(false);
	'.$container.'_grid_ship_only_regions.obj.enableMultiselect(false);
	'.$container.'_grid_ship_only_regions.obj.enableAutoWidth(true,278,278);
	'.$container.'_grid_ship_only_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);
	
	
	
	'.$container.'_grid_ship_only_regions.obj.init();
	
	'.$container.'_grid_ship_only_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_ship_only_region_options',array("id_options"=>$model->id)).'";
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
							url: "'.CController::createUrl('delete_ship_only_region_options').'",
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
								url: "'.CController::createUrl('delete_ship_only_region_options').'",
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
			url: "'.CController::createUrl('edit_ship_only_region_options',array('container'=>$container)).'",
			type: "POST",
			data: { "id":current_id, "id_options":'.$model->id.'  },
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
				url: "'.CController::createUrl('save_ship_only_region_options',array()).'",
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
	
	'.$container.'_grid_do_not_ship_regions.obj.setInitWidths("40,119,119");
	'.$container.'_grid_do_not_ship_regions.obj.setColAlign("center,left,left");
	'.$container.'_grid_do_not_ship_regions.obj.setColSorting("na,na,na");
	'.$container.'_grid_do_not_ship_regions.obj.enableResizing("false,true,true");
	'.$container.'_grid_do_not_ship_regions.obj.setSkin(dhx_skin);
	'.$container.'_grid_do_not_ship_regions.obj.enableDragAndDrop(false);
	'.$container.'_grid_do_not_ship_regions.obj.enableMultiselect(false);
	'.$container.'_grid_do_not_ship_regions.obj.enableAutoWidth(true,278,278);
	'.$container.'_grid_do_not_ship_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);
	
	
	
	'.$container.'_grid_do_not_ship_regions.obj.init();
	
	'.$container.'_grid_do_not_ship_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_do_not_ship_region_options',array("id_options"=>$model->id)).'";
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
							url: "'.CController::createUrl('delete_do_not_ship_region_options').'",
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
								url: "'.CController::createUrl('delete_do_not_ship_region_options').'",
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
			url: "'.CController::createUrl('edit_do_not_ship_region_options',array('container'=>$container)).'",
			type: "POST",
			data: { "id":current_id, "id_options":'.$model->id.'  },
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
				url: "'.CController::createUrl('save_do_not_ship_region_options',array()).'",
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
	
}
	
$script .= '});

';

echo Html::script($script); 
?>