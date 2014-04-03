<?php 
$model_name = get_class($model);

$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$columns = Html::getColumnsMaxLength(Tbl_Options::tableName());	

echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));
echo CHtml::activeHiddenField($model,'id_options_group',array('id'=>'id_options_group'));

$app = Yii::app();

$help_hint_path = '/catalog/options/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
    <div class="row">
    	
        <div style="float:left;">
            <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[active]',($model->active or empty($model->id))?1:0,array('value'=>1,'id'=>'active_1')).'&nbsp;<label for="active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',(!$model->active and !empty($model->id))?1:0,array('value'=>0,'id'=>'active_0')).'&nbsp;<label for="active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
            ?>
            </div>       
        </div>    
        <div style="clear:both;"></div>
        <span id="sku_errorMsg" class="error"></span>
    </div>
    <div class="row">
    	<table border="0" cellpadding="0" cellspacing="0" width="100%">
        	<?php if ($model->input_type < 5) { ?>
            <tr>
        	  <td valign="top"> <strong><?php echo Yii::t('global','LABEL_SKU');?></strong><?php echo (isset($columns['sku']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="sku_maxlength">'.($columns['sku']-strlen($model->sku)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'sku'); ?><br />
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
					
					
					
                     echo CHtml::activeTextField($model,'sku',array('style' => 'width: 250px;','maxlength'=>$columns['sku'], 'id'=>'sku','readonly'=>$readonly,'class'=>$class));
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
                    echo CHtml::activeTextField($model,'cost_price',array('size'=>10, 'id'=>'cost_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="cost_price_errorMsg" class="error"></span>
                    </div>  
                    </div>
           
                    <div style="float:left; margin-left:5px;">
                    <strong><?php echo Yii::t('global','LABEL_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'price',array('size'=>10, 'id'=>'price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="price_errorMsg" class="error"></span>
                    </div>  
                    </div> 
                                 
          	  </td>
           	</tr>
             <tr>
            <td>
            <div style="float:left">
                    <strong><?php echo Yii::t('global','LABEL_SPECIAL_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'special_price',array('size'=>10, 'id'=>'special_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="special_price_errorMsg" class="error"></span>              
					</div>   
                    </div>  
              <div style="float:left; margin-left:7px">
                <strong><?php echo Yii::t('global','LABEL_SPECIAL_FROM_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price-start-date'); ?><br />
                <div>
                  <?php
        echo CHtml::activeTextField($model,'special_price_from_date',array('size'=>15, 'id'=>'special_price_from_date'));
        ?>
                  <br /><span id="special_price_from_date_errorMsg" class="error"></span>
                </div>                
              </div> 
              <div style="float:left; margin-left: 5px;">
                <strong><?php echo Yii::t('global','LABEL_SPECIAL_TO_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'special-price-end-date'); ?><br />
                <div>
                  <?php
        echo CHtml::activeTextField($model,'special_price_to_date',array('size'=>15, 'id'=>'special_price_to_date'));
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
        echo CHtml::radioButton($model_name.'[taxable]',$model->taxable?1:0,array('value'=>1,'id'=>'taxable_1')).'&nbsp;<label for="taxable_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[taxable]',!$model->taxable?1:0,array('value'=>0,'id'=>'taxable_0')).'&nbsp;<label for="taxable_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
	</div>        
    <div style="float:left; margin-left:18px;<?php echo !$model->taxable ? ' display:none;':''; ?>" id="display_tax_group">    
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
		
        echo CHtml::activeDropDownList($model,'id_tax_group',CHtml::listData($command->queryAll(true),'id','name'),array( 'id'=>'id_tax_group','prompt'=>'--'));
        ?>
        <br /><span id="tax_group_errorMsg" class="error"></span>
        </div>                
	</div>  
            </td>
            </tr>
	<?php 
		if ($model->input_type < 5 && $model->id && $app->params['enable_shipping']) { ?>
            
            
            <tr>
            <td>
            
            
            
            <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_USE_SHIPPING_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'use-shipping-price'); ?><br />
        <em><?php echo Yii::t('views/products/edit_shipping_options','LABEL_USE_SHIPPING_PRICE_DESCRIPTION');?></em>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[use_shipping_price]',$model->use_shipping_price?1:0,array('value'=>1,'id'=>'use_shipping_price_1')).'&nbsp;<label for="use_shipping_price_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[use_shipping_price]',!$model->use_shipping_price?1:0,array('value'=>0,'id'=>'use_shipping_price_0')).'&nbsp;<label for="use_shipping_price_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>          
    </div>    
    <div id="use_shipping_price" <?php echo !$model->use_shipping_price ? 'style="display:none;"':''; ?>>      
    <div class="row">
        <strong><?php echo Yii::t('views/products/edit_shipping_options','LABEL_SHIPPING_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipping-price'); ?><br />
       <div style="width:420px">
            <div id="my_toolbar_here"></div>
            <div id="options_price_shipping_region" style="height:150px;"></div>
            <div id="recinfoArea"></div>
        </div>                
	</div> 
    </div>
            
            
            
            </td>
            </tr>
            
            
            
            
            
            
            
            
            
            
            
        	<tr>
        	  <td valign="top">
              <div id="use_weight" <?php echo $model->use_shipping_price ? 'style="display:none;"':''; ?>>
              <strong><?php echo Yii::t('global','LABEL_WEIGHT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'weight'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'weight',array('size'=>5, 'id'=>'weight','onkeyup'=>'rewrite_number($(this).attr("id"));'));
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
				<div id="use_extra_care" <?php echo $model->use_shipping_price ? 'style="display:none;"':''; ?>>
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
                          <div id="my_toolbar_here_ship_only"></div>
                          <div id="ship_only_region" style="height:150px;"></div> 
                      </div>
                  </div>
                  <div style="float:left; margin-left:20px;">
                      <div style="width:280px">
                          <div style="float:left; margin-bottom:5px;"><strong><?php echo Yii::t('views/config/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'do-not-ship-to'); ?><br /><em><?php echo Yii::t('views/config/edit_shipping_options','LABEL_DO_NOT_SHIP_REGION_DESCRIPTION');?></em></div>
                          <div style="clear:both"></div>
                          <div id="my_toolbar_here_not_ship"></div>
                          <div id="not_ship_region" style="height:150px;"></div> 
                      </div>
                  </div>
                  <div style="clear:both"></div>               
              </td>
      	  </tr>
          
          
          
          
          
          
          
          
          
          
          
          
            <?php } ?>
		</table>     
	</div>
    <?php if ($model->input_type < 5) { ?>
    <h2><?php echo Yii::t('views/options/edit_options','LABEL_INVENTORY');?></h2>
<div class="row">               
        <strong><?php echo Yii::t('global','LABEL_TRACK_INVENTORY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'track-inventory'); ?><br />
        <?php 
        echo CHtml::radioButton($model_name.'[track_inventory]',$model->track_inventory?1:0,array('value'=>1,'id'=>'track_inventory_1')).'&nbsp;<label for="track_inventory_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[track_inventory]',!$model->track_inventory?1:0,array('value'=>0,'id'=>'track_inventory_0')).'&nbsp;<label for="track_inventory_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>    
	</div>    
    <div class="row" id="track_inventory" <?php echo !$model->track_inventory ? 'style="display:none;"':''; ?>>                   
    	<table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('global','LABEL_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'qty'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'qty',array('size'=>5, 'id'=>'qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
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
                    echo CHtml::radioButton($model_name.'[notify]',$model->notify?1:0,array('value'=>1,'id'=>'notify_1')).'&nbsp;<label for="notify_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[notify]',!$model->notify?1:0,array('value'=>0,'id'=>'notify_0')).'&nbsp;<label for="notify_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?> 
                    </div>  
                    <div style="float:left; margin-left:10px;<?php echo !$model->notify ? ' display:none;':''; ?>" id="notify_qty_reaches">
                    <strong><?php echo Yii::t('global','LABEL_NOTIFY_QTY_REACHES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'notify-when-qty-reaches'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'notify_qty',array('size'=>5, 'id'=>'notify_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
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
                    echo CHtml::activeTextField($model,'out_of_stock',array('size'=>5, 'id'=>'out_of_stock','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="out_of_stock_errorMsg" class="error"></span>
                    </div> 
                    </td>
            </tr>     
            
             <!-- <tr>              
               <td valign="top">   
                    <strong><?php echo Yii::t('global','LABEL_ALLOW_BACKORDERS');?></strong><br />
                    <?php 
                    echo CHtml::radioButton($model_name.'[allow_backorders]',$model->allow_backorders?1:0,array('value'=>1,'id'=>'allow_backorders_1')).'&nbsp;<label for="allow_backorders_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[allow_backorders]',!$model->allow_backorders?1:0,array('value'=>0,'id'=>'allow_backorders_0')).'&nbsp;<label for="allow_backorders_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                    ?>               
              </td>
            </tr>-->
              <tr>
                <td valign="top">
                <strong><?php echo Yii::t('global','LABEL_IN_STOCK');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'in-stock'); ?><br />
        <?php 
        echo CHtml::radioButton($model_name.'[in_stock]',($model->in_stock or !$model->track_inventory)?1:0,array('value'=>1,'id'=>'in_stock_1')).'&nbsp;<label for="in_stock_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[in_stock]',(!$model->in_stock and $model->track_inventory)?1:0,array('value'=>0,'id'=>'in_stock_0')).'&nbsp;<label for="in_stock_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
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
        <strong><?php echo Yii::t('global','LABEL_MAXLENGTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'maxlength'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'maxlength',array('size'=>5, 'id'=>'maxlength','onkeyup'=>'rewrite_number($(this).attr("id"));'));
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
	$("#taxable_1").click(function(){
		$("#display_tax_group").show();
	});
	
	$("#taxable_0").click(function(){
		$("#display_tax_group").hide();
	});
	
	$("#track_inventory_1").click(function(){
		$("#track_inventory").show();
	});
	
	$("#track_inventory_0").click(function(){
		$("#track_inventory").hide();
	});
	
	$("#notify_1").click(function(){
		$("#notify_qty_reaches").show();
	});
	
	$("#notify_0").click(function(){
		$("#notify_qty_reaches").hide();
	});
	
	
		
	$("#special_price_from_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	$("#special_price_to_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
';

if ($model->input_type < 5 && $model->id && $app->params['enable_shipping']) {
	$script .= '$("#use_shipping_price_0").click(function(){
		$("#use_shipping_price").hide();
		$("#use_weight").show();
		$("#use_measurements").show();
		$("#use_extra_care").show();
	});
	
	$("#use_shipping_price_1").click(function(){
		//Disable the button if new option because we need an id to add region
		grid_regions.toolbar.obj.forEachItem(function(itemId){
			if($("#id").val()>0){
				grid_regions.toolbar.obj.enableItem(itemId);
			}else{
				grid_regions.toolbar.obj.disableItem(itemId);
			}
		});
		
		$("#use_shipping_price").show();
		$("#use_weight").hide();
		$("#use_measurements").hide();
		$("#use_extra_care").show();
	});	
	

	//Options Price Shipping Region Grid

	
	var grid_regions = new Object();
	grid_regions.obj = new dhtmlXGridObject("options_price_shipping_region");
	
	grid_regions.obj.selMultiRows = true;
	grid_regions.obj.setImagePath(dhx_globalImgPath);		
	grid_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').','.Yii::t('global','LABEL_PRICE').'",null,["text-align:center;",,,"text-align:right;"]);
	
	grid_regions.obj.setInitWidthsP("10,35,35,20");
	grid_regions.obj.setColAlign("center,left,left,right");
	grid_regions.obj.setColSorting("na,na,na,na");
	grid_regions.obj.enableResizing("false,true,true,true");
	grid_regions.obj.setSkin(dhx_skin);
	grid_regions.obj.enableDragAndDrop(false);
	grid_regions.obj.enableMultiselect(false);
	grid_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);
	
	//Paging
	grid_regions.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
	grid_regions.obj.enablePaging(true, 100, 3, "recinfoArea");
	grid_regions.obj.setPagingSkin("toolbar", dhx_skin);
	grid_regions.obj.i18n.paging={
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
	
	grid_regions.obj.init();
	grid_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_price_shipping',array("id_options"=>$model->id)).'";
	grid_regions.obj.loadXML(grid_regions.obj.xmlOrigFileUrl);
	
	grid_regions.toolbar = new Object();
	grid_regions.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_here");
	grid_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	grid_regions.toolbar.obj.addButton("add", null, "'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
	grid_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
	grid_regions.toolbar.obj.addSeparator("sep1", null);
	grid_regions.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	grid_regions.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	grid_regions.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	grid_regions.toolbar.obj.attachEvent("onClick", function(id){	
		var obj = this;
		var title = "'.Yii::t('views/products/edit_shipping_options','LABEL_PRICE_SHIPPING_REGION').'";
	
		switch (id) {
			case "add":
				grid_regions.toolbar.add();
				break;
			case "delete":			
				var checked = grid_regions.obj.getCheckedRows(0);
				
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
								load_grid(grid_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});						
					}
				} else {
					alert("'.Yii::t('views/rebatecoupon/edit_info_options','LABEL_ALERT_NO_REGION_CHECKED').'");	
				}
				break;
			case "export_pdf":
				printGridPopup(grid_regions.obj,"pdf",[0],title);
				break;	
			case "export_excel":
				printGridPopup(grid_regions.obj,"excel",[0],title);
				break;		
			case "print":
				printGridPopup(grid_regions.obj,"printview",[0],title);
				break;				
		}
	});
	grid_regions.toolbar.add = function(current_id){

		var wins_region = new Object();
		if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+grid_regions.obj.cellById(current_id,1).getValue()+" - "+grid_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('global','LABEL_BTN_ADD').'";} 
		
		wins_region.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 500, 260);
		wins_region.obj.setText(name);
		wins_region.obj.button("park").hide();
		wins_region.obj.keepInViewport(true);

		wins_options.obj.setModal(false);
		wins_region.obj.setModal(true);
		wins_region.obj.center();
					
		wins_region.toolbar = new Object();
		
		wins_region.toolbar.load = function(current_id){
			
			var obj = wins_region.toolbar.obj;
			
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
						wins_region.toolbar.save(id);
						break;
					case "save_close":
						wins_region.toolbar.save(id,1);
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
									wins_region.obj.close();
								},
								success: function(data){
									load_grid(grid_regions.obj);
									alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
								}
							});					
						}			
						break;
				}
			});	

		}	
		
		wins_region.toolbar.obj = wins_region.obj.attachToolbar();
		wins_region.toolbar.obj.setIconsPath(dhx_globalImgPath);	
		wins_region.toolbar.load(current_id);
		
		wins_region.grid_regions = new Object();
		wins_region.grid_regions.obj = wins_region.obj.attachLayout("1C");
		wins_region.grid_regions.A = new Object();
		wins_region.grid_regions.A.obj = wins_region.grid_regions.obj.cells("a");
		wins_region.grid_regions.A.obj.hideHeader();	
		
		$.ajax({
			url: "'.CController::createUrl('edit_regions_options',array()).'",
			type: "POST",
			data: { "id":current_id, "id_options":'.$model->id.' },
			success: function(data){
				wins_region.grid_regions.A.obj.attachHTMLString(data);		
			}
		});	
				
		
		// clean variables
		wins_region.obj.attachEvent("onClose",function(win){
			wins_region.obj.setModal(false);
			wins_options.obj.setModal(true);
			wins_region = new Object();
			load_grid(grid_regions.obj);

			return true;
		});			
		
		
		wins_region.toolbar.save = function(id,close){
			var obj = wins_region.toolbar.obj;
			$.ajax({
				url: "'.CController::createUrl('save_regions_options',array()).'",
				type: "POST",
				data: $("#"+wins_region.grid_regions.obj.cont.obj.id+" *").serialize(),
				dataType: "json",
				beforeSend: function(){			
					// clear all errors					
					$("#"+wins_region.grid_regions.obj.cont.obj.id+" span.error").html("");
					$("#"+wins_region.grid_regions.obj.cont.obj.id+" *").removeClass("error");
				
					obj.disableItem(id);			
				},
				complete: function(jqXHR, textStatus){
					if (typeof obj.enableItem == "function") obj.enableItem(id);
					
					if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins_region.obj.close();
				},
				success: function(data){						
					// Remove class error to the background of the main div
					$(".div_popup_'.$containerObj.'").removeClass("error_background");
					if (data) {
						if (data.errors) {
							
							$.each(data.errors, function(key, value){
								var id_tag_container = ""+key;
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
							$(".div_popup_'.$containerObj.'").addClass("error_background");																														
						} else {					
							
							alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
							
							// Verify if popup window is close
							if(!close){
								var nom_edit = $("#country_code option:selected").text();
								nom_edit = nom_edit + " - " + $("#state_code option:selected").text();
	
								$("#popup_id").val(data.id);
								wins_region.toolbar.load(data.id);
								wins_region.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
							}else{
								grid_regions.obj.selectRowById(data.id);
							}
						}
					} else {
						alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
					}
				}
			});	
		}	
	}
	
	grid_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
		grid_regions.toolbar.add(rId);
	});
	
	
	
	
	//Ship Only
	var grid_ship_only_regions = new Object();
	grid_ship_only_regions.obj = new dhtmlXGridObject("ship_only_region");
	
	grid_ship_only_regions.obj.selMultiRows = true;
	grid_ship_only_regions.obj.setImagePath(dhx_globalImgPath);		
	grid_ship_only_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);
	
	grid_ship_only_regions.obj.setInitWidths("40,119,119");
	grid_ship_only_regions.obj.setColAlign("center,left,left");
	grid_ship_only_regions.obj.setColSorting("na,na,na");
	grid_ship_only_regions.obj.enableResizing("false,true,true");
	grid_ship_only_regions.obj.setSkin(dhx_skin);
	grid_ship_only_regions.obj.enableDragAndDrop(false);
	grid_ship_only_regions.obj.enableMultiselect(false);
	grid_ship_only_regions.obj.enableAutoWidth(true,278,278);
	grid_ship_only_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);
	
	
	
	grid_ship_only_regions.obj.init();
	
	grid_ship_only_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_ship_only_region_options',array("id_options"=>$model->id)).'";
	grid_ship_only_regions.obj.loadXML(grid_ship_only_regions.obj.xmlOrigFileUrl);
	
	grid_ship_only_regions.toolbar = new Object();
	grid_ship_only_regions.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_here_ship_only");
	grid_ship_only_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	grid_ship_only_regions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
	grid_ship_only_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
	grid_ship_only_regions.toolbar.obj.addSeparator("sep1", null);
	grid_ship_only_regions.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	grid_ship_only_regions.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	grid_ship_only_regions.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	grid_ship_only_regions.toolbar.obj.attachEvent("onClick", function(id){	
		var obj = this;
		var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_TITLE_FREE_SHIPPING_REGIONS').'";
	
		switch (id) {
			case "add":
				
				grid_ship_only_regions.toolbar.add();
				break;
			case "delete":			
				var checked = grid_ship_only_regions.obj.getCheckedRows(0);
				
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
								load_grid(grid_ship_only_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});						
					}
				} else {
					alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED').'");	
				}
				break;
			case "export_pdf":
				printGridPopup(grid_ship_only_regions.obj,"pdf",[0],title);
				break;	
			case "export_excel":
				printGridPopup(grid_ship_only_regions.obj,"excel",[0],title);
				break;		
			case "print":
				printGridPopup(grid_ship_only_regions.obj,"printview",[0],title);
				break;				
		}
	});
	grid_ship_only_regions.toolbar.add = function(current_id){
		var wins_ship_only_regions = new Object();
		if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+grid_ship_only_regions.obj.cellById(current_id,1).getValue()+" - "+grid_ship_only_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('views/config/edit_shipping_options','LABEL_ADD').'";} 
		
		wins_ship_only_regions.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 500, 200);
		wins_ship_only_regions.obj.setText(name);
		wins_ship_only_regions.obj.button("park").hide();
		wins_ship_only_regions.obj.keepInViewport(true);
		wins_options.obj.setModal(false);
		wins_ship_only_regions.obj.setModal(true);
		wins_ship_only_regions.obj.center();
					
		wins_ship_only_regions.toolbar = new Object();
		
		wins_ship_only_regions.toolbar.load = function(current_id){
			var obj = wins_ship_only_regions.toolbar.obj;
			
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
						wins_ship_only_regions.toolbar.save(id);
						break;
					case "save_close":
						wins_ship_only_regions.toolbar.save(id,1);
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
									wins_ship_only_regions.obj.close();
								},
								success: function(data){
									load_grid(grid_ship_only_regions.obj);
									alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
								}
							});					
						}			
						break;
				}
			});	
		}	
		
		wins_ship_only_regions.toolbar.obj = wins_ship_only_regions.obj.attachToolbar();
		wins_ship_only_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
		wins_ship_only_regions.toolbar.load(current_id);
		
		wins_ship_only_regions.grid_ship_only_regions = new Object();
		wins_ship_only_regions.grid_ship_only_regions.obj = wins_ship_only_regions.obj.attachLayout("1C");
		wins_ship_only_regions.grid_ship_only_regions.A = new Object();
		wins_ship_only_regions.grid_ship_only_regions.A.obj = wins_ship_only_regions.grid_ship_only_regions.obj.cells("a");
		wins_ship_only_regions.grid_ship_only_regions.A.obj.hideHeader();	
		
		$.ajax({
			url: "'.CController::createUrl('edit_ship_only_region_options',array()).'",
			type: "POST",
			data: { "id":current_id, "id_options":'.$model->id.'  },
			success: function(data){
				wins_ship_only_regions.grid_ship_only_regions.A.obj.attachHTMLString(data);		
			}
		});	
		
		// clean variables
		wins_ship_only_regions.obj.attachEvent("onClose",function(win){
			wins_ship_only_regions.obj.setModal(false);
			wins_options.obj.setModal(true);
			wins_ship_only_regions = new Object();
			
			return true;
		});			
		
		
		wins_ship_only_regions.toolbar.save = function(id,close){
			var obj = wins_ship_only_regions.toolbar.obj;
			$.ajax({
				url: "'.CController::createUrl('save_ship_only_region_options',array()).'",
				type: "POST",
				data: $("#"+wins_ship_only_regions.grid_ship_only_regions.obj.cont.obj.id+" *").serialize(),
				dataType: "json",
				beforeSend: function(){			
					// clear all errors					
					$("#"+wins_ship_only_regions.grid_ship_only_regions.obj.cont.obj.id+" span.error").html("");
					$("#"+wins_ship_only_regions.grid_ship_only_regions.obj.cont.obj.id+" *").removeClass("error");
				
					obj.disableItem(id);			
				},
				complete: function(jqXHR, textStatus){
					if (typeof obj.enableItem == "function") obj.enableItem(id);
					
					if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins_ship_only_regions.obj.close();
				},
				success: function(data){						
					// Remove class error to the background of the main div
					$(".div_popup_'.$container.'").removeClass("error_background");
					if (data) {
						if (data.errors) {
							
							$.each(data.errors, function(key, value){
								var id_tag_container = ""+key;
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
							load_grid(grid_ship_only_regions.obj);
							
							alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
							
							// Verify if popup window is close
							if(!close){
								var nom_edit = $("#country_code option:selected").text();
								nom_edit = nom_edit + " - " + $("#state_code option:selected").text();
								$("#popup_id").val(data.id);
								wins_ship_only_regions.toolbar.load(data.id);
								wins_ship_only_regions.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
							}else{
								grid_ship_only_regions.obj.selectRowById(data.id);
							}
						}
					} else {
						alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
					}
				}
			});	
		}
	}
	
	grid_ship_only_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
		grid_ship_only_regions.toolbar.add(rId);
	});
	
	
	
	
	//Do Not Ship
	var grid_do_not_ship_regions = new Object();
	grid_do_not_ship_regions.obj = new dhtmlXGridObject("not_ship_region");
	
	grid_do_not_ship_regions.obj.selMultiRows = true;
	grid_do_not_ship_regions.obj.setImagePath(dhx_globalImgPath);		
	grid_do_not_ship_regions.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_COUNTRY').','.Yii::t('global','LABEL_STATE_PROVINCE').'",null,["text-align:center;"]);
	
	grid_do_not_ship_regions.obj.setInitWidths("40,119,119");
	grid_do_not_ship_regions.obj.setColAlign("center,left,left");
	grid_do_not_ship_regions.obj.setColSorting("na,na,na");
	grid_do_not_ship_regions.obj.enableResizing("false,true,true");
	grid_do_not_ship_regions.obj.setSkin(dhx_skin);
	grid_do_not_ship_regions.obj.enableDragAndDrop(false);
	grid_do_not_ship_regions.obj.enableMultiselect(false);
	grid_do_not_ship_regions.obj.enableAutoWidth(true,278,278);
	grid_do_not_ship_regions.obj.enableRowsHover(true,dhx_rowhover_pointer);
	
	
	
	grid_do_not_ship_regions.obj.init();
	
	grid_do_not_ship_regions.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_do_not_ship_region_options',array("id_options"=>$model->id)).'";
	grid_do_not_ship_regions.obj.loadXML(grid_do_not_ship_regions.obj.xmlOrigFileUrl);
	
	grid_do_not_ship_regions.toolbar = new Object();
	grid_do_not_ship_regions.toolbar.obj = new dhtmlXToolbarObject("my_toolbar_here_not_ship");
	grid_do_not_ship_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	grid_do_not_ship_regions.toolbar.obj.addButton("add",null,"'.Yii::t('global','LABEL_BTN_ADD').'","toolbar/add.gif","toolbar/add_dis.gif");
	grid_do_not_ship_regions.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
	grid_do_not_ship_regions.toolbar.obj.addSeparator("sep1", null);
	grid_do_not_ship_regions.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	grid_do_not_ship_regions.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	grid_do_not_ship_regions.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	grid_do_not_ship_regions.toolbar.obj.attachEvent("onClick", function(id){	
		var obj = this;
		var title = "'.Yii::t('views/config/edit_shipping_options','LABEL_TITLE_FREE_SHIPPING_REGIONS').'";
	
		switch (id) {
			case "add":
				
				grid_do_not_ship_regions.toolbar.add();
				break;
			case "delete":			
				var checked = grid_do_not_ship_regions.obj.getCheckedRows(0);
				
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
								load_grid(grid_do_not_ship_regions.obj);
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});						
					}
				} else {
					alert("'.Yii::t('views/config/edit_shipping_options','LABEL_ALERT_NO_CHECKED').'");	
				}
				break;
			case "export_pdf":
				printGridPopup(grid_do_not_ship_regions.obj,"pdf",[0],title);
				break;	
			case "export_excel":
				printGridPopup(grid_do_not_ship_regions.obj,"excel",[0],title);
				break;		
			case "print":
				printGridPopup(grid_do_not_ship_regions.obj,"printview",[0],title);
				break;				
		}
	});
	grid_do_not_ship_regions.toolbar.add = function(current_id){
		var wins_do_not_ship_regions = new Object();
		if(current_id){name = "'.Yii::t('global','LABEL_EDIT').' "+grid_do_not_ship_regions.obj.cellById(current_id,1).getValue()+" - "+grid_do_not_ship_regions.obj.cellById(current_id,2).getValue();} else{ name="'.Yii::t('views/config/edit_shipping_options','LABEL_ADD').'";} 
		
		wins_do_not_ship_regions.obj = dhxWins.createWindow("editRegionWindow", 10, 10, 500, 200);
		wins_do_not_ship_regions.obj.setText(name);
		wins_do_not_ship_regions.obj.button("park").hide();
		wins_do_not_ship_regions.obj.keepInViewport(true);
		wins_options.obj.setModal(false);
		wins_do_not_ship_regions.obj.setModal(true);
		wins_do_not_ship_regions.obj.center();
					
		wins_do_not_ship_regions.toolbar = new Object();
		
		wins_do_not_ship_regions.toolbar.load = function(current_id){
			var obj = wins_do_not_ship_regions.toolbar.obj;
			
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
						wins_do_not_ship_regions.toolbar.save(id);
						break;
					case "save_close":
						wins_do_not_ship_regions.toolbar.save(id,1);
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
									wins_do_not_ship_regions.obj.close();
								},
								success: function(data){
									load_grid(grid_do_not_ship_regions.obj);
									alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
								}
							});					
						}			
						break;
				}
			});	
		}	
		
		wins_do_not_ship_regions.toolbar.obj = wins_do_not_ship_regions.obj.attachToolbar();
		wins_do_not_ship_regions.toolbar.obj.setIconsPath(dhx_globalImgPath);	
		wins_do_not_ship_regions.toolbar.load(current_id);
		
		wins_do_not_ship_regions.grid_do_not_ship_regions = new Object();
		wins_do_not_ship_regions.grid_do_not_ship_regions.obj = wins_do_not_ship_regions.obj.attachLayout("1C");
		wins_do_not_ship_regions.grid_do_not_ship_regions.A = new Object();
		wins_do_not_ship_regions.grid_do_not_ship_regions.A.obj = wins_do_not_ship_regions.grid_do_not_ship_regions.obj.cells("a");
		wins_do_not_ship_regions.grid_do_not_ship_regions.A.obj.hideHeader();	
		
		$.ajax({
			url: "'.CController::createUrl('edit_do_not_ship_region_options',array()).'",
			type: "POST",
			data: { "id":current_id, "id_options":'.$model->id.'  },
			success: function(data){
				wins_do_not_ship_regions.grid_do_not_ship_regions.A.obj.attachHTMLString(data);		
			}
		});	
		
		// clean variables
		wins_do_not_ship_regions.obj.attachEvent("onClose",function(win){
			wins_do_not_ship_regions.obj.setModal(false);
			wins_options.obj.setModal(true);
			wins_do_not_ship_regions = new Object();
			
			return true;
		});			
		
		
		wins_do_not_ship_regions.toolbar.save = function(id,close){
			var obj = wins_do_not_ship_regions.toolbar.obj;
			$.ajax({
				url: "'.CController::createUrl('save_do_not_ship_region_options',array()).'",
				type: "POST",
				data: $("#"+wins_do_not_ship_regions.grid_do_not_ship_regions.obj.cont.obj.id+" *").serialize(),
				dataType: "json",
				beforeSend: function(){			
					// clear all errors					
					$("#"+wins_do_not_ship_regions.grid_do_not_ship_regions.obj.cont.obj.id+" span.error").html("");
					$("#"+wins_do_not_ship_regions.grid_do_not_ship_regions.obj.cont.obj.id+" *").removeClass("error");
				
					obj.disableItem(id);			
				},
				complete: function(jqXHR, textStatus){
					if (typeof obj.enableItem == "function") obj.enableItem(id);
					
					if (close && !jQuery.parseJSON(jqXHR.responseText).errors) wins_do_not_ship_regions.obj.close();
				},
				success: function(data){						
					// Remove class error to the background of the main div
					$(".div_popup_'.$container.'").removeClass("error_background");
					if (data) {
						if (data.errors) {
							
							$.each(data.errors, function(key, value){
								var id_tag_container = ""+key;
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
							load_grid(grid_do_not_ship_regions.obj);
							
							alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");
							
							// Verify if popup window is close
							if(!close){
								var nom_edit = $("#country_code option:selected").text();
								nom_edit = nom_edit + " - " + $("#state_code option:selected").text();
	
								$("#popup_id").val(data.id);
								wins_do_not_ship_regions.toolbar.load(data.id);
								wins_do_not_ship_regions.obj.setText("'.Yii::t('global','LABEL_EDIT').' " + nom_edit);
							}else{
								grid_do_not_ship_regions.obj.selectRowById(data.id);
							}
						}
					} else {
						alert("'.Yii::t('global','LABEL_ALERT_NO_DATA_RETURN').'");	
					}
				}
			});	
		}
	}
	
	grid_do_not_ship_regions.obj.attachEvent("onRowDblClicked",function(rId,cInd){
		grid_do_not_ship_regions.toolbar.add(rId);
	});
	

	';
	
}
	
$script .= '});

';

echo Html::script($script); 
?>