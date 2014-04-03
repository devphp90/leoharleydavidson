<?php 
$model_name = get_class($model);
$columns = Html::getColumnsMaxLength(Tbl_SubscriptionContest::tableName());

$help_hint_path = '/marketing/subscription-contest/';
?>
<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    
    	<div class="row border_bottom">
            <strong>Type</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'type');?>
        <div>
			<?php 
            echo CHtml::radioButton($model_name.'[contest]',$model->contest?1:0,array('value'=>1,'id'=>$container.'_contest_1')).'&nbsp;<label for="'.$container.'_contest_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_CONTEST').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[contest]',!$model->contest?1:0,array('value'=>0,'id'=>$container.'_contest_0')).'&nbsp;<label for="'.$container.'_contest_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_SUBSCRIPTIONS').'</label>'; 
            ?>
            </div>
        </div>
    <div class="row border_bottom">
        <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled');?>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
        ?>
        </div>        
    </div>
    
   
           
                      
    <div class="row border_bottom">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_name_maxlength">'.($columns['name']-strlen($model->name)).'</span>)</em>':''; ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name');?>
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('style' => 'width: 250px;','maxlength'=>$columns['name'], 'id'=>$container.'_name'));
        ?>
        <br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>                
	</div> 
    
     <div class="row border_bottom">
        <strong><?php echo Yii::t('views/subscriptioncontest/edit_info_options','LABEL_CUSTOMER_ONLY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'customer-only');?><br />
            <em><?php echo Yii::t('views/subscriptioncontest/edit_info_options','LABEL_CUSTOMER_ONLY_DESCRIPTION');?></em>
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[customer_only]',$model->customer_only?1:0,array('value'=>1,'id'=>$container.'_customer_only_1')).'&nbsp;<label for="'.$container.'_customer_only_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[customer_only]',!$model->customer_only?1:0,array('value'=>0,'id'=>$container.'_customer_only_0')).'&nbsp;<label for="'.$container.'_customer_only_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>        
    
    
     <div class="row" id="<?php echo $container; ?>_form_other_field" <?php echo($model->customer_only?'style="display:none"':'');?>>
        <strong><?php echo Yii::t('views/subscriptioncontest/edit_info_options','LABEL_FORM');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'form-field');?><br />
            <em><?php echo Yii::t('views/subscriptioncontest/edit_info_options','LABEL_FORM_DESCRIPTION');?></em>
        <div>
        <?php 
        echo CHtml::checkBox($model_name.'[include_form_address]',$model->include_form_address?1:0,array('value'=>1,'id'=>$container.'_include_form_address')).'&nbsp;<label for="'.$container.'_include_form_address" style="display:inline; text-align: left;">'.Yii::t('views/subscriptioncontest/edit_info_options','LABEL_FORM_ADDRESS').'</label>&nbsp;&nbsp;'.CHtml::checkBox($model_name.'[include_form_telephone]',$model->include_form_telephone?1:0,array('value'=>1,'id'=>$container.'_include_form_telephone')).'&nbsp;<label for="'.$container.'_include_form_telephone" style="display:inline; text-align: left;">'.Yii::t('views/subscriptioncontest/edit_info_options','LABEL_FORM_TELEPHONE').'</label>'; 
        ?>
        </div>        
    </div>
    </div>
    
    
    
    
    
    <div class="row border_bottom">
						<strong><?php echo Yii::t('views/subscriptioncontest/edit_info_options','LABEL_FORM_ID_REBATE_COUPON')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'select-coupon');?><br />
            <em><?php echo Yii::t('views/subscriptioncontest/edit_info_options','LABEL_FORM_ID_REBATE_COUPON_DESCRIPTION');?></em>
						<div>
						<select name="SubscriptionContestForm[id_rebate_coupon]">
                        <option value="0">-----------</option>
						<?php
						
						
						$criteria=new CDbCriteria; 
						$criteria->condition='active=1 AND coupon=1'; 
						//$criteria->params=array(':id_parent'=>$id_parent); 	
						$criteria->order='name ASC';	
						
						$eol = "\r\n";
							
						foreach (Tbl_RebateCoupon::model()->findAll($criteria) as $row) {		
							
							echo '<option value="'.$row->id.'" '.($row->id==$model->id_rebate_coupon?'selected="selected"':'').'>'.$row->name.' ('.$row->coupon_code.')</option>';	
						}	

						
						?>
						
						</select>
						</div>
					</div>	
    
    
    
    
    <div class="row border_bottom">
        <div style="float:left; padding-right:5px;">
            <strong><?php echo Yii::t('global','LABEL_START_DATE')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-date');?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'start_date',array('size'=>18, 'id'=>$container.'_start_date'));
            ?>
            <br /><span id="<?php echo $container; ?>_start_date_errorMsg" class="error"></span>
            </div>                
        </div>                
        <div style="float:left; padding-right:5px;">
            <strong><?php echo Yii::t('views/subscriptioncontest/edit_info_options','LABEL_END_DATE')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-date');?>
            <div>
            <?php
            echo CHtml::activeTextField($model,'end_date',array('size'=>18, 'id'=>$container.'_end_date'));
            ?>
            <br /><span id="<?php echo $container; ?>_end_date_errorMsg" class="error"></span>
            </div>                
        </div>       
        <div style="clear:both;"></div>
	</div>
    
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_CONTENT')?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'content');?>
        <div id="<?php echo $container; ?>_subscription_contest_description" style="width:98%;height:350px;"></div>                
	</div>   
     
         
</div>
</div>
<?php
$script = '
$(function(){
	$("#'.$container.'_start_date,#'.$container.'_end_date").datetimepicker({
			dateFormat: "yy-mm-dd",
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});	
	
	
	
	'.$container.'.layout.A.tabbar = new Object();
	
	'.$container.'.layout.A.tabbar.obj = new dhtmlXTabBar("'.$container.'_subscription_contest_description","top");
	'.$container.'.layout.A.tabbar.obj.setImagePath(dhx_globalImgPath);
	'.$container.'.layout.A.tabbar.obj.loadXML("'.CController::createUrl('xml_subscription_contest_description',array('container'=>$container,'id'=>$model->id)).'", function(){
		load_ckeditor("'.$containerLayout.'");	
	
		// listen to creation event of any CKEditor
		$( "#'.$containerLayout.' .editor" ).off("instanceReady.ckeditor");
		$( "#'.$containerLayout.' .editor" ).on( "instanceReady.ckeditor", function( editor ){
			'.$container.'.load_og_form();
		});
	});
	
	
	
	$("#'.$container.'_customer_only_1").click(function(){
		$("#'.$container.'_form_other_field").hide();	
	});
	
	$("#'.$container.'_customer_only_0").click(function(){
		$("#'.$container.'_form_other_field").show();		
	});
	
	
});


';

echo Html::script($script); 
?>