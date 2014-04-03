<?php
class ProductsVariantForm extends CFormModel
{
	// database fields
	public $id=0; //product variant group option id
	public $id_product=0;
	public $cost_price;
	public $sku;
	public $price;
	public $price_type=0;
	public $qty=0;
	public $notify_qty=0;
	public $weight=0;
	public $length=0;
	public $width=0;
	public $height=0;
	public $in_stock=0;
	public $active=0;
	public $product_variant_option=array();
	public $variant;

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
		
		if (Tbl_ProductVariant::model()->count('id!=:id AND sku=:sku AND sku<>""',array(':id'=>$this->id,':sku'=>$this->sku))) {
			$this->addError('sku',Yii::t('global','ERROR_IN_USE',array('{name}'=>$this->sku)));
		}
		if (!empty($this->cost_price) && !is_numeric($this->cost_price)) {
			$this->addError('cost_price',Yii::t('global','NOT_NUMERIC'));
		}		
		
		if (!empty($this->price) && !is_numeric($this->price)) {
			$this->addError('price',Yii::t('global','NOT_NUMERIC'));
		}
				
		$enable_shipping_gateway = 0;	
		if ($app->params['enable_shipping']) {
			$criteria=new CDbCriteria; 
			$criteria->condition='active=:active'; 
			$criteria->params=array(':active'=>'1'); 		
			
			if ($shipping_gateway = Tbl_ShippingGateway::model()->find($criteria)) {
				$enable_shipping_gateway = 1;
			}
		}
		if ($product = Tbl_Product::model()->findByPk($this->id_product)) {
			
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
		}
		
		if($enable_shipping_gateway and !$product->use_shipping_price and empty($this->weight)){
			$this->addError('weight',Yii::t('global','ERROR_EMPTY'));
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
			$model = Tbl_ProductVariant::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ProductVariant;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;	
		}		
		
		$model->id_product = $this->id_product;
		$model->sku = $this->sku;	
		$model->cost_price = $this->cost_price;
		$model->price = $this->price;
		$model->price_type = $this->price_type;
		$model->qty = $this->qty;
		$model->notify_qty = $this->notify_qty;
		$model->weight = $this->weight;
		$model->length = $this->length;
		$model->width = $this->width;
		$model->height = $this->height;
		$model->in_stock = $this->in_stock;
		$model->active = $this->active;
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
