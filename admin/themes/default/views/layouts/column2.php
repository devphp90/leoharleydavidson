<?php $this->beginContent('//layouts/main'); ?>
<div class="admin-body">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
    	<tr>
        	<td align="left" valign="top" style="width: 250px;">
            	<?php $current_controller_id = Yii::app()->getController()->getId();
				$current_controller_action = Yii::app()->getController()->getAction()->getId(); ?>
                <ul id="admin-left-menu">
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_DASHBOARD'),'utf-8'),array('/'),(($current_controller_id == 'site') ? array('class'=>'selected'):'')); ?></li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_SALES'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'sales') ? array('class'=>'selected'):'')); ?></li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_CATALOG'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'categories' || $current_controller_id == 'attributes' || $current_controller_id == 'products') ? array('class'=>'selected'):'')); ?>
                        <ul <?php echo ($current_controller_id == 'categories' || $current_controller_id == 'attributes' || $current_controller_id == 'products') ?  'style="display:block;"':''; ?>>
                            <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_CATEGORIES'),'utf-8'),array('categories/'),(($current_controller_id == 'categories') ? array('class'=>'selected'):'')); ?></li>
                            <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_ATTRIBUTES'),'utf-8'),array('attributes/'),(($current_controller_id == 'attributes') ? array('class'=>'selected'):'')); ?></li>
                            <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_PRODUCTS'),'utf-8'),array('products/'),(($current_controller_id == 'products' && $current_controller_action == 'index') ? array('class'=>'selected'):'')); ?></li>                            
                        </ul>
                    </li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_CUSTOMERS'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'customers') ? array('class'=>'selected'):'')); ?></li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_MARKETING'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'marketing') ? array('class'=>'selected'):'')); ?></li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_CMS'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'cms') ? array('class'=>'selected'):'')); ?></li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_REPORTS_STATS'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'reports_stats') ? array('class'=>'selected'):'')); ?></li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_SYSTEM_SETTINGS'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'settings' || $current_controller_id == 'users' || $current_controller_id == 'price_types') ? array('class'=>'selected'):'')); ?>
                        <ul <?php echo ($current_controller_id == 'settings' || $current_controller_id == 'users' || $current_controller_id == 'price_types') ?  'style="display:block;"':''; ?>>
                            <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_USERS'),'utf-8'),array('users/'),(($current_controller_id == 'users') ? array('class'=>'selected'):'')); ?></li>
                            <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_PRICE_TYPES'),'utf-8'),array('price-types/'),(($current_controller_id == 'price_types') ? array('class'=>'selected'):'')); ?></li>	
                        </ul>
                    </li>
                    <li><?php echo CHtml::link(mb_strtoupper(Yii::t('template','LABEL_HELP'),'utf-8'),'javascript:void(0);',(($current_controller_id == 'help') ? array('class'=>'selected'):'')); ?></li>
                </ul>
			</td>
            <td align="left" valign="top" style="width: auto;">
                <div class="admin-body_content">
                <pre id="global_messages" class="success" style="display: none;"></pre>
                <pre id="global_errors" class="error" style="display: none;"><?php echo Yii::t('template','ERROR_GLOBAL_ERRORS'); ?></pre>
				<?php echo $content; ?>
                </div>
			</td>
		</tr>
	</table>      
</div>
<?php $this->endContent(); ?>