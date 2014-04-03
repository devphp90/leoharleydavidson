<?php
class ProductsVariantGroupOptionForm extends CFormModel
{
	// database fields
	public $id=0; //product variant group option id
	public $id_product_variant_group=0;
	//public $sku;
	public $swatch_type=0; // 0 = one color, 1 = two colors, 2 = three colors, 3 = file
	public $color;
	public $color2;
	public $color3;
	public $filename;
	public $old_filename;
	public $sort_order=0;	
	public $product_variant_group_option_description=array();

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
		// get variant group input type
		if ($pvg = Tbl_ProductVariantGroup::model()->findByPk($this->id_product_variant_group)) {	
			/*if (empty($this->sku)) {
				$this->addError('sku',Yii::t('global','ERROR_EMPTY'));
			} else if (Tbl_ProductVariantGroupOption::model()->count('id!=:id AND id_product_variant_group=:id_product_variant_group AND sku=:sku',array(':id'=>$this->id,':id_product_variant_group'=>$this->id_product_variant_group,':sku'=>$this->sku))) {
				$this->addError('sku',Yii::t('global','ERROR_IN_USE',array('{name}'=>$this->sku)));
			}	*/
			
			// if swatch
			if ($pvg->input_type == 2) {
				switch ($this->swatch_type) {
					// 1 color
					case 0:
						if (empty($this->color)) {
							$this->addError('color',Yii::t('global','ERROR_EMPTY'));
						} 
					 	break;
					// 2 color
					case 1:
						if (empty($this->color)) {
							$this->addError('color',Yii::t('global','ERROR_EMPTY'));
						} 
						
						if (empty($this->color2)) {
							$this->addError('color2',Yii::t('global','ERROR_EMPTY'));
						}				
						break;
					// 3 color
					case 2:
						if (empty($this->color)) {
							$this->addError('color',Yii::t('global','ERROR_EMPTY'));
						} 
						
						if (empty($this->color2)) {
							$this->addError('color2',Yii::t('global','ERROR_EMPTY'));
						}
						
						if (empty($this->color3)) {
							$this->addError('color3',Yii::t('global','ERROR_EMPTY'));
						} 				
						break;
					// filename
					case 3:
						if (empty($this->filename) && empty($this->old_filename)) {
							$this->addError('filename',Yii::t('global','ERROR_NO_FILE_SELECTED'));
						} 						
						break;
				}
			}
			
			// check description values for each languages		
			$columns = Html::getColumnsMaxLength(Tbl_ProductVariantGroupOptionDescription::tableName());
			
			foreach (Tbl_Language::model()->active()->findAll() as $value) {		
			
				// name is required
				if (empty($this->product_variant_group_option_description[$value->code]['name'])) {
					$this->addError('product_variant_group_option_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
				} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
					$this->addError('product_variant_group_option_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);
				}
			}			
		
			return $this->hasErrors() ? false:true;
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));		
		}
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)$app->user->getId();
		$image_base_path = $app->params['product_images_base_path'].'swatch/';
		
		// edit or new
		if ($this->id) {
			$model = Tbl_ProductVariantGroupOption::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_ProductVariantGroupOption;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_product_variant_group=:id_product_variant_group'; 
			$criteria->order='sort_order DESC';				
			$criteria->params=array(':id_product_variant_group'=>$this->id_product_variant_group); 				
			$model->sort_order = (Tbl_ProductVariantGroupOption::model()->find($criteria)->sort_order)+1;			
			
		}		
		
		$model->id_product_variant_group = $this->id_product_variant_group;
		//$model->sku = $this->sku;	
		$model->swatch_type = $this->swatch_type;
		$model->color = $this->color;
		$model->color2 = $this->color2;
		$model->color3 = $this->color3;
		//$model->filename = !empty($this->filename) ? $this->filename:$this->old_filename;		
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {		
			if (!empty($this->filename)) {
				$new_filename = md5($model->id.time().$this->filename).'.jpg';
				
				if (is_file($image_base_path.$this->filename)) {
					rename($image_base_path.$this->filename,$image_base_path.$new_filename);
				}
				
				$model->filename = $new_filename;
				
				if (!empty($this->old_filename) && is_file($image_base_path.$this->old_filename)) {
					@unlink($image_base_path.$this->old_filename);
				}
			} else { 
				$model->filename = $this->old_filename;
			}
			
			$model->save();
		
			foreach ($this->product_variant_group_option_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product_variant_group_option=:id_product_variant_group_option AND language_code=:language_code'; 
				$criteria->params=array(':id_product_variant_group_option'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_ProductVariantGroupOptionDescription::model()->find($criteria)) {
					$model_description = new Tbl_ProductVariantGroupOptionDescription;				
					$model_description->id_product_variant_group_option = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = $value['name'];
				if (!$model_description->save()) {
					if (!$this->id) { 
						$model->delete();
					
						if (!empty($this->filename) && is_file($image_base_path.$this->filename)) {
							@unlink($image_base_path.$this->old_filename);
						}
					}
					
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
