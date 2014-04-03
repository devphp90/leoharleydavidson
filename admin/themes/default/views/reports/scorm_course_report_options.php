<?php 
$help_hint_path = '/statistics/reports/scorm-course-report/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div>
		<table border="0" cellpadding="4" cellspacing="0">
        	<tr>
            	<td valign="top">
                    <strong><?php echo Yii::t('views/reports/scorm_report','LABEL_COURSES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-course'); ?>
                    <div style="width:150px;">
                    <input type="button" value="Filter" onclick="javascript:layout.B.load();" />
                    </div>                   
                </td>
                <td valign="top">
                	<strong><?php echo Yii::t('views/reports/scorm_course_report','LABEL_DATES');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-dates'); ?>
                    <div>
                    	<div>
						<?php
                        echo CHtml::textField('start_date','',array('size'=>20, 'id'=>'start_date'));
                        ?>                        
                        </div>
                        <div>
						<?php
                        echo CHtml::textField('end_date','',array('size'=>20, 'id'=>'end_date'));
                        ?>                          
                        </div>                    
                    </div>
                </td>
                <td valign="top">
                	<strong><?php echo Yii::t('views/reports/scorm_course_report','LABEL_PROFESSION');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-profession'); ?>
                    <div>
					<?php
                        echo CHtml::dropDownList('id_profession','',CHtml::listData($professions,'id','name'),array('id'=>'select_profession','prompt'=>'--'));        
                    ?>                    
                    </div>
                </td>
                <td valign="top">
                	<strong><?php echo Yii::t('views/reports/scorm_course_report','LABEL_PROFESSIONAL_ORDER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-professional-order'); ?>
                    <div>
					<?php
                        echo CHtml::dropDownList('id_professionnal_order','',CHtml::listData($professionnal_order,'id','name'),array('id'=>'select_professional_order','prompt'=>'--'));        
                    ?>                    
                    </div>                    
                </td>                
            </tr>
        </table>   
        <input type="hidden" name="id" id="id" value="11" />     
	</div>  
</div>
</div>
<?php
$script = '
$("#start_date").datetimepicker({
		dateFormat: "yy-mm-dd",
		showOn: "button",
		buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
		buttonImageOnly: true,
		buttonText: "Calendar",
		onSelect: layout.B.load
});

$("#end_date").datetimepicker({
		dateFormat: "yy-mm-dd",
		showOn: "button",
		buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
		buttonImageOnly: true,
		buttonText: "Calendar",
		onSelect: layout.B.load
});

$("#select_profession,#select_professional_order").on("change",function(){ layout.B.load(); });
';

echo Html::script($script); 
?>