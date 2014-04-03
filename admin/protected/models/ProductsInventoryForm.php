<?php
class ProductsInventoryForm extends CFormModel
{
	// database fields
	public $id=0; //product id
	public $track_inventory=0; 
	public $in_stock=0; 
	public $qty=0;	
	public $out_of_stock=0;
	public $out_of_stock_enabled=0;
	public $notify=0;
	public $notify_qty=0; 
	public $allow_backorders=0;
	public $product_has_variant;

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
		if ($this->qty < $this->out_of_stock) {
			$this->addError('out_of_stock',Yii::t('global','ERROR_NOT_GREATER_THEN').$this->qty);
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

		$model->track_inventory = $this->track_inventory;
		$model->in_stock = $this->in_stock;
		$model->qty = $this->qty;
		$model->out_of_stock = $this->out_of_stock;
		$model->out_of_stock_enabled = $this->out_of_stock_enabled;		
		$model->notify = $this->notify;
		$model->notify_qty = $this->notify_qty;		
		$model->allow_backorders = ($model->out_of_stock_enabled?0:$this->allow_backorders);		
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
