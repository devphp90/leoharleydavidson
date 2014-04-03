<?php
class ProductsPriceTiersForm extends CFormModel
{
	// database fields
	public $id=0;
	public $id_product=0;
	public $id_customer_type=0;
	public $qty=0;
	public $price;

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
		if (empty($this->id_customer_type)) {
			$this->id_customer_type = 0;
		}
		
		//if ($this->qty <= 1) {
			//$this->addError('qty',Yii::t('global','ERROR_MUST_BE_GREATER_THEN').'1');
		//}else{
			$model = Tbl_Product::model()->findByPk($this->id_product);
			if($model->max_qty && $model->max_qty < $this->qty){
				$this->addError('qty',Yii::t('global','ERROR_NOT_GREATER_THEN').$model->max_qty);
			}	
		//}
		
		if (!empty($this->price) && !is_numeric($this->price)) {
			$this->addError('price',Yii::t('global','NOT_NUMERIC'));
		}
		
		if (!empty($this->price) && !is_numeric($this->price)) {
			$this->addError('price',Yii::t('global','NOT_NUMERIC'));
		}
			
		if (Tbl_ProductPriceTier::model()->count(
													'id!=:id 
													AND 
													id_product=:id_product 
													AND 
													id_customer_type=:id_customer_type 
													AND 
													qty=:qty'
													
													,array(
													':id'=>$this->id,
													':id_product'=>$this->id_product,
													':id_customer_type'=>$this->id_customer_type,
													':qty'=>$this->qty
													)
												)
											) {
			$this->addError('exist',Yii::t('global','ERROR_IN_USE'));
		}
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$params[':id_product']=$this->id_product;
		$params[':qty']=$this->qty;
		$params[':id_customer_type']=$this->id_customer_type;
		$params[':price']=$this->price;
		$params[':id']=$this->id;
		
		$sql = 'SELECT
		COUNT(id) AS total
		FROM
		product_price_tier
		WHERE
		id_product = :id_product
		AND
		(
			(
				(
					qty = :qty
					AND
						(
							(                	
								0 = :id_customer_type
								AND
								(id_customer_type != :id_customer_type OR id_customer_type = 0)
								AND
								price > :price
							)
							OR
							(
								0 != :id_customer_type
								AND
								(id_customer_type = :id_customer_type OR id_customer_type = 0)
								AND
								price < :price
							)
						)
					)
				)
				OR
				(
					(0 = :id_customer_type OR id_customer_type = :id_customer_type)
					AND
					qty > :qty
					AND
					price >= :price
				)
				OR
				(
					(0 = :id_customer_type OR id_customer_type = :id_customer_type)
					AND
					qty < :qty
					AND
					price <= :price
				)
		) AND id != :id';
		
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true, $params);
		if($row['total']){
			$this->addError('exist',Yii::t('global','ERROR_INVALID'));
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
			$model = Tbl_ProductPriceTier::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ProductPriceTier;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->id_product = $this->id_product;
		$model->id_customer_type = $this->id_customer_type;
		$model->qty = $this->qty;
		$model->price = $this->price;		
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
