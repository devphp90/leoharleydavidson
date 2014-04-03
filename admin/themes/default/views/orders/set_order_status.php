<?php
$app = Yii::app();

$help_hint_path = '/sales/orders/information/';
?>
<form id="set_order_status" style="width:100%; height:100%; overflow:auto;">	
<div style="padding:10px;">	
    <div class="row">
        <div><strong><?php echo Yii::t('views/orders/set_order_status','LABEL_SET_ORDER_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'set-status'); ?></div>      
        <div>
			<?php 
            echo CHtml::dropDownList('status',$status,array(
                -1 => Yii::t('controllers/OrdersController','LABEL_STATUS_CANCELLED'),
                //0 => Yii::t('controllers/OrdersController','LABEL_STATUS_INCOMPLETE'),
                1 => Yii::t('controllers/OrdersController','LABEL_STATUS_PENDING'),
                2 => Yii::t('controllers/OrdersController','LABEL_STATUS_PAYMENT_REVIEW'),
                3 => Yii::t('controllers/OrdersController','LABEL_STATUS_SUSPECTED_FRAUD'),
                5 => Yii::t('controllers/OrdersController','LABEL_STATUS_PROCESSING'),
                6 => Yii::t('controllers/OrdersController','LABEL_STATUS_ON_HOLD'),
                7 => Yii::t('controllers/OrdersController','LABEL_STATUS_COMPLETED'),
            ),array('id'=>$container.'_status','options'=>array(
                -1=>array('style'=>'color:#F00;'),
                7=>array('style'=>'color:#090;'),
            ))); 
            ?>
        </div>
    </div>
    <?php if($app->params['enable_inventory']){?>
    <div id="<?php echo $container;?>_display_status_cancelled" style="display:none">
        <div class="row">
            <strong><?php echo Yii::t('views/orders/set_order_status','LABEL_UPDATE_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'update-qty'); ?>
            <div>
            <?php 
            echo CHtml::radioButton('update_qty',0,array('value'=>1,'id'=>$container.'_update_qty_1')).'&nbsp;<label for="'.$container.'_update_qty_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton('update_qty',1,array('value'=>0,'id'=>$container.'_update_qty_0')).'&nbsp;<label for="'.$container.'_update_qty_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
            ?>
            </div>              
        </div> 
    </div> 
    <?php
	}
	?>        
</div>
</form>
<?php
if($app->params['enable_inventory']){
	$script = '
	$(function(){
		
		$("#'.$container.'_status").change(function(){
			$("#'.$container.'_status").val()
			if($("#'.$container.'_status").val()==-1){
				$("#'.$container.'_display_status_cancelled").show();
			}else{
				$("#'.$container.'_display_status_cancelled").hide();
			}
		});
	});
	';
	
	echo Html::script($script);
}
?>