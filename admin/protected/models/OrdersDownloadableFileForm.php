<?php
class OrdersDownloadableFileForm extends CFormModel
{
	// database fields
	public $id=0; //id
	public $no_days_expire=0;	
	public $no_downloads=0;		
	public $current_no_downloads=0;		
	
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
		$model = Tbl_OrdersItemProductDownloadableFiles::model()->findByPk($this->id);	
		$model->no_days_expire = $this->no_days_expire;
		$model->no_downloads = $this->no_downloads;
		$model->current_no_downloads = $this->current_no_downloads;
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}		
		
		return true;
	}
}
