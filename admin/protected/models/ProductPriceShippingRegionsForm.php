<?php
class ProductPriceShippingRegionsForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_product=0;
	public $price=0;
	public $country_code=0;
	public $state_code=0;
	

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
		if (Tbl_ProductPriceShippingRegion::model()->count('id!=:id AND id_product=:id_product AND country_code=:country_code AND state_code=:state_code',array(':id'=>$this->id,':id_product'=>$this->id_product,':country_code'=>$this->country_code,':state_code'=>$this->state_code))) {
			$this->addError('country_code',Yii::t('global','ERROR_EXIST'));
			$this->addError('state_code',Yii::t('global','ERROR_EXIST'));
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
			$model = Tbl_ProductPriceShippingRegion::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ProductPriceShippingRegion;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->country_code = $this->country_code;
		$model->state_code = $this->state_code;
		$model->id_product = $this->id_product;
		$model->price = $this->price;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));		
		}
		
		$this->id = $model->id;
		
		// Put 1 into product field: use_shipping_price
		Tbl_Product::model()->updateByPk($this->id_product,array('use_shipping_price'=>1));
		
		return true;
	}
}
