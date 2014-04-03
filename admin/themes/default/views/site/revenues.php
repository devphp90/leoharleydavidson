<?php
// Client Script
$cs=Yii::app()->clientScript; 

$cs->registerScript('dhtmlx','
var content = new Object();
content.obj = new Object();

content.obj = templateLayout_B.attachObject("revenues");

$(function(){
	$("#select_revenue_option").on("change",function(){
		if ($(this).val() == 8) $("#revenue_dates").show();			
		else {
			$("#revenue_dates").hide();				
			
			get_revenues_stats();
		}
	});
	
	$("#revenue_start_date,#revenue_end_date").datepicker({
		dateFormat: "yy-mm-dd",
		showOn: "button",
		buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
		buttonImageOnly: true,
		buttonText: "Calendar",
		onClose: function(dateText, inst) { get_revenues_stats(); }
	});		
	
	get_revenues_stats();
});

function get_revenues_stats()
{
	$.ajax({
		url: "'.CController::createUrl('get_revenue_stats').'",
		data: { "revenue_option":$("#select_revenue_option").val(),"revenue_start_date":$("#revenue_start_date").val(),"revenue_end_date":$("#revenue_end_date").val() },
		type: "POST",
		beforeSend: function(jqXHR, settings) {
			$("#revenue_data").html("");	
			ajaxOverlay("revenue_data",0);		
			ajaxOverlay("revenue_data",1);
		},
		complete: function(){
			ajaxOverlay("revenue_data",0);
		},
		success: function(data){
			$("#revenue_data").html("").append(data);
		}
	});	
}

',CClientScript::POS_END);
?>
<div style="width:100%; height:100%; overflow:auto;" id="revenues">	
<div style="padding:10px;">	
    <div class="row" style="width:350px; border:1px solid #CCC; padding:10px; float:left; margin-right:5px;">
        <div>
            <div style="float:left; margin-right:5px;">
                <div style="float:left; margin-right:5px;">
                <?php
                $options=array(
                    0 => 'Until Now',
                    1 => 'Today',
                    2 => 'This month',
                    3 => 'Last 3 months',
                    4 => 'Last 6 months',
                    5 => 'This year',
                    6 => 'Last 5 years',
                    7 => 'Last 10 years',
                    8 => 'Custom',
                );
                
                echo CHtml::dropDownList('revenue_option',0,$options,array('id'=>'select_revenue_option','style'=>'font-size:14px;'));
                ?>
                </div>                
                <div style="float:left;"><?php echo Html::help_hint($help_hint_path.'revenue-option'); ?></div>
                <div style="clear:both;"></div>
            </div>                
            <div style="float:left; display:none;" id="revenue_dates">
                <div style="float:left; margin-right:5px;">
                    <strong>Start Date</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'revenue-start-date'); ?><br />            
                    <?php echo CHtml::textField('revenue_start_date','',array('id'=>'revenue_start_date')); ?>
                </div>
                <div style="float:left;">
                    <strong>End Date</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'revenue-end-date'); ?><br />            
                    <?php echo CHtml::textField('revenue_end_date','',array('id'=>'revenue_end_date')); ?>
                </div>                
                <div style="clear:both;"></div>                        
            </div>
            <div style="clear:both;"></div>        
        </div>  
        
        <div class="row" id="revenue_data" style="min-height:50px;"></div>
	</div>        
    
    <div class="row" style="width:350px; border:1px solid #CCC; padding:10px; float:left; margin-right:5px;">
    	<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td valign="top"><strong style="font-size:18px;">Unsettled Orders</strong></td>
            <td align="center" valign="top" style="font-size:18px;">0</td>
		</tr>
		<tr>
			<td valign="top"><strong style="font-size:18px;">New Comments</strong></td>
            <td align="center" valign="top" style="font-size:18px;">0</td>
		</tr>      
		<tr>
			<td valign="top"><strong style="font-size:18px;">Products Low In Inventory</strong></td>
            <td align="center" valign="top" style="font-size:18px;">0</td>
		</tr>          
		<tr>
			<td valign="top"><strong style="font-size:18px;">Options Low In Inventory</strong></td>
            <td align="center" valign="top" style="font-size:18px;">0</td>
		</tr>         
        </table>                    
    </div>
    
    <div style="clear:both;"></div>        
    
    <div class="row" style="width:350px; border:1px solid #CCC; padding:10px; float:left; margin-right:5px; margin-top:5px;">
    	<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td valign="top"><strong style="font-size:18px;">Top Buyer</strong>
            <div style="margin-top:5px; font-size:14px;">
            <div style="margin-bottom:5px;">Kheang Hok Chin<br />shino@sympatico.ca<br /><?php echo Html::nf(1500); ?></div>
            <a href="#"><strong>View more</strong></a>
            </div>
            </td>
		</tr>
        <tr>            
			<td valign="top"><strong style="font-size:18px;">Best Selling Product</strong>
            <div style="margin-top:5px; font-size:14px;">
            Plain T-Shirt<br />
            <em>Color: Red, Size: Large</em>
            <div style="margin-top:5px; margin-bottom:5px;">
            	<div style="float:left;">150 units sold</div>
				<div style="float:right;"><?php echo Html::nf(180); ?></div>
				<div style="clear:both;"></div>
            </div>                
            <a href="#"><strong>View more</strong></a>
            </div></td>
		</tr>      
        </table>                    
    </div>  
    
      
    
    <div style="clear:both;"></div>        
</div>
</div>