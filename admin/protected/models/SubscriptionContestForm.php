<?php
class SubscriptionContestForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $contest=0;
	
	public $customer_only=0;
	public $include_form_address=0;
	public $include_form_telephone=0;
	public $id_rebate_coupon=0;
	
	public $name;
	public $start_date;
	public $end_date;
	public $active=0;
	public $subscription_contest_description=array();		

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
		$columns = Html::getColumnsMaxLength(Tbl_SubscriptionContest::tableName());	
	

		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		} else if (isset($columns['name']) && strlen($this->name) > $columns['name']) {
			$this->addError('name',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
		}
		
		if (empty($this->start_date)) {
			$this->addError('start_date',Yii::t('global','ERROR_EMPTY'));	
		} /*else if (empty($this->end_date)) {
			$this->addError('end_date',Yii::t('global','ERROR_EMPTY'));	
		}*/ else if ((strtotime($this->start_date) >= strtotime($this->end_date)) && !empty($this->end_date)) {
			$this->addError('start_date',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->end_date)));
		}					

		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->subscription_contest_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('subscription_contest_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('subscription_contest_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
			}	
			
			// description is required
			$description = $this->subscription_contest_description[$value->code]['description'];
			if (empty($description)) {
				$this->addError('subscription_contest_description['.$value->code.'][description]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['description']) && strlen($description) > $columns['description']) {
				$this->addError('subscription_contest_description['.$value->code.'][description]',Yii::t('global','ERROR_MAXLENGTH').$columns['description']);	
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
			$model = Tbl_SubscriptionContest::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_SubscriptionContest;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;				
		}		
		
		$model->name = $this->name;
		$model->contest = $this->contest;
		
		$model->customer_only = $this->customer_only;
		$model->include_form_address = $this->include_form_address;
		$model->include_form_telephone = $this->include_form_telephone;
		$model->id_rebate_coupon = $this->id_rebate_coupon;
		if($this->id_rebate_coupon){
			if ($rebate_coupon = Tbl_RebateCoupon::model()->findByPk($this->id_rebate_coupon)) {
				$model->coupon_code = $rebate_coupon->coupon_code;
			}
		}else{
			$model->coupon_code = "";	
		}
		
		$model->start_date = $this->start_date;
		$model->end_date = $this->end_date;
		$model->id_user_modified = $current_id_user;	
		$model->active = $this->active;				
		
		// try saving
		if ($model->save()) {	
			foreach ($this->subscription_contest_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_subscription_contest=:id_subscription_contest AND language_code=:language_code'; 
				$criteria->params=array(':id_subscription_contest'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_SubscriptionContestDescription::model()->find($criteria)) {
					$model_description = new Tbl_SubscriptionContestDescription;				
					$model_description->id_subscription_contest = $model->id;
					$model_description->language_code = $code;
				}
				$model_description->name = $value['name'];
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
