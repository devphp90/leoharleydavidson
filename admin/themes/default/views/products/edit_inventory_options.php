<?php 
$model_name = get_class($model);

$script = '$(function(){
	$("#'.$container.'_track_inventory_0").click(function(){
		$("#'.$container.'_track_inventory").hide();
	});
	
	$("#'.$container.'_track_inventory_1").click(function(){
		$("#'.$container.'_track_inventory").show();
	});	
});';

echo Html::script($script); 

$help_hint_path = '/catalog/products/inventory/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_TRACK_INVENTORY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'track-inventory'); ?>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[track_inventory]',$model->track_inventory?1:0,array('value'=>1,'id'=>$container.'_track_inventory_1')).'&nbsp;<label for="'.$container.'_track_inventory_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[track_inventory]',!$model->track_inventory?1:0,array('value'=>0,'id'=>$container.'_track_inventory_0')).'&nbsp;<label for="'.$container.'_track_inventory_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>          
    </div> 
    
    <hr />
     
    <div id="<?php echo $container; ?>_track_inventory" <?php echo !$model->track_inventory ? 'style="display:none;"':''; ?>>
    <div class="row">
        <?php echo '<strong>'.Yii::t('global','LABEL_QTY').'</strong> '.($model->product_has_variant?Yii::t('views/products/edit_inventory_options','LABEL_HAS_VARIANT_DEFAULT'):'');?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'qty'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'qty',array('size'=>5, 'id'=>$container.'_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>&nbsp;<span id="<?php echo $container; ?>_qty_errorMsg" class="error"></span>
        </div>                
	</div>
    
    <div class="row">
        <?php echo '<strong>'.Yii::t('global','LABEL_NOTIFY_INVENTORY_LOW').'</strong> '.($model->product_has_variant?Yii::t('views/products/edit_inventory_options','LABEL_HAS_VARIANT_APPLY'):'');?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'notify-when-inventory-is-low'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[notify]',$model->notify?1:0,array('value'=>1,'id'=>$container.'_notify_1')).'&nbsp;<label for="'.$container.'_notify_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[notify]',!$model->notify?1:0,array('value'=>0,'id'=>$container.'_notify_0')).'&nbsp;<label for="'.$container.'_notify_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>              
	</div>        
	<div class="row" id="<?php echo $container;?>_notify_qty" <?php echo !$model->notify ? 'style="display:none;"':''; ?>>
        <?php echo '<strong>'.Yii::t('global','LABEL_NOTIFY_QTY_REACHES').'</strong> '.($model->product_has_variant?Yii::t('views/products/edit_inventory_options','LABEL_HAS_VARIANT_DEFAULT'):'');?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'notify_qty',array('size'=>5, 'id'=>$container.'_notify_qty','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>&nbsp;<span id="<?php echo $container; ?>_notify_qty_errorMsg" class="error"></span>
        </div>      
    </div>    
    <!--<div class="row" id="<?php echo $container;?>_allow_backorders" <?php echo $model->out_of_stock_enabled ? 'style="display:none;"':''; ?>>
    	<strong><?php echo Yii::t('global','LABEL_ALLOW_BACKORDERS');?></strong>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[allow_backorders]',$model->allow_backorders?1:0,array('value'=>1,'id'=>$container.'_allow_backorders_1')).'&nbsp;<label for="'.$container.'_allow_backorders_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[allow_backorders]',!$model->allow_backorders?1:0,array('value'=>0,'id'=>$container.'_allow_backorders_0')).'&nbsp;<label for="'.$container.'_allow_backorders_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>              
	</div> --> 
      
    <hr />
    
    <div class="row">
    	<strong><?php echo Yii::t('views/products/edit_inventory_options','LABEL_DISPLAY_OUT_OF_STOCK');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'in-stock'); ?>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[in_stock]',!$model->in_stock?1:0,array('value'=>0,'id'=>$container.'_in_stock_0')).'&nbsp;<label for="'.$container.'_in_stock_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[in_stock]',$model->in_stock?1:0,array('value'=>1,'id'=>$container.'_in_stock_1')).'&nbsp;<label for="'.$container.'_in_stock_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>              
	</div>        
     
    <div class="row">
        <?php echo '<strong>'.Yii::t('global','LABEL_APPEAR_OUT_OF_STOCK_QTY_REACHES').'</strong> '.($model->product_has_variant?Yii::t('views/products/edit_inventory_options','LABEL_HAS_VARIANT_APPLY'):'');?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'appear-out-of-stock-when-qty-reaches'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'out_of_stock',array('size'=>5, 'id'=>$container.'_out_of_stock','onkeyup'=>'rewrite_number($(this).attr("id"));'));
        ?>&nbsp;<span id="<?php echo $container; ?>_out_of_stock_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
    	<?php echo '<strong>'.Yii::t('views/products/edit_inventory_options','LABEL_OUT_OF_STOCK_ENALBED').'</strong> '.($model->product_has_variant?Yii::t('views/products/edit_inventory_options','LABEL_HAS_VARIANT_APPLY'):'');?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'disable-product-when-out-of-stock'); ?>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[out_of_stock_enabled]',$model->out_of_stock_enabled?1:0,array('value'=>1,'id'=>$container.'_out_of_stock_enabled_1')).'&nbsp;<label for="'.$container.'_out_of_stock_enabled_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[out_of_stock_enabled]',!$model->out_of_stock_enabled?1:0,array('value'=>0,'id'=>$container.'_out_of_stock_enabled_0')).'&nbsp;<label for="'.$container.'_out_of_stock_enabled_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>              
	</div>                   
	 
    </div>
</div>
</div>
<?php
$script = '
$(function(){

	$("#'.$container.'_notify_0").click(function(){
		$("#'.$container.'_notify_qty").hide();
	});
	
	$("#'.$container.'_notify_1").click(function(){
		$("#'.$container.'_notify_qty").show();
	});	
	
	$("#'.$container.'_out_of_stock_enabled_1").click(function(){
		$("#'.$container.'_allow_backorders").hide();
	});
	
	$("#'.$container.'_out_of_stock_enabled_0").click(function(){
		$("#'.$container.'_allow_backorders").show();
	});

});		
';

echo Html::script($script); 
?>