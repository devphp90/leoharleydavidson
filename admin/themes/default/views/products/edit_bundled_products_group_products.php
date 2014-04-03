<?php 
$model_name = get_class($model);

$columns = Html::getColumnsMaxLength(Tbl_Options::tableName());		

echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
echo CHtml::activeHiddenField($model,'use_product_current_price',array('id'=>$container.'_use_product_current_price'));

$help_hint_path = '/catalog/products/bundled-products/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">		
	<?php if (!$model->use_product_current_price) { ?>
    <div class="row">
        <div style="float:left; margin-right: 8px;">
        	<strong><?php echo Yii::t('global','LABEL_PRICE_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price-type'); ?><br />
            <div>
            <?php
            echo CHtml::activeDropDownList($model,'price_type',array(0=>Yii::t('global','LABEL_FIXED'),1=>Yii::t('global','LABEL_PERCENTAGE')),array( 'id'=>$container.'_price_type'));
            ?>
            <br /><span id="<?php echo $container;?>_price_type_errorMsg" class="error"></span>
            </div> 
        </div> 
        <div style="float:left; margin-right:8px;">
            <strong id="<?php echo $container?>_title_price_percent"><?php echo($model->price_type?Yii::t('global','LABEL_PERCENT'):Yii::t('global','LABEL_PRICE'));?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'price'); ?><br />
            <div>
            <?php
            echo CHtml::activeTextField($model,'price',array('size'=>10, 'id'=>$container.'_price'));
            ?>
            <br /><span id="<?php echo $container;?>_price_errorMsg" class="error"></span>
            </div>  
        </div>     
        <div style="clear:both;"></div>
	</div>
    <?php } ?>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'qty'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'qty',array('size' => 5, 'id'=>$container.'_qty'));
        ?>
        <br /><span id="<?php echo $container; ?>_qty_errorMsg" class="error"></span>
        </div> 
    </div>
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_USER_DEFINED_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'user-defined-qty'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[user_defined_qty]',$model->user_defined_qty?1:0,array('value'=>1,'id'=>$container.'_user_defined_qty_1')).'&nbsp;<label for="'.$container.'_user_defined_qty_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[user_defined_qty]',!$model->user_defined_qty?1:0,array('value'=>0,'id'=>$container.'_user_defined_qty_0')).'&nbsp;<label for="'.$container.'_user_defined_qty_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>   
    </div> 
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_DEFAULT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'default'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[selected]',$model->selected?1:0,array('value'=>1,'id'=>$container.'_selected_1')).'&nbsp;<label for="'.$container.'_selected_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[selected]',!$model->selected?1:0,array('value'=>0,'id'=>$container.'_selected_0')).'&nbsp;<label for="'.$container.'_selected_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>   
    </div>        
</div>
</div>
<?php
$script = '
$(function(){
	
	$("#'.$container.'_price_type").change(function(){
		'.$container.'_change_price_type();
	});
	
	function '.$container.'_change_price_type(){
		if($("#'.$container.'_price_type").val()==1){
			$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('global','LABEL_PERCENT').' <em style=\"font-weight:normal\">('.Yii::t('views/products/edit_bundled_products_group_products','LABEL_POURCENT_BASED_ON').' '.Html::nf($product_current_price).')</em>");
			//$("#'.$container.'_price").val("0");
		}else{
			$("#'.$container.'_title_price_percent").html("").append("'.Yii::t('global','LABEL_PRICE').'");
			//$("#'.$container.'_price").val('.$model->price.');
		}	
	}
	'.$container.'_change_price_type();
});
';

echo Html::script($script); 
?>