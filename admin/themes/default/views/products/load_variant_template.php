<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong>
        <div id="<?php echo $container.'_search_variant_template'; ?>"></div>   
    </div>    
</div>
</div>    
<?php 
$script = '
'.$container.'.wins.layout.A.combo = new Object();
'.$container.'.wins.layout.A.combo.search_variant_template = new Object();
// convert div to combobox
'.$container.'.wins.layout.A.combo.search_variant_template.obj = new dhtmlXCombo("'.$container.'_search_variant_template", "'.$container.'_search_variant_template_value", 200);
// enable autocomplete mode
'.$container.'.wins.layout.A.combo.search_variant_template.obj.enableFilteringMode(true, "'.CController::createUrl('xml_list_search_variant_template').'", false);

'.$container.'.wins.layout.A.combo.search_variant_template.obj.attachEvent("onChange", function(){
	var value = $("input[name=\''.$container.'_search_variant_template_value\']").val();
	
	if (value) {
		'.$container.'.wins.layout.B.grid.load(value);
	}
});  

';

echo Html::script($script); 
?>