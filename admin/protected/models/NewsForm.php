<?php
class NewsForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $active; 
	public $date_news;
	public $news_description=array();

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	 
	
	public function rules()
	{
		return array(	
		);
	}	  

	public function validate()
	{	
		if (empty($this->date_news)) {
			$this->addError('date_news',Yii::t('global','ERROR_EMPTY'));
		}
	
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_NewsDescription::tableName());
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->news_description[$value->code]['name'];			
			if (empty($name)) {
				$this->addError('news_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('news_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
			}				
			
			// short desc
			$short_desc = $this->news_description[$value->code]['short_desc'];
			if (empty($short_desc)) {
				$this->addError('news_description['.$value->code.'][short_desc]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['description']) && strlen($short_desc) > $columns['short_desc']) {
				$this->addError('news_description['.$value->code.'][short_desc]',Yii::t('global','ERROR_MAXLENGTH').$columns['short_desc']);	
			}						
			
			// description is required
			$description = $this->news_description[$value->code]['description'];
			if (empty($description)) {
				$this->addError('news_description['.$value->code.'][description]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['description']) && strlen($description) > $columns['description']) {
				$this->addError('news_description['.$value->code.'][description]',Yii::t('global','ERROR_MAXLENGTH').$columns['description']);	
			}	
		}
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$current_datetime = date('Y-m-d H:i:s');
		$app = Yii::app();
		$current_id_user = (int)$app->user->getId();
		$image_base_path = $app->params['root_url'].'images/news/';
		
		// edit or new
		if ($this->id) {
			$model = Tbl_News::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_News;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}			
		$model->id_user_modified = $current_id_user;
		$model->active = $this->active;
		$model->date_news = $this->date_news;
		
		if ($model->save()) {		

			foreach ($this->news_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_news=:id_news AND language_code=:language_code'; 
				$criteria->params=array(':id_news'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_NewsDescription::model()->find($criteria)) {
					$model_description = new Tbl_NewsDescription;				
					$model_description->id_news = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
				$model_description->short_desc = $value['short_desc'];
				$model_description->description = $value['description'];
				
				if (!empty($value['filename'])) {
					$new_filename = md5($model->id.time().$value['filename']).'.jpg';
					
					if (is_file($image_base_path.$value['filename'])) {
						rename($image_base_path.$value['filename'],$image_base_path.$new_filename);
					}					
					
					if (!empty($model_description->filename) && is_file($image_base_path.$model_description->filename)) {
						@unlink($image_base_path.$model_description->filename);
					}

					$model_description->filename = $new_filename;					
				} 					
				
				if (!$model_description->save()) {
					if (!$this->id) { $model->delete(); }
					throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
				}								
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;

		return true;
	}
}
