<?php

/**
 * This is the model class for table "gift_certificate".
 *
 * The followings are the available columns in table 'gift_certificate':
 * @property string $id
 * @property string $sku
 * @property string $upc
 * @property string $start_date
 * @property string $end_date
 * @property string $price
 * @property string $price_value
 * @property integer $active
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_GiftCertificate extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return GiftCertificate the static model class
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
		return 'gift_certificate';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(/*
			array('start_date, end_date, price, active, id_user_created, id_user_modified, date_created, date_modified', 'required'),
			array('active', 'numerical', 'integerOnly'=>true),
			array('price', 'length', 'max'=>10),
			array('id_user_created, id_user_modified', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, sku, upc, start_date, end_date, price, price_value, active, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
		*/);
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
		return array(/*
			'id' => 'ID',
			'sku' => 'Sku',
			'upc' => 'Upc',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'price' => 'Price',
			'price_value' => 'Price Value',
			'active' => 'Active',
			'id_user_created' => 'Id User Created',
			'id_user_modified' => 'Id User Modified',
			'date_created' => 'Date Created',
			'date_modified' => 'Date Modified',
		*/);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('sku',$this->sku,true);
		$criteria->compare('upc',$this->upc,true);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('end_date',$this->end_date,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('price_value',$this->price_value,true);
		$criteria->compare('active',$this->active);
		$criteria->compare('id_user_created',$this->id_user_created,true);
		$criteria->compare('id_user_modified',$this->id_user_modified,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}