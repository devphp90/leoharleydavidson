<?php

class PubController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{					
		// display the form
		$this->render('index');	
	}
	
	/**
	 * This is the action to edit or create a product
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new PubForm;	
		
		$id = (int)$id;
		
		if ($id) { 
			if (!$p = Tbl_Pub::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		

			$model->id = $id;	
		}			
		
		
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}	
	
	
	
	/**
	 * This is the action to delete a pub
	 */
	public function actionDelete()
	{
		// current product
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_pub) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_pub=:id'; 
				$criteria->params=array(':id'=>$id_pub);										
				$image_base_path = Yii::app()->params['root_url'].'_images/pub/';
				
				foreach (Tbl_PubDescription::model()->findAll($criteria) as $row) {
					if ($row->filename && is_file($image_base_path.$row->filename)) @unlink($image_base_path.$row->filename);
					$row->delete();
				}
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_pub);					
				
				// delete all
				Tbl_Pub::model()->deleteAll($criteria);			
			}
		}		
	}
		
	public function actionEdit_options($id=0, $container)
	{
		$model = new PubForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($pub = Tbl_Pub::model()->findByPk($id)) {
				$model->id = $pub->id;
				$model->name = $pub->name;
				$model->width = $pub->width;
				$model->display_in_column = $pub->display_in_column;
				$model->display_in_page = $pub->display_in_page;
				$model->display_start_date = ($pub->display_start_date != '0000-00-00 00:00:00') ? $pub->display_start_date:'';
				$model->display_end_date = ($pub->display_end_date != '0000-00-00 00:00:00') ? $pub->display_end_date:'';
				$model->active = $pub->active;
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options',array('model'=>$model, 'container'=>$container));	
	}
	
	/**
	 *	This is a function we will use to get the cmspages 
	 */	
	public function get_cmspages(&$array=array(),$id_parent=0,&$ind=0,$lang='en')
	{
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Cmspage::model()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			
			
			$child = Tbl_Cmspage::model()->count($criteria2) ? 1:0;	
			
			$criteria3=new CDbCriteria; 
			$criteria3->condition='id_cmspage=:id_cmspage AND language_code=:language_code'; 
			$criteria3->params=array(':id_cmspage'=>$row->id,':language_code'=>$lang); 								
					
			//if(!$row->home_page && !$row->header_only){
				$array[$row->id] = CHtml::encode(($ind ? str_repeat('-',$ind).' ':'').Tbl_CmspageDescription::model()->find($criteria3)->name);
			//}
			
			if ($child) { $ind += 2; $this->get_cmspages($array,$row->id,$ind,$lang); }
		}	
		
		$ind=0;			
	}		
    
	public function actionXml_list_description($container, $id= 0)
	{
		$model = new PubForm;
		$model_name = get_class($model);
		$current_datetime = date('Y-m-d H:i:s');
		
		$app = Yii::app();
		
		
		$id = (int)$id;
		
		$model->id = $id;
		
		if ($id) {
			if ($pub = Tbl_Pub::model()->findByPk($id)) {							
				// grab description information 
				foreach ($pub->tbl_pub_description as $row) {
					$model->pub_description[$row->language_code]['url_type'] = $row->url_type;
					$model->pub_description[$row->language_code]['url'] = $row->url;
					$model->pub_description[$row->language_code]['target_blank'] = $row->target_blank;
					$model->pub_description[$row->language_code]['filename'] = $row->filename;
					$model->pub_description[$row->language_code]['id_cmspage'] = $row->id_cmspage;
					$model->pub_description[$row->language_code]['id_subscription_contest'] = $row->id_subscription_contest;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_PubDescription::tableName());		
		$app = Yii::app();		
		$include_path = $app->params['includes_js_path'];	
		
		$help_hint_path = '/marketing/pub/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';

		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			$ind=0;
			$cmspages=array();
			$this->get_cmspages($cmspages,0,$ind,$value->code);
						
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_">	
					<div style="padding:10px;">			
					
					<div class="row border_bottom">
						<strong>'.Yii::t('views/config/edit_banner','LABEL_URL_TYPE').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'url-type').'
						<div>'.CHtml::radioButton($model_name.'[pub_description]['.$value->code.'][url_type]',!$model->pub_description[$value->code]['url_type']?1:0,array('value'=>0,'id'=>$container.'_pub_description['.$value->code.'][url_type_0]','class'=>$container.'_select_url_type_'.$value->code)).'&nbsp;<label for="'.$container.'_pub_description['.$value->code.'][url_type_0]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_NO_URL').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[pub_description]['.$value->code.'][url_type]',$model->pub_description[$value->code]['url_type']==1?1:0,array('value'=>1,'id'=>$container.'_pub_description['.$value->code.'][url_type_1]','class'=>$container.'_select_url_type_'.$value->code)).'&nbsp;<label for="'.$container.'_pub_description['.$value->code.'][url_type_1]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_URL').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[pub_description]['.$value->code.'][url_type]',$model->pub_description[$value->code]['url_type'] == 2?1:0,array('value'=>2,'id'=>$container.'_pub_description['.$value->code.'][url_type_2]','class'=>$container.'_select_url_type_'.$value->code)).'&nbsp;<label for="'.$container.'_pub_description['.$value->code.'][url_type_2]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_CMSPAGE').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[pub_description]['.$value->code.'][url_type]',$model->pub_description[$value->code]['url_type'] == 3?1:0,array('value'=>3,'id'=>$container.'_pub_description['.$value->code.'][url_type_3]','class'=>$container.'_select_url_type_'.$value->code)).'&nbsp;<label for="'.$container.'_pub_description['.$value->code.'][url_type_3]" style="display:inline; text-align: left;">'.Yii::t('views/config/edit_banner','LABEL_REGISTRATION_CONTEST').'</label>
						</div>        
					</div>							
					
					<div class="row" id="'.$container.'_'.$value->code.'_url_type_1" '.($model->pub_description[$value->code]['url_type'] != 1 ? 'style="display:none;"':'').'>
						<div class="row border_bottom">
							<strong>'.Yii::t('views/config/edit_banner','LABEL_URL').' '.Yii::t('views/config/edit_banner','LABEL_URL_DESCRIPTION').'</strong>'.
							(isset($columns['url']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_pub_description['.$value->code.'][url]_maxlength">'.($columns['url']-strlen($model->pub_description[$value->code]['url'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'url').'
							<div>'.
							CHtml::textField($model_name.'[pub_description]['.$value->code.'][url]',$model->pub_description[$value->code]['url']?$model->pub_description[$value->code]['url']:'http://',array('style' => 'width: 98%;','maxlength'=>$columns['url'], 'id'=>$container.'_pub_description['.$value->code.'][url]')).'
							<br /><span id="'.$container.'_pub_description['.$value->code.'][url]_errorMsg" class="error"></span>
							</div>
						</div>								
						
						<div class="row border_bottom">
							<strong>'.Yii::t('views/config/edit_banner','LABEL_TARGET_BLANK').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'target-blank').'
							<div>'.
							CHtml::radioButton($model_name.'[pub_description]['.$value->code.'][target_blank]',$model->pub_description[$value->code]['target_blank']?1:0,array('value'=>1,'id'=>$container.'_pub_description['.$value->code.'][target_blank_1]')).'&nbsp;<label for="'.$container.'_pub_description['.$value->code.'][target_blank_1]" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[pub_description]['.$value->code.'][target_blank]',!$model->pub_description[$value->code]['target_blank']?1:0,array('value'=>0,'id'=>$container.'_pub_description['.$value->code.'][target_blank_0]')).'&nbsp;<label for="'.$container.'_pub_description['.$value->code.'][target_blank_0]" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>
							</div>        
						</div>
					</div>		
					
					
					<div class="row border_bottom" id="'.$container.'_'.$value->code.'_url_type_2" '.($model->pub_description[$value->code]['url_type'] != 2 ? 'style="display:none;"':'').'>
						<strong>'.Yii::t('views/config/edit_banner','LABEL_CMSPAGE').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'id-cmspage').'
						<div>
						<select name="'.$model_name.'[pub_description]['.$value->code.'][id_cmspage]" id="'.$container.'_pub_description['.$value->code.'][id_cmspage]">
						';
						
						if (sizeof($cmspages)) {
						
							foreach ($cmspages as $id_cmspage => $page) {
								echo '<option value="'.$id_cmspage.'" '.(($model->pub_description[$value->code]['id_cmspage']==$id_cmspage)?'selected="selected"':'').'>'.$page.'</option>';	
							}
						}
						
					echo '
						</select>
						<br /><span id="'.$container.'_pub_description['.$value->code.'][id_cmspage]_errorMsg" class="error"></span>
						</div>
					</div>	
					
					<div class="row border_bottom" id="'.$container.'_'.$value->code.'_url_type_3" '.($model->pub_description[$value->code]['url_type'] != 3 ? 'style="display:none;"':'').'>
						<strong>'.Yii::t('views/config/edit_banner','LABEL_REGISTRATION_CONTEST').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'id-subscription-contest').'
						<div>
						<select name="'.$model_name.'[pub_description]['.$value->code.'][id_subscription_contest]" id="'.$container.'_pub_description['.$value->code.'][id_subscription_contest]">
						';
					
					$criteria=new CDbCriteria; 
					$criteria->condition='((active=1 AND (end_date = "0000-00-00 00:00:00" OR end_date >= "'.$current_datetime.'")) OR id = "'.$model->pub_description[$value->code]['id_subscription_contest'].'")'; 									
					
					if (sizeof($subscription_contests = Tbl_SubscriptionContest::model()->findAll($criteria))) {
						
						foreach ($subscription_contests as $row) {		
							echo '<option value="'.$row->id.'" '.(($model->pub_description[$value->code]['id_subscription_contest']==$row->id)?'selected="selected"':'').'>'.$row->name.'</option>';	
						}						
					}
					
					echo '</select>
					<br /><span id="'.$container.'_pub_description['.$value->code.'][id_subscription_contest]_errorMsg" class="error"></span>
					</div>			
					</div>						
    
					<div class="row">
						<div style="margin-bottom:15px;" id="'.$container.'_pub_description_'.$value->code.'_image">
						'.($model->pub_description[$value->code]['filename'] ? '<img src="/_images/pub/'.$model->pub_description[$value->code]['filename'].'">':'').'</div>
						<div id="'.$container.'_pub_description_'.$value->code.'_image_upload_button"></div><br />
						<div id="'.$container.'_pub_description_'.$value->code.'_image_upload_queue" syle="margin-top:30px; margin-bottom:5px;"></div>
						'.CHtml::hiddenField($model_name.'[pub_description]['.$value->code.'][filename]',"",array('id'=>$container.'_pub_description_'.$value->code.'_filename')).
						'
						<span id="'.$container.'_pub_description['.$value->code.'][filename]_errorMsg" class="error"></span>                        
					</div>			
					<script type="text/javascript">
					$(function(){
	
						// bind upload file input
						$("#'.$container.'_pub_description_'.$value->code.'_image_upload_button").uploadify({
							"swf" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify.swf?'.time().'",
							"uploader" : "'.CController::createUrl('upload_image_banner').'",
							"checkExisting" : false,
							"formData" : {"PHPSESSID":"'.session_id().'"},
							"cancelImage" : "'.$include_path.'jquery/uploadify-v3.1.1/uploadify-cancel.png",
							"buttonText" : "'.Yii::t('views/pub/edit_options','BTN_SELECT_PUB').'",
							"width" : 170,
							"fileTypeDesc" : "Images (*.gif, *.jpeg, *.jpg, *.png)",
							"fileTypeExts" : "*.gif;*.jpeg;*.jpg;*.png",		
							// 5 mb limit per file		
							"fileSizeLimit" : 5242880,
							"auto" : true,
							"onUploadStart" : function(file) {
								//$("#'.$container.'_pub_description_'.$value->code.'_image_upload_button").uploadify("settings", "formData", {"size":$("input[id^=\''.$container.'_width_\']:checked").val()});
								
							},
							
					
							"queueID" : "'.$container.'_pub_description_'.$value->code.'_image_upload_queue",
							
							"onUploadSuccess" : function(file,data,response){
								if (data.indexOf("file:") == -1) {	
								alert(data);		
									$("#" + file.id).addClass("uploadifyError").find(".data").html(" - " + data);
								} else {
									var filename = data.replace("file:","");				
									
									$("#'.$container.'_pub_description_'.$value->code.'_filename").val(filename);
									
									$("#'.$container.'_pub_description_'.$value->code.'_image").html("").append("<img src=\"/_images/pub/"+filename+"\" />");
								}
							}
						});		
						
						$(".'.$container.'_select_url_type_'.$value->code.'").on("click",function(){
							$("div[id^=\''.$container.'_'.$value->code.'_url_type_\']:not(#'.$value->code.'_url_type_"+$(this).val()+")").hide();
							
							$("#'.$container.'_'.$value->code.'_url_type_"+$(this).val()).show();
						});
					});
					</script>		
					
					</div>
					</div>
				]]>
			</content>
		</tab>';
		
			++$i;
		}
		
		echo '</row></tabbar>';
	}
	
	
	/**
	 * This is the action to upload images
	 */
	public function actionUpload_image_banner()
	{			
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		//$size = $_POST['size'];
		$size = 800;
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['root_url'].'_images/pub/';
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
			$force_crop = 0;
			$allowed_ext = array(
				'gif',
				'jpeg',
				'jpg',
				'png',
			);									
			
			if (empty($ext) || !in_array($ext, $allowed_ext)) {
				echo Yii::t('global','ERROR_ALLOWED_IMAGES');				
				exit;
			} else {	
				// original file renamed
				$original = md5($targetFile.time()).'.'.$ext;	
				$filename = md5($original).'.jpg';				
			
				$image = new SimpleImage();
				if (!$image->load($tempFile)) {
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;
				}			
				
				$width = $image->getWidth();
				$height = $image->getHeight();
				
				if (!$width || !$height) {
					
					echo Yii::t('global','ERROR_LOAD_IMAGE_FAILED');
					exit;					
				}
				
				// if our image size is smaller than selected size
				//if ($width < $size) { 
				//	echo Yii::t('global', 'ERROR_MIN_IMAGE_RESOLUTION', array('{width}'=>$size));							
				//	exit;
				//}					
															
				// save image
				if (!$image->resizeToWidth($size)) {
					echo Yii::t('global', 'ERROR_RESIZE_BANNER_FAILED');						
					exit;		
				} else if (!$image->save($targetPath.$filename)) {
					echo Yii::t('global', 'ERROR_SAVE_BANNER_FAILED');						
					exit;									
				}

				echo 'file:'.$filename;
				exit;
				break;
			}
			
			echo Yii::t('global','ERROR_UPLOAD_IMAGE_FAILED');
		}				
	}			
	
	/**
	 * This is the action to get an XML list of tag for this product
	 */
	public function actionXml_list($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		
		$app = Yii::app();
		
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		// filters					
		
		$sql = "SELECT 
		COUNT(pub.id) AS total 
		FROM 
		pub 
		INNER JOIN 
		pub_description 
		ON 
		(pub.id = pub_description.id_pub AND pub_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		pub.id,
		pub.name,
		pub.width,
		pub.display_in_column,
		pub.display_in_page,
		pub.display_start_date,
		pub.display_end_date,
		pub.active,
		pub_description.filename
		FROM 
		pub 
		INNER JOIN 
		pub_description 
		ON 
		(pub.id = pub_description.id_pub AND pub_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY pub.sort_order ASC, pub.date_created DESC";	
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA[<img src="/_images/pub/'.$row['filename'].'" width="'.$row['width'].'" />]]></cell>
			<cell type="ro"><![CDATA['.($row['display_in_column'] ? Yii::t('views/pub/edit_options','LABEL_RIGHT'):Yii::t('views/pub/edit_options','LABEL_LEFT')).']]></cell>
			<cell type="ro"><![CDATA['.($row['display_in_page'] ? Yii::t('views/pub/edit_options','LABEL_EVERY_PAGE'):Yii::t('views/pub/edit_options','LABEL_HOME_PAGE')).']]></cell>
			<cell type="ro"><![CDATA['.(($row['display_start_date'] != '0000-00-00 00:00:00') ? $row['display_start_date']:'-').']]></cell>
			<cell type="ro"><![CDATA['.(($row['display_end_date'] != '0000-00-00 00:00:00') ? $row['display_end_date']:'-').']]></cell>	
			<cell type="ch"><![CDATA['.$row['active'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to toggle active
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($pub = Tbl_Pub::model()->findByPk($id)) {
			$pub->active = $active;
			if (!$pub->save()) {
				throw new CException('unable to save');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	/**
	 * This is the action to save suggestion order
	 */
	public function actionSave_sort_order()
	{
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_pub) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id_pub); 					
								
				if ($ps = Tbl_Pub::model()->find($criteria)) {
					$ps->sort_order = $i;
					$ps->save();
					
					++$i;
				}
			}
		}
	}
	
	
	
	public function actionSave()
	{
		$model = new PubForm;
		
		
		// collect user input data
		if(isset($_POST['PubForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['PubForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array('id'=>$model->id);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}

	
	/**
	 * Filters
	 */
		
    public function filters()
    {
        return array(
            'accessControl',
        );
    }
	
	
	/**
	 * Access Rules
	 */
	
	/*
    public function accessRules()
    {
        return array(	
        );
    }*/
	
}