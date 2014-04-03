<?php
class FreeShippingRegionsForm extends CFormModel
{
	// database fields
	public $id=0; 
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
		if (Tbl_ConfigFreeShippingRegion::model()->count('id!=:id AND country_code=:country_code AND state_code=:state_code',array(':id'=>$this->id,':country_code'=>$this->country_code,':state_code'=>$this->state_code))) {
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
			$model = Tbl_ConfigFreeShippingRegion::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ConfigFreeShippingRegion;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->country_code = $this->country_code;
		$model->state_code = $this->state_code;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));		
		}
		
		$this->id = $model->id;
		
		if (Tbl_ConfigFreeShippingRegion::model()->count()) {
			Tbl_Config::model()->updateAll(array('value'=>1),"name='enable_free_shipping'");
		}
		
		return true;
	}
}
