<?php 
$model_name = get_class($model);
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
$columns = Html::getColumnsMaxLength(Tbl_StoreLocations::tableName());	
echo CHtml::activeHiddenField($model,'id',array('id'=>'id'));
$app = Yii::app();

$include_path = $app->params['includes_js_path'];	

$help_hint_path = '/settings/general/store-locations/';
?>
<div style="width:100%; height:100%; overflow:auto;" class="div_<?php echo $container;?>">

<div style="padding:10px; position:relative;">	
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_ENABLED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'enabled'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[active]',($model->active or empty($model->id))?1:0,array('value'=>1,'id'=>'active_1')).'&nbsp;<label for="active_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[active]',(!$model->active and !empty($model->id))?1:0,array('value'=>0,'id'=>'active_0')).'&nbsp;<label for="active_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_HIDE_ADDRESS');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'hide-address'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[hide_address]',$model->hide_address ?1:0,array('value'=>1,'id'=>'hide_address_1')).'&nbsp;<label for="hide_address_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[hide_address]',(!$model->hide_address or empty($model->id))?1:0,array('value'=>0,'id'=>'hide_address_0')).'&nbsp;<label for="hide_address_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div> 
    <hr />      
     <div class="row">
        <strong><?php echo Yii::t('global','LABEL_NAME');?></strong><?php echo (isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="name_maxlength">'.($columns['name']-strlen($model->name)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'name'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'name',array('size'=>30, 'id'=>'name'));
        ?>
        <br /><span id="name_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_ADDRESS');?></strong><?php echo (isset($columns['address']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_address_maxlength">'.($columns['address']-strlen($model->address)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'address'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'address',array('style' => 'width: 250px;','maxlength'=>$columns['address'], 'id'=>'address'));
        ?>
        <br /><span id="address_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_CITY');?></strong><?php echo (isset($columns['city']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="city_maxlength">'.($columns['city']-strlen($model->city)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'city'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'city',array('style' => 'width: 250px;','maxlength'=>$columns['city'], 'id'=>'city'));
        ?>
        <br /><span id="city_errorMsg" class="error"></span>
        </div>                
	</div>    
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_COUNTRY');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'country'); ?><br />
        <div>
        <?php
        echo Html::generateCountryList($model_name.'[country_code]', $model->country_code, '', array('onchange'=>'js: get_province_list(this.value);','prompt'=>'--', 'id'=>'country_code'));
        ?>
        <br /><span id="country_code_errorMsg" class="error"></span>
        </div>                
    </div> 
    <div class="row">
        <strong><?php echo Yii::t('global','LABEL_STATE_PROVINCE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'state'); ?><br />
        <div id="list_states">
        <?php
        echo Html::generateStateList($model_name.'[state_code]', $model->country_code, $model->state_code, '', array( 'style'=>'min-width:80px;','prompt'=>'--', 'id'=>'state_code'));
        ?>
        <br /><span id="state_code_errorMsg" class="error"></span>
        </div>                
    </div>     
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_ZIP');?></strong><?php echo (isset($columns['zip']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="zip_maxlength">'.($columns['zip']-strlen($model->zip)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'zip'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'zip',array('style' => 'width: 250px;','maxlength'=>$columns['zip'], 'id'=>'zip'));
        ?>
        <br /><span id="zip_errorMsg" class="error"></span>
        </div>                
	</div>
    <hr />
    <div style="background-color:#EAEAEA; padding:5px;">   
    <div class="row">
        <div style="float:left"><strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_LATITUDE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'lat'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'lat',array('style' => 'width: 100px;','maxlength'=>$columns['lat'], 'id'=>'lat'));
        ?>
        <br /><span id="lat_errorMsg" class="error"></span>
        </div> 
        </div>
        <div style="float:left; margin-left:10px;">
             <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_LONGITUDE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'lng'); ?><br />
            <div>
            <?php
            echo CHtml::activeTextField($model,'lng',array('style' => 'width: 100px;','maxlength'=>$columns['lng'], 'id'=>'lng'));
            ?>
            <br /><span id="lng_errorMsg" class="error"></span>
            </div>                
        </div> 
        <div style="float:left; margin-left:20px; padding-top:17px;">
        <input type="button" value="Geocode" id="geocode" />
        </div>
        <div style="clear:both"></div>              
	</div>    
   	</div>
     <hr />
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_TELEPHONE');?></strong><?php echo (isset($columns['telephone']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="telephone_maxlength">'.($columns['telephone']-strlen($model->telephone)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'telephone'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'telephone',array('style' => 'width: 250px;','maxlength'=>$columns['telephone'], 'id'=>'telephone'));
        ?>
        <br /><span id="telephone_errorMsg" class="error"></span>
        </div>                
	</div>      
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_FAX');?></strong><?php echo (isset($columns['fax']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="fax_maxlength">'.($columns['fax']-strlen($model->fax)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'fax'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'fax',array('style' => 'width: 250px;','maxlength'=>$columns['fax'], 'id'=>'fax'));
        ?>
        <br /><span id="fax_errorMsg" class="error"></span>
        </div>                
	</div>
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_EMAIL');?></strong><?php echo (isset($columns['email']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="email_maxlength">'.($columns['email']-strlen($model->email)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'email'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'email',array('style' => 'width: 250px;','maxlength'=>$columns['email'], 'id'=>'email'));
        ?>
        <br /><span id="email_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_URL');?></strong><?php echo (isset($columns['url']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="url_maxlength">'.($columns['url']-strlen($model->url)).'</span>)</em>':''); ?>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'url'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'url',array('style' => 'width: 250px;','maxlength'=>$columns['url'], 'id'=>'url'));
        ?>
        <br /><span id="url_errorMsg" class="error"></span>
        </div>                
	</div>            
    <div class="row">
    	<strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_IMAGE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'logo'); ?><br />
    
    	<div id="image" style="margin-top:10px;">
		<?php if ($model->image_old && is_file($app->params['root_url'].'images/stores/'.$model->image_old)) { ?>
		<img src="<?php echo $app->params['root_relative_url'].'images/stores/'.$model->image_old; ?>" /><div style="margin-bottom:5px; margin-top:5px;"><a href="javascript:void(0);" onclick="javascript:delete_image('<?php echo $model->image_old; ?>')"><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_DELETE_IMAGE'); ?></a></div>	
		<?php } ?>
        </div>
        <div id="image_upload_button"></div>
        <div id="image_upload_queue" style="margin-top:15px;"></div>    
        <?php echo CHtml::activeHiddenField($model,'image',array('id'=>'filename')); ?>
    </div>    
    
    <h2><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPENING_HOURS');?></h2>
    
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPEN_MON');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'open-day'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[open_mon]',$model->open_mon?1:0,array('value'=>1,'id'=>'open_mon_1')).'&nbsp;<label for="open_mon_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[open_mon]',(!$model->open_mon or empty($model->id))?1:0,array('value'=>0,'id'=>'open_mon_0')).'&nbsp;<label for="open_mon_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>    
    <div class="row open_mon" <?php echo !$model->open_mon ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_START_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_mon_start_time',array('size'=>20, 'id'=>'open_mon_start_time'));
        ?>
        <br /><span id="open_mon_start_time_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row open_mon" <?php echo !$model->open_mon ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_END_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_mon_end_time',array('size'=>20, 'id'=>'open_mon_end_time'));
        ?>
        <br /><span id="open_mon_end_time_errorMsg" class="error"></span>
        </div>                
	</div> 
    
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPEN_TUE');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'open-day'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[open_tue]',$model->open_tue?1:0,array('value'=>1,'id'=>'open_tue_1')).'&nbsp;<label for="open_tue_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[open_tue]',(!$model->open_tue or empty($model->id))?1:0,array('value'=>0,'id'=>'open_tue_0')).'&nbsp;<label for="open_tue_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>    
    <div class="row open_tue" <?php echo !$model->open_tue ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_START_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_tue_start_time',array('size'=>20, 'id'=>'open_tue_start_time'));
        ?>
        <br /><span id="open_tue_start_time_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row open_tue" <?php echo !$model->open_tue ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_END_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_tue_end_time',array('size'=>20, 'id'=>'open_tue_end_time'));
        ?>
        <br /><span id="open_tue_end_time_errorMsg" class="error"></span>
        </div>                
	</div>     
    
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPEN_WED');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'open-day'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[open_wed]',$model->open_wed?1:0,array('value'=>1,'id'=>'open_wed_1')).'&nbsp;<label for="open_wed_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[open_wed]',(!$model->open_wed or empty($model->id))?1:0,array('value'=>0,'id'=>'open_wed_0')).'&nbsp;<label for="open_wed_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>    
    <div class="row open_wed" <?php echo !$model->open_wed ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_START_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_wed_start_time',array('size'=>20, 'id'=>'open_wed_start_time'));
        ?>
        <br /><span id="open_wed_start_time_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row open_wed" <?php echo !$model->open_wed ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_END_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_wed_end_time',array('size'=>20, 'id'=>'open_wed_end_time'));
        ?>
        <br /><span id="open_wed_end_time_errorMsg" class="error"></span>
        </div>                
	</div>     
    
    
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPEN_THU');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'open-day'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[open_thu]',$model->open_thu?1:0,array('value'=>1,'id'=>'open_thu_1')).'&nbsp;<label for="open_thu_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[open_thu]',(!$model->open_thu or empty($model->id))?1:0,array('value'=>0,'id'=>'open_thu_0')).'&nbsp;<label for="open_thu_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>    
    <div class="row open_thu" <?php echo !$model->open_thu ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_START_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_thu_start_time',array('size'=>20, 'id'=>'open_thu_start_time'));
        ?>
        <br /><span id="open_thu_start_time_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row open_thu" <?php echo !$model->open_thu ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_END_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_thu_end_time',array('size'=>20, 'id'=>'open_thu_end_time'));
        ?>
        <br /><span id="open_thu_end_time_errorMsg" class="error"></span>
        </div>                
	</div>       


    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPEN_FRI');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'open-day'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[open_fri]',$model->open_fri?1:0,array('value'=>1,'id'=>'open_fri_1')).'&nbsp;<label for="open_fri_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[open_fri]',(!$model->open_fri or empty($model->id))?1:0,array('value'=>0,'id'=>'open_fri_0')).'&nbsp;<label for="open_fri_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>    
    <div class="row open_fri" <?php echo !$model->open_fri ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_START_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_fri_start_time',array('size'=>20, 'id'=>'open_fri_start_time'));
        ?>
        <br /><span id="open_fri_start_time_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row open_fri" <?php echo !$model->open_fri ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_END_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_fri_end_time',array('size'=>20, 'id'=>'open_fri_end_time'));
        ?>
        <br /><span id="open_fri_end_time_errorMsg" class="error"></span>
        </div>                
	</div>       
            

    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPEN_SAT');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'open-day'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[open_sat]',$model->open_sat?1:0,array('value'=>1,'id'=>'open_sat_1')).'&nbsp;<label for="open_sat_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[open_sat]',(!$model->open_sat or empty($model->id))?1:0,array('value'=>0,'id'=>'open_sat_0')).'&nbsp;<label for="open_sat_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>    
    <div class="row open_sat" <?php echo !$model->open_sat ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_START_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_sat_start_time',array('size'=>20, 'id'=>'open_sat_start_time'));
        ?>
        <br /><span id="open_sat_start_time_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row open_sat" <?php echo !$model->open_sat ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_END_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_sat_end_time',array('size'=>20, 'id'=>'open_sat_end_time'));
        ?>
        <br /><span id="open_sat_end_time_errorMsg" class="error"></span>
        </div>                
	</div>              
    
    
    <div class="row">
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_OPEN_SUN');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'open-day'); ?><br />
        <div>
        <?php 
        echo CHtml::radioButton($model_name.'[open_sun]',$model->open_sun ?1:0,array('value'=>1,'id'=>'open_sun_1')).'&nbsp;<label for="open_sun_1" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[open_sun]',(!$model->open_sun or empty($model->id))?1:0,array('value'=>0,'id'=>'open_sun_0')).'&nbsp;<label for="open_sun_0" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>'; 
        ?>
        </div>       
    </div>    
    <div class="row open_sun" <?php echo !$model->open_sun ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_START_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'start-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_sun_start_time',array('size'=>20, 'id'=>'open_sun_start_time'));
        ?>
        <br /><span id="open_sun_start_time_errorMsg" class="error"></span>
        </div>                
	</div>  
    <div class="row open_sun" <?php echo !$model->open_sun ? 'style="display:none;"':''; ?>>
        <strong><?php echo Yii::t('views/config/edit_store_locations_options','LABEL_END_TIME');?></strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'end-time'); ?><br />
        <div>
        <?php
        echo CHtml::activeTextField($model,'open_sun_end_time',array('size'=>20, 'id'=>'open_sun_end_time'));
        ?>
        <br /><span id="open_sun_end_time_errorMsg" class="error"></span>
        </div>                
	</div>      
</div>
</div>
<?php 
$script = '
$(function(){
		
	$("input[id$=\'_start_time\'], input[id$=\'_end_time\']").timepicker({
			showOn: "button",
			buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Calendar"
	});
	
	$("input[id^=\'open_\']:radio").on("click",function(){
		var id = $(this).prop("id");
		id = id.replace("_1","").replace("_0","");
		
		switch ($(this).val()) {
			case "0":
				$("."+id).hide();
				break;
			case "1":
				$("."+id).show();			
				break;	
		}
	});	
	
	// bind upload file input
	$("#image_upload_button").uploadify({
		"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
		"uploader" : "'.CController::createUrl('upload_image_store_location',array('id'=>$model->id)).'",
		"checkExisting" : false,
		"formData" : {"PHPSESSID":"'.session_id().'"},
		"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
		//"buttonImage" : true,
		"multi" : false,
		"buttonText" : "'.Yii::t('global','LABEL_IMAGE_BTN_FILE').'",
		"width" : 170,
		"fileTypeDesc" : "Images (*.gif, *.jpeg, *.jpg, *.png)",
		"fileTypeExts" : "*.gif;*.jpeg;*.jpg;*.png",		
		// 5 mb limit per file		
		"fileSizeLimit" : 52428800,
		"requeueErrors" : false,
		"auto"  : true,

		"queueID" : "image_upload_queue",
		
		"onUploadSuccess" : function(file,data,response){
			if (data != "true") {				
				var filename = data.replace("file:","");				
				
				$("#filename").val(filename);
				
				$("#image").html("").append("<img src=\"/images/stores/"+filename+"\" /><div style=\"margin-bottom:5px; margin-top:5px;\"><a href=\"javascript:void(0);\" onclick=\"javascript:delete_image(\'"+filename+"\')\">'.Yii::t('views/config/edit_store_locations_options','LABEL_DELETE_IMAGE').'</a></div>");				
			} else {
				$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
			}
		},
		"onQueueComplete" : function(stats){
		}
	});	
	
	$("#geocode").on("click",function(){
		var address = "";		
		if ($("#address").val()) address += $("#address").val()+" ";
		if ($("#city").val()) address += $("#city").val()+" ";
		if ($("#state_code").val()) address += $("#state_code").val()+" ";
		if ($("#zip").val()) address += $("#zip").val()+" ";		
			
		geocode(address, "lat", "lng");
	});	
});

function delete_image(filename){
	if (confirm("'.Yii::t('views/config/edit_store_locations_options','LABEL_DELETE_IMAGE_CONFIRM').'")) {
		$.ajax({
			url: "'.CController::createUrl('delete_image_store_location').'",	
			data: { filename:filename, id:$("#id").val() },
			success: function(data) {
				$("#image").html("");
				$("#filename").val("");	
			}
		});	
	}
}

function get_province_list(current_id){
	// ajax request			
	$.ajax({
		url: "'.CController::createUrl('get_province_list_store_location').'",
		type: "POST",
		data: { "country":current_id },
		success: function(data) {					
			$("#list_states").html("").append(data);	
		}
	});		
}
';

echo Html::script($script); 
?>