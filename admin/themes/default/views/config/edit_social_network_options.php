<?php 
$model_name = get_class($model);

$help_hint_path = '/settings/general/social-networks/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">	
<div style="padding:10px;">	
    <div class="row">
    	<table border="0" cellpadding="2" cellspacing="2" width="100%">
        	<tr>
            	<td valign="top" width="30%">
                    <strong><?php echo Yii::t('views/config/edit_social_network_options','LABEL_FACEBOOK');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'facebook'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_social_network_options','LABEL_FACEBOOK_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'settings[facebook]',array('style'=>'width:500px;', 'id'=>'settings[facebook]'));
                    ?>
                    <br /><span id="settings[facebook]_errorMsg" class="error"></span>
                    </div>                 
                </td>
			</tr>
        	<tr>
            	<td valign="top" width="30%">
                    <strong><?php echo Yii::t('views/config/edit_social_network_options','LABEL_TWITTER');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'twitter'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_social_network_options','LABEL_TWITTER_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                    <?php
                    echo CHtml::activeTextField($model,'settings[twitter]',array('style'=>'width:500px;', 'id'=>'settings[twitter]'));
                    ?>
                    <br /><span id="settings[twitter]_errorMsg" class="error"></span>
                    </div>                 
                </td>
			</tr>    
        	<tr>
            	<td valign="top" width="30%">
                    <strong><?php echo Yii::t('views/config/edit_social_network_options','LABEL_GOOGLE_ANALYTICS_TRACKING_ID');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'google-analytics-tracking-id'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_social_network_options','LABEL_GOOGLE_ANALYTICS_TRACKING_ID_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                     <?php
                    echo CHtml::activeTextField($model,'settings[google_analytics]',array('style'=>'width:250px;','id'=>'settings[google_analytics]'));
                    ?>
                    </div>                 
                </td>
			</tr>
        	<tr>
            	<td valign="top" width="30%">
                    <strong><?php echo Yii::t('views/config/edit_social_network_options','LABEL_GOOGLE_ANALYTICS_PROFILE_ID');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'google-analytics-profile-id'); ?><br />
       				<em><?php echo Yii::t('views/config/edit_social_network_options','LABEL_GOOGLE_ANALYTICS_PROFILE_ID_DESCRIPTION');?></em>
				</td>
                <td valign="top">
                    <div>
                     <?php
                    echo CHtml::activeTextField($model,'settings[google_analytics_profile_id]',array('style'=>'width:250px;','id'=>'settings[google_analytics_profile_id]'));
                    ?>
                    </div>                 
                </td>
			</tr>   
        	<tr>
            	<td valign="top" width="30%">
                    <strong><?php echo Yii::t('views/config/edit_social_network_options','LABEL_GOOGLE_ANALYTICS_EMAIL');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'google-analytics-email'); ?><br />
				</td>
                <td valign="top">
                    <div>
                     <?php
                    echo CHtml::activeTextField($model,'settings[google_analytics_email]',array('style'=>'width:250px;','id'=>'settings[google_analytics_email]'));
                    ?>
                    </div>                 
                </td>
			</tr>   
        	<tr>
            	<td valign="top" width="30%">
                    <strong><?php echo Yii::t('views/config/edit_social_network_options','LABEL_GOOGLE_ANALYTICS_PASSWORD');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'google-analytics-password'); ?><br />
				</td>
                <td valign="top">
                    <div>
                     <?php
                    echo CHtml::activePasswordField($model,'settings[google_analytics_password]',array('style'=>'width:250px;','id'=>'settings[google_analytics_password]'));
                    ?>
                    </div>                 
                </td>
			</tr>                                                                                                                              
		</table>                                                
    </div>     
</div>
</div>