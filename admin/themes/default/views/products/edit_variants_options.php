<?php 
$model_name = get_class($model);

// get list of product groups
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$columns = Html::getColumnsMaxLength(Tbl_ProductVariant::tableName());	

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); 
echo CHtml::activeHiddenField($model,'id_product',array('id'=>$container.'_id_product')); 

$app = Yii::app();

$help_hint_path = '/catalog/products/variants/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    	<table border="0" cellpadding="0" cellspacing="0">
        	<tr>
            <td>
            <div class="row">
    	 <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
                    <div>
                    <?php 
                    echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
                    ?>
                    </div>   
    
    
    
   			 </div>
              </td>
           	</tr>
        	<tr>
        	  <td><strong><?php echo Yii::t('global','LABEL_SKU');?></strong><?php echo (isset($columns['sku']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_sku_maxlength">'.($columns['sku']-strlen($model->sku)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'sku'); ?><br />
        	    <div>
				<?php
                $readonly = 0;
				$class = '';
                //Verify if product is sold, we put the field SKU readonly
                $criteria=new CDbCriteria; 
                $criteria->condition='id_product_variant=:id_product_variant'; 
                $criteria->params=array(':id_product_variant'=>$model->id);
                if(Tbl_OrdersItemProduct::model()->find($criteria)){
                	$readonly = 1;
					$class = 'disabled';
                }
                echo CHtml::activeTextField($model,'sku',array('style' => 'width: 250px;','maxlength'=>$columns['sku'], 'id'=>$container.'_sku','readonly'=>$readonly,'class'=>$class));
                ?>
        	      <br /><span id="<?php echo $container; ?>_sku_errorMsg" class="error"></span>
      	      </div>      </td>
       	  </tr>
            <?php if ($track_inventory) { ?>
            <tr>                
                <td valign="top">
			        <div style="float:left">
                    <strong><?php echo Yii::t('global','LABEL_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'qty'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'qty',array('size' => 5, 'id'=>$container.'_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="<?php echo $container; ?>_qty_errorMsg" class="error"></span>
                    </div> 
                    </div>
                    <div style="float:left; margin-left:5px;">
                    <?php if ($notify) { ?>
			        <strong><?php echo Yii::t('global','LABEL_NOTIFY_QTY_REACHES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'notify-when-qty-reaches'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'notify_qty',array('size' => 5, 'id'=>$container.'_notify_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="<?php echo $container; ?>_notify_qty_errorMsg" class="error"></span>
                    </div>
                    <?php } ?>
                    </div>  
                </td>    
           	</tr>
            <?php } ?>
            <tr>
            	<td valign="top">
			        <div style="float:left;">
                    <strong><?php echo Yii::t('views/products/edit_variants','LABEL_COST_PRICE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'cost-price'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'cost_price',array('size'=>10, 'id'=>$container.'_cost_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="cost_price_errorMsg" class="error"></span>
                    </div>  
                    </div>
                    <div style="float:left; margin-left: 8px;">
                     <strong><?php echo Yii::t('views/products/edit_variants','LABEL_PRICE_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price-type'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeDropDownList($model,'price_type',array(0=>Yii::t('global','LABEL_FIXED'),1=>Yii::t('global','LABEL_PERCENTAGE')),array( 'id'=>$container.'_price_type','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="price_type_errorMsg" class="error"></span>
                    </div> 
                    </div> 
                    <div style="float:left; margin-left:5px;">
                    <strong id="<?php echo $container?>_title_price_percent"><?php echo($model->price_type?Yii::t('global','LABEL_PERCENT'):Yii::t('views/products/edit_variants','LABEL_PRICE'));?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price'); ?><br />
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'price',array('size'=>10, 'id'=>$container.'_price','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    ?>
                    <br /><span id="price_errorMsg" class="error"></span>
                    </div>  
                    </div> 
				</td>                 
           	</tr>
            
        
            <tr>
                <td valign="top">
                 	<div>
                        <strong><?php echo Yii::t('global','LABEL_IN_STOCK');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'in-stock'); ?>
                        <div>
                        <?php 
                        echo CHtml::radioButton($model_name.'[in_stock]',$model->in_stock?1:0,array('value'=>1,'id'=>$container.'_in_stock_1')).'&nbsp;<label for="'.$container.'_in_stock_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[in_stock]',!$model->in_stock?1:0,array('value'=>0,'id'=>$container.'_in_stock_0')).'&nbsp;<label for="'.$container.'_in_stock_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
                        ?>
                        </div>              
                	</div>
                    <?php
                    if(!$use_shipping_price){
					?>
                        <div style="margin-top: 3px;">  
                            <strong><?php echo Yii::t('global','LABEL_WEIGHT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'weight'); ?><br />
                            <div>
                            <?php
                            echo CHtml::activeTextField($model,'weight',array('size' => 10, 'id'=>$container.'_weight','onkeyup'=>'rewrite_number($(this).attr("id"));'));
							
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
                            <br /><span id="<?php echo $container; ?>_weight_errorMsg" class="error"></span>
                            </div>  
                        </div> 
                        
                         <div>
                            <?php
                            switch($app->params['measurement_unit']){
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
                                echo CHtml::activeTextField($model,'length',array('size'=>5, 'id'=>$container.'_length','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                    
                                echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
                                ?>
                                <br /><span id="<?php echo $container; ?>_length_label_errorMsg" class="error"></span>
                                </div>                
                            </div>            
                            
                            <div style="float:left; margin-right:10px;">
                                <strong><?php echo Yii::t('global','LABEL_WIDTH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'width'); ?><br />
                                <div>
                                <?php
                                echo CHtml::activeTextField($model,'width',array('size'=>5, 'id'=>$container.'_width','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                                
                                echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
                                ?>
                                <br /><span id="<?php echo $container; ?>_width_label_errorMsg" class="error"></span>
                                </div>                
                            </div>            
                            
                            <div style="float:left; margin-right:10px;">
                                <strong><?php echo Yii::t('global','LABEL_HEIGHT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'height'); ?><br />
                                <div>
                                <?php
                                echo CHtml::activeTextField($model,'height',array('size'=>5, 'id'=>$container.'_height','onkeyup'=>'rewrite_number($(this).attr("id"));'));
                                
                                echo '&nbsp;<span style="font-size:14px;">' . $measuring_unit . '</span>';
                                ?>
                                <br /><span id="<?php echo $container; ?>_height_label_errorMsg" class="error"></span>
                                </div>                
                            </div>           
                            
                            <div style="clear:both;"></div>
                        </div>                        
                     <?php
					}
					 ?>
                </td>                
            </tr>
		</table>
   
</div>
</div>
<?php
$script = '
$(function(){
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
	
	$("#'.$container.'_price_type").change(function(){
		if($("#'.$container.'_price_type").val()==1){
			$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('global','LABEL_PERCENT').'");
		}else{
			$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('views/products/edit_variants','LABEL_PRICE').'");
		}
	});
});
';

echo Html::script($script); 
?>