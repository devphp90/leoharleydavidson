<?php
class ImportFileForm extends CFormModel
{
	// database fields
	public $id=0;
	public $id_import_tpl=0;
	public $id_import_tpl_type=0;
	public $filename='';
	public $source='';
	public $pid=0;
	public $columns_separated_with=',';
	public $columns_enclosed_with='"';
	public $columns_escaped_with='\\';	
	public $skip_first_row=0;
	public $set_active=0;
	public $errors='';
	public $status=0;
	public $date_imported='';		

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
		
		switch ($this->id_import_tpl_type) {
			case 0:
			case 1:
			case 2:		
				switch ($this->id_import_columns) {
					// name
					case 1:
					// short_desc			
					case 2:
					// description
					case 3:		
					// meta_description	
					case 4:
					// meta_keywords
					case 5:
					// alias
					case 6:
						foreach ($this->languages as $language_code) {
							$model = new Tbl_ImportTplColumns;	
							$model->id_import_tpl = $this->id_import_tpl;
							$model->id_import_columns = $this->id_import_columns;
							$model->extra = $language_code;
							$model->sort_order = Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$this->id_import_tpl))+1;
							if (!$model->save())	throw new CException(Yii::t('global','ERROR_SAVING'));							
						}
						break;
					// additional images
					case 16:
						for ($i=0; $i<$this->additional_images_qty; ++$i) {
							$model = new Tbl_ImportTplColumns;	
							$model->id_import_tpl = $this->id_import_tpl;
							$model->id_import_columns = $this->id_import_columns;
							$model->extra = '';
							$model->sort_order = Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$this->id_import_tpl))+1;
							if (!$model->save())	throw new CException(Yii::t('global','ERROR_SAVING'));				
						}
						break;
					default:
						$model = new Tbl_ImportTplColumns;	
						$model->id_import_tpl = $this->id_import_tpl;
						$model->id_import_columns = $this->id_import_columns;
						$model->extra = '';
						$model->sort_order = Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$this->id_import_tpl))+1;
						if (!$model->save())	throw new CException(Yii::t('global','ERROR_SAVING'));	
						break;
				}
				break;
			case 3:
				break;
		}

		return true;
	}
}
?>