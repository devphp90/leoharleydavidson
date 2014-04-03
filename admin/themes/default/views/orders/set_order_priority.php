<?php
$app = Yii::app();

$help_hint_path = '/sales/orders/information/';
?>
<form id="set_order_status" style="width:100%; height:100%; overflow:auto;">	
<div style="padding:10px;">	
    <div class="row">
    <div>
        <strong><?php echo Yii::t('views/orders/set_order_priority','LABEL_SET_ORDER_PRIORITY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'set-priority'); ?>
    </div>   
    <div>
    <?php 
    echo CHtml::dropDownList('priority',$status,array(
		0 => Yii::t('controllers/OrdersController','LABEL_PRIORITY_NORMAL'),
		1 => Yii::t('controllers/OrdersController','LABEL_PRIORITY_ATTENTION'),
		2 => Yii::t('controllers/OrdersController','LABEL_PRIORITY_URGENT'),
	),array('id'=>$container.'_priority','options'=>array(
		1=>array('style'=>'color:#E839D7;'),
		2=>array('style'=>'color:#F00;'),
	))); 
    ?>
    </div> 
    </div>     
</div>
</form>

    