<?php
class TaxExceptionsForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_tax_rule=0;
	public $id_tax_rule_rate=0;
	public $id_customer_type=0; 
	public $id_tax_group=0; 	
	public $tax_rate=array();
	

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
		if (Tbl_TaxRuleException::model()->count('id!=:id AND id_tax_rule=:id_tax_rule AND id_customer_type=:id_customer_type AND id_tax_group=:id_tax_group',array(':id'=>$this->id,':id_tax_rule'=>$this->id_tax_rule,':id_customer_type'=>$this->id_customer_type,':id_tax_group'=>$this->id_tax_group))) {
			$this->addError('id_tax_group',Yii::t('global','ERROR_EXIST'));
			$this->addError('id_customer_type',Yii::t('global','ERROR_EXIST'));
		}
		
			
		$criteria=new CDbCriteria; 
		$criteria->condition='id_tax_rule_exception=:id_tax_rule_exception'; 
		$criteria->params=array(':id_tax_rule_exception'=>$this->id); 							
		
		foreach ($this->tax_rate as $key=>$value) {																							
			if (!is_numeric($this->tax_rate[$key]['rate']) or $this->tax_rate[$key]['rate']=="") {
				$this->addError('tax_rate['.$key.'][rate]',Yii::t('global','NOT_NUMERIC'));
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
			$model = Tbl_TaxRuleException::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_TaxRuleException;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->id_tax_rule = $this->id_tax_rule;
		$model->id_customer_type = $this->id_customer_type;
		$model->id_tax_group = $this->id_tax_group;
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {		
			foreach ($this->tax_rate as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_rule_exception=:id_tax_rule_exception AND id_tax_rule_rate=:id_tax_rule_rate'; 
				$criteria->params=array(':id_tax_rule_exception'=>$model->id,':id_tax_rule_rate'=>$code); 					
				
				if (!$model_description = Tbl_TaxRuleExceptionRate::model()->find($criteria)) {
					$model_description = new Tbl_TaxRuleExceptionRate;				
					$model_description->id_tax_rule_exception = $model->id;
					$model_description->id_tax_rule_rate = $code;
					$model_description->rate = $value['rate'];
				}
				
				$model_description->rate = $value['rate'];		
				if (!$model_description->save()) {
					if (!$this->id) { $model->delete(); }
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
