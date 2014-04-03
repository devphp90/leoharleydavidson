<?php
// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/reports/index','LABEL_TITLE').'");

var layout = new Object();
layout.obj = templateLayout_B.attachLayout("1C");

layout.A = new Object();
layout.A.obj = layout.obj.cells("a");
layout.A.obj.hideHeader();

$.ajax({
	url: "'.CController::createUrl('index_options').'",
	type: "POST",
	success: function(data){
		layout.A.obj.attachHTMLString(data);		
	}
});		

',CClientScript::POS_END);
?>