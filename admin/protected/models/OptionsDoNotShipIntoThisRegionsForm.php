<?php
class OptionsDoNotShipIntoThisRegionsForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $country_code=0;
	public $state_code=0;
	public $id_options=0;
	

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
		if (Tbl_OptionsDoNotShipRegion::model()->count('id!=:id AND id_options=:id_options AND country_code=:country_code AND state_code=:state_code',array(':id'=>$this->id,':id_options'=>$this->id_options,':country_code'=>$this->country_code,':state_code'=>$this->state_code))) {
			$this->addError('country_code',Yii::t('global','ERROR_EXIST'));
			$this->addError('state_code',Yii::t('global','ERROR_EXIST'));
		}elseif (Tbl_OptionsShipOnlyRegion::model()->count('id_options=:id_options AND country_code=:country_code AND state_code=:state_code',array(':id_options'=>$this->id_options,':country_code'=>$this->country_code,':state_code'=>$this->state_code))) {
			$this->addError('country_code',Yii::t('global','ERROR_EXIST_IN_SHIP_ONLY'));
			$this->addError('state_code',Yii::t('global','ERROR_EXIST_IN_SHIP_ONLY'));
		}elseif (Tbl_ConfigShipOnlyRegion::model()->count('country_code=:country_code AND state_code=:state_code',array(':country_code'=>$this->country_code,':state_code'=>$this->state_code))) {
			$this->addError('country_code',Yii::t('global','ERROR_EXIST_IN_SHIP_ONLY_GENERAL_CONFIG'));
			$this->addError('state_code',Yii::t('global','ERROR_EXIST_IN_SHIP_ONLY_GENERAL_CONFIG'));
		}elseif (Tbl_OptionsDoNotShipRegion::model()->count('country_code=:country_code AND state_code=:state_code',array(':country_code'=>$this->country_code,':state_code'=>'')) && !empty($this->state_code)) {
			$this->addError('country_code',Yii::t('global','ERROR_ALREADY_COUNTRY_ALL'));
			$this->addError('state_code',Yii::t('global','ERROR_ALREADY_COUNTRY_ALL'));
		}elseif ((Tbl_OptionsDoNotShipRegion::model()->count('country_code=:country_code AND state_code!=:state_code',array(':country_code'=>$this->country_code,':state_code'=>''))) && empty($this->state_code)) {
			$this->addError('country_code',Yii::t('global','ERROR_ALREADY_COUNTRY_NOT_ALL'));
			$this->addError('state_code',Yii::t('global','ERROR_ALREADY_COUNTRY_NOT_ALL'));
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
			$model = Tbl_OptionsDoNotShipRegion::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_OptionsDoNotShipRegion;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->country_code = $this->country_code;
		$model->state_code = $this->state_code;
		$model->id_options = $this->id_options;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));		
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
