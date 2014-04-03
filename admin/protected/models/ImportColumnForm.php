<?php
class ImportColumnForm extends CFormModel
{
	// database fields
	public $id_import_tpl=0;
	public $type_import_tpl=0;
	public $id_import_columns=0; 
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
		if (!$this->id_import_tpl) $this->addError('id_import_tpl',Yii::t('global','ERROR_EMPTY'));			
		if (!$this->id_import_columns) $this->addError('id_import_columns',Yii::t('global','ERROR_EMPTY'));			
		
		switch ($this->type_import_tpl) {
			// add / add/update / update products
			case 0:
			case 1:
			case 2:				
				switch ($this->id_import_columns) {
					// sub category
					case 28:
					//verify if category added before sub category
					if(!Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl and id_import_columns = 27',array(':id_import_tpl'=>$this->id_import_tpl))){$this->addError('select_languages',Yii::t('views/import/edit_column_options','ERROR_SELECT_CATEGORY'));break;};
					// category
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
						if (!sizeof($this->languages)) $this->addError('select_languages',Yii::t('views/import/edit_column_options','ERROR_SELECT_LANGUAGES'));	
						break;
					// additional images
					case 16:
						if (!$this->additional_images_qty) $this->addError('additional_images_qty',Yii::t('global','ERROR_EMPTY'));	
						break;
				} 		
				break;
			// add category
			case 3:
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
		
		switch ($this->type_import_tpl) {
			// add / add/update / update products
			case 0:
			case 1:
			case 2:						
				switch ($this->id_import_columns) {
					// category
					case 27:
					// sub category
					case 28:
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
						
						switch ($this->id_import_columns) {
							// special price
							case 12:
							// special price from date
							case 13:
							// special price to date
							case 14:
								// special price 
								if (!Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl AND id_import_columns=:id_import_columns',array(':id_import_tpl'=>$this->id_import_tpl,':id_import_columns'=>12))) {
									$model_add = new Tbl_ImportTplColumns;	
									$model_add->id_import_tpl = $this->id_import_tpl;
									$model_add->id_import_columns = 12;
									$model_add->sort_order = Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$this->id_import_tpl))+1;
									if (!$model_add->save())	throw new CException(Yii::t('global','ERROR_SAVING'));											
								}								

								// special price from date	
								if (!Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl AND id_import_columns=:id_import_columns',array(':id_import_tpl'=>$this->id_import_tpl,':id_import_columns'=>13))) {
									$model_add = new Tbl_ImportTplColumns;	
									$model_add->id_import_tpl = $this->id_import_tpl;
									$model_add->id_import_columns = 13;
									$model_add->sort_order = Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$this->id_import_tpl))+1;
									if (!$model_add->save())	throw new CException(Yii::t('global','ERROR_SAVING'));											
								}
								
								// special price to date	
								if (!Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl AND id_import_columns=:id_import_columns',array(':id_import_tpl'=>$this->id_import_tpl,':id_import_columns'=>14))) {
									$model_add = new Tbl_ImportTplColumns;	
									$model_add->id_import_tpl = $this->id_import_tpl;
									$model_add->id_import_columns = 14;
									$model_add->sort_order = Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$this->id_import_tpl))+1;
									if (!$model_add->save())	throw new CException(Yii::t('global','ERROR_SAVING'));											
								}								
								break;
						}							
						break;
				}
				break;
			// add category
			case 3:
				break;
		}

		return true;
	}
}
?>