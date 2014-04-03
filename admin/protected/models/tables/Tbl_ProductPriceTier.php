<?php

/**
 * This is the model class for table "product_price_tier".
 *
 * The followings are the available columns in table 'product_price_tier':
 * @property string $id
 * @property string $id_product
 * @property string $id_customer_type
 * @property string $qty
 * @property string $price
 */
class Tbl_ProductPriceTier extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductPriceTier the static model class
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
		return 'product_price_tier';
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
			array('id_product, id_customer_type, qty, price', 'required'),
			array('id_product, id_customer_type, qty', 'length', 'max'=>11),
			array('price', 'length', 'max'=>13),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_product, id_customer_type, qty, price', 'safe', 'on'=>'search'),
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
			'id_customer_type' => 'Id Customer Type',
			'qty' => 'Qty',
			'price' => 'Price',
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
		$criteria->compare('id_product',$this->id_product,true);
		$criteria->compare('id_customer_type',$this->id_customer_type,true);
		$criteria->compare('qty',$this->qty,true);
		$criteria->compare('price',$this->price,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}