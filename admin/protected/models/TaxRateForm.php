<?php
class TaxRateForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $rate=0;	

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
		if (!is_numeric($this->rate)) {
			$this->addError('rate',Yii::t('global','NOT_NUMERIC'));
		}

		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$current_id_user = Yii::app()->user->getId();
		
		// edit
		$model = Tbl_TaxRuleRate::model()->findByPk($this->id);	
		
		$old_rate = $model->rate;
		$model->rate = $this->rate;
		$model->id_user_modified = $current_id_user;
			
				
		
		// try saving
		if (!$model->save()) {	
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}else{
			// check to update exception if same rate of old rate
			$criteria=new CDbCriteria; 
			$criteria->condition='id_tax_rule_rate=:id_tax_rule_rate AND rate=:old_rate'; 
			$criteria->params=array(':id_tax_rule_rate'=>$this->id,':old_rate'=>$old_rate); 
			
			foreach (Tbl_TaxRuleExceptionRate::model()->findAll($criteria) as $model_2) {
				$model_2->rate = $this->rate;	
				if (!$model_2->save()) {	
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}	
		}
		
		$this->id = $model->id;	
		return true;
	}
}
