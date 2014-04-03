<?php 
$model_name = get_class($model);
?>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm',array(
	'id'=>'login-form',
	'action'=>'javascript:void(0);',
)); 

$linked_stores = Html::generateLinkedStoreList();
?>

<div class="row">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
    	<tr>
        	<td valign="top" colspan="2" align="center">
            <div style="margin-bottom:20px">
            <?php
            if(!empty(Yii::app()->params['reseller'])){
				echo '<img src="/includes/reseller/'.Yii::app()->params['reseller'].'/logo_admin.png" alt="'.Yii::app()->params['reseller'].'" />';
			}else{
				echo '<img src="/includes/reseller/SimpleCommerce/logo_admin.png" alt="Simple Commerce" style="border: none;" />';
			}
			?>
            
            </div>
            	<?php //echo CHtml::image(Html::themeImageUrl('sclogo.png'),'',array('style'=>'margin-bottom:15px;')); ?>
            </td>
        </tr>
        <?php
		if (isset($linked_stores) && is_array($linked_stores) && sizeof($linked_stores)) {
		?>
    	<tr>
        	<td valign="top" width="40%">
				<strong><?php echo Yii::t('views/site/login_form', 'LABEL_STORE'); ?></strong>
			</td>
            <td valign="top">               
				<?php echo $form->dropDownList($model,'id_linked_store',$linked_stores,array('prompt'=>$_SERVER['HTTP_HOST'],'id'=>'select_store')); ?>
        		<?php echo $form->error($model,'id_linked_store'); ?>
			</td>
		</tr>        
        <?php
		}
		?>
    	<tr>
        	<td valign="top" width="40%">
				<strong><?php echo Yii::t('views/site/login_form', 'LABEL_USERNAME'); ?></strong>
			</td>
            <td valign="top">               
				<?php echo $form->textField($model,'username',array('maxlength'=>90,  'style' => 'width: 150px;')); ?>
        		<?php echo $form->error($model,'username'); ?>
			</td>
		</tr>
		<tr>
        	<td valign="top">
				<strong><?php echo Yii::t('views/site/login_form', 'LABEL_PASSWORD'); ?></strong>
			</td>
            <td valign="top">                
				<?php echo $form->passwordField($model,'password',array('maxlength'=>90, 'style' => 'width: 150px;')); ?>
				<?php echo $form->error($model,'password'); ?>
			</td>
		</tr>
        <tr>
        	<td valign="top">
				<strong><?php echo Yii::t('views/site/login_form', 'LABEL_LANGUAGE'); ?></strong>
			</td>
            <td valign="top">               
				<?php echo Html::generateLanguageList($model_name.'[_lang]','',array('style'=>'width:150px;')); ?>
			</td>
		</tr>
        <tr>
        	<td valign="top">
				<strong><?php echo Yii::t('views/site/login_form', 'LABEL_REMEMBER_ME'); ?></strong>
			</td>
            <td valign="top">               
				<?php echo $form->checkbox($model,'rememberMe'); ?>
			</td>
		</tr>        
		<tr>
        	<td valign="top">&nbsp;</td>
            <td valign="top">    
				<?php echo CHtml::ajaxSubmitButton(Yii::t('views/site/login_form','LABEL_LOGIN'),CController::createUrl('login'),array('success'=>'function(data) { if (data) { $("#data").html(\'\').append(data); } else { window.location.replace(\''.CController::createUrl('index').'\'); }}'))?>
			</td>
		</tr>
        
         
            <?php
            if(!empty(Yii::app()->params['reseller']) && !Yii::app()->params['white_label']){
				echo '<tr style="margin-top:20px;"><td colspan="2"><div style="float:right; margin-left: 10px;margin-top:20px;"><a href="http://www.simplecommerce.com" target="_blank"><img src="/includes/reseller/SimpleCommerce/logo_bottom_powered_admin.png" width="62" height="20" alt="Simple Commerce" style="border: none;" /></a></div><div style="float:right; font-size: 9px; color: #999;margin-top:20px;">'.Yii::t('global', 'LABEL_POWERED_BY').'</div></td></tr>';
			}
			?>
            
            
        
        
	</table>                                    
</div>
<?php $this->endWidget(); ?>
</div>
<?php
if (isset($linked_stores) && is_array($linked_stores) && sizeof($linked_stores)) {
echo Html::script('$(function(){
$("#select_store").on("change",function(){
	var url = $(this).val();
	
	if (url.length) {
		window.location.replace(url);
	}
});
});'); 
}
?>