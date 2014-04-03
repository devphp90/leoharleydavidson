<?php
class ProductsAddBundledProductsTemplateForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_product=0;
	public $name; 

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
		$columns = Html::getColumnsMaxLength(Tbl_TplProductBundledProductCategory::tableName());	
		
		$criteria=new CDbCriteria; 
		$criteria->condition='name=:name'; 
		$criteria->params=array(':name'=>$this->name); 					
		
		// name is required
		$name = $this->name;
		if (empty($name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
			$this->addError('name',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
		} else if (Tbl_TplProductBundledProductCategory::model()->count($criteria)) {
			$this->addError('name',Yii::t('global','ERROR_EXIST'));	
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
		
		$model = new Tbl_TplProductBundledProductCategory;	
		$model->id_user_created = $current_id_user;	
		$model->id_user_modified = $current_id_user;		
		$model->date_created = $current_datetime;							
		$model->name = $this->name;
		
		if ($model->save()) {		
			$this->id = $model->id;
		
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product=:id_product'; 
			$criteria->params=array(':id_product'=>$this->id_product); 	
			$criteria->order='sort_order ASC';		
		
			foreach (Tbl_ProductBundledProductGroup::model()->findAll($criteria) as $row) {
				$model = new Tbl_TplProductBundledProductGroup;
				$model->id_tpl_product_bundled_product_category = $this->id;
				$model->input_type = $row->input_type;
				$model->required = $row->required;
				$model->sort_order = $row->sort_order;
				$model->id_user_created = $current_id_user;	
				$model->id_user_modified = $current_id_user;		
				$model->date_created = $current_datetime;						
				
				if ($model->save())	{
					foreach ($row->tbl_product_bundled_product_group_description as $row_description) {
						$model_description = new Tbl_TplProductBundledProductGroupDescription;
						$model_description->id_tpl_product_bundled_product_group = $model->id;
						$model_description->language_code = $row_description->language_code;
						$model_description->name = $row_description->name;
						
						if (!$model_description->save()) {
							$model->delete();
							throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));
						}
					}					
				} else { 
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
