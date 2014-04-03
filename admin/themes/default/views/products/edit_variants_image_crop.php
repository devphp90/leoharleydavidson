<?php 		
$containerForm = $container.'_form';

// register css/script files and scripts
$app=Yii::app();

$image_base_path = $app->params['product_images_base_path'];
$image_base_url = $app->params['product_images_base_url'];
$original = $product_image->original;

$image = new SimpleImage();
if (!$image->load($image_base_path.'original/'.$original)) {
	// error 	
	throw new CException(Yii::t('products','ERROR_LOAD_IMAGE_FAILED'));
}

if ($rotate) {
	if ($rotate == -360 || $rotate == 360) { $rotate = 0; }
	else if (!$image->rotate($rotate)) {
		throw new CException('Rotate failed');
	}
}

$original_width = $image->getWidth();
$original_height = $image->getHeight();

switch ($app->params['images_orientation']) {
	case 'portrait':	
		$box_width = $app->params['portrait_cover_width'];
		$box_height = $app->params['portrait_cover_height'];
		
		$preview_width = $app->params['portrait_suggest_width'];
		$preview_height = $app->params['portrait_suggest_height'];

		$zoom_width = $app->params['portrait_zoom_width'];
		$zoom_height = $app->params['portrait_zoom_height'];
		break;	
	case 'landscape':
		$box_width = $app->params['landscape_cover_width'];
		$box_height = $app->params['landscape_cover_height'];
		
		$preview_width = $app->params['landscape_suggest_width'];
		$preview_height = $app->params['landscape_suggest_height'];

		$zoom_width = $app->params['landscape_zoom_width'];
		$zoom_height = $app->params['landscape_zoom_height'];
		break;
}

$ratio = $zoom_width/$zoom_height;	

$script = '
$(function(){
	var jcrop_api = $.Jcrop("#'.$container.'_jcrop_target",{
		onChange: showPreview,
		onSelect: showPreview,
		aspectRatio: '.$ratio.',
		boxWidth: '.$box_width.',
		boxHeight: '.$box_height.'
	});		
});

function showPreview(coords){
	if (parseInt(coords.w) > 0)
	{
		var rx = '.$preview_width.' / coords.w;
		var ry = '.$preview_height.' / coords.h;
 
		$("#'.$container.'_jcrop_preview").css({
			width: Math.round(rx * '.$original_width.') + "px",
			height: Math.round(ry * '.$original_height.') + "px",
			marginLeft: "-" + Math.round(rx * coords.x) + "px",
			marginTop: "-" + Math.round(ry * coords.y) + "px"
		});		
	}

	$("#'.$container.'_x").val(coords.x);
	$("#'.$container.'_y").val(coords.y);
	$("#'.$container.'_w").val(coords.w);
	$("#'.$container.'_h").val(coords.h);		
};';

echo Html::script($script);
?>
<div id="<?php echo $containerForm; ?>" style="width:100%; height:100%; padding:0; margin:0; overflow:auto;">
<div style="padding:10px;">	
    <div class="row">
        <div style="float:left;">
            <strong><?php echo Yii::t('global','LABEL_IMAGE_ORIGINAL');?></strong>
            <div style="border: 1px solid #303030; margin-bottom:5px;">
            <?php echo CHtml::image(CController::createUrl('edit_variants_image_crop_load_image',array('id_product_image_variant'=>$product_image->id_product_image_variant,'id_product_image'=>$product_image->id,'rotate'=>$rotate,'t'=>time())),Yii::t('products','LABEL_ORIGINAL_IMAGE'),array('id'=>$container.'_jcrop_target','border'=>0, 'width'=>$original_width, 'height'=>$original_height)); ?>
            </div>
            <?php echo Yii::t('global','LABEL_IMAGE_SELECT_REGION_CROP');?>
        </div>
        <div style="float:left; margin-left:5px;">
            <strong><?php echo Yii::t('global','LABEL_IMAGE_PREVIEW');?></strong>
            <div style="width:<?php echo $preview_width; ?>px;height:<?php echo $preview_height; ?>px;overflow:hidden; border: 1px solid #303030;">
            <?php echo CHtml::image(CController::createUrl('edit_variants_image_crop_load_image',array('id_product_image_variant'=>$product_image->id_product_image_variant,'id_product_image'=>$product_image->id,'rotate'=>$rotate,'t'=>time())),Yii::t('products','LABEL_PREVIEW_IMAGE'),array('id'=>$container.'_jcrop_preview','border'=>0, 'width'=>$preview_width, 'height'=>$preview_height)); ?>
            </div>        
        </div>
        <div style="clear:both;"></div>
    </div>    
</div>
<?php echo CHtml::hiddenField('id',$product_image->id,array('id'=>$container.'_crop_image_id')); ?>
<?php echo CHtml::hiddenField('x','',array('id'=>$container.'_x')); ?>
<?php echo CHtml::hiddenField('y','',array('id'=>$container.'_y')); ?>
<?php echo CHtml::hiddenField('w','',array('id'=>$container.'_w')); ?>
<?php echo CHtml::hiddenField('h','',array('id'=>$container.'_h')); ?>
<?php echo CHtml::hiddenField('rotate',$rotate,array('id'=>$container.'_rotate')); ?>
</div>