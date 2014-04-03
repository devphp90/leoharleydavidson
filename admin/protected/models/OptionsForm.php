<?php
class OptionsForm extends CFormModel
{
	// database fields
	public $id=0; //product variant group id
	public $sku;
	public $maxlength=20;
	public $cost_price;
	public $price_type=0; // 0 = fixed, 1 = percentage
	public $price;
	public $special_price;
	public $special_price_from_date;
	public $special_price_to_date;
	public $track_inventory=0;
	public $qty=1;
	public $out_of_stock=0;
	public $notify=0;
	public $notify_qty=0;
	public $allow_backorders=0;
	public $weight=0;
	public $length=0;
	public $width=0;
	public $height=0;
	public $extra_care=0;
	public $use_shipping_price=0;	
	public $taxable=0;
	public $id_tax_group=0;
	public $in_stock=0;
	public $active=1;	
	public $options_description=array();
	public $id_options_group=0;
	
	public $input_type=0;

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
		// check description values for each languages		
		$columns = Html::getColumnsMaxLength(Tbl_OptionsDescription::tableName());
		
		foreach (Tbl_Language::model()->active()->findAll() as $value) {		
		
			// name is required
			$name = $this->options_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('options_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('options_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
			}
			
			// description is required
			$description = $this->options_description[$value->code]['description'];
			if (!empty($description) && isset($columns['description']) && strlen($description) > $columns['description']) {
				$this->addError('options_description['.$value->code.'][description]',Yii::t('global','ERROR_MAXLENGTH').$columns['description']);	
			}
		}
		if (!$this->use_shipping_price && $this->input_type < 5 && ($this->weight==0) && $this->id) {
			$this->addError('weight',Yii::t('global','ERROR_EMPTY'));
		}
		
		if (!empty($this->cost_price) && !is_numeric($this->cost_price)) {
			$this->addError('cost_price',Yii::t('global','NOT_NUMERIC'));
		}		
		
		if (!empty($this->price) && !is_numeric($this->price)) {
			$this->addError('price',Yii::t('global','NOT_NUMERIC'));
		}
		
		if (!empty($this->special_price) and $this->special_price != 0.00) {
			if (empty($this->special_price_from_date)) {
				$this->addError('special_price_from_date',Yii::t('global','ERROR_EMPTY',array('{name}'=>$this->special_price_from_date)));
			} 
			if (empty($this->special_price_to_date)) {
				$this->addError('special_price_to_date',Yii::t('global','ERROR_EMPTY',array('{name}'=>$this->special_price_to_date)));
			}
		}
		if (!empty($this->special_price) and $this->special_price>$this->price) {
			$this->addError('special_price',Yii::t('global','ERROR_NOT_GREATER_THEN',array('{name}'=>$this->special_price)).$this->price);
		}	
	
		if (!empty($this->special_price_from_date) && !empty($this->special_price_to_date) && strtotime($this->special_price_from_date) >= strtotime($this->special_price_to_date)) {
			$this->addError('special_price_from_date',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->special_price_to_date)));
		}			
		
		if (!empty($this->sku) && Tbl_Options::model()->count('id!=:id AND sku=:sku',array(':id'=>$this->id,':sku'=>$this->sku))) {
			$this->addError('sku',Yii::t('global','ERROR_IN_USE',array('{name}'=>$this->sku)));
		}		
		
		/*if(!$this->use_shipping_price && ($this->weight==0)){
			$this->addError('weight',Yii::t('global','ERROR_EMPTY'));
		}*/			
		
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
			$model = Tbl_Options::model()->findByPk($this->id);	
			
		} else {
			$model = new Tbl_Options;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;						
		}
		$model->id_options_group = $this->id_options_group;
		$model->sku = $this->sku;		
		$model->cost_price = $this->cost_price;
		$model->price_type = $this->price_type;
		$model->price = $this->price;
		$model->special_price = $this->special_price;
		$model->special_price_from_date = $this->special_price_from_date;
		$model->special_price_to_date = $this->special_price_to_date;
		$model->track_inventory = $this->track_inventory;
		$model->qty = $this->qty;
		$model->out_of_stock = $this->out_of_stock;
		$model->notify = $this->notify;
		$model->notify_qty = $this->notify_qty;
		$model->allow_backorders = $this->allow_backorders;
		$model->weight = $this->weight;
		$model->length = $this->length;
		$model->width = $this->width;
		$model->height = $this->height;
		$model->use_shipping_price = $this->use_shipping_price;
		$model->extra_care = ($this->use_shipping_price)?0:$this->extra_care;
		$model->taxable = $this->taxable;
		$model->id_tax_group = $this->id_tax_group;
		$model->in_stock = $this->in_stock;
		$model->active = $this->active;
		
		$model->id_user_modified = $current_id_user;
		
		if ($model->save()) {	
			foreach ($this->options_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_options=:id_options AND language_code=:language_code'; 
				$criteria->params=array(':id_options'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_OptionsDescription::model()->find($criteria)) {
					$model_description = new Tbl_OptionsDescription;				
					$model_description->id_options = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
				$model_description->description = $value['description'];
				if (!$model_description->save()) {
					if (!$this->id) $model->delete();
					throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
				}
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;
		
		return true;
	}
}
