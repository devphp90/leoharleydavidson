<?php
class RebateCouponForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $type=-1;
	public $coupon=0;
	public $name;
	public $coupon_code; 
	public $start_date;
	public $end_date;
	public $coupon_max_usage=0;
	public $coupon_max_usage_customer=0;
	public $all_product=0;
	public $applicable_on_sale=0;
	public $min_cart_value=0;
	public $max_weight=0;
	public $discount_type=0;
	public $discount=0;
	public $min_qty_required=0;
	public $max_qty_allowed=0;
	public $buy_x_qty=0;
	public $get_y_qty=0;
	public $active=0;
	public $rebate_coupon_description=array();		

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
		$columns = Html::getColumnsMaxLength(Tbl_RebateCoupon::tableName());	
	
		if ($this->type == -1) {
			$this->addError('type',Yii::t('global','ERROR_EMPTY'));	
		}
		
		// Percent/Fixed amount off first purchase
		if ($this->type == 5) {
			$this->coupon = 0;
			$this->coupon_code = "";
			$this->coupon_max_usage = 0;
			$this->coupon_max_usage_customer = 0;	
		}

		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		} else if (isset($columns['name']) && strlen($this->name) > $columns['name']) {
			$this->addError('name',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
		}
		
		if (empty($this->buy_x_qty) && $this->type == 2) {
			$this->addError('buy_x_qty',Yii::t('global','ERROR_EMPTY'));	
		} else if (!is_numeric($this->buy_x_qty) && $this->type == 2) {
			$this->addError('buy_x_qty',Yii::t('global','NOT_NUMERIC'));		
		}
		
		if (empty($this->get_y_qty) && $this->type == 2) {
			$this->addError('get_y_qty',Yii::t('global','ERROR_EMPTY'));	
		} else if (!is_numeric($this->get_y_qty) && $this->type == 2) {
			$this->addError('get_y_qty',Yii::t('global','NOT_NUMERIC'));	
		}
		
		
		if ($this->coupon) {
			if (empty($this->coupon_code)) {
				$this->addError('coupon_code',Yii::t('global','ERROR_EMPTY'));
			} else if (isset($columns['coupon_code']) && strlen($this->coupon_code) > $columns['coupon_code']) {
				$this->addError('coupon_code',Yii::t('global','ERROR_MAXLENGTH').$columns['coupon_code']);	
			} else if (Tbl_RebateCoupon::model()->count('id!=:id AND coupon_code=:coupon_code',array(':id'=>$this->id,':coupon_code'=>$this->coupon_code))) {
				$this->addError('coupon_code',Yii::t('global','ERROR_IN_USE',array('{name}'=>$this->coupon_code)));
			}
		}
		
		if (empty($this->start_date)) {
			$this->addError('start_date',Yii::t('global','ERROR_EMPTY'));	
		} /*else if (empty($this->end_date)) {
			$this->addError('end_date',Yii::t('global','ERROR_EMPTY'));	
		}*/ else if ((strtotime($this->start_date) >= strtotime($this->end_date)) && !empty($this->end_date)) {
			$this->addError('start_date',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->end_date)));
		}					
		
		if ($this->type != 3 && $this->type != 4) {
			if (empty($this->discount) or $this->discount=='0') {
				$this->addError('discount',Yii::t('global','ERROR_EMPTY'));	
			} else if (!is_numeric($this->discount)) {
				$this->addError('discount',Yii::t('global','NOT_NUMERIC'));
			} else if ($this->discount_type && ($this->discount <= 0 || $this->discount > 100)) {
				$this->addError('discount',Yii::t('global','ERROR_INVALID'));
			}
		}else if ($this->type == 4) {
			if (empty($this->max_weight) or $this->max_weight=='0') {
				$this->addError('max_weight',Yii::t('global','ERROR_EMPTY'));	
			}	
			
			// if rebate check if another free shipping rebate is conflicting
			if (!$this->coupon) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id!=:id AND coupon=0 AND type=4 AND ((start_date BETWEEN :start_date AND :end_date) OR (end_date BETWEEN :start_date AND :end_date) OR (:start_date BETWEEN start_date AND end_date) OR (:end_date BETWEEN start_date AND end_date) OR (start_date >= :start_date AND :end_date = "" OR start_date <= :start_date AND end_date = "0000-00-00 00:00:00"))'; 
				$criteria->params=array(':id'=>$this->id,':start_date'=>$this->start_date,':end_date'=>$this->end_date); 		
				
				if (Tbl_RebateCoupon::model()->find($criteria)) $this->addError('dates',Yii::t('views/rebatecoupon/edit_info_options','ERROR_REBATE_ALREADY_EXIST'));
			}
		}
		
		if (!empty($this->min_qty_required) && !is_numeric($this->min_qty_required)) {
			$this->addError('min_qty_required',Yii::t('global','NOT_NUMERIC'));
		}	
		
		if (!empty($this->coupon_max_usage) && !is_numeric($this->coupon_max_usage)) {
			$this->addError('coupon_max_usage',Yii::t('global','NOT_NUMERIC'));
		}
		
		if (!empty($this->coupon_max_usage_customer) && !is_numeric($this->coupon_max_usage_customer)) {
			$this->addError('coupon_max_usage_customer',Yii::t('global','NOT_NUMERIC'));
		}
		
		if ((!empty($this->coupon_max_usage_customer) && is_numeric($this->coupon_max_usage_customer)) && (!empty($this->coupon_max_usage) && is_numeric($this->coupon_max_usage))) {
			if ($this->coupon_max_usage_customer > $this->coupon_max_usage) {
				$this->addError('coupon_max_usage_customer',Yii::t('global','ERROR_NOT_GREATER_THEN') . $this->coupon_max_usage);
			}
		}			

		if (!empty($this->max_qty_allowed) && !is_numeric($this->max_qty_allowed)) {
			$this->addError('max_qty_allowed',Yii::t('global','NOT_NUMERIC'));
		}elseif(!empty($this->max_qty_allowed) and $this->type == 2 and $this->max_qty_allowed < ($this->buy_x_qty+$this->get_y_qty)){
			$this->addError('max_qty_allowed',Yii::t('global','ERROR_MUST_BE_GREATER_THEN') . ' ' . ($this->buy_x_qty+$this->get_y_qty));
		}elseif((!empty($this->max_qty_allowed) and !empty($this->buy_x_qty)) and $this->type == 2){
			$temp = $this->max_qty_allowed;
			$temp %= $this->buy_x_qty+$this->get_y_qty;
			if($temp>=$this->buy_x_qty and $temp<($this->buy_x_qty+$this->get_y_qty)){
				$this->addError('max_qty_allowed',Yii::t('global','ERROR_INVALID'));
			}
		}
		

		
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// description is required
			$description = $this->rebate_coupon_description[$value->code]['description'];
			if (empty($description)) {
				$this->addError('rebate_coupon_description['.$value->code.'][description]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['description']) && strlen($description) > $columns['description']) {
				$this->addError('rebate_coupon_description['.$value->code.'][description]',Yii::t('global','ERROR_MAXLENGTH').$columns['description']);	
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
		$current_id_user = Yii::app()->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_RebateCoupon::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_RebateCoupon;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;				
		}		
		
		$model->type = $this->type;
		$model->name = $this->name;
		$model->coupon = $this->coupon;
		$model->coupon_code = $this->coupon_code;
		$model->coupon_max_usage = $this->coupon_max_usage;
		$model->coupon_max_usage_customer = $this->coupon_max_usage_customer;		
		$model->start_date = $this->start_date;
		$model->end_date = $this->end_date;
		$model->all_product = $this->all_product;
		$model->applicable_on_sale = $this->applicable_on_sale;
		$model->min_cart_value = $this->min_cart_value;
		$model->max_weight = $this->max_weight;
		$model->discount_type = $this->discount_type;
		$model->discount = $this->discount;
		$model->min_qty_required = $this->min_qty_required ? $this->min_qty_required:0;
		$model->max_qty_allowed = $this->max_qty_allowed ? $this->max_qty_allowed:0;
		$model->buy_x_qty = $this->buy_x_qty;
		$model->get_y_qty = $this->get_y_qty;
		$model->id_user_modified = $current_id_user;	
		$model->active = $this->active;				
		
		// try saving
		if ($model->save()) {	
			foreach ($this->rebate_coupon_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_rebate_coupon=:id_rebate_coupon AND language_code=:language_code'; 
				$criteria->params=array(':id_rebate_coupon'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_RebateCouponDescription::model()->find($criteria)) {
					$model_description = new Tbl_RebateCouponDescription;				
					$model_description->id_rebate_coupon = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->description = $value['description'];
				if (!$model_description->save()) {
					if (!$this->id) $model->delete();
					throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
				}
			}
				
		}else{
			throw new CException(Yii::t('global','ERROR_SAVING'));
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
