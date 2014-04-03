<?php 
$model_name = get_class($model);
$include_path = Yii::app()->params['includes_js_path'];	
$help_hint_path = '/settings/import-export/import/';
?>
<div style="width:100%; height:100%; overflow:auto;" id="div_<?php echo $container;?>_import_files_options_container">	
<div style="padding:10px;">	
	<?php echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_file_id')).
	CHtml::activeHiddenField($model,'id_import_tpl',array('id'=>$container.'_id_import_tpl')); ?>
    
    <?php 
	if ($pid) {
		if ($pid == $model->pid) echo '<div class="row" style="margin-bottom:5px;"><div style="float:left"><span class="error">'.Yii::t('views/import/edit_import_files_options','LABEL_CURRENTLY_IMPORT').'</span></div><div style="float:left; margin-left:5px;"><img src="'.Html::imageUrl("ajax-loader.gif").'" height="16" width="16" /></div><div style="clear:both"></div></div>';
		else echo '<div class="row" style="margin-bottom:5px;"><span class="error">'.Yii::t('views/import/edit_import_files_options','LABEL_IMPORT_PROGRESS').'</span></div>'; 	
	} else if ($model->status != 0 && $model->status != 3) echo '<div class="row" style="margin-bottom:5px;"><span class="error">'.Yii::t('views/import/edit_import_files_options','LABEL_IMPORT_INTERRUPTED').'</span></div>';
	?>
    
    <div class="row">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td valign="top" width="50%">
                    <strong><?php echo Yii::t('views/import/edit_import_files_options','LABEL_COLUMNS_SEPARATED_WITH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'columns-separated-with'); ?>&nbsp;&nbsp;
                </td>
                <td valign="top">
                    <?php
                    echo CHtml::activeTextField($model,'columns_separated_with',array('size' => '1','maxlength'=>1, 'id'=>$container.'_columns_separated_with'));
                    ?>
                </td>
            </tr>
            <tr>
                <td valign="top" colspan="2">
                    <span id="<?php echo $container; ?>_columns_separated_with_errorMsg" class="error"></span>
                </td>
            </tr>    
            <tr>
                <td valign="top">
                    <strong><?php echo Yii::t('views/import/edit_import_files_options','LABEL_COLUMNS_ENCLOSED_WITH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'columns-enclosed-with'); ?>&nbsp;&nbsp;
                </td>                    
                <td valign="top">
                    <?php
                    echo CHtml::activeTextField($model,'columns_enclosed_with',array('size' => '1','maxlength'=>1, 'id'=>$container.'_columns_enclosed_with'));
                    ?>		
                </td>
            </tr>
            <tr>
                <td valign="top" colspan="2">
                    <span id="<?php echo $container; ?>_columns_enclosed_with_errorMsg" class="error"></span>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo Yii::t('views/import/edit_import_files_options','LABEL_COLUMNS_ESCAPED_WITH');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'columns-escaped-with'); ?>&nbsp;&nbsp;
                </td>
                <td valign="top">                                                
                    <?php
                    echo CHtml::activeTextField($model,'columns_escaped_with',array('size' => '1','maxlength'=>1, 'id'=>$container.'_columns_escaped_with'));
                    ?>
                </td>
            </tr>
            <tr>
                <td valign="top" colspan="2">
                    <span id="<?php echo $container; ?>_columns_escaped_with_errorMsg" class="error"></span>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong><?php echo Yii::t('views/import/edit_import_files_options','LABEL_SKIP_FIRST_ROW');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'skip-first-row'); ?>&nbsp;&nbsp;
                </td>
                <td valign="top">
                    <?php
                    echo CHtml::radioButton($model_name.'[skip_first_row]',$model->skip_first_row?1:0,array('value'=>1,'id'=>$container.'_skip_first_row_1')).'&nbsp;<label for="'.$container.'_skip_first_row_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[skip_first_row]',!$model->skip_first_row?1:0,array('value'=>0,'id'=>$container.'_skip_first_row_0')).'&nbsp;<label for="'.$container.'_skip_first_row_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>';
                    ?>
                </td>
            </tr> 
            <tr <?php echo($model->id_import_tpl_type!=2?'':'style="display:none"');?>>
                <td valign="top" style="padding-top:8px;">
                    <strong><?php echo Yii::t('views/import/edit_import_files_options','LABEL_SET_ACTIVE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'set-active'); ?>&nbsp;&nbsp;
                </td>
                <td valign="top" style="padding-top:8px;">
                    <?php
                    echo CHtml::radioButton($model_name.'[set_active]',$model->set_active?1:0,array('value'=>1,'id'=>$container.'_set_active_1')).'&nbsp;<label for="'.$container.'_set_active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[set_active]',!$model->set_active?1:0,array('value'=>0,'id'=>$container.'_set_active_0')).'&nbsp;<label for="'.$container.'_set_active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>';
                    ?>
                </td>
            </tr>     
        </table>            	
	</div> 
     
    <div class="row" id="<?php echo $container.'_preview'; ?>" style="width:100%;padding:0;height:200px; margin-top:10px;"></div>     
</div>
</div>
<?php
$script = '$(function(){
	$("#'.$container.'_columns_separated_with").on("change",function(){
		if ($(this).val().length > 0) {
			$(this).removeClass("error");
			$("#'.$container.'_columns_separated_with_errorMsg").html("");
			'.$container.'.wins.grid.load();
		} else {
			$(this).not(".error").addClass("error");
			$("#'.$container.'_columns_separated_with_errorMsg").html("'.Yii::t('global','ERROR_EMPTY').'");
		}
	});
	
	$("#'.$container.'_columns_enclosed_with").on("change",function(){
		if ($(this).val().length > 0) {
			$(this).removeClass("error");
			$("#'.$container.'_columns_enclosed_with_errorMsg").html("");
			'.$container.'.wins.grid.load();
		} else {
			$(this).not(".error").addClass("error");
			$("#'.$container.'_columns_enclosed_with_errorMsg").html("'.Yii::t('global','ERROR_EMPTY').'");
		}
	});
	
	$("#'.$container.'_columns_escaped_with").on("change",function(){
		if ($(this).val().length > 0) {
			$(this).removeClass("error");
			$("#'.$container.'_columns_escaped_with_errorMsg").html("");
			'.$container.'.wins.grid.load();		
		} else {
			$(this).not(".error").addClass("error");
			$("#'.$container.'_columns_escaped_with_errorMsg").html("'.Yii::t('global','ERROR_EMPTY').'");
		}
	});
	
	$("#'.$container.'_skip_first_row_1, #'.$container.'_skip_first_row_0").on("click",function(){
		if ($("#'.$container.'_columns_separated_with").val().length > 0 && $("#'.$container.'_columns_enclosed_with").val().length > 0 && $("#'.$container.'_columns_escaped_with").val().length > 0) {
			'.$container.'.wins.grid.load();
		} 		
	});
});

'.($pid ? $container.'.wins.toolbar.obj.disableItem("import");':'');

echo Html::script($script); 
?>