<?php

/**
 * This is the model class for table "combo_product".
 *
 * The followings are the available columns in table 'combo_product':
 * @property string $id
 * @property string $id_package
 * @property string $id_product
 * @property string $id_product_variant
 * @property string $qty
 */
class Tbl_SubscriptionContestPerson extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return PackageProduct the static model class
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
		return 'subscription_contest_person';
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
			array('id_package, id_product, id_product_variant', 'required'),
			array('id_package, id_product, id_product_variant, qty', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_package, id_product, id_product_variant, qty', 'safe', 'on'=>'search'),
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
			'id_package' => 'Id Package',
			'id_product' => 'Id Product',
			'id_product_variant' => 'Id Product Variant',
			'qty' => 'Qty',
			*/
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.
		/*
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('id_package',$this->id_package,true);
		$criteria->compare('id_product',$this->id_product,true);
		$criteria->compare('id_product_variant',$this->id_product_variant,true);
		$criteria->compare('qty',$this->qty,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}