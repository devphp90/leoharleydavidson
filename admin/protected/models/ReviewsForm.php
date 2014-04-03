<?php
class ReviewsForm extends CFormModel
{
	// database fields
	public $id=0;
	public $title;
	public $review;
	public $anonymous=0; 

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
		
		
		if (empty($this->title)) {
			$this->addError('title',Yii::t('global','ERROR_EMPTY'));
		}
		if (empty($this->review)) {
			$this->addError('review',Yii::t('global','ERROR_EMPTY'));
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
		
		
		$model = Tbl_ProductReview::model()->findByPk($this->id);			
		
		//$model->product_type = $this->product_type;
		$model->title = $this->title;
		$model->review = $this->review;
		$model->anonymous = $this->anonymous;
		$model->id_user_modified = $current_id_user;
		
		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
