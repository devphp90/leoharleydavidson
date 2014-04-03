<?php
class ProductsBundledProductGroupProductForm extends CFormModel
{
	// database fields
	public $id=0; //id
	public $price_type=0;
	public $price=0;
	public $qty=0;
	public $user_defined_qty=0;	
	public $selected=0;
	public $use_product_current_price=1;
	public $use_product_special_price=0;

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
		$qty = (int)$this->qty;
		$qty = $qty ? $qty:1;
		$this->qty = $qty;
		
		if (!$this->use_product_current_price && ($this->price < 0 || !is_numeric($this->price))) {
			$this->addError('price',Yii::t('global','ERROR_INVALID'));
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
		
		// edit or new
		if (!$model = Tbl_ProductBundledProductGroupProduct::model()->findByPk($this->id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}	
		
		$model->price_type = $this->price_type;
		$model->price = $this->price;	
		$model->qty = $this->qty;	
		$model->user_defined_qty = $this->user_defined_qty;	
		$model->selected = $this->selected;	
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {	
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		// if default unset others
		if ($this->selected) {
			$sql = 'UPDATE
			product_bundled_product_group_product
			SET
			selected=0
			WHERE
			id_product_bundled_product_group=:id_product_bundled_product_group
			AND
			id!=:id';
			$command=$connection->createCommand($sql);
			$command->execute(array(':id_product_bundled_product_group'=>$model->id_product_bundled_product_group,':id'=>$this->id));			
		}
		
		return true;
	}
}
