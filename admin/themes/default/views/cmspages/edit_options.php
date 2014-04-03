<?php 
$model_name = get_class($model);
echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id')); 
echo CHtml::activeHiddenField($model,'home_page',array('id'=>$container.'_home_page'));

$help_hint_path = '/cms-pages/';

$current_datetime = date('Y-m-d H:i:s');
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding-top:10px; padding-left:10px;">	
    <h1><?php echo Yii::t('global','LABEL_TITLE_PARAMETERS');?></h1>
    <div style="display:<?php echo($model->home_page?'none':'block');?>">
        <div class="row border_bottom">
            <strong><?php echo Yii::t('global','LABEL_STATUS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[active]',$model->active?1:0,array('value'=>1,'id'=>$container.'_active_1')).'&nbsp;<label for="'.$container.'_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',!$model->active?1:0,array('value'=>0,'id'=>$container.'_active_0')).'&nbsp;<label for="'.$container.'_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
            ?>
            </div>        
        </div>
    
        <div id="<?php echo $container.'_display_menu'; ?>" class="row border_bottom" <?php echo $model->header_only ? 'style="display:none;"':''; ?>>
            <div><strong><?php echo Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display'); ?><br />
            <em><?php echo Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_DESCRIPTION');?></em></div>
            <div style="float:left">
            <?php 
            echo CHtml::radioButton($model_name.'[display]',$model->display?1:0,array('value'=>1,'id'=>$container.'_display_1')).'&nbsp;<label for="'.$container.'_display_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display]',!$model->display?1:0,array('value'=>0,'id'=>$container.'_display_0')).'&nbsp;<label for="'.$container.'_display_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
            ?>
            </div> 
            <div style="float:left; margin-left:10px;<?php echo($model->display || $model->header_only?' display:none;':'');?>" id="<?php echo $container;?>_display_url">
            <?php
            foreach (Tbl_Language::model()->active()->findAll() as $value) {
				echo '<div style="margin-top:5px;">http://<strong>'.Yii::t('views/cmspages/edit_options','LABEL_YOUR_DOMAIN_NAME').'</strong>/'.$value->code.'/page/'.(!$model->cmspage_description[$value->code]['alias']?'<strong>'.Yii::t('views/cmspages/edit_options','LABEL_YOUR_DOMAIN_NAME_ALIAS_LANGUAGE',array("{name_language}" =>strtoupper($value->name))).'</strong>':$model->cmspage_description[$value->code]['alias']).'</div>';
			}
			?></div>
            <div style="clear:both"></div>       
        </div>
        
         <div id="<?php echo $container.'_display_header_only'; ?>" style="display:<?php echo(!$model->display ?'none':'block');?>">
        <div class="row border_bottom">
            <strong><?php echo Yii::t('views/cmspages/edit_options','LABEL_ONLY_MENU');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'header-only'); ?><br />
            <em><?php echo Yii::t('views/cmspages/edit_options','LABEL_ONLY_MENU_DESCRIPTION');?></em>
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[header_only]',$model->header_only?1:0,array('value'=>1,'id'=>$container.'_header_only_1')).'&nbsp;<label for="'.$container.'_header_only_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[header_only]',!$model->header_only?1:0,array('value'=>0,'id'=>$container.'_header_only_0')).'&nbsp;<label for="'.$container.'_header_only_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
            ?>
            </div>        
        </div>
        </div>
        
        
        <div id="<?php echo $container.'_display_menu_wich'; ?>" style="display:<?php echo(($model->header_only or !$model->display)?'none':'block');?>">
        <div class="row border_bottom"> <strong><?php echo Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'display-menu'); ?><br />
            <em><?php echo Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH_DESCRIPTION');?></em>
            <div>
                <?php
                echo CHtml::radioButton($model_name.'[display_menu]',($model->display_menu==0)?1:0,array('value'=>0,'id'=>'display_menu_0')).'&nbsp;<label for="display_menu_0" style="display:inline; text-align: left;">'.Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH_BOTH').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display_menu]',($model->display_menu==1)?1:0,array('value'=>1,'id'=>'display_menu_1')).'&nbsp;<label for="display_menu_1" style="display:inline; text-align: left;">'.Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH_TOP_ONLY').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[display_menu]',($model->display_menu==2)?1:0,array('value'=>2,'id'=>'display_menu_2')).'&nbsp;<label for="display_menu_2" style="display:inline; text-align: left;">'.Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH_BOTTOM_ONLY').'</label>';
                ?>
            </div>                 
        </div>  
        </div>
        
        <div id="<?php echo $container.'_external_link'; ?>" class="row border_bottom" <?php echo $model->header_only ? 'style="display:none;"':''; ?>>
            <strong><?php echo Yii::t('views/cmspages/edit_options','LABEL_EXTERNAL_LINK'); ?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'external-link'); ?><br />
            <em><?php echo Yii::t('views/cmspages/edit_options','LABEL_EXTERNAL_LINK_DESCRIPTION'); ?></em>
            <div>
            <?php
            echo CHtml::radioButton($model_name.'[external_link]',$model->external_link?1:0,array('value'=>1,'id'=>$container.'_external_link_1')).'&nbsp;<label for="'.$container.'_external_link_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[external_link]',!$model->external_link?1:0,array('value'=>0,'id'=>$container.'_external_link_0')).'&nbsp;<label for="'.$container.'_external_link_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; ?>
            
            </div>        
        </div>
        
        
        <div id="<?php echo $container.'_indexing'; ?>" class="row border_bottom" style="display:<?php echo(($model->header_only or !$model->display)?'none':'block');?>">
            <strong><?php echo Yii::t('views/cmspages/edit_options','LABEL_INDEXING');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'indexing'); ?><br />
            <div>
            <?php 
            echo CHtml::radioButton($model_name.'[indexing]',$model->indexing?1:0,array('value'=>1,'id'=>$container.'_indexing_1')).'&nbsp;<label for="'.$container.'_indexing_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_ENABLED').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[indexing]',!$model->indexing?1:0,array('value'=>0,'id'=>$container.'_indexing_0')).'&nbsp;<label for="'.$container.'_indexing_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_DISABLED').'</label>'; 
            ?>
            </div>        
        </div>           
        
        <div id="<?php echo $container.'_id_subscription_contest_container'; ?>" class="row border_bottom" style="display:<?php echo(($model->header_only or !$model->display)?'none':'block');?>">
            <strong><?php echo Yii::t('views/config/edit_banner','LABEL_REGISTRATION_CONTEST');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'id_subscription_contest'); ?><br />
            <div>
            <select name="<?php echo $model_name.'[id_subscription_contest]'; ?>" id="<?php echo $container.'_id_subscription_contest'; ?>">
            <option value="">--</option>
			<?php					
				$criteria=new CDbCriteria; 
				$criteria->condition='((active=1 AND (end_date = "0000-00-00 00:00:00" OR end_date >= "'.$current_datetime.'")) OR id = "'.$model->id_subscription_contest.'")'; 									
				
				if (sizeof($subscription_contests = Tbl_SubscriptionContest::model()->findAll($criteria))) {
					
					foreach ($subscription_contests as $row) {		
						echo '<option value="'.$row->id.'" '.(($model->id_subscription_contest==$row->id)?'selected="selected"':'').'>'.$row->name.'</option>';	
					}						
				}
			?>	
			</select>
			<br /><span id="<?php echo $container;?>_id_subscription_contest_errorMsg" class="error"></span>				
            </div>        
        </div>            
        
        
        <div id="<?php echo $container.'_pages_all'; ?>" style="display:<?php echo($model->display!=0?'block':'none');?>" class="border_bottom">
         <div class="row ">
            <strong><?php echo Yii::t('views/cmspages/edit_options','LABEL_PARENT_PAGE_UNDER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'parent-page'); ?><br />
            <em><?php echo Yii::t('views/cmspages/edit_options','LABEL_PARENT_PAGE_UNDER_DESCRIPTION');?></em>
        </div>   
        <div class="row" id="<?php echo $container.'_pages'; ?>" style="background-color:#f5f5f5;border :1px solid Silver; padding:5px; width:300px;">
        </div><br /><span id="<?php echo $container; ?>_pages_errorMsg" class="error"></span>
        </div>                 
    </div>
    <?php
    if($model->home_page){
		echo '<div class="row">'.Yii::t('views/cmspages/edit_options','LABEL_NO_OPTION').'</div>';	
	}
	?>
    
    
</div>
</div>
<?php

$script = '
$(function(){
	$("#'.$container.'_display_1").on("click",function(){
		$("#'.$container.'_pages_all").show();
		$("#'.$container.'_external_link").show();
		$("#'.$container.'_display_header_only").show();
		$("#'.$container.'_display_menu_wich").show();
		$("#'.$container.'_id_subscription_contest_container").show();
		$("#'.$container.'_display_url").hide();		
		
		if ($("#'.$container.'_layout input[name=\''.$model_name.'[external_link]\']:checked").val() == 1) {
			$(".'.$container.'_external_link_display").show();
			$(".'.$container.'_content_display").hide();			
		} else {
			$(".'.$container.'_external_link_display").hide();
			$(".'.$container.'_content_display").show();			
		}
	});
	
	$("#'.$container.'_display_0").on("click",function(){
		$("#'.$container.'_pages_all").hide();
		$("#'.$container.'_external_link").hide();
		$("#'.$container.'_display_header_only").hide();
		$("#'.$container.'_display_menu_wich").hide();
		$("#'.$container.'_display_url").show();
		$(".'.$container.'_external_link_display").hide();
		$("#'.$container.'_id_subscription_contest_container").hide();	
		$(".'.$container.'_content_display").show();					
	});

	$("#'.$container.'_header_only_1").on("click",function(){
		
		$(".'.$container.'_header_display").hide();
		$("#'.$container.'_display_menu").hide();
		$("#'.$container.'_external_link").hide();
		$("#'.$container.'_display_menu_wich").hide();
		$("#'.$container.'_indexing").hide();		
		$(".'.$container.'_external_link_display").hide();
		$(".'.$container.'_content_display").hide();	
		$("#'.$container.'_id_subscription_contest_container").hide();			
	});
	
	$("#'.$container.'_header_only_0").on("click",function(){
		$(".'.$container.'_header_display").show();
		$("#'.$container.'_display_menu").show();
		$("#'.$container.'_external_link").show();
		$("#'.$container.'_display_menu_wich").show();
		$("#'.$container.'_id_subscription_contest_container").show();		
		
		if ($("#'.$container.'_layout input[name=\''.$model_name.'[external_link]\']:checked").val() == 1) {
			$(".'.$container.'_external_link_display").show();
			$(".'.$container.'_content_display").hide();			
			$("#'.$container.'_indexing").hide();	
		} else {
			$(".'.$container.'_external_link_display").hide();
			$(".'.$container.'_content_display").show();						
			$("#'.$container.'_indexing").show();	
		}
	});
	
	$("#'.$container.'_indexing_1").on("click",function(){
		$("#'.$container.'_layout .display_indexing").show();
	});
	
	$("#'.$container.'_indexing_0").on("click",function(){
		$("#'.$container.'_layout .display_indexing").hide();		
	});	
	
	$("#'.$container.'_external_link_1").on("click",function(){
		$(".'.$container.'_external_link_display").show();
		$(".'.$container.'_content_display").hide();	
		$("#'.$container.'_indexing_0").trigger("click");		
		$("#'.$container.'_indexing_0").prop("disabled",true);
		$("#'.$container.'_indexing_1").prop("disabled",true);
	});
	
	$("#'.$container.'_external_link_0").on("click",function(){
		$(".'.$container.'_external_link_display").hide();	
		$(".'.$container.'_content_display").show();	
		$("#'.$container.'_indexing_0").prop("disabled",false);
		$("#'.$container.'_indexing_1").prop("disabled",false);
		$("#'.$container.'_indexing_1").trigger("click");		
	});		
	
	$("#'.$container.'_id_subscription_contest").on("change",function(){		
		if ($(this).val() != "") {
			$("#'.$container.'_external_link_0").trigger("click");				
			$("#'.$container.'_indexing_0").trigger("click");		
			
			$("#'.$container.'_external_link_0").prop("disabled",true);
			$("#'.$container.'_external_link_1").prop("disabled",true);			
			

			$("#'.$container.'_indexing_0").prop("disabled",true);
			$("#'.$container.'_indexing_1").prop("disabled",true);			
					
			$(".'.$container.'_content_display").hide();
		} else {
			
			$("#'.$container.'_external_link_0").prop("disabled",false);
			$("#'.$container.'_external_link_1").prop("disabled",false);			
			

			$("#'.$container.'_indexing_0").prop("disabled",false);
			$("#'.$container.'_indexing_1").prop("disabled",false);				
			$(".'.$container.'_content_display").show();
		}
	});

});

'.$container.'.layout.B.tree = new Object();
'.$container.'.layout.B.tree.obj = new dhtmlXTreeObject("'.$container.'_pages","100%","100%",0);
'.$container.'.layout.B.tree.obj.setSkin(dhx_skin);
'.$container.'.layout.B.tree.obj.setImagePath(dhx_globalImgPath);
'.$container.'.layout.B.tree.obj.enableRadioButtons(true);
'.$container.'.layout.B.tree.obj.enableSingleRadioMode(true);
'.$container.'.layout.B.tree.obj.loadXML("'.CController::createUrl('xml_list_pages',array('id'=>$model->id)).'",function(){
	var obj = '.$container.'.layout.B.tree.obj;
	'.($model->id_parent ? 'obj.setCheck('.$model->id_parent.',1);':'').'
});	

'.$container.'.layout.B.tree.obj.attachEvent("onSelect", function(id){
	this.setCheck(id,this.isItemChecked(id)?0:1);	
});
'
;

echo Html::script($script); 
?>