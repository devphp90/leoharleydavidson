<?php
class ImportForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $type=0; 
	public $subtract_qty=0;
	public $name;

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
		
		/*
		foreach ($this->columns as $id_column => $value) {
			switch ($this->type) {
				// products
				case 0:
					switch ($id_column) {
						// name
						case 1:
							if (!sizeof($value['language_code'])) $this->addError('columns[1]',Yii::t('global','ERROR_EMPTY'));	
							break;
						// short_desc
						case 2:
							if (!sizeof($value['language_code'])) $this->addError('columns[2]',Yii::t('global','ERROR_EMPTY'));	
							break;
						// description 
						case 3:
							if (!sizeof($value['language_code'])) $this->addError('columns[3]',Yii::t('global','ERROR_EMPTY'));	
							break;
						// meta_description 
						case 4:
							if (!sizeof($value['language_code'])) $this->addError('columns[4]',Yii::t('global','ERROR_EMPTY'));	
							break;
						// alias 
						case 5:
							if (!sizeof($value['language_code'])) $this->addError('columns[5]',Yii::t('global','ERROR_EMPTY'));	
							break;
						// sku
						case 6:
							if (!sizeof($value['language_code'])) $this->addError('columns[6]',Yii::t('global','ERROR_EMPTY'));	
							break;
						// brand
						case 7:						
							break;
						// model
						case 8:
							break;
						// cost_price
						case 9:
							break;
						// price
						case 10:
							break;
						// special_price
						case 11:
							break;
						// special_price_from_date
						case 12:
							break;
						// special_price_to_date
						case 13:
							break;
						// cover_image
						case 14:
							break;
						// images
						case 15:
							if (!sizeof($value['qty'])) $this->addError('columns[15]',Yii::t('global','ERROR_EMPTY'));	
							break;
						// qty
						case 16:
							break;
						// notify_qty		
						case 17:
							break;
						// out_of_stock
						case 18:
							break;
						// out_of_stock_enabled	
						case 19:
							break;
						// weight			
						case 20:
							break;
						// enable_local_pickup	
						case 21:
							break;
						// used
						case 22:
							break;
						// featured
						case 23:
							break;	
						// taxable						
						case 24:
							break;
					}
					break;
			}
		}*/
		
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
		
		// edit or new
		if ($this->id) {
			$model = Tbl_ImportTpl::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ImportTpl;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}			
		$model->id_user_modified = $current_id_user;
		$model->type = $this->type;		
		$model->subtract_qty = $this->subtract_qty;
		$model->name = $this->name;
		
		if ($model->save()) {		
			// if we changed the type we need to remove other columns from other type

		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;

		return true;
	}
}
