<?php
class ProductsShippingForm extends CFormModel
{
	// database fields
	public $id=0;
	public $weight=0;
	public $length=0;
	public $width=0;
	public $height=0;
	public $use_shipping_price=0;
	public $heavy_weight=0;
	public $extra_care=0;
	public $enable_local_pickup=-1;
		

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
		$app = Yii::app();
		
		$enable_shipping_gateway = 0;	
		if ($app->params['enable_shipping']) {
			$criteria=new CDbCriteria; 
			$criteria->condition='active=:active'; 
			$criteria->params=array(':active'=>'1'); 		
			
			if ($shipping_gateway = Tbl_ShippingGateway::model()->find($criteria)) {
				$enable_shipping_gateway = 1;
			}
		}
		if(!$enable_shipping_gateway and !$this->use_shipping_price and !$this->heavy_weight){
			$this->addError('use_shipping_price',Yii::t('views/products/edit_shipping_options','ERROR_SHIPPING_GATEWAY'));
		}
		if($enable_shipping_gateway and !$this->use_shipping_price && ($this->weight==0) and !$this->heavy_weight){
			$this->addError('weight',Yii::t('global','ERROR_EMPTY'));
		}		
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$current_id_user = (int)Yii::app()->user->getId();
		
		$model = Tbl_Product::model()->findByPk($this->id);	

		$model->weight = $this->weight;
		$model->length = $this->length;
		$model->width = $this->width;
		$model->height = $this->height;
		$model->heavy_weight = $this->heavy_weight;
		$model->use_shipping_price = ($this->heavy_weight?1:$this->use_shipping_price);
		
		$model->extra_care = ($this->use_shipping_price)?0:$this->extra_care;
		$model->enable_local_pickup = $this->enable_local_pickup;
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
