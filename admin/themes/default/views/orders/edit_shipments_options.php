<?php 
$model_name = get_class($model);
//$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$columns = Html::getColumnsMaxLength(Tbl_OrdersShipment::tableName());	

$help_hint_path = '/sales/orders/shipments/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); ?>
<?php echo CHtml::activeHiddenField($model,'id_orders',array('id'=>$container.'_id_orders')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row" style="float:left; margin-right:5px;">
        <strong><?php echo Yii::t('views/orders/edit_shipments_options','LABEL_SHIPMENT_NO');?></strong>
        <?php echo (isset($columns['shipment_no']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_shipment_no_maxlength">'.($columns['shipment_no']-strlen($model->shipment_no)).'</span>)</em>':'') ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipment-no'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'shipment_no',array( 'id'=>$container.'_shipment_no','maxlength'=>$columns['shipment_no']));
        ?>
        <br /><span id="<?php echo $container; ?>_shipment_no_errorMsg" class="error"></span>
        </div>                
	</div>       
    <div class="row" style="float:left; margin-right:5px;">
        <strong><?php echo Yii::t('views/orders/edit_shipments_options','LABEL_SHIPMENT_DATE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'shipment-date'); ?>
        <br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'date_shipment',array( 'id'=>$container.'_date_shipment'));
        ?>
        <br /><span id="<?php echo $container; ?>_date_shipment_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row" style="float:left; margin-right:5px;">
        <strong><?php echo Yii::t('views/orders/edit_shipments_options','LABEL_TRACKING_NO');?></strong>
        <?php echo (isset($columns['tracking_no']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_tracking_no_maxlength">'.($columns['tracking_no']-strlen($model->tracking_no)).'</span>)</em>':'') ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'tracking-no'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'tracking_no',array( 'id'=>$container.'_tracking_no','style'=>'width:250px;','maxlength'=>$columns['tracking_no']));
        ?>
        <br /><span id="<?php echo $container; ?>_tracking_no_errorMsg" class="error"></span>
        </div>                
	</div>      
    
    <div style="clear:both;"></div>    
    <div class="row">
        <strong><?php echo Yii::t('views/orders/edit_shipments_options','LABEL_TRACKING_URL');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'tracking-url'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'tracking_url',array( 'id'=>$container.'_tracking_url','style'=>'width:100%;'));
        ?>
        <br /><span id="<?php echo $container; ?>_tracking_url_errorMsg" class="error"></span>
        </div>                
	</div>      
    <div class="row">
        <strong><?php echo Yii::t('views/orders/edit_shipments_options','LABEL_COMMENTS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'comments'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextArea($model,'comments',array('id'=>$container.'_comments','style'=>'width:100%;height:30px;'));
        ?>
        <br /><span id="<?php echo $container; ?>_comments_errorMsg" class="error"></span>
        </div>                
	</div>      
    <div style="clear:both;"></div>       
</div>
<?php
$script = '
$(function(){
	$("#'.$container.'_date_shipment").datepicker({
		dateFormat: "yy-mm-dd",
		showOn: "button",
		buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
		buttonImageOnly: true,
		buttonText: "Calendar"
	});
});';

echo Html::script($script); 
?>