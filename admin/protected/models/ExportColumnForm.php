<?php
class ExportColumnForm extends CFormModel
{
	// database fields
	public $id_export_tpl=0;
	public $type_export_tpl=0;
	public $id_export_columns=0; 
	public $languages=array(); 
	public $additional_images_qty=0;

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
		if (!$this->id_export_tpl) $this->addError('id_export_tpl',Yii::t('global','ERROR_EMPTY'));			
		if (!$this->id_export_columns) $this->addError('id_export_columns',Yii::t('global','ERROR_EMPTY'));			
		
		switch ($this->type_export_tpl) {
			// products
			case 0:
				switch ($this->id_export_columns) {
					// categories
					case 27:
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
						if (!sizeof($this->languages)) $this->addError('select_languages',Yii::t('views/export/edit_column_options','ERROR_SELECT_LANGUAGES'));	
						break;
					// additional images
					case 16:
						if (!$this->additional_images_qty) $this->addError('additional_images_qty',Yii::t('global','ERROR_EMPTY'));	
						break;
				} 		
				break;
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
		
		switch ($this->type_export_tpl) {
			// products
			case 0:
				switch ($this->id_export_columns) {
					// categories
					case 27:
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
							$model = new Tbl_ExportTplColumns;	
							$model->id_export_tpl = $this->id_export_tpl;
							$model->id_export_columns = $this->id_export_columns;
							$model->extra = $language_code;
							$model->sort_order = Tbl_ExportTplColumns::model()->count('id_export_tpl=:id_export_tpl',array(':id_export_tpl'=>$this->id_export_tpl))+1;
							if (!$model->save())	throw new CException(Yii::t('global','ERROR_SAVING'));							
						}
						break;				
					// additional images
					case 16:
						for ($i=0; $i<$this->additional_images_qty; ++$i) {
							$model = new Tbl_ExportTplColumns;	
							$model->id_export_tpl = $this->id_export_tpl;
							$model->id_export_columns = $this->id_export_columns;
							$model->extra = '';
							$model->sort_order = Tbl_ExportTplColumns::model()->count('id_export_tpl=:id_export_tpl',array(':id_export_tpl'=>$this->id_export_tpl))+1;
							if (!$model->save())	throw new CException(Yii::t('global','ERROR_SAVING'));				
						}
						break;
					default:
						$model = new Tbl_ExportTplColumns;	
						$model->id_export_tpl = $this->id_export_tpl;
						$model->id_export_columns = $this->id_export_columns;
						$model->extra = '';
						$model->sort_order = Tbl_ExportTplColumns::model()->count('id_export_tpl=:id_export_tpl',array(':id_export_tpl'=>$this->id_export_tpl))+1;
						if (!$model->save())	throw new CException(Yii::t('global','ERROR_SAVING'));											
						break;
				}
				break;
		}

		return true;
	}
}
?>