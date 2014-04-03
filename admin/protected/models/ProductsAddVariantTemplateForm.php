<?php
class ProductsAddVariantTemplateForm extends CFormModel
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
		$columns = Html::getColumnsMaxLength(Tbl_TplProductVariantCategory::tableName());	
		
		$criteria=new CDbCriteria; 
		$criteria->condition='name=:name'; 
		$criteria->params=array(':name'=>$this->name); 					
		
		// name is required
		$name = $this->name;
		if (empty($name)) {
			$this->addError('name',Yii::t('global','ERROR_EMPTY'));	
		} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
			$this->addError('name',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
		} else if (Tbl_TplProductVariantCategory::model()->count($criteria)) {
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
		$image_base_path = Yii::app()->params['product_images_base_path'].'swatch/';
		
		$model = new Tbl_TplProductVariantCategory;	
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
		
			foreach (Tbl_ProductVariantGroup::model()->findAll($criteria) as $row) {
				$model = new Tbl_TplProductVariantGroup;
				$model->id_tpl_product_variant_category = $this->id;
				$model->input_type = $row->input_type;
				$model->sort_order = $row->sort_order;
				$model->id_user_created = $current_id_user;	
				$model->id_user_modified = $current_id_user;		
				$model->date_created = $current_datetime;							
				
				if ($model->save())	{
					foreach ($row->tbl_product_variant_group_description as $row_description) {
						$model_description = new Tbl_TplProductVariantGroupDescription;
						$model_description->id_tpl_product_variant_group = $model->id;
						$model_description->language_code = $row_description->language_code;
						$model_description->name = $row_description->name;
						$model_description->description = $row_description->description;
						
						if (!$model_description->save()) {
							throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
						}
					}
					
					foreach ($row->tbl_product_variant_group_option as $row_option) {
						$model_option = new Tbl_TplProductVariantGroupOption;
						$model_option->id_tpl_product_variant_group = $model->id;
						$model_option->swatch_type = $row_option->swatch_type;
						$model_option->color = $row_option->color;
						$model_option->color2 = $row_option->color2;
						$model_option->color3 = $row_option->color3;						
						$model_option->sort_order = $row_option->sort_order;
						$model_option->id_user_created = $current_id_user;	
						$model_option->id_user_modified = $current_id_user;		
						$model_option->date_created = $current_datetime;							
						
						if ($model_option->save()){
							if (!empty($row_option->filename)) {
								$new_filename = md5($model_option->id.time().$row_option->filename).'.jpg';
								
								if (is_file($image_base_path.$row_option->filename)) {
									copy($image_base_path.$row_option->filename,$image_base_path.$new_filename);
								}													
								
								$model_option->filename = $new_filename;								
								$model_option->save();								
							}
							
							foreach ($row_option->tbl_product_variant_group_option_description as $row_option_description) {
								$model_option_description = new Tbl_TplProductVariantGroupOptionDescription;	
								$model_option_description->id_tpl_product_variant_group_option = $model_option->id;
								$model_option_description->language_code = $row_option_description->language_code;
								$model_option_description->name = $row_option_description->name;
								
								if (!$model_option_description->save()) {
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
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
