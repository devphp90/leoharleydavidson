<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
$columns = Html::getColumnsMaxLength(Tbl_ImportTpl::tableName());	
echo CHtml::activeHiddenField($model,'id',array('id'=>$container.'_id'));
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	
$help_hint_path = '/settings/import-export/import/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
    <div class="row">
    <h3>Cette section vous permet d'importer un fichier de type CSV contenant une liste de produits qui aura été créé selon le gabarit de colonnes que vous aurez créé ci-bas.</h3>
    </div>
    
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('size'=>30, 'id'=>$container.'_name','maxlength'=>$columns['name']));
        ?>
		<br /><span id="<?php echo $container; ?>_name_errorMsg" class="error"></span>
        </div>       
    </div>
    <div class="row">
    <div style="float:left">
        <strong><?php echo Yii::t('views/import/edit_options','LABEL_TYPE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'type'); ?><br />
        <div>
        <?php
			$options=array(
				// add products
				0 => Yii::t('views/import/edit_options','LABEL_TYPE_0'),
				// add / update products
				1 => Yii::t('views/import/edit_options','LABEL_TYPE_1'),
				// update products
				2 => Yii::t('views/import/edit_options','LABEL_TYPE_2'),

/*				3 => Yii::t('views/import/edit_options','LABEL_TYPE_3'),
				4 => Yii::t('views/import/edit_options','LABEL_TYPE_4'),
				5 => Yii::t('views/import/edit_options','LABEL_TYPE_5'),
*/				
			);
			
			echo CHtml::activeDropDownList($model,'type',$options,array('id'=>$container.'_select_type'));        
        ?>
        <br /><span id="<?php echo $container; ?>_type_errorMsg" class="error"></span>
        </div>                
    </div> 
    
    <div id="<?php echo $container.'_subtract_qty_container'; ?>" style="<?php echo !$model->type ? 'display:none;':''; ?>float:left; padding-left:20px;">
        <strong><?php echo Yii::t('views/import/edit_options','LABEL_SUBTRACT_QTY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'subtract_qty'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[subtract_qty]',$model->subtract_qty?1:0,array('value'=>1,'id'=>$container.'_subtract_qty_1')).'&nbsp;<label for="'.$container.'_subtract_qty_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[subtract_qty]',!$model->subtract_qty?1:0,array('value'=>0,'id'=>$container.'_subtract_qty_0')).'&nbsp;<label for="'.$container.'_subtract_qty_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>                
    </div>  
    <div style="clear:both"></div>
    </div>  
</div>
</div>
<?php 
$script = '
$(function(){		
	$("#'.$container.'_select_type").on("change",function(){
		switch ($(this).val()) {
			default:
				$("#'.$container.'_subtract_qty_container").hide();
				break;
			case "1":
			case "2":
				$("#'.$container.'_subtract_qty_container").show();
				break;				
		}
	});
});
';

echo Html::script($script); 
?>