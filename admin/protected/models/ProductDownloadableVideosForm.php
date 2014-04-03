<?php
class ProductDownloadableVideosForm extends CFormModel
{
	// database fields
	public $id=0; //id
	public $id_product=0;
	public $id_product_variant=0;
	public $name='';	
	public $embed_code='';	
	public $stream=0;
	public $filename='';		
	public $no_days_expire=0;	
	public $no_downloads=0;		
	public $description=array();
	
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
		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));
		} 
		
		if (empty($this->embed_code) && !$this->stream) {
			$this->addError('embed_code',Yii::t('global','ERROR_EMPTY'));
		} else if (empty($this->filename) && $this->stream) {
			$this->addError('filename',Yii::t('global','ERROR_NO_FILE_SELECTED'));
		} 

		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
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
		$current_id_user = (int)Yii::app()->user->getId();
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$file_base_path = dirname(Yii::app()->getBasePath()).'/protected/streaming_videos/';
		
		// edit or new
		if ($this->id) {
			$model = Tbl_ProductDownloadableVideos::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ProductDownloadableVideos;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->id_product = $this->id_product;
		$model->id_product_variant = $this->id_product_variant;
		$model->name = $this->name;
		$model->embed_code = $this->embed_code;
		$model->stream = $this->stream;
		$model->no_days_expire = $this->no_days_expire;
		$model->no_downloads = $this->no_downloads;
		$model->id_user_modified = $current_id_user;

		if (!empty($this->filename) && is_file($file_base_path.$this->filename)) {			
			// Unlink Source if exist
			if (!empty($model->source) && is_file($file_base_path.$model->source)) {
				@unlink($file_base_path.$model->source);
			}
			// Create source name and rename current file with this source name and delete current file
			$new_filename = md5($this->filename.time()).'.'.pathinfo($this->filename,PATHINFO_EXTENSION);
			rename($file_base_path.$this->filename,$file_base_path.$new_filename);						
			@unlink($file_base_path.$this->filename);

			$model->filename = $this->filename;					
			$model->source = $new_filename;
		}

		
		if ($model->save()) {		
			foreach ($this->description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_downloadable_videos=:id_product_downloadable_videos AND language_code=:language_code'; 
				$criteria->params=array(':id_product_downloadable_videos'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_ProductDownloadableVideosDescription::model()->find($criteria)) {
					$model_description = new Tbl_ProductDownloadableVideosDescription;				
					$model_description->id_product_downloadable_videos = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
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
