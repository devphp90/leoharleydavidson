<?php 
$app = Yii::app();

$connection=$app->db;   // assuming you have configured a "db" connection
?>
<?php echo CHtml::hiddenField('id_product_image_variant',$id_product_image_variant,array('id'=>$container.'_id_product_image_variant')); ?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">		
	<div class="row"><strong><?php echo Yii::t('global','LABEL_VARIANT');?></strong></div>
	<?php
    $sql = 'SELECT 
    product_variant_group.id,
    product_variant_group_description.name
    FROM 
    product_variant_group 
    INNER JOIN 
    product_variant_group_description
    ON
    (product_variant_group.id = product_variant_group_description.id_product_variant_group AND product_variant_group_description.language_code = :language_code) 
    WHERE 
    product_variant_group.id_product = :id_product
    ORDER BY 
    product_variant_group.sort_order ASC';	
    $command=$connection->createCommand($sql);			
    
    $sql = 'SELECT 
    product_variant_group_option.id,
    product_variant_group_option_description.name
    FROM 
    product_variant_group_option 
    INNER JOIN 
    product_variant_group_option_description
    ON
    (product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option AND product_variant_group_option_description.language_code = :language_code) 
    WHERE 
    product_variant_group_option.id_product_variant_group = :id_product_variant_group
    ORDER BY 
    product_variant_group_option.sort_order ASC';	
    $command2=$connection->createCommand($sql);									
    
    $i=0;
    foreach ($command->queryAll(true,array(':id_product'=>$id_product,':language_code'=>$app->language)) as $row) {		
        echo '<div class="row">
        <strong>'.$row['name'].'</strong>
        <div>';
		
		echo CHtml::hiddenField('variant['.$i.'][id_product_variant_group]',$row['id'],array('id'=>$container.'_id_product_variant_group'));
        
        echo CHtml::dropDownList('variant['.$i.'][id_product_variant_group_option]','',CHtml::listData($command2->queryAll(true,array(':id_product_variant_group'=>$row['id'],':language_code'=>$app->language)),'id','name'),array( 'id'=>$container.'_id_product_variant_group_option','prompt'=>($i?Yii::t('global','LABEL_PROMPT'):'--')));
        
        echo '</div></div>';
        
        ++$i;
    }
    ?>     
</div>
</div>
<?php
$script = '$(function(){	
	$("#'.$container.'_id_product_variant_group_option").change(function(){
		$.ajax({
			url: "'.CController::createUrl('edit_variants_get_image_variant',array('container'=>$containerObj,'id_product'=>$id)).'",
			type: "POST",
			data: $("#"+'.$container.'.wins_image.tabs.images.layout.obj.cont.obj.id+" *").serialize(),
			success: function(data){
				$("#'.$container.'_id_product_image_variant").val(data);
			}
		});			
	});
});
';

echo Html::script($script); 
?>