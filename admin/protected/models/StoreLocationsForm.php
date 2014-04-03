<?php
class StoreLocationsForm extends CFormModel
{
	// database fields
	public $id=0;
	public $hide_address=0; 
	public $name;
	public $address;
	public $city;
	public $state_code;
	public $zip;
	public $country_code;
	public $lat;
	public $lng;
	public $telephone;
	public $fax;
	public $email;
	public $url;
	public $image;
	public $image_old;
	public $open_mon=0;
	public $open_mon_start_time;
	public $open_mon_end_time;
	public $open_tue=0;
	public $open_tue_start_time;
	public $open_tue_end_time;	
	public $open_wed=0;
	public $open_wed_start_time;
	public $open_wed_end_time;	
	public $open_thu=0;
	public $open_thu_start_time;
	public $open_thu_end_time;	
	public $open_fri=0;
	public $open_fri_start_time;
	public $open_fri_end_time;	
	public $open_sat=0;
	public $open_sat_start_time;
	public $open_sat_end_time;	
	public $open_sun=0;
	public $open_sun_start_time;
	public $open_sun_end_time;	
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
		$current_date = date('Y-m-d');
	
		if (empty($this->name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));
		} 
		

		if (empty($this->address)) {
			$this->addError('address',Yii::t('global','ERROR_EMPTY'));
		} 
		
		if (empty($this->city)) {
			$this->addError('city',Yii::t('global','ERROR_EMPTY'));
		} 
		
		/*if (empty($this->state_code)) {
			$this->addError('state_code',Yii::t('global','ERROR_EMPTY'));
		} 

		if (empty($this->zip)) {
			$this->addError('zip',Yii::t('global','ERROR_EMPTY'));
		} */
		
		if (empty($this->country_code)) {
			$this->addError('country_code',Yii::t('global','ERROR_EMPTY'));
		} 
		
		if (empty($this->lat)) {
			$this->addError('lat',Yii::t('global','ERROR_EMPTY'));
		} 
		
		if (empty($this->lng)) {
			$this->addError('lng',Yii::t('global','ERROR_EMPTY'));
		} 
		
		if ($this->open_mon) {
			if (empty($this->open_mon_start_time)) {
				$this->addError('open_mon_start_time',Yii::t('global','ERROR_EMPTY'));
			} 
			
			if (empty($this->open_mon_end_time)) {
				$this->addError('open_mon_end_time',Yii::t('global','ERROR_EMPTY'));
			} 			
			
			if ($this->open_mon_start_time && $this->open_mon_end_time && strtotime($current_date.' '.$this->open_mon_start_time) >= strtotime($current_date.' '.$this->open_mon_end_time)) $this->addError('open_mon_start_time',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->open_mon_end_time)));
		}
		
		if ($this->open_tue) {
			if (empty($this->open_tue_start_time)) {
				$this->addError('open_tue_start_time',Yii::t('global','ERROR_EMPTY'));
			} 
			
			if (empty($this->open_tue_end_time)) {
				$this->addError('open_tue_end_time',Yii::t('global','ERROR_EMPTY'));
			} 			
			
			if ($this->open_tue_start_time && $this->open_tue_end_time && strtotime($current_date.' '.$this->open_tue_start_time) >= strtotime($current_date.' '.$this->open_tue_end_time)) $this->addError('open_tue_start_time',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->open_tue_end_time)));
		}		
		
		if ($this->open_wed) {
			if (empty($this->open_wed_start_time)) {
				$this->addError('open_wed_start_time',Yii::t('global','ERROR_EMPTY'));
			} 
			
			if (empty($this->open_wed_end_time)) {
				$this->addError('open_wed_end_time',Yii::t('global','ERROR_EMPTY'));
			} 			
			
			if ($this->open_wed_start_time && $this->open_wed_end_time && strtotime($current_date.' '.$this->open_wed_start_time) >= strtotime($current_date.' '.$this->open_wed_end_time)) $this->addError('open_wed_start_time',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->open_wed_end_time)));
		}		
		
		if ($this->open_thu) {
			if (empty($this->open_thu_start_time)) {
				$this->addError('open_thu_start_time',Yii::t('global','ERROR_EMPTY'));
			} 
			
			if (empty($this->open_thu_end_time)) {
				$this->addError('open_thu_end_time',Yii::t('global','ERROR_EMPTY'));
			} 			
			
			if ($this->open_thu_start_time && $this->open_thu_end_time && strtotime($current_date.' '.$this->open_thu_start_time) >= strtotime($current_date.' '.$this->open_thu_end_time)) $this->addError('open_thu_start_time',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->open_thu_end_time)));
		}		
		
		if ($this->open_fri) {
			if (empty($this->open_fri_start_time)) {
				$this->addError('open_fri_start_time',Yii::t('global','ERROR_EMPTY'));
			} 
			
			if (empty($this->open_fri_end_time)) {
				$this->addError('open_fri_end_time',Yii::t('global','ERROR_EMPTY'));
			} 			
			
			if ($this->open_fri_start_time && $this->open_fri_end_time && strtotime($current_date.' '.$this->open_fri_start_time) >= strtotime($current_date.' '.$this->open_fri_end_time)) $this->addError('open_fri_start_time',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->open_fri_end_time)));
		}				
		
		if ($this->open_sat) {
			if (empty($this->open_sat_start_time)) {
				$this->addError('open_sat_start_time',Yii::t('global','ERROR_EMPTY'));
			} 
			
			if (empty($this->open_sat_end_time)) {
				$this->addError('open_sat_end_time',Yii::t('global','ERROR_EMPTY'));
			} 			
			
			if ($this->open_sat_start_time && $this->open_sat_end_time && strtotime($current_date.' '.$this->open_sat_start_time) >= strtotime($current_date.' '.$this->open_sat_end_time)) $this->addError('open_sat_start_time',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->open_sat_end_time)));
		}				
		
		if ($this->open_sun) {
			if (empty($this->open_sun_start_time)) {
				$this->addError('open_sun_start_time',Yii::t('global','ERROR_EMPTY'));
			} 
			
			if (empty($this->open_sun_end_time)) {
				$this->addError('open_sun_end_time',Yii::t('global','ERROR_EMPTY'));
			} 			
			
			if ($this->open_sun_start_time && $this->open_sun_end_time && strtotime($current_date.' '.$this->open_sun_start_time) >= strtotime($current_date.' '.$this->open_sun_end_time)) $this->addError('open_sun_start_time',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->open_sun_end_time)));
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
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// edit or new
		if ($this->id) {
			$model = Tbl_StoreLocations::model()->findByPk($this->id);				
		} else {
			$model = new Tbl_StoreLocations;	
		}		
		
		$model->hide_address = $this->hide_address;
		$model->name = $this->name;
		$model->address = $this->address;
		$model->city = $this->city;
		$model->state_code = $this->state_code;
		$model->zip = $this->zip;
		$model->country_code = $this->country_code;
		$model->lat = $this->lat;
		$model->lng = $this->lng;
		$model->telephone = $this->telephone;
		$model->fax = $this->fax;
		$model->email = $this->email;
		$model->url = $this->url;
		
		if ($this->image) {
			if ($model->image && is_file(Yii::app()->params['root_url'].'images/stores/'.$model->image)) @unlink(Yii::app()->params['root_url'].'images/stores/'.$model->image);
			
			$model->image = $this->image;
		} 
		
		$model->open_mon = $this->open_mon;
		$model->open_mon_start_time = $this->open_mon_start_time;
		$model->open_mon_end_time = $this->open_mon_end_time;
		$model->open_tue = $this->open_tue;
		$model->open_tue_start_time = $this->open_tue_start_time;
		$model->open_tue_end_time = $this->open_tue_end_time;	
		$model->open_wed = $this->open_wed;
		$model->open_wed_start_time = $this->open_wed_start_time;
		$model->open_wed_end_time = $this->open_wed_end_time;	
		$model->open_thu = $this->open_thu;
		$model->open_thu_start_time = $this->open_thu_start_time;
		$model->open_thu_end_time = $this->open_thu_end_time;	
		$model->open_fri = $this->open_fri;
		$model->open_fri_start_time = $this->open_fri_start_time;
		$model->open_fri_end_time = $this->open_fri_end_time;	
		$model->open_sat = $this->open_sat;
		$model->open_sat_start_time = $this->open_sat_start_time;
		$model->open_sat_end_time = $this->open_sat_end_time;	
		$model->open_sun = $this->open_sun;
		$model->open_sun_start_time = $this->open_sun_start_time;
		$model->open_sun_end_time = $this->open_sun_end_time;	
		$model->active = $this->active;

		if (!$model->save()) {		
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}

		$this->id = $model->id;

		return true;
	}
}
