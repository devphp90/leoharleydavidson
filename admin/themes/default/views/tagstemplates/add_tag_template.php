<?php
$columns = Html::getColumnsMaxLength(Tbl_TplTagGroup::tableName());

echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="name_maxlength">'.($columns['name']-strlen($model->name)).'</span>)</em>':''; ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('style' => 'width:99%;', 'maxlength'=>$columns['name'], 'id'=>'name'));
        ?>
        <br /><span id="name_errorMsg" class="error"></span>
        </div>   
    </div>    
</div>
</div>    
<?php 
$script = '';

//echo Html::script($script); 
?>