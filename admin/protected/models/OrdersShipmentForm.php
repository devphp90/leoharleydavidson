<?php
class OrdersShipmentForm extends CFormModel
{
	// database fields
	public $id=0;
	public $id_orders=0;
	public $shipment_no='';
	public $date_shipment='';
	public $tracking_no='';
	public $tracking_url='';
	public $comments='';
	public $products=array();
	
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
		if (empty($this->shipment_no)) {
			$this->addError('shipment_no',Yii::t('global','ERROR_EMPTY'));
		} else if (Tbl_OrdersShipment::model()->count('id!=:id AND shipment_no=:shipment_no',array(':id'=>$this->id,':shipment_no'=>$this->shipment_no))) {
			$this->addError('shipment_no',Yii::t('global','ERROR_IN_USE',array('{shipment_no}'=>$this->shipment_no)));
		} 
		if (empty($this->date_shipment)) {
			$this->addError('date_shipment',Yii::t('global','ERROR_EMPTY'));
		} 
		/*if (empty($this->tracking_no)) {
			$this->addError('tracking_no',Yii::t('global','ERROR_EMPTY'));
		} 
		if (empty($this->tracking_no)) {
			$this->addError('tracking_url',Yii::t('global','ERROR_EMPTY'));
		}*/
		
		if (!sizeof($this->products)) {
			$this->addError('select_products',Yii::t('global','ERROR_EMPTY'));
		} else {
			foreach ($this->products as $i => $row){
				if ($row['qty'] <= 0) $this->addError('products['.$i.'][qty]',Yii::t('global','ERROR_EMPTY'));	
			}
		}
	
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)$app->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_OrdersShipment::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_OrdersShipment;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;			
		}		
		
		$model->id_orders = $this->id_orders;
		$model->shipment_no = $this->shipment_no;
		$model->date_shipment = $this->date_shipment;
		$model->tracking_no = $this->tracking_no;
		$model->tracking_url = $this->tracking_url;		
		$model->comments = htmlspecialchars($this->comments);
		if ($model->save()) {		
			$criteria=new CDbCriteria; 
			$criteria->condition='id_orders_shipment=:id_orders_shipment'; 
			$criteria->params=array(':id_orders_shipment'=>$model->id); 					
			
			// delete all
			Tbl_OrdersShipmentItem::model()->deleteAll($criteria);	

			foreach ($this->products as $row) {
				$model_shipment_item = new Tbl_OrdersShipmentItem;
				$model_shipment_item->id_orders_shipment = $model->id;
				$model_shipment_item->id_orders_item_product = (!$row['is_option'] ? $row['id']:0);
				$model_shipment_item->id_orders_item_option = ($row['is_option'] ? $row['id']:0);
				$model_shipment_item->qty = $row['qty'];
				if (!$model_shipment_item->save()) {
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		return true;
	}		
}
