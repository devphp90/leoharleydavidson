<?php
class OptionsGroupForm extends CFormModel
{
	// database fields
	public $id=0; //product variant group id
	public $input_type=0; // 0 = dropdown,1 = radio, 3 = checkbox, 4 = multi-select,  5 = textfield, 6 = textarea, 7 = file, 8 = date, 9 = date & time, 10 = time
	public $from_to=0;
	public $user_defined_qty=0;
	public $max_qty=1;
	public $maxlength=0;
	public $id_product=0;
	public $tbl_options_group_description=array();

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
		// check description values for each languages		
		$columns = Html::getColumnsMaxLength(Tbl_OptionsGroupDescription::tableName());
		
		foreach (Tbl_Language::model()->active()->findAll() as $value) {		
		
			// name is required
			$name = $this->tbl_options_group_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('tbl_options_group_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('tbl_options_group_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
			}
			
			// description is required
			$description = $this->tbl_options_group_description[$value->code]['description'];
			if (!empty($description) && isset($columns['description']) && strlen($description) > $columns['description']) {
				$this->addError('tbl_options_group_description['.$value->code.'][description]',Yii::t('global','ERROR_MAXLENGTH').$columns['description']);	
			}
			
			/*
			if (empty($this->tpl_product_option_group_description[$value->code]['description'])) {
				$this->addError('tpl_product_option_group_description['.$value->code.'][description]',Yii::t('global','ERROR_EMPTY'));	
			}*/	
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
		
		// edit or new
		if ($this->id) {
			$model = Tbl_OptionsGroup::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_OptionsGroup;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;								
			
		}		
		
		$model->input_type = $this->input_type;	
		$model->from_to = $this->from_to;
		$model->maxlength = $this->maxlength;
		$model->user_defined_qty = $this->user_defined_qty;
		$model->max_qty = $this->max_qty;
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {	
			foreach ($this->tbl_options_group_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_options_group=:id_options_group AND language_code=:language_code'; 
				$criteria->params=array(':id_options_group'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_OptionsGroupDescription::model()->find($criteria)) {
					$model_description = new Tbl_OptionsGroupDescription;				
					$model_description->id_options_group = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
				$model_description->description = $value['description'];
				if (!$model_description->save()) {
					if (!$this->id) $model->delete();
					throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
				}
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		//If create new from product
		if($this->id_product and $this->id){
			//Verify if already exist
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product AND id_options_group=:id_options_group'; 
			$criteria->params=array(':id_options_group'=>$this->id,':id_product'=>$this->id_product); 		
			
			if (!Tbl_ProductOptionsGroup::model()->count($criteria)) {
				$model = new Tbl_ProductOptionsGroup;	
				$model->id_user_created = $current_id_user;			
				$model->date_created = $current_datetime;
				$model->id_product = $this->id_product;	
				$model->id_options_group = $this->id;

				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND id_options_group=:id_options_group'; 
				$criteria->params=array(':id_product'=>$this->id_product,':id_options_group'=>$this->id); 
				$criteria->order='sort_order ASC';
				
				$model->sort_order = Tbl_ProductOptionsGroup::model()->count($criteria)+1;
					
				if (!$model->save()) {		
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}
		}
		
		
		
		return true;
	}
}
