<?php
class BannerForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $active; 
	public $name;
	public $display_start_date;
	public $display_end_date;
	public $banner_description=array();

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
	{	/*		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_BannerDescription::tableName());		
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// file is required
			/*
			$image = $this->banner_description[$value->code]['filename'];
			if (empty($filename)) {
				$this->addError('banner_description['.$value->code.'][filename]',Yii::t('global','ERROR_EMPTY'));	
			} 
		}*/
		
		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		} 
		
		if (!empty($this->display_start_date) && !empty($this->display_end_date) && strtotime($this->display_start_date) >= strtotime($this->display_end_date)) {
			$this->addError('display_start_date',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->display_end_date)));
		}
		
		foreach ($this->banner_description as $code => $value) {
			switch ($value['url_type']) {
				// no url
				case 0:
					break;
				// url
				case 1:	
					if (empty($value['url'])) $this->addError('banner_description['.$code.'][url]',Yii::t('global','ERROR_EMPTY'));
					break;
				// cmspage
				case 2:
					if (empty($value['id_cmspage'])) $this->addError('banner_description['.$code.'][id_cmspage]',Yii::t('global','ERROR_EMPTY'));
					break;
				// registration contest
				case 3:
					if (empty($value['id_subscription_contest'])) $this->addError('banner_description['.$code.'][id_subscription_contest]',Yii::t('global','ERROR_EMPTY'));
					break;
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
		$image_base_path = $app->params['root_url'].'_images/banner/';
		
		// edit or new
		if ($this->id) {
			$model = Tbl_Banner::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_Banner;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}			
		$model->id_user_modified = $current_id_user;
		$model->active = $this->active;
		$model->name = $this->name;
		$model->display_start_date = $this->display_start_date;
		$model->display_end_date = $this->display_end_date;
		
		if ($model->save()) {		

			foreach ($this->banner_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_banner=:id_banner AND language_code=:language_code'; 
				$criteria->params=array(':id_banner'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_BannerDescription::model()->find($criteria)) {
					$model_description = new Tbl_BannerDescription;				
					$model_description->id_banner = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->url_type = $value['url_type'];
				
				switch ($model_description->url_type) {
					// no url
					case 0:
						$model_description->url = '';
						$model_description->target_blank = 0;		
						$model_description->id_cmspage = 0;
						$model_description->id_subscription_contest = 0;					
						break;
					// url
					case 1:	
						$model_description->url = $value['url'];
						$model_description->target_blank = $value['target_blank'];		
						$model_description->id_cmspage = 0;
						$model_description->id_subscription_contest = 0;
						break;
					// cmspage
					case 2:
						$model_description->url = '';
						$model_description->target_blank = 0;		
						$model_description->id_cmspage = $value['id_cmspage'];
						$model_description->id_subscription_contest = 0;
						break;
					// registration contest
					case 3:
						$model_description->url = '';
						$model_description->target_blank = 0;		
						$model_description->id_cmspage = 0;
						$model_description->id_subscription_contest = $value['id_subscription_contest'];
						break;
				}
				
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
