<?php
class ProductsForm extends CFormModel
{
	// database fields
	public $id=0; //product id
	public $product_type=0;
	public $sku; //SKU
	public $taxable=1; //taxable	
	public $id_tax_group=0;	
	public $brand; //brand name	
	public $brand_new_value; //brand name	
	public $model; //model name
	public $year;
	public $mileage;
	public $color;
	public $model_new_value; //model name
	public $cost_price=0;
	public $price=0;
	public $special_price=0;
	public $special_price_from_date;
	public $special_price_to_date;
	public $sell_price=0;
	public $discount_type=0;
	public $discount=0;
	public $use_product_current_price=1;
	public $use_product_special_price=0;
	public $user_defined_qty=0;
	public $max_qty=0;
	public $product_description=array();
	public $date_displayed;
	public $used=0;
	public $featured=0;
	public $active=0;
	public $display_in_catalog=1;	
	public $downloadable=0;
	public $min_qty=0;
	public $display_multiple_variants_form=0;

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
		if (empty($this->sku)) {
			$this->addError('sku',Yii::t('global','ERROR_EMPTY'));
		} else if (Tbl_Product::model()->count('id!=:id AND sku=:sku',array(':id'=>$this->id,':sku'=>$this->sku))) {
			$this->addError('sku',Yii::t('global','ERROR_IN_USE',array('{name}'=>$this->sku)));
		}
		
		if (!empty($this->special_price) and $this->special_price != 0.00) {
			if ($this->special_price >= $this->price) {
				$this->addError('special_price',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->price)));
			}
			
			if (empty($this->special_price_from_date)) {
				$this->addError('special_price_from_date',Yii::t('global','ERROR_EMPTY'));
			} 
			if (empty($this->special_price_to_date)) {
				$this->addError('special_price_to_date',Yii::t('global','ERROR_EMPTY'));
			}
			
			if (!empty($this->special_price_from_date) && !empty($this->special_price_to_date) && strtotime($this->special_price_from_date) >= strtotime($this->special_price_to_date)) {
				$this->addError('special_price_from_date',Yii::t('global','ERROR_MUST_BE_LOWER_THAN',array('{value}'=>$this->special_price_to_date)));
			}		
		}		
		
		if (!empty($this->model) and empty($this->brand)) {
			$this->addError('brand',Yii::t('global','ERROR_EMPTY'));
		}
		
		switch ($this->product_type) {
			// product
			case 0:
			// bundled products
			case 2:			

						
				break;
			// combo deals
			case 1:
				if (!empty($this->discount) && !is_numeric($this->discount)) {
					$this->addError('discount',Yii::t('global','NOT_NUMERIC'));
				} else if ($this->discount_type && $this->discount > 100) {
					$this->addError('discount',Yii::t('global','ERROR_INVALID'));
				}
				break;
		}
		


		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_ProductDescription::tableName());
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
			// name is required
			$name = $this->product_description[$value->code]['name'];
			if (empty($name)) {
				$this->addError('product_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
				$this->addError('product_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
			}						
			
			// description is required
			$description = $this->product_description[$value->code]['description'];
			/*if (empty($description)) {
				$this->addError('product_description['.$value->code.'][description]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['description']) && strlen($description) > $columns['description']) {
				$this->addError('product_description['.$value->code.'][description]',Yii::t('global','ERROR_MAXLENGTH').$columns['description']);	
			}*/		
			
			// meta_description is required
			$meta_description = $this->product_description[$value->code]['meta_description'];
			if (empty($this->product_description[$value->code]['meta_description'])) {
				$this->addError('product_description['.$value->code.'][meta_description]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['meta_description']) && strlen($meta_description) > $columns['meta_description']) {
				$this->addError('product_description['.$value->code.'][meta_description]',Yii::t('global','ERROR_MAXLENGTH').$columns['meta_description']);	
			}			
			
			// meta_keywords is required
			$meta_keywords = $this->product_description[$value->code]['meta_keywords'];
			if (empty($this->product_description[$value->code]['meta_keywords'])) {
				$this->addError('product_description['.$value->code.'][meta_keywords]',Yii::t('global','ERROR_EMPTY'));	
			} else if (isset($columns['meta_keywords']) && strlen($meta_keywords) > $columns['meta_keywords']) {
				$this->addError('product_description['.$value->code.'][meta_keywords]',Yii::t('global','ERROR_MAXLENGTH').$columns['meta_keywords']);	
			}	
						
			// alias is required
			$alias = mb_strtolower($this->product_description[$value->code]['alias'],'utf-8');
			$this->product_description[$value->code]['alias'] = $alias;														
			if (empty($alias)) {								
				$this->addError('product_description['.$value->code.'][alias]',Yii::t('global','ERROR_EMPTY'));	
				$this->product_description[$value->code]['error']=1;
			} else {
				if (isset($columns['alias']) && strlen($alias) > $columns['alias']) {
					$this->addError('product_description['.$value->code.'][alias]',Yii::t('global','ERROR_MAXLENGTH').$columns['alias']);	
				// check if alias is valid
				} else if (!preg_match('/[^0-9a-z-_\s]/',$alias)) {										
					// check if alias is already in use
					$criteria=new CDbCriteria; 
					$criteria->condition='language_code=:language_code AND alias=:alias'; 
					$criteria->params=array(':language_code'=>$value->code,':alias'=>$alias); 							
					
					$product_description = Tbl_ProductDescription::model()->find($criteria);
					
					// if in use, tell us by whom
					if (($product = Tbl_ProductDescription::model()->find($criteria)) && $product->id_product != $this->id) {
						$criteria->params=array(':language_code'=>Yii::app()->language,':alias'=>$alias);
													
						$product = Tbl_Product::model()->getDescription()->find($criteria);
						
						$this->addError('product_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_EXIST', array('{name}'=>$product->tbl_product_description[0]->name.' (SKU #: '.$product->sku.')')));				
					}				
				} else {
					$this->addError('product_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_INVALID'));					
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
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		// edit or new
		$price_updated = 0;
		if ($this->id) {
			$model = Tbl_Product::model()->findByPk($this->id);	
			
			switch ($this->product_type) {
				case 0:
					// if we changed cost price
					// if we changed the price
					// if we changed special price end date
					// if we changed special price
					if ($this->cost_price != $model->cost_price || $this->price != $model->price || $this->special_price_to_date != "0000-00-00 00:00:00" && $current_datetime < $this->special_price_to_date && $this->special_price_to_date != $model->special_price_to_date || $this->special_price_to_date != "0000-00-00 00:00:00" && $current_datetime < $this->special_price_to_date &&
					$this->special_price != $model->special_price) {
						if ($this->special_price_to_date != "0000-00-00 00:00:00" && $current_datetime < $this->special_price_to_date) $model->sell_price = $this->special_price;
						else $model->sell_price = $this->price;
						
						$price_updated = 1;
					}
					break;
				case 1:
					// if we changed the discount type or percent discount
					if ($this->discount_type != $model->discount_type || $this->discount != $model->discount) {
						// update combo cost_price, price and sell_price
						$sql = 'SELECT
						get_product_cost_price(:id_product,0) AS cost_price,
						get_combo_base_price(:id_product) AS price,
						get_product_current_price(:id_product,0,0) AS sell_price';									
						$command=$connection->createCommand($sql);
						$row = $command->queryRow(true, array(':id_product'=>$model->id));
						
						$model->cost_price = $row['cost_price'];
						$model->price = $row['price'];
						$model->sell_price = $row['sell_price'];
					}
					break;
				case 2:			
					// if we changed to use current price or not, or special price		
					if ($this->use_product_current_price != $model->use_product_current_price || $this->use_product_special_price != $model->use_product_special_price) {
						// update product bundled cost_price, price and sell_price
						$sql = 'SELECT
						get_product_cost_price(:id_product,0) AS cost_price,
						get_product_current_price(:id_product,0,0) AS sell_price';									
						$command=$connection->createCommand($sql);
						$row = $command->queryRow(true, array(':id_product'=>$model->id));
						
						$model->cost_price = $row['cost_price'];
						$model->price = $row['sell_price'];
						$model->sell_price = $row['sell_price'];	
					}
					break;
			}
		} else {
			$model = new Tbl_Product;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		
		$model->product_type = $this->product_type;
		$model->sku = $this->sku;
		switch ($this->product_type) {
			case 0:
				$model->cost_price = $this->cost_price;
				$model->special_price = $this->special_price;
				$model->special_price_from_date = $this->special_price_from_date;
				$model->special_price_to_date = $this->special_price_to_date;
				$model->price = $this->price;
				break;
			case 1:
				$model->discount_type = $this->discount_type;
				$model->discount = $this->discount;					
				break;
			case 2:
				$model->use_product_current_price = $this->use_product_current_price;
				$model->use_product_special_price = ($this->use_product_current_price ? $this->use_product_special_price:0);			
				break;
		}
		$model->user_defined_qty = $this->user_defined_qty;
		$model->max_qty = $this->max_qty;
		$model->brand = trim($this->brand);
		$model->model = trim($this->model);
		$model->year = trim($this->year);
		$model->mileage = trim($this->mileage);
		$model->color = trim($this->color);
		$model->taxable = $this->taxable;		
		$model->id_tax_group = $this->id_tax_group;
		$model->used = $this->used;
		$model->featured = $this->featured;
		$model->active = $this->active;	
		$model->display_in_catalog = $this->display_in_catalog;	
		$model->downloadable = $this->downloadable;		
		$model->min_qty = $this->min_qty;
		$model->display_multiple_variants_form = $this->display_multiple_variants_form;
		if(empty($this->date_displayed)){
			$model->date_displayed = date("Y-m-d H:i:00");
		}else{
			$model->date_displayed = $this->date_displayed;
		}
		
		// has variants		
		if ($model->id && Tbl_ProductVariantGroup::model()->count('id_product=:id_product',array(':id_product'=>$model->id)) && !$this->product_type) $model->has_variants = 1;
		else $model->has_variants = 0;
		
		$model->id_user_modified = $current_id_user;
		if ($model->save()) {		
			foreach ($this->product_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_product=:id_product AND language_code=:language_code'; 
				$criteria->params=array(':id_product'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_ProductDescription::model()->find($criteria)) {
					$model_description = new Tbl_ProductDescription;				
					$model_description->id_product = $model->id;
					$model_description->language_code = $code;
				}
				
				$model_description->name = trim($value['name']);
				$model_description->description = $value['description'];
				$model_description->short_desc = $value['short_desc'];
				$model_description->meta_description = $value['meta_description'];
				$model_description->meta_keywords = $value['meta_keywords'];				
				$model_description->alias = $value['alias'];			
				if (!$model_description->save()) {
					if (!$this->id) { $model->delete(); }
					throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
				}
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		// if price has been update, update combo and bundled products associated
		if ($price_updated) {
			switch ($this->product_type) {
				case 0:
					// update combo's associated
					$sql = 'UPDATE					
					product_combo
					INNER JOIN
					product 			
					ON
					(product_combo.id_product = product.id)
					SET
					product.cost_price = get_product_cost_price(product.id,0),
					product.price = get_combo_base_price(product.id),
					product.sell_price = get_product_current_price(product.id,0,0)			
					WHERE
					product_combo.id_combo_product = :id_product';	
							
					$command=$connection->createCommand($sql);
					$command->execute(array(':id_product'=>$model->id));
					
					// update bundle product
					$sql = 'UPDATE			
					product_bundled_product_group_product
					INNER JOIN 
					(product_bundled_product_group CROSS JOIN product)
					ON
					(product_bundled_product_group_product.id_product_bundled_product_group = product_bundled_product_group.id AND product_bundled_product_group.id_product = product.id)
					SET
					product.cost_price = get_product_cost_price(product.id,0),
					product.price = get_product_current_price(product.id,0,0),
					product.sell_price = get_product_current_price(product.id,0,0)		
					WHERE
					product_bundled_product_group_product.id_product = :id_product';
					
					$command=$connection->createCommand($sql);
					$command->execute(array(':id_product'=>$model->id));
					break;
			}
		}
		
		$this->id = $model->id;
		$this->product_type = $model->product_type;
		
		return true;
	}
}
