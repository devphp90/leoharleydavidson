<?php 
$help_hint_path = '/statistics/reports/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
	<ul>
	    <li><a href="<?php echo CController::createUrl('coupon_report'); ?>"><?php echo Yii::t('views/reports/coupon_report','LABEL_TITLE');?></a></li>
    	<!--<li><a href="<?php echo CController::createUrl('scorm_report'); ?>">ADL SCORM 1.2 - Courses</a></li>!-->
	</ul>	            
</div>
</div>