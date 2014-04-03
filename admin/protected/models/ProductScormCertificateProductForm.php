<?php
class ProductScormCertificateProductForm extends CFormModel
{
	// database fields
	public $id=0; //id
	public $id_scorm_certificate=0;
	public $id_product_downloadable_files=0;
	public $additional_field = array();		
	
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
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		
		// edit or new
		if ($this->id) {
			$model = Tbl_ScormCertificateProduct::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ScormCertificateProduct;						
		}		
		
		$model->id_scorm_certificate = $this->id_scorm_certificate;
		$model->id_product_downloadable_files = $this->id_product_downloadable_files;

		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		// Additonnals Fields
		if ($this->id) {
			Tbl_ScormCertificateAdditionalFieldValue::model()->deleteAll('id_scorm_certificate_product=:id_scorm_certificate_product',array(':id_scorm_certificate_product'=>$this->id));
			foreach ($this->additional_field as $row => $value) {
				$model_additional = new Tbl_ScormCertificateAdditionalFieldValue;
				$model_additional->id_scorm_cetificate_additional_field = $row;
				$model_additional->id_scorm_certificate_product = $this->id;
				$model_additional->value = $value;
				if (!$model_additional->save()) {		
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}
		} 
		
		$this->id = $model->id;
		
		return true;
	}
}
