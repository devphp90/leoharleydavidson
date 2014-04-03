<?php

class NewsController extends Controller
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
		$model=new NewsForm;	
		
		$id = (int)$id;
		
		if ($id) { 
			if (!$p = Tbl_News::model()->findByPk($id)) {
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
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_news=:id'; 
				$criteria->params=array(':id'=>$id);										
				$image_base_path = Yii::app()->params['root_url'].'images/news/';
				
				foreach (Tbl_NewsDescription::model()->findAll($criteria) as $row) {
					if ($row->filename && is_file($image_base_path.$row->filename)) @unlink($image_base_path.$row->filename);
					$row->delete();
				}
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id);					
				
				// delete all
				Tbl_News::model()->deleteAll($criteria);			
			}
		}		
	}
		
	public function actionEdit_options($id=0, $container)
	{
		$model = new NewsForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($pub = Tbl_News::model()->findByPk($id)) {
				$model->id = $pub->id;
				$model->date_news = ($pub->date_news != '0000-00-00') ? $pub->date_news:'';
				$model->active = $pub->active;
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		} else {
			$model->date_news = date('Y-m-d');	
		}
		
		$this->renderPartial('edit_options',array('model'=>$model, 'container'=>$container));	
	}
	
	public function actionXml_list_description($container, $id= 0)
	{
		$model = new NewsForm;
		$model_name = get_class($model);
		$current_datetime = date('Y-m-d H:i:s');
		
		$app = Yii::app();
		
		
		$id = (int)$id;
		
		$model->id = $id;
		
		if ($id) {
			if ($pub = Tbl_News::model()->findByPk($id)) {							
				// grab description information 
				foreach ($pub->tbl_news_description as $row) {
					$model->news_description[$row->language_code]['name'] = $row->name;
					$model->news_description[$row->language_code]['short_desc'] = $row->short_desc;					
					$model->news_description[$row->language_code]['description'] = $row->description;										
					$model->news_description[$row->language_code]['filename'] = $row->filename;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_NewsDescription::tableName());		
		$app = Yii::app();		
			
		$help_hint_path = '/marketing/news/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';

		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
						
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'">	
					<div style="padding:10px;">			
					<div class="row">
						<strong>'.Yii::t('global','LABEL_NAME').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_news_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->news_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'name').'
						<div>'.
						CHtml::activeTextField($model,'news_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_news_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_news_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					
					<div class="row">
						 <strong>'.Yii::t('views/news/index','LABEL_IMAGE').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'image').'<br />
						<div style="margin-bottom:15px;" id="'.$container.'_news_description_image_'.$value->code.'">
						'.($model->news_description[$value->code]['filename'] ? '<img src="/images/news/'.$model->news_description[$value->code]['filename'].'" width="'.$app->params['news_width'].'"><div style="margin-top:5px;"><a href="javascript:void(0);" onclick="javascript:'.$container.'.delete_image('.$container.'.id_pub,\''.$value->code.'\',\''.$model->news_description[$value->code]['filename'].'\');">'.Yii::t('global','LABEL_BTN_DELETE').'</a></div>':'').'</div>
						<div id="'.$container.'_news_description_image_upload_button_'.$value->code.'"></div><br />
						<div id="'.$container.'_news_description_image_upload_queue_'.$value->code.'" syle="margin-top:30px; margin-bottom:5px;"></div>
						'.CHtml::hiddenField($model_name.'[news_description]['.$value->code.'][filename]',"",array('id'=>$container.'_news_description_filename_'.$value->code)).
						'
						<span id="'.$container.'_news_description['.$value->code.'][filename]_errorMsg" class="error"></span>                        
					</div>	
					
					
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION_SHORT').'</strong>'.
						(isset($columns['short_desc']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_news_description['.$value->code.'][short_desc]_maxlength">'.($columns['short_desc']-strlen($model->news_description[$value->code]['short_desc'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'short-desc').'
						<div>'.
						CHtml::activeTextArea($model,'news_description['.$value->code.'][short_desc]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['short_desc'], 'id'=>$container.'_news_description['.$value->code.'][short_desc]')).'
						<br /><span id="'.$container.'_news_description['.$value->code.'][short_desc]_errorMsg" class="error"></span>
						</div>
					</div> 
					
					<div class="row">
						<strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'description').'
						<div>'.
						CHtml::activeTextArea($model,'news_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'class' => 'editor', 'rows' => 6, 'id'=>$container.'_news_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_news_description['.$value->code.'][description]_errorMsg" class="error"></span>
						</div>
					</div>	 				
    					
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
	public function actionUpload_image()
	{			
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$size = Yii::app()->params['news_width'];
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $app->params['root_url'].'images/news/';
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
				if ($width < $size) { 
					echo Yii::t('global', 'ERROR_MIN_IMAGE_RESOLUTION', array('{width}'=>$size));							
					exit;
				}					
															
				// save image
				if (!$image->resizeToWidth($size)) {
					echo Yii::t('global', 'ERROR_UPLOAD_IMAGE_FAILED');						
					exit;		
				} else if (!$image->save($targetPath.$filename)) {
					echo Yii::t('global', 'ERROR_UPLOAD_IMAGE_FAILED');						
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
		COUNT(news.id) AS total 
		FROM 
		news 
		INNER JOIN 
		news_description 
		ON 
		(news.id = news_description.id_news AND news_description.language_code = '".Yii::app()->language."') 
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
		news.id,
		news.date_news,
		news.active,
		news_description.name,
		news_description.short_desc,
		news_description.description,
		news_description.filename
		FROM 
		news 
		INNER JOIN 
		news_description 
		ON 
		(news.id = news_description.id_news AND news_description.language_code = '".Yii::app()->language."') 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY date_news DESC";	
		
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
			<cell type="ro"><![CDATA['.$row['date_news'].']]></cell>			
			<cell type="ro"><![CDATA['.($row['filename'] ? '<img src="/images/news/'.$row['filename'].'" width="'.$app->params['news_width'].'" />':'').']]></cell>
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
		
		if ($pub = Tbl_News::model()->findByPk($id)) {
			$pub->active = $active;
			if (!$pub->save()) {
				throw new CException('unable to save');	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
		
	public function actionSave()
	{
		$model = new NewsForm;
		
		
		// collect user input data
		if(isset($_POST['NewsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['NewsForm'] as $name=>$value)
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
	
	public function actionDelete_image($id=0,$language_code='en',$filename='')
	{
		if ($id) {
			$model = Tbl_NewsDescription::model()->find('id_news=:id_news AND language_code=:language_code',array(':id_news'=>$id,':language_code'=>$language_code));
			$model->filename = '';
			$model->save();
		}
		
		$path = Yii::app()->params['root_url'].'images/news/';
		
		if (is_file($path.$filename)) @unlink($path.$filename);
		
		echo 'true';
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