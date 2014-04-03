<?php

/**
 * This is the model class for table "product".
 *
 * The followings are the available columns in table 'product':
 * @property string $date_modified
 */
class Tbl_ScormCertificateCondition extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Product the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
    public function defaultScope()
    {
        return array(
        );
    }		
	
    public function scopes()
    {
        return array();
    }		

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'scorm_certificate_condition';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			/*
			array('sku, id_user_created, id_user_modified, date_created', 'required'),
			array('taxable, track_inventory, in_stock, notify, allow_backorders, use_shipping_price, active', 'numerical', 'integerOnly'=>true),
			array('sku, brand, model', 'length', 'max'=>50),
			array('id_tax_group, qty, out_of_stock, notify_qty, weight, id_template_product_variant, id_template_product_option, id_user_created, id_user_modified', 'length', 'max'=>11),
			array('cost_price, price, shipping_price', 'length', 'max'=>13),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, sku, taxable, id_tax_group, brand, model, track_inventory, in_stock, qty, out_of_stock, notify, notify_qty, allow_backorders, cost_price, price, weight, use_shipping_price, shipping_price, date_displayed, active, id_template_product_variant, id_template_product_option, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
			*/
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			/*
			'id' => 'ID',
			'sku' => 'Sku',
			'taxable' => 'Taxable',
			'id_tax_group' => 'Id Tax Group',
			'brand' => 'Brand',
			'model' => 'Model',
			'track_inventory' => 'Track Inventory',
			'in_stock' => 'In Stock',
			'qty' => 'Qty',
			'out_of_stock' => 'Out Of Stock',
			'notify' => 'Notify',
			'notify_qty' => 'Notify Qty',
			'allow_backorders' => 'Allow Backorders',
			'cost_price' => 'Cost Price',
			'price' => 'Price',
			'weight' => 'Weight',
			'use_shipping_price' => 'Use Shipping Price',
			'shipping_price' => 'Shipping Price',
			'date_displayed' => 'Date Displayed',
			'active' => 'Active',
			'id_template_product_variant' => 'Id Template Product Variant',
			'id_template_product_option' => 'Id Template Product Option',
			'id_user_created' => 'Id User Created',
			'id_user_modified' => 'Id User Modified',
			'date_created' => 'Date Created',
			'date_modified' => 'Date Modified',
			*/
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		/*
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('sku',$this->sku,true);
		$criteria->compare('taxable',$this->taxable);
		$criteria->compare('id_tax_group',$this->id_tax_group,true);
		$criteria->compare('brand',$this->brand,true);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('track_inventory',$this->track_inventory);
		$criteria->compare('in_stock',$this->in_stock);
		$criteria->compare('qty',$this->qty,true);
		$criteria->compare('out_of_stock',$this->out_of_stock,true);
		$criteria->compare('notify',$this->notify);
		$criteria->compare('notify_qty',$this->notify_qty,true);
		$criteria->compare('allow_backorders',$this->allow_backorders);
		$criteria->compare('cost_price',$this->cost_price,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('weight',$this->weight,true);
		$criteria->compare('use_shipping_price',$this->use_shipping_price);
		$criteria->compare('shipping_price',$this->shipping_price,true);
		$criteria->compare('date_displayed',$this->date_displayed,true);
		$criteria->compare('active',$this->active);
		$criteria->compare('id_template_product_variant',$this->id_template_product_variant,true);
		$criteria->compare('id_template_product_option',$this->id_template_product_option,true);
		$criteria->compare('id_user_created',$this->id_user_created,true);
		$criteria->compare('id_user_modified',$this->id_user_modified,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}