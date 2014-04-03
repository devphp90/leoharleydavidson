<?php
class ConfigFixedShippingPriceForm extends CFormModel
{
	// database fields
	public $id;
	public $price;
	public $max_cart_price;
	
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
		if (empty($this->max_cart_price)) $this->addError('max_cart_price',Yii::t('global','ERROR_EMPTY'));
		else if (Tbl_ConfigFixedShippingPrice::model()->count('max_cart_price = :max_cart_price AND id != :id',array(':max_cart_price' => $this->max_cart_price,':id' => $this->id))) $this->addError('max_cart_price',Yii::t('global','ERROR_EXIST'));
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{

		if($this->id) $model = Tbl_ConfigFixedShippingPrice::model()->findByPk($this->id);		
		else $model = new Tbl_ConfigFixedShippingPrice;
			
		$model->price = $this->price;
		$model->max_cart_price = $this->max_cart_price;
		
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
