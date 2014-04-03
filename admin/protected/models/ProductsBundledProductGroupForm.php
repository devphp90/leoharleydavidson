<?php
class ProductsBundledProductGroupForm extends CFormModel
{
	// database fields
	public $id=0; //id
	public $id_product=0; //product id
	public $input_type=0; //0 = dropdown, 1 = radio, 2 = checkbox, 3 = multi select	
	public $required=0;	
	public $sort_order=0;	
	public $product_bundled_product_group_description=array();

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
		$columns = Html::getColumnsMaxLength(Tbl_ProductBundledProductGroupDescription::tableName());
		
		foreach (Tbl_Language::model()->active()->findAll() as $value) {		
		
			// name is required
			$name = $this->product_bundled_product_group_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('product_bundled_product_group_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('product_bundled_product_group_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
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
		
		// edit or new
		if ($this->id) {
			$model = Tbl_ProductBundledProductGroup::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ProductBundledProductGroup;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product'; 
			$criteria->order='sort_order DESC';				
			$criteria->params=array(':id_product'=>$this->id_product); 				
			$model->sort_order = (Tbl_ProductBundledProductGroup::model()->find($criteria)->sort_order)+1;			
			
		}		
		
		$model->id_product = $this->id_product;
		$model->input_type = $this->input_type;	
		$model->required = $this->required;	
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {	
			foreach ($this->product_bundled_product_group_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_bundled_product_group=:id_product_bundled_product_group AND language_code=:language_code'; 
				$criteria->params=array(':id_product_bundled_product_group'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_ProductBundledProductGroupDescription::model()->find($criteria)) {
					$model_description = new Tbl_ProductBundledProductGroupDescription;				
					$model_description->id_product_bundled_product_group = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
				if (!$model_description->save()) {
					if (!$this->id) $model->delete();
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
