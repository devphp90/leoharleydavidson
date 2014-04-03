<?php

/**
 * This is the model class for table "product_variant".
 *
 * The followings are the available columns in table 'product_variant':
 * @property string $id
 * @property integer $id_product
 * @property integer $price_type
 * @property string $price
 * @property string $qty
 * @property string $notify_qty
 * @property string $weight
 * @property integer $active
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_ProductVariant extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductVariant the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'product_variant';
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
			array('id_product', 'required'),
			array('id_product, active', 'numerical', 'integerOnly'=>true),
			array('cost_price, price', 'length', 'max'=>13),
			array('qty, notify_qty, weight, id_user_created, id_user_modified', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_product, cost_price, price, qty, notify_qty, weight, active, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
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
		return array(
			'tbl_product_variant_option' => array(self::HAS_MANY, 'Tbl_ProductVariantOption', 'id_product_variant'),	
			'tbl_product_variant_image' => array(self::HAS_MANY, 'Tbl_ProductVariantImage', 'id_product_variant'),	
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			/*
			'id' => 'ID',
			'id_product' => 'Id Product',
			'cost_price' => 'Cost Price',
			'price' => 'Price',
			'qty' => 'Qty',
			'notify_qty' => 'Notify Qty',
			'weight' => 'Weight',
			'active' => 'Active',
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
		$criteria->compare('id_product',$this->id_product);
		$criteria->compare('cost_price',$this->cost_price,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('qty',$this->qty,true);
		$criteria->compare('notify_qty',$this->notify_qty,true);
		$criteria->compare('weight',$this->weight,true);
		$criteria->compare('active',$this->active);
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