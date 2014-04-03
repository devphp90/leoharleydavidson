<?php
class CustomersCustomFieldsForm extends CFormModel
{
	// database fields
	public $id_customer=0;
	public $custom_fields=array();

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
		// get list of custom fields
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$sql = 'SELECT 
		custom_fields.id,
		custom_fields.type,
		custom_fields.required,
		custom_fields_description.name
		FROM 
		custom_fields 
		INNER JOIN
		custom_fields_description
		ON
		(custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code = :language_code)
		WHERE
		custom_fields.form = 0
		ORDER BY 
		custom_fields.sort_order ASC';
		$command = $connection->createCommand($sql);
		
		$sql = 'SELECT 
		custom_fields_option.id,
		custom_fields_option.add_extra,
		custom_fields_option.extra_required,
		custom_fields_option_description.name
		FROM
		custom_fields_option
		INNER JOIN 
		custom_fields_option_description
		ON
		(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code = :language_code)
		WHERE
		custom_fields_option.id_custom_fields = :id_custom_fields
		ORDER BY
		custom_fields_option.sort_order ASC';
		$command_option = $connection->createCommand($sql);
		
		$custom_fields=array();
		foreach ($command->queryAll(true, array(':language_code'=>Yii::app()->language)) as $row) {
			$custom_fields[$row['id']] = array(
				'id' => $row['id'],
				'type' => $row['type'],
				'required' => $row['required'],
				'name' => $row['name'],
			);
			
			// check for required values 
			if ($row['required'] && empty($this->custom_fields[$row['id']]['value'])) $this->addError('custom_fields['.$row['id'].'][value]',Yii::t('global','ERROR_EMPTY'));
			// if extra, check if empty
			else if ($row['type'] == 1 || $row['type'] == 2) { 		
				foreach ($command_option->queryAll(true, array(':language_code'=>Yii::app()->language,':id_custom_fields'=>$row['id'])) as $row_option) {
					if ($row['type'] == 2 && $this->custom_fields[$row['id']]['value'] == $row_option['id'] && $row_option['add_extra'] && $row_option['extra_required'] && empty($this->custom_fields[$row['id']]['extra'])) {
						$this->addError('custom_fields['.$row['id'].'][extra]',Yii::t('global','ERROR_EMPTY'));
					} else if (!empty($this->custom_fields[$row['id']]['options'][$row_option['id']]['value']) && $row_option['add_extra'] && $row_option['extra_required'] && empty($this->custom_fields[$row['id']]['options'][$row_option['id']]['extra'])) {
						$this->addError('custom_fields['.$row['id'].'][options]['.$row_option['id'].'][extra]',Yii::t('global','ERROR_EMPTY'));
					}
				}
			}
		}			
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		Tbl_CustomerCustomFieldsValue::model()->deleteAll('id_customer=:id_customer',array(':id_customer'=>$this->id_customer));
		
		if (sizeof($this->custom_fields)) {
			foreach ($this->custom_fields as $id_custom_fields => $row) {
				if (empty($row['options']) && !empty($row['value'])) {
					$model = new Tbl_CustomerCustomFieldsValue;
					$model->id_customer = $this->id_customer;
					$model->id_custom_fields = $id_custom_fields;
					$model->id_custom_fields_option = $row['value'];
					//$model->value = $row['extra'];
					$model->value = !empty($row['extra']) ? $row['extra']:$row['value'];	
								
					if (!$model->save()) {		
						throw new CException(Yii::t('global','ERROR_SAVING'));	
					}
				} else if (is_array($row['options']) && sizeof($row['options'])){
					foreach ($row['options'] as $id_custom_fields_option => $row_option) {
						if (!empty($row_option['value']) or !empty($row_option['extra'])) {
							$model = new Tbl_CustomerCustomFieldsValue;
							$model->id_customer = $this->id_customer;
							$model->id_custom_fields = $id_custom_fields;
							$model->id_custom_fields_option = $id_custom_fields_option;
							$model->value = $row_option['extra'];	
							
							
							if (!$model->save()) {		
								throw new CException(Yii::t('global','ERROR_SAVING'));	
							}						
						}
					}
				}//throw new CException('<pre>'.print_r($this->custom_fields,1).'</pre>'.'id_customer = '.$this->id_customer);
			}
		}
		
		return true;
	}
}
