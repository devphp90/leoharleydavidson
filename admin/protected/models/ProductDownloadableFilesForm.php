<?php
class ProductDownloadableFilesForm extends CFormModel
{
	// database fields
	public $id=0; //id
	public $id_product=0;
	public $id_product_variant=0;
	public $name='';	
	public $filename='';
	public $no_days_expire=0;	
	public $no_downloads=0;		
	public $description=array();
	public $type='';
	
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
		if ($this->id) {
			$model = Tbl_ProductDownloadableFiles::model()->findByPk($this->id);	
		}
	
		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));
		} 
		
		if (empty($this->filename) || $model && empty($model->filename)) {
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
		
		$file_base_path = dirname(Yii::app()->getBasePath()).'/protected/downloadable_files/';
		
		// edit or new
		if ($this->id) {
			$model = Tbl_ProductDownloadableFiles::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ProductDownloadableFiles;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->id_product = $this->id_product;
		$model->id_product_variant = $this->id_product_variant;
		$model->name = $this->name;

		
		
		
		
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
			$model->type = strtoupper(pathinfo($this->filename,PATHINFO_EXTENSION));
		}
		
		
		
		 		

		$model->no_days_expire = $this->no_days_expire;
		$model->no_downloads = $this->no_downloads;
		$model->id_user_modified = $current_id_user;
		
		if (!empty($model->source) && pathinfo($model->source,PATHINFO_EXTENSION) == 'zip') {
			// check if scorm course
			$za = new ZipArchive();	
			
			// open zip 
			if ($za->open($file_base_path.$model->source) === TRUE) {	
		
				// look for manifest file
				if ($fp = $za->getStream('imsmanifest.xml')) {								
					// read content of manifest into variable
					$contents = '';
					while (!feof($fp)) $contents .= fread($fp, 2);
				
					fclose($fp);	
					
					// check if scorm 1.2
					// look for metadata tag
					preg_match('/<metadata>(.*)<\/metadata>/is',$contents,$matches);
					
					// found something
					if (sizeof($matches)) {
						$metadata = $matches[1];
						
						// grab schema and version
						preg_match('/<schema>(.*?)<\/schema>/is',$metadata,$matches);
						$schema = sizeof($matches) ? $matches[1]:'';
						
						preg_match('/<schemaversion>(.*?)<\/schemaversion>/is',$metadata,$matches);	
						$schema_version = sizeof($matches) ? $matches[1]:'';
						
						if ($schema == 'ADL SCORM' && $schema_version == '1.2') $model->type = $schema.' '.$schema_version;
					}
										
					$za->close();
				}			
			}
		}		
		
		if ($model->save()) {		
			// if scorm
			if ($model->type == 'ADL SCORM 1.2') {
				$course_path = Yii::app()->params['root_url'].'courses/scorm/'.$model->id.'/';
				// if path doesn't exist, create it
				if (!is_dir($course_path)) mkdir($course_path,0777);
				else Html::empty_dir($course_path);				
				
				// check if scorm course
				$za = new ZipArchive();				
				
				$za->open($file_base_path.$model->source);			
						
				// extract course to course path
				$za->extractTo($course_path);	
				
				$za->close();
			}
		
			foreach ($this->description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_downloadable_files=:id_product_downloadable_files AND language_code=:language_code'; 
				$criteria->params=array(':id_product_downloadable_files'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_ProductDownloadableFilesDescription::model()->find($criteria)) {
					$model_description = new Tbl_ProductDownloadableFilesDescription;				
					$model_description->id_product_downloadable_files = $model->id;
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
		$this->type = $model->type;
		
		return true;
	}
}
