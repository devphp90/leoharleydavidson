<?php
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('views/site/login_form', 'LABEL_LOGIN');
?>
<style type="text/css">
html, body { background-color: #FFF; }
</style>
<div style="position: relative; width: 400px; margin:0 auto; margin-top: 50px; margin-bottom:10px; border:1px solid #DEDEDC; padding:20px;">
<div style="position:absolute; bottom:5px; left: 5px;"><?php echo file_get_contents(getcwd().'/version-sc') . (file_exists($_SERVER['DOCUMENT_ROOT'].'/exclude-file.txt')?'-m':'');?></div>
    <div id="data"><?php $this->renderPartial('login_form', array('model'=>$model)); ?></div>
</div><!-- form -->
<script type="text/javascript">
$(function(){
	if (typeof(templateLayout) !== 'undefined') window.location.href='<?php echo CController::createAbsoluteUrl('login'); ?>';
});
</script>