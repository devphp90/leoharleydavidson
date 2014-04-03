<?php
class TaxRulesForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $name; 
	public $country_code;
	public $state_code;
	public $zip_from="";
	public $zip_to="";
	public $active=0;	

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
		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));
		} else if (Tbl_TaxRule::model()->count('id!=:id AND name=:name',array(':id'=>$this->id,':name'=>$this->name))) {
			$this->addError('name',Yii::t('global','ERROR_IN_USE'));
		} else {			
			$criteria=new CDbCriteria; 
			$criteria->condition='id!=:id AND country_code=:country_code AND state_code=:state_code AND zip_from=:zip_from AND zip_to=:zip_to'; 
			$criteria->params=array(':id'=>$this->id,':country_code'=>$this->country_code,':state_code'=>$this->state_code,':zip_from'=>$this->zip_from,':zip_to'=>$this->zip_to); 			
			
			if (Tbl_TaxRule::model()->count($criteria)) {
				$this->addError('country_code',Yii::t('global','ERROR_EXIST'));
				$this->addError('state_code',Yii::t('global','ERROR_EXIST'));
				$this->addError('zip_from',Yii::t('global','ERROR_EXIST'));
				$this->addError('zip_to',Yii::t('global','ERROR_EXIST'));
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
		$current_id_user = (int)Yii::app()->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_TaxRule::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_TaxRule;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->name = $this->name;
		$model->country_code = $this->country_code;
		$model->state_code = $this->state_code;
		$model->zip_from = $this->zip_from;		
		$model->zip_to = $this->zip_to;		
		$model->active = $this->active;		
		$model->id_user_modified = $current_id_user;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
