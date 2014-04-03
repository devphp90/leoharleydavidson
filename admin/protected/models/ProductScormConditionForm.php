<?php
class ProductScormConditionForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_scorm_certificate_product=0;
	public $id_custom_fields=0;
	public $type=0;
	public $id_custom_fields_option=0;
	public $score_from=0;
	public $score_to=0;
	

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
		if (!$this->id_custom_fields) {
			$this->addError('id_custom_fields',Yii::t('global','ERROR_EMPTY'));
		}elseif ($this->id_custom_fields == -1) {
			if (!$this->score_from) {
				$this->addError('score_from',Yii::t('global','ERROR_EMPTY'));
			}
			if (!$this->score_to) {
				$this->addError('score_to',Yii::t('global','ERROR_EMPTY'));
			}
			
			if($this->score_from && $this->score_to){
				$criteria=new CDbCriteria; 
				$criteria->condition='id_scorm_certificate_product=:id_scorm_certificate_product AND id_custom_fields=:id_custom_fields AND id_custom_fields_option=:id_custom_fields_option AND id<>:id'; 
				$criteria->params=array(':id_scorm_certificate_product'=>$this->id_scorm_certificate_product,':id_custom_fields'=>$this->id_custom_fields,':id_custom_fields_option'=>$this->id_custom_fields_option,':id'=>$this->id); 	
				if (Tbl_ScormCertificateCondition::model()->find($criteria)) {
					$this->addError('id_custom_fields',Yii::t('global','ERROR_EXIST'));
				}
			}
			
			
		}else{
			if (!$this->id_custom_fields_option && $this->id_custom_fields != -1) {
				$this->addError('id_custom_fields_option',Yii::t('global','ERROR_EMPTY'));
			}

			$criteria=new CDbCriteria; 
			$criteria->condition='id_scorm_certificate_product=:id_scorm_certificate_product AND id_custom_fields=:id_custom_fields AND id_custom_fields_option=:id_custom_fields_option AND id<>:id'; 
			$criteria->params=array(':id_scorm_certificate_product'=>$this->id_scorm_certificate_product,':id_custom_fields'=>$this->id_custom_fields,':id_custom_fields_option'=>$this->id_custom_fields_option,':id'=>$this->id); 	
			if (Tbl_ScormCertificateCondition::model()->find($criteria)) {
				$this->addError('id_custom_fields','');
				$this->addError('id_custom_fields_option',Yii::t('global','ERROR_EXIST'));
			}
		}

		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		
		// edit or new
		if ($this->id) {
			$model = Tbl_ScormCertificateCondition::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ScormCertificateCondition;					
		}		
		
		$model->id_scorm_certificate_product = $this->id_scorm_certificate_product;
		$model->id_custom_fields = $this->id_custom_fields;
		$model->id_custom_fields_option = $this->id_custom_fields==-1?0:$this->id_custom_fields_option;
		$model->score_from = $this->id_custom_fields==-1?$this->score_from:0;
		$model->score_to = $this->id_custom_fields==-1?$this->score_to:0;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));		
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
